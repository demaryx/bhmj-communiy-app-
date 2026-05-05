<?php
require_once 'config.php';

echo "<style>body{font-family:'Outfit',sans-serif; background:#f4f7f6; padding:40px; color:#333;} .card{background:#fff; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.1); max-width:750px; margin:auto;} .badge{display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;} .married{background:#d1fae5; color:#065f46;} .single{background:#dbeafe; color:#1e40af;}</style>";
echo "<div class='card'><h1>🔗 Seeding Linked Family Records</h1><hr style='border:0;border-top:1px solid #eee;margin:20px 0'>";

$mysqli->query("TRUNCATE TABLE members");

// Pre-define CNICs so we can cross-reference them
$members = [
    // === MARRIED MEMBERS (5) ===
    [
        'name' => 'Ahmed Khan', 'cnic' => '42101-1234567-1', 'id_no' => 'BHMJ-7000',
        'father' => 'Akbar Khan', 'surname' => 'Khan', 'native' => 'Karachi',
        'dob' => '1980-03-15', 'mobile' => '0300-1234567', 'email' => 'ahmed@bhmj.com',
        'address' => '15-B, Gulshan-e-Iqbal, Karachi', 'status' => 'Married',
        // Spouse is Zainab (BHMJ-7005 below - she is also registered)
        'family_tree' => [
            ['name' => 'Zainab Malik', 'relation' => 'Spouse', 'cnic' => '42101-9876543-5', 'dob' => '1985-06-20', 'status' => 'Alive'],
            ['name' => 'Ali Ahmed', 'relation' => 'Son', 'cnic' => '42101-1111111-1', 'dob' => '2010-01-10', 'status' => 'Alive'],
        ],
        'family_details' => [
            ['name' => 'Akbar Khan', 'relation' => 'Father', 'status' => 'Deceased', 'cnic' => '42101-0000001-1', 'contact' => '0301-0000001'],
            ['name' => 'Fatima Bibi', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000002-2', 'contact' => '0301-0000002'],
            ['name' => 'Hassan Khan', 'relation' => 'Brother', 'status' => 'Alive', 'cnic' => '42101-0000003-3', 'contact' => '0301-0000003'],
        ]
    ],
    [
        'name' => 'Mustafa Qureshi', 'cnic' => '42101-2345678-2', 'id_no' => 'BHMJ-7001',
        'father' => 'Ibrahim Qureshi', 'surname' => 'Qureshi', 'native' => 'Hyderabad',
        'dob' => '1975-07-22', 'mobile' => '0301-2345678', 'email' => 'mustafa@bhmj.com',
        'address' => 'A-45, PECHS, Karachi', 'status' => 'Married',
        // Father of Bilal (BHMJ-7006 below)
        'family_tree' => [
            ['name' => 'Rabia Siddiqui', 'relation' => 'Spouse', 'cnic' => '42101-8888888-8', 'dob' => '1979-11-05', 'status' => 'Alive'],
            ['name' => 'Bilal Mustafa', 'relation' => 'Son', 'cnic' => '42101-3456789-3', 'dob' => '2005-03-14', 'status' => 'Alive'], // Bilal's CNIC
        ],
        'family_details' => [
            ['name' => 'Ibrahim Qureshi', 'relation' => 'Father', 'status' => 'Deceased', 'cnic' => '42101-0000010-1', 'contact' => '0302-1111111'],
            ['name' => 'Amina Qureshi', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000011-2', 'contact' => '0302-2222222'],
        ]
    ],
    [
        'name' => 'Omar Alvi', 'cnic' => '42101-3333333-3', 'id_no' => 'BHMJ-7002',
        'father' => 'Tariq Alvi', 'surname' => 'Alvi', 'native' => 'Lahore',
        'dob' => '1982-12-01', 'mobile' => '0302-3333333', 'email' => 'omar@bhmj.com',
        'address' => 'B-12, Defence, Karachi', 'status' => 'Married',
        'family_tree' => [
            ['name' => 'Sara Rizvi', 'relation' => 'Spouse', 'cnic' => '42101-7777777-7', 'dob' => '1984-09-15', 'status' => 'Alive'],
            ['name' => 'Hamza Omar', 'relation' => 'Son', 'cnic' => '42101-5555555-5', 'dob' => '2012-06-20', 'status' => 'Alive'],
            ['name' => 'Hiba Omar', 'relation' => 'Daughter', 'cnic' => '', 'dob' => '2015-03-08', 'status' => 'Alive'],
        ],
        'family_details' => [
            ['name' => 'Tariq Alvi', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000020-1', 'contact' => '0303-1111111'],
            ['name' => 'Nadia Alvi', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000021-2', 'contact' => '0303-2222222'],
            ['name' => 'Usman Alvi', 'relation' => 'Brother', 'status' => 'Alive', 'cnic' => '42101-0000022-3', 'contact' => '0303-3333333'],
        ]
    ],
    [
        'name' => 'Yousuf Hashmi', 'cnic' => '42101-4444444-4', 'id_no' => 'BHMJ-7003',
        'father' => 'Saleem Hashmi', 'surname' => 'Hashmi', 'native' => 'Multan',
        'dob' => '1978-04-10', 'mobile' => '0303-4444444', 'email' => 'yousuf@bhmj.com',
        'address' => 'H-55, North Nazimabad, Karachi', 'status' => 'Married',
        'family_tree' => [
            ['name' => 'Asma Bhatti', 'relation' => 'Spouse', 'cnic' => '42101-6666666-6', 'dob' => '1982-02-28', 'status' => 'Alive'],
        ],
        'family_details' => [
            ['name' => 'Saleem Hashmi', 'relation' => 'Father', 'status' => 'Deceased', 'cnic' => '42101-0000030-1', 'contact' => '0304-1111111'],
            ['name' => 'Kiran Hashmi', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000031-2', 'contact' => '0304-2222222'],
            ['name' => 'Kamran Hashmi', 'relation' => 'Brother', 'status' => 'Alive', 'cnic' => '42101-0000032-3', 'contact' => '0304-3333333'],
        ]
    ],
    [
        'name' => 'Hussain Malik', 'cnic' => '42101-5555555-5', 'id_no' => 'BHMJ-7004',
        'father' => 'Ghulam Malik', 'surname' => 'Malik', 'native' => 'Sukkur',
        'dob' => '1985-09-25', 'mobile' => '0304-5555555', 'email' => 'hussain@bhmj.com',
        'address' => 'F-101, Gulberg, Karachi', 'status' => 'Married',
        'family_tree' => [
            ['name' => 'Madiha Abbasi', 'relation' => 'Spouse', 'cnic' => '42101-4321098-9', 'dob' => '1989-07-14', 'status' => 'Alive'],
            ['name' => 'Zara Hussain', 'relation' => 'Daughter', 'cnic' => '', 'dob' => '2014-11-30', 'status' => 'Alive'],
        ],
        'family_details' => [
            ['name' => 'Ghulam Malik', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000040-1', 'contact' => '0305-1111111'],
            ['name' => 'Rehana Malik', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000041-2', 'contact' => '0305-2222222'],
        ]
    ],

    // === SINGLE/UNMARRIED MEMBERS (5) ===
    [
        'name' => 'Bilal Shaikh', 'cnic' => '42101-3456789-3', 'id_no' => 'BHMJ-7005',
        'father' => 'Mustafa Qureshi', 'surname' => 'Shaikh', 'native' => 'Karachi',   // Father is Mustafa (BHMJ-7001)
        'dob' => '2005-03-14', 'mobile' => '0305-3456789', 'email' => 'bilal@bhmj.com',
        'address' => 'A-45, PECHS, Karachi', 'status' => 'Single',
        'family_tree' => [],
        'family_details' => [
            ['name' => 'Mustafa Qureshi', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-2345678-2', 'contact' => '0301-2345678'],  // Father's real CNIC
            ['name' => 'Rabia Siddiqui', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-8888888-8', 'contact' => '0301-9999999'],
        ]
    ],
    [
        'name' => 'Zainab Malik', 'cnic' => '42101-9876543-5', 'id_no' => 'BHMJ-7006',
        'father' => 'Tariq Malik', 'surname' => 'Malik', 'native' => 'Islamabad',  // Spouse of Ahmed (BHMJ-7000)
        'dob' => '1985-06-20', 'mobile' => '0306-9876543', 'email' => 'zainab@bhmj.com',
        'address' => '15-B, Gulshan-e-Iqbal, Karachi', 'status' => 'Married',
        'family_tree' => [
            ['name' => 'Ahmed Khan', 'relation' => 'Spouse', 'cnic' => '42101-1234567-1', 'dob' => '1980-03-15', 'status' => 'Alive'],  // Ahmed's real CNIC
        ],
        'family_details' => [
            ['name' => 'Tariq Malik', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000050-1', 'contact' => '0306-1111111'],
            ['name' => 'Nargis Malik', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000051-2', 'contact' => '0306-2222222'],
        ]
    ],
    [
        'name' => 'Zain Memon', 'cnic' => '42101-6789012-6', 'id_no' => 'BHMJ-7007',
        'father' => 'Rashid Memon', 'surname' => 'Memon', 'native' => 'Quetta',
        'dob' => '2000-11-11', 'mobile' => '0307-6789012', 'email' => 'zain@bhmj.com',
        'address' => 'D-22, Clifton, Karachi', 'status' => 'Single',
        'family_tree' => [],
        'family_details' => [
            ['name' => 'Rashid Memon', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000060-1', 'contact' => '0307-1111111'],
            ['name' => 'Shaheen Memon', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000061-2', 'contact' => '0307-2222222'],
            ['name' => 'Adnan Memon', 'relation' => 'Brother', 'status' => 'Alive', 'cnic' => '42101-0000062-3', 'contact' => '0307-3333333'],
        ]
    ],
    [
        'name' => 'Ali Bhatti', 'cnic' => '42101-7890123-7', 'id_no' => 'BHMJ-7008',
        'father' => 'Nasir Bhatti', 'surname' => 'Bhatti', 'native' => 'Faisalabad',
        'dob' => '1998-08-08', 'mobile' => '0308-7890123', 'email' => 'ali@bhmj.com',
        'address' => 'G-77, Liaquatabad, Karachi', 'status' => 'Single',
        'family_tree' => [],
        'family_details' => [
            ['name' => 'Nasir Bhatti', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000070-1', 'contact' => '0308-1111111'],
            ['name' => 'Samina Bhatti', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000071-2', 'contact' => '0308-2222222'],
            ['name' => 'Asad Bhatti', 'relation' => 'Brother', 'status' => 'Alive', 'cnic' => '42101-0000072-3', 'contact' => '0308-3333333'],
            ['name' => 'Hina Bhatti', 'relation' => 'Sister', 'status' => 'Alive', 'cnic' => '42101-0000073-4', 'contact' => '0308-4444444'],
        ]
    ],
    [
        'name' => 'Usman Rizvi', 'cnic' => '42101-8901234-8', 'id_no' => 'BHMJ-7009',
        'father' => 'Waqar Rizvi', 'surname' => 'Rizvi', 'native' => 'Peshawar',
        'dob' => '1995-02-14', 'mobile' => '0309-8901234', 'email' => 'usman@bhmj.com',
        'address' => 'K-3, Nazimabad, Karachi', 'status' => 'Single',
        'family_tree' => [],
        'family_details' => [
            ['name' => 'Waqar Rizvi', 'relation' => 'Father', 'status' => 'Alive', 'cnic' => '42101-0000080-1', 'contact' => '0309-1111111'],
            ['name' => 'Lubna Rizvi', 'relation' => 'Mother', 'status' => 'Alive', 'cnic' => '42101-0000081-2', 'contact' => '0309-2222222'],
        ]
    ],
];

foreach ($members as $m) {
    $ftJson = json_encode($m['family_tree']);
    $psJson = json_encode($m['family_details']);
    $joinDate = date('Y-m-d', strtotime('-' . rand(0, 180) . ' days'));
    
    $sql = "INSERT INTO members (full_name, cnic, membership_number, father_name, surname, native_place, date_of_birth, mobile_1, email, residential_address, join_date, marital_status, occupation, family_tree, family_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $occ = 'Business';
    $stmt->bind_param("sssssssssssssss", $m['name'], $m['cnic'], $m['id_no'], $m['father'], $m['surname'], $m['native'], $m['dob'], $m['mobile'], $m['email'], $m['address'], $joinDate, $m['status'], $occ, $ftJson, $psJson);
    
    if ($stmt->execute()) {
        $badge = $m['status'] == 'Married' ? "<span class='badge married'>Married</span>" : "<span class='badge single'>Single</span>";
        echo "<div style='display:flex;align-items:center;justify-content:space-between;padding:12px;margin:6px 0;background:#f8fafc;border-radius:10px;'>";
        echo "<div><strong>{$m['name']}</strong> <span style='color:#64748b; font-size:0.85rem;'>({$m['cnic']})</span></div>";
        echo "<div>$badge</div></div>";
    }
}

echo "<hr style='border:0;border-top:1px solid #eee;margin:25px 0'>";
echo "<h3 style='color:#10b981;'>✅ 10 Linked Family Records Created!</h3>";
echo "<p style='color:#64748b; line-height:1.8;'><strong>Try these searches:</strong><br>
🔍 Search <code>42101-2345678-2</code> → See Mustafa (Father of Bilal)<br>
🔍 Search <code>42101-3456789-3</code> → See Bilal (Son, Father's CNIC linked)<br>
🔍 Search <code>42101-1234567-1</code> → See Ahmed (Spouse: Zainab)<br>
🔍 Search <code>42101-9876543-5</code> → See Zainab (Spouse: Ahmed's CNIC linked)</p>";
echo "<a href='family_search.php' style='display:block;text-align:center;padding:15px;background:#2563eb;color:#fff;text-decoration:none;border-radius:12px;font-weight:800;margin-top:20px;'>Go to Family Search →</a>";
echo "</div>";
?>
