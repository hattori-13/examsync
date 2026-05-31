<?php
/**
 * EXAMSYNC - Home / Setup Hub (Full Stack + CRUD + Danger Zone)
 * System Developer: Kert Bryan Dingcong
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'database/dbcon.php';

// =====================================================================
// FEATURE: Download Pre-made Excel/CSV Reference Template
// =====================================================================
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="EXAMSYNC_Subject_Template.csv"');
    $output = fopen('php://output', 'w');
    // Add columns (NOW INCLUDES PROGRAM)
    fputcsv($output, ['Program', 'Subject Code', 'Adviser', 'Year', 'Section', 'Proctor']);
    // Add reference examples
    fputcsv($output, ['BSIT', 'CC101', 'Eulalia S. Dagunan', '1', 'A', 'Jhon Mark M. Truces']);
    fputcsv($output, ['BSED-SCI', 'SCI1', 'Ariel Solina', '3', 'B', 'Ariel Solina']); 
    fputcsv($output, ['CRIM', 'GE1', 'Jade Mozunes', '1', 'C', 'Jobil Pandan']);
    fclose($output);
    exit();
}

// =====================================================================
// BACKEND API ENGINE 
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        if ($_POST['action'] === 'add_timeslot') {
            $stmt = $pdo->prepare("INSERT INTO time_slots (day_name, start_time, end_time) VALUES (?, ?, ?)");
            $stmt->execute([trim($_POST['day_name']), trim($_POST['start_time']), trim($_POST['end_time'])]);
            echo json_encode(['status' => 'success', 'message' => 'Time slot added!']); exit();
        }

        if ($_POST['action'] === 'add_room') {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_name) VALUES (?)");
            $stmt->execute([trim($_POST['room_name'])]);
            echo json_encode(['status' => 'success', 'message' => 'Room added!']); exit();
        }

        if ($_POST['action'] === 'add_subject_manual') {
            $stmt = $pdo->prepare("INSERT INTO exam_subjects (program, subject_code, adviser, year_level, section, proctor) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['program']), trim($_POST['subject_code']), trim($_POST['adviser']), 
                trim($_POST['year_level']), trim($_POST['section']), trim($_POST['proctor'])
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Subject added manually!']); exit();
        }

        if ($_POST['action'] === 'delete_item') {
            $table = $_POST['table'];
            $id = $_POST['id'];
            $allowed_tables = ['rooms', 'time_slots', 'exam_subjects'];
            if (in_array($table, $allowed_tables)) {
                $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'Record deleted.']); exit();
            }
        }

        // --- NEW: DELETE ALL DATA WITH TRANSACTION SAFETY ---
        if ($_POST['action'] === 'delete_all_data') {
            $pdo->beginTransaction();
            try {
                $pdo->exec("DELETE FROM generated_schedules");
                $pdo->exec("DELETE FROM exam_subjects");
                $pdo->exec("DELETE FROM rooms");
                $pdo->exec("DELETE FROM time_slots");
                
                // Reset Auto Increments
                $pdo->exec("ALTER TABLE generated_schedules AUTO_INCREMENT = 1");
                $pdo->exec("ALTER TABLE exam_subjects AUTO_INCREMENT = 1");
                $pdo->exec("ALTER TABLE rooms AUTO_INCREMENT = 1");
                $pdo->exec("ALTER TABLE time_slots AUTO_INCREMENT = 1");

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'All system data has been completely wiped.']); exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Wipe failed. Data restored safely.']); exit();
            }
        }

        if ($_POST['action'] === 'edit_room') {
            $pdo->prepare("UPDATE rooms SET room_name = ? WHERE id = ?")->execute([trim($_POST['room_name']), $_POST['id']]);
            echo json_encode(['status' => 'success', 'message' => 'Room updated!']); exit();
        }
        if ($_POST['action'] === 'edit_timeslot') {
            $pdo->prepare("UPDATE time_slots SET day_name = ?, start_time = ?, end_time = ? WHERE id = ?")
                ->execute([trim($_POST['day_name']), trim($_POST['start_time']), trim($_POST['end_time']), $_POST['id']]);
            echo json_encode(['status' => 'success', 'message' => 'Time slot updated!']); exit();
        }
        if ($_POST['action'] === 'edit_subject') {
            $pdo->prepare("UPDATE exam_subjects SET program=?, subject_code=?, adviser=?, year_level=?, section=?, proctor=? WHERE id=?")
                ->execute([trim($_POST['program']), trim($_POST['subject_code']), trim($_POST['adviser']), trim($_POST['year_level']), trim($_POST['section']), trim($_POST['proctor']), $_POST['id']]);
            echo json_encode(['status' => 'success', 'message' => 'Subject updated!']); exit();
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]); exit();
    }
}

// --- BULK UPLOAD ACTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bulk_upload'])) {
    header('Content-Type: application/json');
    $file = $_FILES['bulk_upload'];
    
    if ($file['error'] == 0 && strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'csv') {
        $handle = fopen($file['tmp_name'], "r");
        $rowCount = 0;
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO exam_subjects (program, subject_code, adviser, year_level, section, proctor) VALUES (?, ?, ?, ?, ?, ?)");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Skip headers
                if ($rowCount == 0 && stripos($data[0], 'Program') !== false) { $rowCount++; continue; }
                
                // Ensure 6 columns exist
                if (isset($data[0], $data[1], $data[2], $data[3], $data[4], $data[5])) {
                    $stmt->execute([trim($data[0]), trim($data[1]), trim($data[2]), trim($data[3]), trim($data[4]), trim($data[5])]);
                    $rowCount++;
                }
            }
            $pdo->commit();
            fclose($handle);
            echo json_encode(['status' => 'success', 'message' => "$rowCount subjects processed!"]); exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Upload failed: ' . $e->getMessage()]); exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Please upload a valid .csv file.']); exit();
    }
}

// FRONTEND DATA FETCHING
$stats = [
    'slots' => $pdo->query("SELECT COUNT(*) FROM time_slots")->fetchColumn(),
    'rooms' => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
    'subjects' => $pdo->query("SELECT COUNT(*) FROM exam_subjects")->fetchColumn(),
];

$db_slots = $pdo->query("SELECT * FROM time_slots ORDER BY day_name, start_time")->fetchAll();
$db_rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_name")->fetchAll();
$db_subjects = $pdo->query("SELECT * FROM exam_subjects ORDER BY program, year_level, subject_code")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXAMSYNC - Setup Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f5f5f7; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="antialiased text-gray-900" x-data="examsyncApp()">

    <nav class="bg-white/80 backdrop-blur-xl border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="font-bold text-xl tracking-tight">EXAMSYNC</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-500">Registrar: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex gap-8">
        
        <aside class="w-64 shrink-0">
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 sticky top-24">
                <nav class="space-y-2 text-sm font-medium">
                    <button @click="activeTab = 'dashboard'" :class="{'bg-blue-50 text-blue-600': activeTab === 'dashboard'}" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition hover:bg-gray-50 text-gray-600">Overview</button>
                    <button @click="activeTab = 'time'" :class="{'bg-blue-50 text-blue-600': activeTab === 'time'}" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition hover:bg-gray-50 text-gray-600">Add Time Slots</button>
                    <button @click="activeTab = 'rooms'" :class="{'bg-blue-50 text-blue-600': activeTab === 'rooms'}" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition hover:bg-gray-50 text-gray-600">Add Rooms</button>
                    <button @click="activeTab = 'subjects'" :class="{'bg-blue-50 text-blue-600': activeTab === 'subjects'}" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition hover:bg-gray-50 text-gray-600">Add Subjects</button>
                    <hr class="border-gray-100 my-2">
                    <button @click="activeTab = 'directory'" :class="{'bg-indigo-50 text-indigo-700': activeTab === 'directory'}" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition hover:bg-gray-50 text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        View Data Directory
                    </button>
                </nav>
            </div>
        </aside>

        <main class="flex-1 overflow-hidden">
            
            <div x-show="activeTab === 'dashboard'" x-transition class="space-y-6">
                <h2 class="text-3xl font-bold tracking-tight">Setup Overview</h2>
                <div class="grid grid-cols-3 gap-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="text-gray-400 font-medium text-sm mb-4">Time Slots Configured</div>
                        <div class="text-4xl font-bold text-gray-900"><?php echo $stats['slots']; ?></div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="text-gray-400 font-medium text-sm mb-4">Rooms Available</div>
                        <div class="text-4xl font-bold text-gray-900"><?php echo $stats['rooms']; ?></div>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                        <div class="text-gray-400 font-medium text-sm mb-4">Exam Subjects</div>
                        <div class="text-4xl font-bold text-gray-900"><?php echo $stats['subjects']; ?></div>
                    </div>
                </div>

                <div class="mt-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-8 text-center shadow-md">
                    <h3 class="text-2xl font-bold text-white mb-2">Ready to Synchronize?</h3>
                    <p class="text-blue-100 mb-6">Ensure all data is encoded before running the AI Engine.</p>
                    <button @click="generateSchedule()" class="inline-block bg-white text-blue-600 px-8 py-3.5 rounded-2xl font-bold text-lg hover:scale-105 transition shadow-lg border-0 cursor-pointer">
                        ✨ Generate Schedule
                    </button>
                </div>

                <div class="mt-8 bg-red-50 rounded-3xl p-8 border border-red-100 flex justify-between items-center shadow-sm">
                    <div>
                        <h3 class="text-xl font-bold text-red-700 mb-1">Danger Zone</h3>
                        <p class="text-sm text-red-500 font-medium">Permanently wipe all Time Slots, Rooms, Subjects, and Schedules.</p>
                    </div>
                    <button @click="deleteAllData()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3.5 rounded-xl font-bold transition shadow-md active:scale-95 border-0 cursor-pointer">
                        Delete All Data
                    </button>
                </div>
            </div>

            <div x-show="activeTab === 'time'" style="display: none;" x-transition class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-2xl font-bold tracking-tight mb-6">Add Time Slots</h2>
                <form @submit.prevent="submitForm('add_timeslot', $event)" class="bg-gray-50 p-6 rounded-2xl border border-gray-200 flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Day Name</label>
                        <select name="day_name" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            <option value="Day 1">Day 1</option>
                            <option value="Day 2">Day 2</option>
                            <option value="Day 3">Day 3</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Start Time</label>
                        <input type="time" name="start_time" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">End Time</label>
                        <input type="time" name="end_time" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl px-6 py-2.5 transition">Save Slot</button>
                </form>
            </div>

            <div x-show="activeTab === 'rooms'" style="display: none;" x-transition class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-2xl font-bold tracking-tight mb-6">Add Rooms</h2>
                <form @submit.prevent="submitForm('add_room', $event)" class="flex gap-4">
                    <input type="text" name="room_name" placeholder="E.g., SC1, LAB3" required class="flex-1 bg-gray-50 border border-gray-300 rounded-xl px-4 py-3">
                    <button type="submit" class="bg-black hover:bg-gray-800 text-white font-medium rounded-xl px-8 py-3 transition">Add Room</button>
                </form>
            </div>

            <div x-show="activeTab === 'subjects'" style="display: none;" x-transition class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold tracking-tight">Add Subjects</h2>
                    <div class="bg-gray-100 p-1 rounded-xl flex">
                        <button @click="loadMethod = 'manual'" :class="{'bg-white shadow': loadMethod === 'manual'}" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Manual Input</button>
                        <button @click="loadMethod = 'upload'" :class="{'bg-white shadow': loadMethod === 'upload'}" class="px-4 py-2 rounded-lg text-sm font-semibold transition">CSV/Excel Upload</button>
                    </div>
                </div>

                <div x-show="loadMethod === 'manual'">
                    <form @submit.prevent="submitForm('add_subject_manual', $event)" class="bg-blue-50 p-6 rounded-2xl border border-blue-100 flex flex-col gap-4">
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-2">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Program / Course</label>
                                <select name="program" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 shadow-sm appearance-none">
                                    <option value="" disabled selected>Select Program</option>
                                    <option value="BSA">BSA</option>
                                    <option value="BEED">BEED</option>
                                    <option value="BSED-ENG">BSED-ENG</option>
                                    <option value="BSED-SCI">BSED-SCI</option>
                                    <option value="BSBA-FM">BSBA-FM</option>
                                    <option value="BSBA-HR">BSBA-HR</option>
                                    <option value="BSIT">BSIT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Subject Code</label>
                                <input type="text" name="subject_code" placeholder="CC101" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Adviser</label>
                                <input type="text" name="adviser" placeholder="Adviser Name" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Year</label>
                                <input type="text" name="year_level" placeholder="1, 2, 3, 4" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Section</label>
                                <input type="text" name="section" placeholder="A, B, C" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Proctor</label>
                                <input type="text" name="proctor" placeholder="Proctor Name" required class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5">
                            </div>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl px-6 py-3 mt-2 transition self-end">Add Subject</button>
                    </form>
                </div>

                <div x-show="loadMethod === 'upload'" style="display:none;">
                    <div class="flex justify-end mb-4">
                        <a href="home.php?download_template=1" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Pre-made Excel Reference
                        </a>
                    </div>
                    <form @submit.prevent="uploadFile($event)" class="border-2 border-dashed border-gray-300 rounded-3xl p-12 text-center hover:bg-gray-50 transition cursor-pointer relative">
                        <input type="file" name="bulk_upload" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Upload File</h3>
                        <p class="text-sm text-gray-500 mb-4">Must match the template exactly (Save Excel as .CSV before upload)</p>
                        <button type="submit" class="bg-black text-white font-medium rounded-xl px-6 py-2 shadow-sm relative z-10 pointer-events-none">Sync File</button>
                    </form>
                </div>
            </div>

            <div x-show="activeTab === 'directory'" style="display: none;" x-transition class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col h-[calc(100vh-10rem)]" x-data="{ dirTab: 'subjects' }">
                
                <div class="flex justify-between items-center mb-6 shrink-0">
                    <h2 class="text-2xl font-bold tracking-tight">Database Directory</h2>
                    <div class="bg-gray-100 p-1 rounded-xl flex gap-1">
                        <button @click="dirTab = 'subjects'" :class="{'bg-white shadow text-gray-900': dirTab === 'subjects', 'text-gray-500': dirTab !== 'subjects'}" class="px-5 py-2 rounded-lg text-sm font-semibold transition">Subjects</button>
                        <button @click="dirTab = 'rooms'" :class="{'bg-white shadow text-gray-900': dirTab === 'rooms', 'text-gray-500': dirTab !== 'rooms'}" class="px-5 py-2 rounded-lg text-sm font-semibold transition">Rooms</button>
                        <button @click="dirTab = 'slots'" :class="{'bg-white shadow text-gray-900': dirTab === 'slots', 'text-gray-500': dirTab !== 'slots'}" class="px-5 py-2 rounded-lg text-sm font-semibold transition">Time Slots</button>
                    </div>
                </div>

                <div x-show="dirTab === 'subjects'" class="flex-1 overflow-hidden flex flex-col border border-gray-200 rounded-2xl">
                    <div class="bg-gray-50 grid grid-cols-6 px-6 py-3 border-b border-gray-200 font-semibold text-sm text-gray-600 shrink-0">
                        <div>Program / Code</div>
                        <div>Year/Sec</div>
                        <div class="col-span-2">Adviser</div>
                        <div>Proctor</div>
                        <div class="text-right">Action</div>
                    </div>
                    <div class="overflow-y-auto custom-scroll p-2 space-y-1">
                        <?php foreach($db_subjects as $sub): ?>
                            <div class="grid grid-cols-6 items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition text-sm text-gray-800">
                                <div>
                                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($sub['program']); ?></span>
                                    <span class="text-gray-500 ml-1"><?php echo htmlspecialchars($sub['subject_code']); ?></span>
                                </div>
                                <div>Yr <?php echo htmlspecialchars($sub['year_level']); ?>-<?php echo htmlspecialchars($sub['section']); ?></div>
                                <div class="col-span-2 truncate pr-2"><?php echo htmlspecialchars($sub['adviser']); ?></div>
                                <div class="truncate pr-2 font-medium"><?php echo htmlspecialchars($sub['proctor']); ?></div>
                                <div class="text-right flex justify-end gap-2">
                                    <button @click="openEditModal('exam_subjects', <?php echo htmlspecialchars(json_encode($sub)); ?>)" class="text-gray-400 hover:text-blue-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                                    <button @click="deleteItem('exam_subjects', <?php echo $sub['id']; ?>)" class="text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div x-show="dirTab === 'rooms'" style="display: none;" class="flex-1 overflow-hidden flex flex-col border border-gray-200 rounded-2xl">
                    <div class="bg-gray-50 grid grid-cols-2 px-6 py-3 border-b border-gray-200 font-semibold text-sm text-gray-600 shrink-0">
                        <div>Room Name</div>
                        <div class="text-right">Action</div>
                    </div>
                    <div class="overflow-y-auto custom-scroll p-2 space-y-1">
                        <?php foreach($db_rooms as $room): ?>
                            <div class="grid grid-cols-2 items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition text-sm text-gray-800">
                                <div class="font-bold"><?php echo htmlspecialchars($room['room_name']); ?></div>
                                <div class="text-right flex justify-end gap-2">
                                    <button @click="openEditModal('rooms', <?php echo htmlspecialchars(json_encode($room)); ?>)" class="text-gray-400 hover:text-blue-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                    <button @click="deleteItem('rooms', <?php echo $room['id']; ?>)" class="text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div x-show="dirTab === 'slots'" style="display: none;" class="flex-1 overflow-hidden flex flex-col border border-gray-200 rounded-2xl">
                    <div class="bg-gray-50 grid grid-cols-4 px-6 py-3 border-b border-gray-200 font-semibold text-sm text-gray-600 shrink-0">
                        <div>Day</div>
                        <div>Start Time</div>
                        <div>End Time</div>
                        <div class="text-right">Action</div>
                    </div>
                    <div class="overflow-y-auto custom-scroll p-2 space-y-1">
                        <?php foreach($db_slots as $slot): ?>
                            <div class="grid grid-cols-4 items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition text-sm text-gray-800">
                                <div class="font-bold"><?php echo htmlspecialchars($slot['day_name']); ?></div>
                                <div><?php echo date('g:i A', strtotime($slot['start_time'])); ?></div>
                                <div><?php echo date('g:i A', strtotime($slot['end_time'])); ?></div>
                                <div class="text-right flex justify-end gap-2">
                                    <button @click="openEditModal('time_slots', <?php echo htmlspecialchars(json_encode($slot)); ?>)" class="text-gray-400 hover:text-blue-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                    <button @click="deleteItem('time_slots', <?php echo $slot['id']; ?>)" class="text-gray-400 hover:text-red-500"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div x-show="editModal.open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="editModal.open = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg border border-white/50">
            <h3 class="text-2xl font-bold mb-6">Edit Record</h3>
            
            <form @submit.prevent="submitEdit()">
                <input type="hidden" name="id" x-model="editModal.data.id">
                
                <div x-show="editModal.type === 'rooms'">
                    <label class="block text-sm font-semibold mb-1">Room Name</label>
                    <input type="text" name="room_name" x-model="editModal.data.room_name" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-4">
                </div>

                <div x-show="editModal.type === 'time_slots'">
                    <label class="block text-sm font-semibold mb-1">Day</label>
                    <input type="text" name="day_name" x-model="editModal.data.day_name" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-4">
                    <label class="block text-sm font-semibold mb-1">Start Time</label>
                    <input type="time" name="start_time" x-model="editModal.data.start_time" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-4">
                    <label class="block text-sm font-semibold mb-1">End Time</label>
                    <input type="time" name="end_time" x-model="editModal.data.end_time" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-4">
                </div>

                <div x-show="editModal.type === 'exam_subjects'">
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1">Program</label>
                            <select name="program" x-model="editModal.data.program" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option value="BSA">BSA</option>
                                <option value="BEED">BEED</option>
                                <option value="BSED-ENG">BSED-ENG</option>
                                <option value="BSED-SCI">BSED-SCI</option>
                                <option value="BSBA-FM">BSBA-FM</option>
                                <option value="BSBA-HR">BSBA-HR</option>
                                <option value="BSIT">BSIT</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1">Subject Code</label>
                            <input type="text" name="subject_code" x-model="editModal.data.subject_code" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5">
                        </div>
                    </div>

                    <label class="block text-sm font-semibold mb-1">Adviser</label>
                    <input type="text" name="adviser" x-model="editModal.data.adviser" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-4">
                    
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1">Year</label>
                            <input type="text" name="year_level" x-model="editModal.data.year_level" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold mb-1">Section</label>
                            <input type="text" name="section" x-model="editModal.data.section" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5">
                        </div>
                    </div>
                    
                    <label class="block text-sm font-semibold mb-1">Proctor</label>
                    <input type="text" name="proctor" x-model="editModal.data.proctor" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 mb-6">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="editModal.open = false" class="px-5 py-2.5 rounded-xl font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-semibold bg-blue-600 hover:bg-blue-700 text-white">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="toast.visible" x-transition.opacity class="fixed bottom-5 right-5 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-[200]">
        <span x-text="toast.message" class="text-sm font-medium"></span>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('examsyncApp', () => ({
                activeTab: 'dashboard',
                loadMethod: 'manual',
                toast: { visible: false, message: '' },
                
                editModal: { open: false, type: '', data: {} },
                
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => this.toast.visible = false, 3000);
                },

                // --- NEW DELETE ALL DATA LOGIC ---
                async deleteAllData() {
                    if(!confirm("⚠️ WARNING: This will permanently delete ALL Time Slots, Rooms, Subjects, and Generated Schedules. This action CANNOT be undone.\n\nAre you absolutely sure?")) return;
                    
                    const check = prompt("To confirm, please type the word DELETE below:");
                    if(check !== 'DELETE') {
                        this.showToast("Data wipe cancelled.");
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'delete_all_data');
                    try {
                        const response = await fetch('home.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        this.showToast(result.message);
                        if(result.status === 'success') setTimeout(() => window.location.reload(), 1500);
                    } catch (error) { this.showToast("Failed to delete data."); }
                },

                async submitForm(actionName, event) {
                    const form = event.target;
                    const formData = new FormData(form);
                    formData.append('action', actionName);
                    try {
                        const response = await fetch('home.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        this.showToast(result.message);
                        if(result.status === 'success') {
                            form.reset();
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } catch (error) { this.showToast("Error processing request."); }
                },

                async uploadFile(event) {
                    const formData = new FormData(event.target);
                    this.showToast("Uploading and processing file...");
                    try {
                        const response = await fetch('home.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        this.showToast(result.message);
                        if(result.status === 'success') setTimeout(() => window.location.reload(), 1500);
                    } catch (error) { this.showToast("Error uploading file."); }
                },

                async deleteItem(table, id) {
                    if(!confirm("Are you sure you want to delete this record?")) return;
                    const formData = new FormData();
                    formData.append('action', 'delete_item');
                    formData.append('table', table);
                    formData.append('id', id);
                    try {
                        const response = await fetch('home.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        this.showToast(result.message);
                        if(result.status === 'success') setTimeout(() => window.location.reload(), 800);
                    } catch (error) { this.showToast("Delete failed."); }
                },

                openEditModal(type, data) {
                    this.editModal.type = type;
                    this.editModal.data = { ...data }; 
                    this.editModal.open = true;
                },
                
                async submitEdit() {
                    const formData = new FormData();
                    let action = '';
                    if(this.editModal.type === 'rooms') action = 'edit_room';
                    if(this.editModal.type === 'time_slots') action = 'edit_timeslot';
                    if(this.editModal.type === 'exam_subjects') action = 'edit_subject';
                    
                    formData.append('action', action);
                    for (const key in this.editModal.data) {
                        formData.append(key, this.editModal.data[key]);
                    }

                    try {
                        const response = await fetch('home.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        this.showToast(result.message);
                        this.editModal.open = false;
                        if(result.status === 'success') setTimeout(() => window.location.reload(), 800);
                    } catch (error) { this.showToast("Update failed."); }
                },

                async generateSchedule() {
                    this.showToast("Initializing AI Engine... Please wait.");
                    try {
                        const response = await fetch('trigger_engine.php');
                        const result = await response.json();
                        this.showToast(result.message);
                        if(result.status === 'success') {
                            setTimeout(() => { window.location.href = 'schedule.php'; }, 2000);
                        }
                    } catch (error) {
                        this.showToast("Engine execution failed.");
                    }
                }
            }));
        });
    </script>
</body>
</html>