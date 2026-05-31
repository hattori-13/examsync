<?php
/**
 * EXAMSYNC - Public Viewer Portal
 * System Developer: Kert Bryan Dingcong
 */

require_once 'database/dbcon.php';

// =====================================================================
// API ENGINE: Handle AJAX requests for fetching schedules
// =====================================================================
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        // Fetch STUDENT Schedule
        if ($_GET['action'] === 'fetch_student') {
            $program = trim($_GET['program']);
            $year = trim($_GET['year']);
            $section = trim($_GET['section']);
            
            $query = "
                SELECT t.day_name, t.start_time, t.end_time, r.room_name, 
                       s.program, s.subject_code, s.proctor 
                FROM generated_schedules gs
                JOIN time_slots t ON gs.time_slot_id = t.id
                JOIN rooms r ON gs.room_id = r.id
                JOIN exam_subjects s ON gs.exam_subject_id = s.id
                WHERE s.program = ? AND s.year_level = ? AND s.section = ?
                ORDER BY t.day_name, t.start_time
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$program, $year, $section]);
            $results = $stmt->fetchAll();
            
            echo json_encode(['status' => 'success', 'data' => $results]);
            exit();
        }

        // Fetch TEACHER Schedule
        if ($_GET['action'] === 'fetch_teacher') {
            $teacher = trim($_GET['teacher_name']);
            
            // We fetch anywhere the teacher is listed as the Proctor (where they physically need to be)
            $query = "
                SELECT t.day_name, t.start_time, t.end_time, r.room_name, 
                       s.program, s.subject_code, s.year_level, s.section 
                FROM generated_schedules gs
                JOIN time_slots t ON gs.time_slot_id = t.id
                JOIN rooms r ON gs.room_id = r.id
                JOIN exam_subjects s ON gs.exam_subject_id = s.id
                WHERE s.proctor = ?
                ORDER BY t.day_name, t.start_time
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$teacher]);
            $results = $stmt->fetchAll();
            
            echo json_encode(['status' => 'success', 'data' => $results]);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error']);
        exit();
    }
}

// =====================================================================
// INITIAL PAGE LOAD: Fetch dropdown data
// =====================================================================
// Fetch Teachers
$teachersQuery = $pdo->query("SELECT proctor AS name FROM exam_subjects UNION SELECT adviser AS name FROM exam_subjects");
$teacherList = $teachersQuery->fetchAll(PDO::FETCH_COLUMN);
$teacherList = array_filter($teacherList);
sort($teacherList);

