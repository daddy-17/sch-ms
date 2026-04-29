<?php
session_start();
header('Content-Type: application/json');

require_once 'supabase.php';

// Rate Limiting Logic (Simplified Session-based for MVP)
// Follows patterns from prior rate-limiting implementations
$ip = $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 300; // 5 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

if ($_SESSION['login_attempts'] >= $max_attempts) {
    if (time() - $_SESSION['last_attempt_time'] < $lockout_time) {
        $time_left = $lockout_time - (time() - $_SESSION['last_attempt_time']);
        $minutes = ceil($time_left / 60);
        echo json_encode(['success' => false, 'error' => "Rate limit exceeded. Try again in {$minutes} minute(s)."]);
        exit;
    } else {
        // Reset after lockout expires
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

// Attempt login via Supabase Stub
$auth_result = Supabase::authenticate($username, $password);

if ($auth_result['success']) {
    // Reset rate limiting on success
    $_SESSION['login_attempts'] = 0;
    $_SESSION['user_id'] = $auth_result['user_id'];
    $_SESSION['role'] = $auth_result['role'];
    
    echo json_encode(['success' => true, 'role' => $auth_result['role']]);
} else {
    // Increment rate limiting on failure
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    
    $attempts_left = $max_attempts - $_SESSION['login_attempts'];
    $error_msg = "Invalid credentials. {$attempts_left} attempt(s) remaining.";
    
    echo json_encode(['success' => false, 'error' => $error_msg]);
}
