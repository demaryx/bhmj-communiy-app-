<?php
require_once 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $membershipNumber = trim($_POST['membership_number'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $fatherName = trim($_POST['father_name'] ?? '');
        $grandfatherName = trim($_POST['grandfather_name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $nativePlace = trim($_POST['native_place'] ?? '');
        $cnic = trim($_POST['cnic'] ?? '');
        $residentialAddress = trim($_POST['residential_address'] ?? '');
        $cityCountry = trim($_POST['city_country'] ?? '');
        $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
        $mobile1 = trim($_POST['mobile_1'] ?? '');
        $mobile2 = trim($_POST['mobile_2'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        $maritalStatus = trim($_POST['marital_status'] ?? '');
        $fatherOrBrotherName = trim($_POST['father_or_brother_name'] ?? '');
        $fatherOrBrotherMembershipNo = trim($_POST['father_or_brother_membership_no'] ?? '');
        
        $membershipType = $_POST['membership_type'] ?? 'Standard';
        $amount = floatval($_POST['amount'] ?? 0);
        $joinDate = date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');

        $immediateFamily = [];
        if (isset($_POST['family_tree_name'])) {
            foreach ($_POST['family_tree_name'] as $i => $name) {
                if (trim($name) === '') continue;
                $immediateFamily[] = [
                    'name' => trim($name),
                    'relation' => trim($_POST['family_tree_relation'][$i] ?? ''),
                    'cnic' => trim($_POST['family_tree_cnic'][$i] ?? ''),
                    'dob' => trim($_POST['family_tree_dob'][$i] ?? ''),
                    'contact' => trim($_POST['family_tree_contact'][$i] ?? ''),
                ];
            }
        }

        $parentsSiblings = [];
        if (isset($_POST['parents_name'])) {
            foreach ($_POST['parents_name'] as $i => $name) {
                if (trim($name) === '') continue;
                $parentsSiblings[] = [
                    'name' => trim($name),
                    'relation' => trim($_POST['parents_relation'][$i] ?? ''),
                    'status' => trim($_POST['parents_status'][$i] ?? ''),
                    'cnic' => trim($_POST['parents_cnic'][$i] ?? ''),
                    'contact' => trim($_POST['parents_contact'][$i] ?? ''),
                    'membership_info' => trim($_POST['parents_membership_info'][$i] ?? ''),
                ];
            }
        }

        if ($fullName === '' || $cnic === '') {
            $error = 'Full Name and CNIC are required.';
        } else {
            $stmt = $mysqli->prepare('INSERT INTO members (membership_number, full_name, father_name, grandfather_name, surname, native_place, cnic, residential_address, city_country, date_of_birth, mobile_1, mobile_2, email, occupation, marital_status, father_or_brother_name, father_or_brother_membership_no, membership_type, amount, join_date, family_tree, family_details, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $familyTreeJson = json_encode($immediateFamily, JSON_UNESCAPED_UNICODE);
            $familyDetailsJson = json_encode($parentsSiblings, JSON_UNESCAPED_UNICODE);
            $stmt->bind_param('ssssssssssssssssssdssss', 
                $membershipNumber, $fullName, $fatherName, $grandfatherName, $surname, $nativePlace, $cnic, $residentialAddress, $cityCountry, $dateOfBirth, $mobile1, $mobile2, $email, $occupation, $maritalStatus, $fatherOrBrotherName, $fatherOrBrotherMembershipNo, $membershipType, $amount, $joinDate, $familyTreeJson, $familyDetailsJson, $notes
            );
            
            if ($stmt->execute()) {
                $message = 'Membership application submitted successfully.';
            } else {
                $error = 'Database error: ' . $mysqli->error;
            }
        }
    }
}

$token = csrfToken();
?>

<style>
    .section-title { 
        background: #f1f5f9; 
        padding: 10px 15px; 
        font-weight: 700; 
        margin: 30px 0 20px; 
        border-left: 4px solid var(--primary);
        font-size: 14px;
        text-transform: uppercase;
    }
    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    label { font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--secondary); }
    input, select, textarea { 
        padding: 10px 12px; 
        border: 1px solid var(--border); 
        border-radius: 6px; 
        font-size: 14px; 
        outline: none; 
        transition: border-color 0.2s;
    }
    input:focus { border-color: var(--primary); }
    .full-width { grid-column: span 2; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
    th, td { border: 1px solid var(--border); padding: 10px; text-align: left; }
    th { background: #f8fafc; font-weight: 600; }
    .btn-form { 
        background: var(--primary); 
        color: white; 
        border: none; 
        padding: 12px 24px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        margin-top: 30px;
    }
    .btn-add { background: #10b981; padding: 6px 12px; font-size: 12px; margin-top: 10px; }
    .cnic-search-btn { background: #e2e8f0; border: none; padding: 4px 8px; border-radius: 4px; font-size: 10px; cursor: pointer; margin-left: 5px; }
    .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .signature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-top: 50px; text-align: center; font-size: 12px; }
    .sig-line { border-top: 1px solid #333; padding-top: 10px; margin-top: 40px; }
    @media (max-width: 768px) {
        .grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
        .signature-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>New Membership</h1>
        <p>Complete the applicant information form to register a new member.</p>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 20px 25px -5px rgba(0,0,0,0.05);">
    <div style="background: linear-gradient(135deg, #111827 0%, #1e293b 100%); padding: 48px 40px; text-align: center; color: #fff;">
        <img src="assets/logo.png" alt="Logo" style="width: 80px; margin-bottom: 20px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));">
        <h2 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.02em;">BOMBAY HALAI MEMON JAMAT</h2>
        <p style="margin: 8px 0 0; font-size: 15px; opacity: 0.7; font-weight: 500;">Official Membership Registration Portal</p>
    </div>

    <div style="padding: 48px;">
        <?php if ($message): ?><div class="alert alert-success" style="border-radius: 12px; margin-bottom: 32px; font-weight: 500;"><?=$message?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error" style="border-radius: 12px; margin-bottom: 32px; font-weight: 500;"><?=$error?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=$token?>">
        
        <div class="grid">
            <div class="form-group full-width">
                <label>New Membership Number:</label>
                <input type="text" name="membership_number" placeholder="Enter number">
            </div>
            
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="full_name">
            </div>
            <div class="form-group">
                <label>Father's Name:</label>
                <input type="text" name="father_name">
            </div>
            
            <div class="form-group">
                <label>Grand Father's Name:</label>
                <input type="text" name="grandfather_name">
            </div>
            <div class="form-group">
                <label>Surname:</label>
                <input type="text" name="surname">
            </div>
            
            <div class="form-group">
                <label>Native Place:</label>
                <input type="text" name="native_place">
            </div>
            <div class="form-group">
                <label>CNIC No:</label>
                <input type="text" name="cnic" class="cnic-mask" placeholder="xxxxx-xxxxxxx-x">
            </div>
            
            <div class="form-group">
                <label>Date of Birth:</label>
                <input type="date" name="date_of_birth">
            </div>
            <div class="form-group">
                <label>Residential Address:</label>
                <input type="text" name="residential_address">
            </div>
            
            <div class="form-group">
                <label>City / Country:</label>
                <input type="text" name="city_country">
            </div>
            <div class="form-group">
                <label>Mobile Number-1:</label>
                <input type="text" name="mobile_1">
            </div>
            
            <div class="form-group">
                <label>Mobile Number-2:</label>
                <input type="text" name="mobile_2">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email">
            </div>
            
            <div class="form-group">
                <label>Occupation (Job / Business / Unemployed):</label>
                <input type="text" name="occupation">
            </div>
            <div class="form-group">
                <label>Marital Status:</label>
                <select name="marital_status">
                    <option value="">Select Status</option>
                    <option value="Married">Married</option>
                    <option value="Unmarried">Unmarried</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Divorced">Divorced</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Father or Brother Membership Name:</label>
                <input type="text" name="father_or_brother_name">
            </div>
            <div class="form-group">
                <label>Father or Brother Membership No:</label>
                <input type="text" name="father_or_brother_membership_no">
            </div>
        </div>

        <div class="section-title">SPOUSE & CHILDREN (IMMEDIATE FAMILY TREE)</div>
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px;">
            <table id="familyTreeTable" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>CNIC</th>
                        <th>Date of Birth</th>
                        <th>Contact</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="family_tree_name[]" style="width:100%"></td>
                        <td><input type="text" name="family_tree_relation[]" style="width:100%"></td>
                        <td>
                            <div style="display:flex;align-items:center;">
                                <input type="text" name="family_tree_cnic[]" class="cnic-input cnic-mask" style="width:100%">
                                <button type="button" class="cnic-search-btn" title="Search by CNIC">🔍</button>
                            </div>
                        </td>
                        <td><input type="date" name="family_tree_dob[]" style="width:100%"></td>
                        <td><input type="text" name="family_tree_contact[]" style="width:100%"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-add btn-form" onclick="addRow('familyTreeTable')">+ Add Row</button>

        <div class="section-title">PARENTS & SIBLINGS INFORMATION</div>
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px;">
            <table id="parentsSiblingsTable" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>Status</th>
                        <th>CNIC</th>
                        <th>Contact</th>
                        <th>Membership Info</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><input type="text" name="parents_name[]" style="width:100%"></td>
                        <td><input type="text" name="parents_relation[]" style="width:100%"></td>
                        <td>
                            <select name="parents_status[]" style="width:100%">
                                <option value="Alive">Alive</option>
                                <option value="Deceased">Deceased</option>
                            </select>
                        </td>
                        <td><input type="text" name="parents_cnic[]" class="cnic-input cnic-mask" style="width:100%"></td>
                        <td><input type="text" name="parents_contact[]" style="width:100%"></td>
                        <td><input type="text" name="parents_membership_info[]" style="width:100%"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-add btn-form" onclick="addRow('parentsSiblingsTable')">+ Add Row</button>

        <div style="text-align: right; margin-top: 50px;">
            <button type="submit" class="btn-form" style="padding: 16px 48px; font-size: 16px; border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(37, 99, 235, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(37, 99, 235, 0.3)'">Submit Application</button>
        </div>
    </form>
</div>

<script>
    function formatCNIC(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 13) val = val.substring(0, 13);
        
        let formatted = '';
        if (val.length > 0) {
            formatted = val.substring(0, 5);
            if (val.length > 5) {
                formatted += '-' + val.substring(5, 12);
                if (val.length > 12) {
                    formatted += '-' + val.substring(12, 13);
                }
            }
        }
        input.value = formatted;
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cnic-mask')) {
            formatCNIC(e.target);
        }
    });

    function addRow(tableId) {
        const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
        const rowCount = table.rows.length;
        const row = table.insertRow(rowCount);
        
        const cell1 = row.insertCell(0);
        cell1.innerHTML = rowCount + 1;
        
        const cell2 = row.insertCell(1);
        cell2.innerHTML = `<input type="text" name="${tableId === 'familyTreeTable' ? 'family_tree_name[]' : 'parents_name[]'}" style="width:100%">`;
        
        const cell3 = row.insertCell(2);
        cell3.innerHTML = `<input type="text" name="${tableId === 'familyTreeTable' ? 'family_tree_relation[]' : 'parents_relation[]'}" style="width:100%">`;
        
        if (tableId === 'familyTreeTable') {
            const cell4 = row.insertCell(3);
            cell4.innerHTML = `
                <div style="display:flex;align-items:center;">
                    <input type="text" name="family_tree_cnic[]" class="cnic-input cnic-mask" style="width:100%">
                    <button type="button" class="cnic-search-btn" title="Search by CNIC">🔍</button>
                </div>`;
            
            const cell5 = row.insertCell(4);
            cell5.innerHTML = `<input type="date" name="family_tree_dob[]" style="width:100%">`;
            
            const cell6 = row.insertCell(5);
            cell6.innerHTML = `<input type="text" name="family_tree_contact[]" style="width:100%">`;
            
            const cell7 = row.insertCell(6);
            cell7.innerHTML = `<button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red;border:none;background:none;cursor:pointer;">✕</button>`;
        } else {
            const cell4 = row.insertCell(3);
            cell4.innerHTML = `
                <select name="parents_status[]" style="width:100%">
                    <option value="Alive">Alive</option>
                    <option value="Deceased">Deceased</option>
                </select>`;
            
            const cell5 = row.insertCell(4);
            cell5.innerHTML = `<input type="text" name="parents_cnic[]" class="cnic-input cnic-mask" style="width:100%">`;
            
            const cell6 = row.insertCell(5);
            cell6.innerHTML = `<input type="text" name="parents_contact[]" style="width:100%">`;
            
            const cell7 = row.insertCell(6);
            cell7.innerHTML = `<input type="text" name="parents_membership_info[]" style="width:100%">`;
            
            const cell8 = row.insertCell(7);
            cell8.innerHTML = `<button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red;border:none;background:none;cursor:pointer;">✕</button>`;
        }
        
        attachSearchHandlers();
    }

    function attachSearchHandlers() {
        document.querySelectorAll('.cnic-search-btn').forEach(btn => {
            btn.onclick = function() {
                const row = this.closest('tr');
                const cnicInput = row.querySelector('.cnic-input');
                const cnic = cnicInput.value.trim();
                
                if (!cnic) {
                    alert('Please enter a CNIC first');
                    return;
                }
                
                this.innerHTML = '⌛';
                fetch(`api/search_member.php?cnic=${cnic}`)
                    .then(res => res.json())
                    .then(res => {
                        this.innerHTML = '🔍';
                        if (res.success) {
                            row.querySelector('input[name*="name[]"]').value = res.data.full_name;
                            const dobInput = row.querySelector('input[name*="dob[]"]');
                            if (dobInput) dobInput.value = res.data.date_of_birth;
                            const contactInput = row.querySelector('input[name*="contact[]"]');
                            if (contactInput) contactInput.value = res.data.mobile_1;
                        } else {
                            alert(res.message || 'Member not found');
                        }
                    })
                    .catch(err => {
                        this.innerHTML = '🔍';
                        alert('Error searching member');
                    });
            };
        });
    }
    
    window.onload = attachSearchHandlers;
</script>

<?php require_once 'includes/footer.php'; ?>



