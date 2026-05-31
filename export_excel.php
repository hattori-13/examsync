<?php
/**
 * EXAMSYNC - Tarpapel Excel Exporter (Legal Size / Yellow Cells / Program Included)
 * System Developer: Kert Bryan Dingcong
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized Access.");
}

require_once 'database/dbcon.php';

// Force the browser to output an Excel File
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=EXAMSYNC_Master_Tarpapel_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch Rooms & Time Slots
$rooms = $pdo->query("SELECT id, room_name FROM rooms ORDER BY room_name")->fetchAll();
$timeSlots = $pdo->query("SELECT id, day_name, start_time, end_time FROM time_slots ORDER BY day_name, start_time")->fetchAll();

$days = [];
foreach ($timeSlots as $ts) {
    $days[$ts['day_name']][] = $ts;
}

// Fetch the Generated Schedule (FIXED: Added s.program to the SELECT statement)
$query = "
    SELECT gs.time_slot_id, gs.room_id, 
           s.proctor, s.program, s.subject_code, s.year_level, s.section
    FROM generated_schedules gs
    JOIN exam_subjects s ON gs.exam_subject_id = s.id
";
$schedules = $pdo->query($query)->fetchAll();

// Map the data (FIXED: Prepended the Program to the Subject String)
$scheduleMap = [];
foreach ($schedules as $row) {
    $time_id = $row['time_slot_id'];
    $room_id = $row['room_id'];
    $scheduleMap[$time_id][$room_id] = [
        'subject' => htmlspecialchars($row['program']) . " " . htmlspecialchars($row['subject_code']) . " (Yr " . htmlspecialchars($row['year_level']) . " - Sec " . htmlspecialchars($row['section']) . ")",
        'proctor' => htmlspecialchars($row['proctor'])
    ];
}
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        /* Microsoft Office Specific Page Setup for Legal Size (8.5 x 14 inches) */
        @page {
            mso-page-orientation: landscape;
            size: 14in 8.5in; 
            margin: 0.5in 0.5in 0.5in 0.5in;
        }

        /* Tarpapel Grid Styling */
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 2px solid #000000; padding: 10px; text-align: center; vertical-align: middle; }
        
        .header-main { font-size: 24pt; font-weight: bold; background-color: #1e3a8a; color: #ffffff; padding: 20px; text-transform: uppercase; }
        .header-day { font-size: 18pt; font-weight: bold; background-color: #dbeafe; color: #1e3a8a; text-transform: uppercase; padding: 15px; }
        
        .room-col { font-weight: bold; background-color: #e2e8f0; color: #0f172a; width: 150px; font-size: 14pt; }
        .time-col { font-weight: bold; background-color: #f1f5f9; color: #334155; width: 200px; font-size: 14pt; }
        
        /* The Assigned Cell: We set the background to solid yellow here */
        .cell-assigned { background-color: #FFFF00; width: 200px; vertical-align: middle; border: 2px solid #000000; }
        .cell-empty { background-color: #ffffff; color: #94a3b8; font-size: 11pt; border: 1px solid #d1d5db; }
        
        /* Large fonts for readability from a distance */
        .subj-text { font-size: 16pt; font-weight: bold; color: #000000; margin-bottom: 6px; }
        .proc-text { font-size: 12pt; font-style: italic; color: #333333; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="<?php echo count($rooms) + 1; ?>" class="header-main">MASTER EXAM SCHEDULE</td>
        </tr>

        <?php foreach ($days as $dayName => $slots): ?>
            
            <tr>
                <td colspan="<?php echo count($rooms) + 1; ?>" class="header-day"><?php echo htmlspecialchars($dayName); ?></td>
            </tr>

            <tr>
                <td class="time-col" style="background-color: #94a3b8; color: white;">TIME SLOT</td>
                <?php foreach ($rooms as $room): ?>
                    <td class="room-col"><?php echo htmlspecialchars($room['room_name']); ?></td>
                <?php endforeach; ?>
            </tr>

            <?php foreach ($slots as $ts): ?>
                <tr>
                    <td class="time-col">
                        <?php echo date('g:i A', strtotime($ts['start_time'])) . '<br>to<br>' . date('g:i A', strtotime($ts['end_time'])); ?>
                    </td>
                    
                    <?php foreach ($rooms as $room): ?>
                        <?php 
                            $time_id = $ts['id'];
                            $room_id = $room['id'];
                            
                            // Apply the SOLID YELLOW class if assigned
                            if (isset($scheduleMap[$time_id][$room_id])) {
                                $data = $scheduleMap[$time_id][$room_id];
                                echo '<td class="cell-assigned">';
                                echo '<div class="subj-text">' . $data['subject'] . '</div>';
                                echo '<div class="proc-text">Proctor: ' . $data['proctor'] . '</div>';
                                echo '</td>';
                            } else {
                                echo '<td class="cell-empty"></td>'; 
                            }
                        ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            
            <tr><td colspan="<?php echo count($rooms) + 1; ?>" style="border: none; height: 30px;"></td></tr>

        <?php endforeach; ?>
    </table>

</body>
</html>