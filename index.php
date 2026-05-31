<?php
/**
 * EXAMSYNC - Exam Schedule Assignment System
 * System Developer: Kert Bryan Dingcong
 * Institution: Binalbagan Catholic College
 */

session_start();

// If registrar is already logged in, redirect to the setup hub
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// Include the centralized database connection
require_once 'database/dbcon.php';

$error = '';

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Fetch user securely using prepared statements and the $pdo object from dbcon.php
            $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $userRecord = $stmt->fetch();

            // Verify password using bcrypt
            if ($userRecord && password_verify($password, $userRecord['password'])) {
                // Login Success
                $_SESSION['user_id'] = $userRecord['id'];
                $_SESSION['role'] = $userRecord['role'];
                $_SESSION['username'] = $userRecord['username'];
                
                header("Location: home.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }

        } catch (PDOException $e) {
            $error = 'Query failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXAMSYNC - Registrar Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Apple-style typography tweaking */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        /* Custom background gradient to simulate macOS wallpaper */
        .macos-bg {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,0) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,0.1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,0.1) 0, transparent 50%);
        }
    </style>
</head>
<body class="macos-bg min-h-screen flex items-center justify-center p-4 antialiased selection:bg-blue-500 selection:text-white">

    <div class="bg-white/60 backdrop-blur-2xl border border-white/50 shadow-[0_8px_30px_rgb(0,0,0,0.12)] rounded-[2rem] p-10 w-full max-w-md transition-all duration-300">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">EXAMSYNC</h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Registrar Portal</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50/80 border border-red-200 text-red-600 text-sm rounded-xl p-3 mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php" class="space-y-5">
            
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-1 ml-1">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username"
                    class="w-full bg-white/70 border border-gray-200 text-gray-900 text-sm rounded-xl px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all placeholder-gray-400 shadow-sm"
                    placeholder="Enter registrar username">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1 ml-1">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                    class="w-full bg-white/70 border border-gray-200 text-gray-900 text-sm rounded-xl px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all placeholder-gray-400 shadow-sm"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl px-4 py-3.5 transition-colors duration-200 shadow-md shadow-blue-500/20 active:scale-[0.98]">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-xs text-gray-400 font-medium">Binalbagan Catholic College &copy; <?php echo date("Y"); ?></p>
        </div>
    </div>

</body>
</html>