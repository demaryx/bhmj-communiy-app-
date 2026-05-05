<?php
require_once '../config.php';
secureSessionStart();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$cnic = $_GET['cnic'] ?? '';

if (empty($cnic)) {
    echo json_encode(['error' => 'CNIC required']);
    exit;
}

$stmt = $mysqli->prepare('SELECT full_name, date_of_birth, mobile_1 FROM members WHERE cnic = ? LIMIT 1');
$stmt->bind_param('s', $cnic);
$stmt->execute();
$result = $stmt->get_result();

if ($member = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'data' => $member
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Member not found'
    ]);
}
