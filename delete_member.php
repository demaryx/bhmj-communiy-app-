<?php
require_once 'config.php';
secureSessionStart();

if (empty($_SESSION['user_id'])) {
    die('Unauthorized');
}

$id = $_GET['id'] ?? 0;
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: members_list.php');
exit;