// Fetch Programs dynamically from the database
$programsQuery = $pdo->query("SELECT DISTINCT program FROM exam_subjects ORDER BY program");
$programList = $programsQuery->fetchAll(PDO::FETCH_COLUMN);
$programList = array_filter($programList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXAMSYNC - Public Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Soft gradient background */
            background: linear-gradient(135deg, #f0f4f8 0%, #dbeafe 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="antialiased text-gray-900" x-data="viewerApp()">

    <nav class="bg-white/60 backdrop-blur-xl border-b border-white/50 sticky top-0 z-50 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="font-bold text-xl tracking-tight text-gray-800">EXAMSYNC Portal</span>
            </div>
            <div>
                <a href="view.php" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition">VIEW OTHER PROGRAM &rarr;</a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div x-show="viewState === 'search'" x-transition class="bg-white/80 backdrop-blur-2xl rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-white p-8 md:p-12">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-gray-900 mb-3">Find Your Exam Schedule</h1>
                <p class="text-gray-500">Select your role below to check your designated rooms and times.</p>
            </div>

            <div class="flex bg-gray-100/80 p-1.5 rounded-2xl mb-10 max-w-sm mx-auto shadow-inner">
                <button @click="role = 'student'" :class="{'bg-white shadow-md text-blue-600 font-bold': role === 'student', 'text-gray-500 hover:text-gray-700': role !== 'student'}" class="flex-1 py-3 rounded-xl text-sm font-medium transition-all duration-300">
                    👨‍🎓 Student
                </button>
                <button @click="role = 'teacher'" :class="{'bg-white shadow-md text-blue-600 font-bold': role === 'teacher', 'text-gray-500 hover:text-gray-700': role !== 'teacher'}" class="flex-1 py-3 rounded-xl text-sm font-medium transition-all duration-300">
                    👨‍🏫 Teacher
                </button>
            </div>

            <div x-show="role === 'student'" x-transition>
                <form @submit.prevent="fetchSchedule('student')" class="space-y-5 max-w-sm mx-auto">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Program / Course</label>
                        <select x-model="studentProgram" required class="w-full bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                            <option value="" disabled selected>Select Program</option>
                            <?php foreach($programList as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog); ?>"><?php echo htmlspecialchars($prog); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Year Level</label>
                        <select x-model="studentYear" required class="w-full bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                            <option value="" disabled selected>Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Section</label>
                        <select x-model="studentSection" required class="w-full bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                            <option value="" disabled selected>Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl px-4 py-4 transition-all shadow-lg shadow-blue-500/30 active:scale-95">
                        View Schedule
                    </button>
                </form>
            </div>

            <div x-show="role === 'teacher'" style="display: none;" x-transition>
                <form @submit.prevent="fetchSchedule('teacher')" class="space-y-5 max-w-sm mx-auto">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Select Faculty Name</label>
                        <select x-model="teacherName" required class="w-full bg-white border border-gray-200 text-gray-900 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                            <option value="" disabled selected>Search Directory...</option>
                            <?php foreach($teacherList as $name): ?>
                                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full mt-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl px-4 py-4 transition-all shadow-lg active:scale-95">
                        View Proctoring Schedule
                    </button>
                </form>
            </div>

        </div>

        <div x-show="viewState === 'results'" style="display: none;" x-transition>
            
            <button @click="viewState = 'search'" class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-800 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Search
            </button>

            <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1" x-text="resultTitle"></h2>
                    <p class="text-sm text-gray-500" x-text="resultSubtitle"></p>
                </div>
                <div class="bg-blue-50 text-blue-700 font-bold px-4 py-2 rounded-xl text-lg" x-text="schedules.length + ' Exams'"></div>
            </div>

            <div x-show="schedules.length === 0" class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Schedule Found</h3>
                <p class="text-gray-500">The registrar has not finalized a schedule for this query yet, or it does not exist.</p>
            </div>

            <div class="space-y-4">
                <template x-for="exam in schedules" :key="exam.day_name + exam.start_time">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:shadow-md transition">
                        
                        <div class="flex items-center gap-4 md:w-1/3">
                            <div class="bg-blue-50 text-blue-600 font-bold text-xs uppercase tracking-wide px-3 py-1.5 rounded-lg text-center min-w-[70px]">
                                <span x-text="exam.day_name"></span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900" x-text="formatTime(exam.start_time) + ' - ' + formatTime(exam.end_time)"></div>
                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Exact Time
                                </div>
                            </div>
                        </div>

                        <div class="md:w-1/3">
                            <div class="text-lg font-bold text-gray-900">
                                <span class="text-blue-600 mr-1" x-text="exam.program"></span><span x-text="exam.subject_code"></span>
                            </div>
                            
                            <template x-if="role === 'student'">
                                <div class="text-sm text-gray-600 font-medium" x-text="'Proctor: ' + exam.proctor"></div>
                            </template>
                            <template x-if="role === 'teacher'">
                                <div class="text-sm text-gray-600 font-medium" x-text="'Yr ' + exam.year_level + ' - Sec ' + exam.section"></div>
                            </template>
                        </div>

                        <div class="md:w-1/4 text-right">
                            <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-100 text-indigo-800 px-4 py-2 rounded-xl">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <span class="font-bold text-lg" x-text="exam.room_name"></span>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

        </div>

    </main>

    <div x-show="loading" style="display: none;" class="fixed inset-0 bg-white/70 backdrop-blur-sm z-[100] flex items-center justify-center">
        <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="font-semibold text-gray-700">Accessing Database...</span>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('viewerApp', () => ({
                role: 'student', 
                viewState: 'search', 
                loading: false,
                
                // Form Inputs
                studentProgram: '',
                studentYear: '',
                studentSection: '',
                teacherName: '',
                
                // Result Data
                schedules: [],
                resultTitle: '',
                resultSubtitle: '',

                formatTime(timeString) {
                    if (!timeString) return '';
                    const [hourString, minute] = timeString.split(':');
                    const hour = parseInt(hourString, 10);
                    const ampm = hour >= 12 ? 'PM' : 'AM';
                    const formattedHour = hour % 12 || 12;
                    return `${formattedHour}:${minute} ${ampm}`;
                },

                async fetchSchedule(type) {
                    this.loading = true;
                    let url = '';

                    if (type === 'student') {
                        url = `view.php?action=fetch_student&program=${encodeURIComponent(this.studentProgram)}&year=${this.studentYear}&section=${this.studentSection}`;
                        this.resultTitle = `${this.studentProgram} (Yr ${this.studentYear} - Sec ${this.studentSection})`;
                        this.resultSubtitle = "Student Master Schedule";
                    } else if (type === 'teacher') {
                        url = `view.php?action=fetch_teacher&teacher_name=${encodeURIComponent(this.teacherName)}`;
                        this.resultTitle = this.teacherName;
                        this.resultSubtitle = "Faculty Proctoring Schedule";
                    }

                    try {
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            this.schedules = data.data;
                            this.viewState = 'results';
                        } else {
                            alert("Database connection error.");
                        }
                    } catch (error) {
                        alert("Network error. Please try again.");
                    } finally {
                        setTimeout(() => { this.loading = false; }, 400);
                    }
                }
            }));
        });
    </script>
</body>
</html>