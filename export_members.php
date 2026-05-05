<?php
require_once 'config.php';
secureSessionStart();

if (empty($_SESSION['user_id'])) {
    die('Unauthorized');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=BHMJ_Members_List_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Membership Number', 'Full Name', 'CNIC', 'Mobile', 'Email', 'Join Date', 'Type']);

$query = "SELECT id, membership_number, full_name, cnic, mobile_1, email, join_date, membership_type FROM members ORDER BY id DESC";
$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
