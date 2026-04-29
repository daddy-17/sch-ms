<?php
session_start();
header('Content-Type: application/json');

require_once 'supabase.php';

if (!isset($_SESSION['token'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Fetch counts for dashboard stats
// We use head requests with count=exact to get table sizes efficiently

function getCount($table) {
    $url = SUPABASE_URL . '/rest/v1/' . $table . '?select=*';
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . $_SESSION['token'],
        'Prefer: count=exact'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Extract Content-Range header
    $count = 0;
    if (preg_match('/Content-Range: \d+-\d+\/(\d+)/i', $response, $matches)) {
        $count = (int)$matches[1];
    }
    curl_close($ch);
    return $count;
}

$stats = [
    'total_students' => getCount('students'),
    'total_teachers' => getCount('teachers'),
    'total_classes' => getCount('classes'),
    'attendance_rate' => '94%' // Placeholder for MVP
];

echo json_encode(['success' => true, 'data' => $stats]);
