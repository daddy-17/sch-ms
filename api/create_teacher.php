<?php
session_start();
header('Content-Type: application/json');

require_once 'supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$department = $input['department'] ?? '';
$email = $input['email'] ?? '';

if (empty($name) || empty($department) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

$body = [
    'name' => $name,
    'department' => $department,
    'email' => $email
];

$response = Supabase::request('POST', 'teachers', $body);

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo json_encode(['success' => true, 'data' => $response['data']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create teacher', 'details' => $response['data']]);
}
