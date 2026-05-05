<?php
require_once 'config.php';

$names = ["Ahmed Ali", "Zainab Khan", "Bilal Ahmed", "Sara Malik", "Mustafa Raza"];
$types = ["Gold", "Silver", "Platinum", "Silver", "Gold"];

for($i=0; $i<5; $i++) {
    $num = "BHMJ-" . rand(1000, 9999);
    $cnic = rand(11111, 99999) . "-" . rand(1111111, 9999999) . "-" . rand(1, 9);
    // Mix of 18+ and under 18 for testing notifications
    $year = ($i % 2 == 0) ? rand(1980, 2004) : 2010; 
    $dob = $year . "-" . rand(1, 12) . "-" . rand(1, 28);
    
    $stmt = $mysqli->prepare("INSERT INTO members (membership_number, full_name, cnic, date_of_birth, mobile_1, membership_type) VALUES (?, ?, ?, ?, ?, ?)");
    $mobile = "0300" . rand(1111111, 9999999);
    $stmt->bind_param("ssssss", $num, $names[$i], $cnic, $dob, $mobile, $types[$i]);
    $stmt->execute();
}

echo "<h1>Success!</h1><p>5 new members have been added to the database for testing.</p><a href='members_list.php'>View Members</a>";
unlink(__FILE__); // Self-destruct after running
