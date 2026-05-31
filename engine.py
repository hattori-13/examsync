import mysql.connector
import json
import random

def generate_schedule():
    try:
        # 1. Connect to Database
        conn = mysql.connector.connect(
            host="127.0.0.1",
            user="root",
            password="",
            database="examsync_db"
        )
        cursor = conn.cursor(dictionary=True)

        # 2. Clear old schedules safely
        cursor.execute("DELETE FROM generated_schedules")
        cursor.execute("ALTER TABLE generated_schedules AUTO_INCREMENT = 1")
        conn.commit()

        # 3. Fetch Master Data
        cursor.execute("SELECT id FROM time_slots ORDER BY day_name, start_time")
        time_slots = cursor.fetchall()

        cursor.execute("SELECT id FROM rooms")
        rooms = cursor.fetchall()

        # UPDATED: We MUST fetch the 'program' column so we can differentiate BSIT 1-A from BSA 1-A
        cursor.execute("SELECT id, program, subject_code, year_level, section, proctor FROM exam_subjects")
        subjects = cursor.fetchall()

        # Shuffle the subjects to ensure a fair, random distribution of schedules
        random.shuffle(subjects) 

        # 4. Constraint Trackers
        proctor_busy = {}   # { "Proctor Name": [time_slot_id1, time_slot_id2] }
        student_group_busy = {}   # UPDATED: { "BSIT-1-A": [time_slot_id1, time_slot_id2] }
        room_busy = {}      # { room_id: [time_slot_id1] }

        schedules_to_insert = []
        unassigned = 0

        # 5. The Sync Engine Logic
        for sub in subjects:
            sub_id = sub['id']
            proctor = sub['proctor']
            
            # UPDATED: Combine Program, Year, and Section to track unique cohorts (e.g., "BSIT-1-A")
            student_group = f"{sub['program']}-{sub['year_level']}-{sub['section']}"

            assigned = False

            for ts in time_slots:
                ts_id = ts['id']

                # HARD CONSTRAINT 1: Is the PROCTOR already watching an exam at this exact time?
                if proctor in proctor_busy and ts_id in proctor_busy[proctor]:
                    continue
                
                # HARD CONSTRAINT 2: Is this specific STUDENT GROUP (Program+Year+Section) taking an exam right now?
                if student_group in student_group_busy and ts_id in student_group_busy[student_group]:
                    continue
                
                # If no conflict, find the first empty room for this time slot
                available_room = None
                for r in rooms:
                    r_id = r['id']
                    if r_id not in room_busy or ts_id not in room_busy[r_id]:
                        available_room = r_id
                        break
                
                # If we found an available room, lock it in!
                if available_room:
                    schedules_to_insert.append((sub_id, available_room, ts_id, 'Draft'))
                    
                    # Update trackers to prevent future overlaps
                    if proctor not in proctor_busy: proctor_busy[proctor] = []
                    proctor_busy[proctor].append(ts_id)
                    
                    if student_group not in student_group_busy: student_group_busy[student_group] = []
                    student_group_busy[student_group].append(ts_id)
                    
                    if available_room not in room_busy: room_busy[available_room] = []
                    room_busy[available_room].append(ts_id)
                    
                    assigned = True
                    break
            
            # If the loop finishes and no time/room was found, log it as an error
            if not assigned:
                unassigned += 1

        # 6. Bulk Insert the generated schedule back into MySQL
        if schedules_to_insert:
            sql = "INSERT INTO generated_schedules (exam_subject_id, room_id, time_slot_id, status) VALUES (%s, %s, %s, %s)"
            cursor.executemany(sql, schedules_to_insert)
            conn.commit()

        # 7. Return Success to PHP
        print(json.dumps({
            "status": "success", 
            "message": f"Schedule Built! {len(schedules_to_insert)} assigned, {unassigned} failed."
        }))

    except mysql.connector.Error as err:
        print(json.dumps({"status": "error", "message": f"Database Error: {err}"}))
    except Exception as e:
        print(json.dumps({"status": "error", "message": f"System Error: {str(e)}"}))
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()

if __name__ == "__main__":
    generate_schedule()