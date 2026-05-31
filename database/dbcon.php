<?php
/**
 * EXAMSYNC - Database Connection (PDO)
 * System Developer: Kert Bryan Dingcong
 */

$host = '127.0.0.1';
$db   = 'examsync_db';
$user = 'root'; // Default XAMPP/WAMP username
$pass = '';     // Default XAMPP/WAMP password (leave empty if not set)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Set PDO options for security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If connection fails, stop execution and show a clean error
    die("Database Connection Failed: " . $e->getMessage());
}
?>