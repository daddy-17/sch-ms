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
$class_id = $input['class_id'] ?? '';
$parent_contact = $input['parent_contact'] ?? '';

if (empty($name) || empty($class_id) || empty($parent_contact)) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

$body = [
    'name' => $name,
    'class_id' => $class_id,
    'parent_contact' => $parent_contact
];

$response = Supabase::request('POST', 'students', $body);

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo json_encode(['success' => true, 'data' => $response['data']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create student', 'details' => $response['data']]);
}
