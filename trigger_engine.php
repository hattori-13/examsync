<?php
/**
 * EXAMSYNC - Python Engine Trigger Bridge
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Ensure the header is JSON
header('Content-Type: application/json');

// Execute the Python script. 
// Note: If 'python' doesn't work, you may need to use 'python3' or provide the full path to your python.exe (e.g., 'C:\\Python39\\python.exe engine.py')
$command = escapeshellcmd('python engine.py');
$output = shell_exec($command);

if ($output === null) {
    echo json_encode(["status" => "error", "message" => "Failed to execute Python Engine. Check paths."]);
} else {
    // Return Python's JSON output directly back to the frontend
    echo $output;
}
?>