<?php
require_once 'includes/header.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: members_list.php');
    exit;
}

$message = '';
$error = '';

// Fetch member data
$stmt = $mysqli->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    die('Member not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'operator'])) {
        die('Unauthorized');
    }
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $cnic = trim($_POST['cnic'] ?? '');
        
        if ($fullName === '' || $cnic === '') {
            $error = 'Full Name and CNIC are required.';
        } else {
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

            $familyTreeJson = json_encode($immediateFamily, JSON_UNESCAPED_UNICODE);
            $familyDetailsJson = json_encode($parentsSiblings, JSON_UNESCAPED_UNICODE);

            $updateStmt = $mysqli->prepare("UPDATE members SET membership_number=?, full_name=?, father_name=?, grandfather_name=?, surname=?, native_place=?, cnic=?, residential_address=?, city_country=?, date_of_birth=?, mobile_1=?, mobile_2=?, email=?, occupation=?, marital_status=?, father_or_brother_name=?, father_or_brother_membership_no=?, family_tree=?, family_details=? WHERE id=?");
            
            $updateStmt->bind_param('sssssssssssssssssssi', 
                $_POST['membership_number'], $fullName, $_POST['father_name'], $_POST['grandfather_name'], $_POST['surname'], $_POST['native_place'], $cnic, $_POST['residential_address'], $_POST['city_country'], $_POST['date_of_birth'], $_POST['mobile_1'], $_POST['mobile_2'], $_POST['email'], $_POST['occupation'], $_POST['marital_status'], $_POST['father_or_brother_name'], $_POST['father_or_brother_membership_no'], $familyTreeJson, $familyDetailsJson, $id
            );
            
            if ($updateStmt->execute()) {
                $message = 'Member details updated successfully.';
                // Refresh data
                $stmt->execute();
                $member = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Database error: ' . $mysqli->error;
            }
        }
    }
}

$token = csrfToken();
$familyTree = json_decode($member['family_tree'], true) ?: [];
$parentsSiblings = json_decode($member['family_details'], true) ?: [];
?>

<style>
    .section-title { background: #f1f5f9; padding: 10px 15px; font-weight: 700; margin: 30px 0 20px; border-left: 4px solid var(--primary); font-size: 14px; text-transform: uppercase; }
    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    label { font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--secondary); }
    input, select, textarea { padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.2s; }
    input:focus { border-color: var(--primary); }
    .full-width { grid-column: span 2; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
    th, td { border: 1px solid var(--border); padding: 10px; text-align: left; }
    th { background: #f8fafc; font-weight: 600; }
    .btn-form { background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 30px; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Edit Member Details</h1>
        <p>Updating record for: <strong><?= htmlspecialchars($member['full_name']) ?></strong></p>
    </div>
    <a href="members_list.php" style="text-decoration:none; color: var(--primary); font-weight: 600;">← Back to Directory</a>
</div>

<div class="card" style="padding: 48px;">
    <?php if ($message): ?><div class="alert alert-success" style="margin-bottom: 30px;"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error" style="margin-bottom: 30px;"><?= $error ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        
        <div class="grid">
            <div class="form-group full-width">
                <label>Membership Number:</label>
                <input type="text" name="membership_number" value="<?= htmlspecialchars($member['membership_number']) ?>">
            </div>
            
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>">
            </div>
            <div class="form-group">
                <label>Father's Name:</label>
                <input type="text" name="father_name" value="<?= htmlspecialchars($member['father_name']) ?>">
            </div>
            
            <div class="form-group">
                <label>Grand Father's Name:</label>
                <input type="text" name="grandfather_name" value="<?= htmlspecialchars($member['grandfather_name']) ?>">
            </div>
            <div class="form-group">
                <label>Surname:</label>
                <input type="text" name="surname" value="<?= htmlspecialchars($member['surname']) ?>">
            </div>
            
            <div class="form-group">
                <label>CNIC No:</label>
                <input type="text" name="cnic" class="cnic-mask" value="<?= htmlspecialchars($member['cnic']) ?>">
            </div>
            <div class="form-group">
                <label>Date of Birth:</label>
                <input type="date" name="date_of_birth" value="<?= htmlspecialchars($member['date_of_birth']) ?>">
            </div>
            
            <div class="form-group">
                <label>Residential Address:</label>
                <input type="text" name="residential_address" value="<?= htmlspecialchars($member['residential_address']) ?>">
            </div>
            <div class="form-group">
                <label>Mobile Number-1:</label>
                <input type="text" name="mobile_1" value="<?= htmlspecialchars($member['mobile_1']) ?>">
            </div>
        </div>

        <div class="section-title">Immediate Family Tree</div>
        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table id="familyTreeTable" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th width="50">S.No</th>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>CNIC</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($familyTree)): ?>
                        <tr><td>1</td><td><input type="text" name="family_tree_name[]" style="width:100%"></td><td><input type="text" name="family_tree_relation[]" style="width:100%"></td><td><input type="text" name="family_tree_cnic[]" class="cnic-mask" style="width:100%"></td><td></td></tr>
                    <?php else: foreach($familyTree as $i => $f): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><input type="text" name="family_tree_name[]" value="<?= htmlspecialchars($f['name']) ?>" style="width:100%"></td>
                            <td><input type="text" name="family_tree_relation[]" value="<?= htmlspecialchars($f['relation']) ?>" style="width:100%"></td>
                            <td><input type="text" name="family_tree_cnic[]" class="cnic-mask" value="<?= htmlspecialchars($f['cnic']) ?>" style="width:100%"></td>
                            <td><button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red;border:none;background:none;cursor:pointer;">✕</button></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn-add btn-form" onclick="addRow('familyTreeTable')" style="background: #10b981; padding: 6px 12px; font-size: 12px; margin-top: 0;">+ Add Row</button>

        <div style="text-align: right; margin-top: 40px;">
            <button type="submit" class="btn-form">Save Changes</button>
        </div>
    </form>
</div>

<script>
    // Include formatCNIC and addRow logic here as well
    function formatCNIC(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 13) val = val.substring(0, 13);
        let formatted = '';
        if (val.length > 0) {
            formatted = val.substring(0, 5);
            if (val.length > 5) {
                formatted += '-' + val.substring(5, 12);
                if (val.length > 12) formatted += '-' + val.substring(12, 13);
            }
        }
        input.value = formatted;
    }
    document.addEventListener('input', function(e) { if (e.target.classList.contains('cnic-mask')) formatCNIC(e.target); });

    function addRow(tableId) {
        const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
        const rowCount = table.rows.length;
        const row = table.insertRow(rowCount);
        row.innerHTML = `<td>${rowCount+1}</td><td><input type="text" name="family_tree_name[]" style="width:100%"></td><td><input type="text" name="family_tree_relation[]" style="width:100%"></td><td><input type="text" name="family_tree_cnic[]" class="cnic-mask" style="width:100%"></td><td><button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red;border:none;background:none;cursor:pointer;">✕</button></td>`;
    }
</script>

<?php require_once 'includes/footer.php'; ?>
