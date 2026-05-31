<?php
/**
 * EXAMSYNC - Schedule Results & Editor
 * System Developer: Kert Bryan Dingcong
 * Institution: Binalbagan Catholic College
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'database/dbcon.php';

// Fetch the generated schedule using a JOIN to bring all normalized data together
// FIXED: Added s.program to the SELECT query to prevent undefined variable errors
$query = "
    SELECT gs.id as schedule_id, gs.status,
           t.day_name, t.start_time, t.end_time,
           r.room_name,
           s.program, s.subject_code, s.adviser, s.year_level, s.section, s.proctor
    FROM generated_schedules gs
    JOIN time_slots t ON gs.time_slot_id = t.id
    JOIN rooms r ON gs.room_id = r.id
    JOIN exam_subjects s ON gs.exam_subject_id = s.id
    ORDER BY t.day_name, t.start_time, r.room_name
";

$stmt = $pdo->query($query);
$schedules = $stmt->fetchAll();

// Group schedules by Day and Time Slot for a better UI presentation
$groupedSchedules = [];
foreach ($schedules as $row) {
    $day = $row['day_name'];
    $time = date('g:i A', strtotime($row['start_time'])) . ' - ' . date('g:i A', strtotime($row['end_time']));
    
    if (!isset($groupedSchedules[$day])) {
        $groupedSchedules[$day] = [];
    }
    if (!isset($groupedSchedules[$day][$time])) {
        $groupedSchedules[$day][$time] = [];
    }
    $groupedSchedules[$day][$time][] = $row;
}

// Fetch all available rooms dynamically for the Edit Modal dropdown
$allRooms = $pdo->query("SELECT room_name FROM rooms ORDER BY room_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXAMSYNC - Master Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f5f5f7; }
        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body class="antialiased text-gray-900" x-data="{ editModalOpen: false, selectedExam: null }">

    <nav class="bg-white/80 backdrop-blur-xl border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    <a href="home.php" class="p-2 hover:bg-gray-100 rounded-full transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <span class="font-bold text-xl tracking-tight">Master Schedule</span>
                </div>
                <div class="flex items-center gap-3">
                        <a href="export_excel.php" target="_blank" class="flex items-center gap-2 bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Export Tarpapel (Excel)
                        </a>
                   
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if (empty($groupedSchedules)): ?>
            <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100 max-w-2xl mx-auto mt-10">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">No Schedule Generated Yet</h2>
                <p class="text-gray-500 mb-8">Please return to the Setup Hub, upload your constraints, and run the Sync Engine to generate the exam schedule.</p>
                <a href="home.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-md">Go to Setup Hub</a>
            </div>
        <?php else: ?>

            <div class="space-y-10">
                <?php foreach ($groupedSchedules as $day => $timeSlots): ?>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-3 h-8 bg-blue-500 rounded-full"></span>
                            <?php echo htmlspecialchars($day); ?>
                        </h2>

                        <div class="space-y-6">
                            <?php foreach ($timeSlots as $time => $exams): ?>
                                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row gap-6">
                                    
                                    <div class="w-48 shrink-0 flex flex-col justify-center border-r border-gray-100 pr-6">
                                        <div class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Time Slot</div>
                                        <div class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($time); ?></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
                                        <?php foreach ($exams as $exam): ?>
                                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 hover:border-blue-300 hover:shadow-md transition group cursor-pointer"
                                                 @click="editModalOpen = true; selectedExam = {
                                                    id: '<?php echo $exam['schedule_id']; ?>',
                                                    proctor: '<?php echo addslashes($exam['proctor']); ?>',
                                                    subject: '<?php echo addslashes($exam['program'] . ' ' . $exam['subject_code'] . ' (Yr ' . $exam['year_level'] . ' - Sec ' . $exam['section'] . ')'); ?>',
                                                    room: '<?php echo addslashes($exam['room_name']); ?>'
                                                 }">
                                                
                                                <div class="flex justify-between items-start mb-3">
                                                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-md">
                                                        <?php echo htmlspecialchars($exam['room_name']); ?>
                                                    </span>
                                                    <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </div>
                                                
                                                <div class="font-bold text-gray-900 text-lg leading-tight mb-1">
                                                    <span class="text-blue-600 mr-1"><?php echo htmlspecialchars($exam['program']); ?></span><?php echo htmlspecialchars($exam['subject_code']); ?>
                                                    <span class="text-gray-500 font-medium text-sm block mt-0.5">Yr <?php echo htmlspecialchars($exam['year_level']); ?> - Sec <?php echo htmlspecialchars($exam['section']); ?></span>
                                                </div>
                                                <div class="text-sm text-gray-600 flex items-center gap-1.5 mt-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    <?php echo htmlspecialchars($exam['proctor']); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </main>

    <div x-show="editModalOpen" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <div x-show="editModalOpen" 
                     @click.away="editModalOpen = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-white/50">
                    
                    <div class="bg-white px-8 pb-8 pt-8">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-2xl font-bold leading-6 text-gray-900 mb-2" id="modal-title">Edit Assignment</h3>
                                <p class="text-sm text-gray-500 mb-6">Manually override the AI's room assignment. Be careful not to create conflicts.</p>
                                
                                <div class="space-y-4">
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                        <div class="text-sm text-gray-500 font-medium" x-text="'Proctor: ' + selectedExam?.proctor"></div>
                                        <div class="text-lg font-bold text-gray-900" x-text="selectedExam?.subject"></div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1 ml-1">Change Room</label>
                                        <select class="w-full bg-white border border-gray-200 text-gray-900 text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                                            <option x-text="selectedExam?.room" selected></option>
                                            
                                            <?php foreach($allRooms as $r): ?>
                                                <option value="<?php echo htmlspecialchars($r['room_name']); ?>">
                                                    <?php echo htmlspecialchars($r['room_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-8 py-4 flex flex-row-reverse gap-3">
                        <button type="button" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:w-auto transition">Save Changes</button>
                        <button type="button" @click="editModalOpen = false" class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto transition">Cancel</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>