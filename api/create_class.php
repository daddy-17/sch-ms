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
$grade_level = $input['grade_level'] ?? '';

if (empty($name) || empty($grade_level)) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

$body = [
    'name' => $name,
    'grade_level' => $grade_level
];

$response = Supabase::request('POST', 'classes', $body);

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo json_encode(['success' => true, 'data' => $response['data']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create class', 'details' => $response['data']]);
}
