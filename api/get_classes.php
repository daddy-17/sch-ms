<?php
session_start();
header('Content-Type: application/json');

require_once 'supabase.php';

if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Fetch classes
$response = Supabase::request('GET', 'classes?select=*');

if ($response['status'] >= 200 && $response['status'] < 300) {
    echo json_encode(['success' => true, 'data' => $response['data']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch classes']);
}
