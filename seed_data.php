<?php
require_once 'config.php';

$names = ["Fatima Zahra", "Usman Ghani", "Hafsa Malik", "Ibrahim Khalil", "Ayesha Siddiqua"];
$cnics = ["42101-5556667-1", "42101-8889990-2", "42101-2223334-3", "42101-7776665-4", "42101-4443332-5"];
$types = ["Silver", "Platinum", "Gold", "Standard", "Platinum"];

for ($i = 0; $i < 5; $i++) {
    $num = "BHMJ-" . rand(5000, 9999);
    $dob = date('Y-m-d', strtotime('-' . rand(20, 45) . ' years'));
    $mobile = "0333" . rand(1111111, 9999999);
    $joinDate = date('Y-m-d');
    
    $stmt = $mysqli->prepare("INSERT INTO members (membership_number, full_name, cnic, date_of_birth, mobile_1, membership_type, join_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $num, $names[$i], $cnics[$i], $dob, $mobile, $types[$i], $joinDate);
    $stmt->execute();
}

header('Location: members_list.php?seeded=true');
exit;
