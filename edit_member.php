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
                        'cnic' => trim($_POST['parents_cnic'][$i] ?? ''),
                        'contact' => trim($_POST['parents_contact'][$i] ?? ''),
                    ];
                }
            }

            $familyTreeJson = json_encode($immediateFamily, JSON_UNESCAPED_UNICODE);
            $familyDetailsJson = json_encode($parentsSiblings, JSON_UNESCAPED_UNICODE);

            $updateStmt = $mysqli->prepare("UPDATE members SET membership_number=?, full_name=?, father_name=?, grandfather_name=?, surname=?, native_place=?, cnic=?, residential_address=?, city_country=?, date_of_birth=?, mobile_1=?, mobile_2=?, email=?, occupation=?, marital_status=?, father_or_brother_name=?, father_or_brother_membership_no=?, family_tree=?, family_details=?, notes=? WHERE id=?");
            
            $updateStmt->bind_param('ssssssssssssssssssssi', 
                $_POST['membership_number'], $fullName, $_POST['father_name'], $_POST['grandfather_name'], $_POST['surname'], $_POST['native_place'], $cnic, $_POST['residential_address'], $_POST['city_country'], $_POST['date_of_birth'], $_POST['mobile_1'], $_POST['mobile_2'], $_POST['email'], $_POST['occupation'], $_POST['marital_status'], $_POST['father_or_brother_name'], $_POST['father_or_brother_membership_no'], $familyTreeJson, $familyDetailsJson, $_POST['notes'], $id
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

<div class="page-header" style="margin-bottom: 40px;">
    <h1 style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Edit Member Record</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Updating information for <strong><?= htmlspecialchars($member['full_name']) ?></strong></p>
</div>

<?php if ($message): ?>
    <div style="background: #ecfdf5; color: #065f46; padding: 20px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 30px; font-weight: 600;">✅ <?= $message ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #fef2f2; color: #991b1b; padding: 20px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 30px; font-weight: 600;">❌ <?= $error ?></div>
<?php endif; ?>

<form method="POST" class="card" style="padding: 48px;">
    <input type="hidden" name="csrf_token" value="<?= $token ?>">
    
    <div class="section-title">Personal Information</div>
    <div class="grid">
        <div class="form-group">
            <label>Membership Number</label>
            <input type="text" name="membership_number" value="<?= htmlspecialchars($member['membership_number']) ?>">
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required value="<?= htmlspecialchars($member['full_name']) ?>">
        </div>
        <div class="form-group">
            <label>Father's Name</label>
            <input type="text" name="father_name" value="<?= htmlspecialchars($member['father_name']) ?>">
        </div>
        <div class="form-group">
            <label>Grandfather's Name</label>
            <input type="text" name="grandfather_name" value="<?= htmlspecialchars($member['grandfather_name']) ?>">
        </div>
        <div class="form-group">
            <label>Surname / Cast</label>
            <input type="text" name="surname" value="<?= htmlspecialchars($member['surname']) ?>">
        </div>
        <div class="form-group">
            <label>Native Place</label>
            <input type="text" name="native_place" value="<?= htmlspecialchars($member['native_place']) ?>">
        </div>
        <div class="form-group">
            <label>CNIC Number</label>
            <input type="text" name="cnic" required value="<?= htmlspecialchars($member['cnic']) ?>">
        </div>
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" value="<?= $member['date_of_birth'] ?>">
        </div>
    </div>

    <div class="section-title">Contact & Communication</div>
    <div class="grid">
        <div class="form-group">
            <label>Mobile 1 (Primary)</label>
            <input type="text" name="mobile_1" value="<?= htmlspecialchars($member['mobile_1']) ?>">
        </div>
        <div class="form-group">
            <label>Mobile 2</label>
            <input type="text" name="mobile_2" value="<?= htmlspecialchars($member['mobile_2']) ?>">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>">
        </div>
        <div class="form-group">
            <label>Occupation</label>
            <input type="text" name="occupation" value="<?= htmlspecialchars($member['occupation']) ?>">
        </div>
        <div class="form-group" style="grid-column: span 2;">
            <label>Residential Address</label>
            <input type="text" name="residential_address" value="<?= htmlspecialchars($member['residential_address']) ?>">
        </div>
        <div class="form-group">
            <label>City / Country</label>
            <input type="text" name="city_country" value="<?= htmlspecialchars($member['city_country']) ?>">
        </div>
        <div class="form-group">
            <label>Marital Status</label>
            <select name="marital_status" onchange="toggleFamilyFields(this.value)">
                <option value="Single" <?= $member['marital_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                <option value="Married" <?= $member['marital_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                <option value="Widow" <?= $member['marital_status'] == 'Widow' ? 'selected' : '' ?>>Widow</option>
                <option value="Divorced" <?= $member['marital_status'] == 'Divorced' ? 'selected' : '' ?>>Divorced</option>
            </select>
        </div>
    </div>

    <div class="section-title">Organizational Details</div>
    <div class="grid">
        <div class="form-group">
            <label>Father or Brother Name</label>
            <input type="text" name="father_or_brother_name" value="<?= htmlspecialchars($member['father_or_brother_name']) ?>">
        </div>
        <div class="form-group">
            <label>Father or Brother Membership No</label>
            <input type="text" name="father_or_brother_membership_no" value="<?= htmlspecialchars($member['father_or_brother_membership_no']) ?>">
        </div>
    </div>

    <div id="spouse-children-section" style="<?= $member['marital_status'] == 'Married' ? '' : 'display:none;' ?>">
        <div class="section-title">Spouse & Children Information</div>
        <div id="spouse-children-rows">
            <?php foreach ($familyTree as $f): ?>
                <div class="grid" style="margin-bottom: 20px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div class="form-group"><label>Full Name</label><input type="text" name="family_tree_name[]" value="<?= htmlspecialchars($f['name']) ?>"></div>
                    <div class="form-group"><label>Relation</label><input type="text" name="family_tree_relation[]" value="<?= htmlspecialchars($f['relation']) ?>"></div>
                    <div class="form-group"><label>CNIC (Optional)</label><input type="text" name="family_tree_cnic[]" value="<?= htmlspecialchars($f['cnic'] ?? '') ?>"></div>
                    <div class="form-group"><label>Date of Birth</label><input type="date" name="family_tree_dob[]" value="<?= $f['dob'] ?? '' ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addRow('spouse-children-rows', 'family_tree')" class="btn-primary" style="background: var(--secondary); margin-bottom: 30px; font-size: 0.85rem;">+ Add Spouse/Child</button>
    </div>

    <div class="section-title">Parents & Siblings Information</div>
    <div id="parents-siblings-rows">
        <?php foreach ($parentsSiblings as $p): ?>
            <div class="grid" style="margin-bottom: 20px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="form-group"><label>Full Name</label><input type="text" name="parents_name[]" value="<?= htmlspecialchars($p['name']) ?>"></div>
                <div class="form-group"><label>Relation</label><input type="text" name="parents_relation[]" value="<?= htmlspecialchars($p['relation']) ?>"></div>
                <div class="form-group"><label>CNIC</label><input type="text" name="parents_cnic[]" value="<?= htmlspecialchars($p['cnic'] ?? '') ?>"></div>
                <div class="form-group"><label>Contact No</label><input type="text" name="parents_contact[]" value="<?= htmlspecialchars($p['contact'] ?? '') ?>"></div>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" onclick="addRow('parents-siblings-rows', 'parents')" class="btn-primary" style="background: var(--secondary); margin-bottom: 30px; font-size: 0.85rem;">+ Add Parent/Sibling</button>

    <div class="section-title">Additional Notes</div>
    <div class="form-group">
        <textarea name="notes" rows="4"><?= htmlspecialchars($member['notes']) ?></textarea>
    </div>

    <div style="margin-top: 50px; display: flex; justify-content: flex-end; gap: 20px;">
        <a href="members_list.php" class="btn-primary" style="background: transparent; color: var(--secondary); border: 1px solid var(--border);">Cancel</a>
        <button type="submit" class="btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">Update Record</button>
    </div>
</form>

<script>
function toggleFamilyFields(status) {
    const section = document.getElementById('spouse-children-section');
    if (status === 'Married') {
        section.style.display = 'block';
        if (document.getElementById('spouse-children-rows').children.length === 0) {
            addRow('spouse-children-rows', 'family_tree');
        }
    } else {
        section.style.display = 'none';
    }
}

function addRow(containerId, type) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'grid';
    div.style.marginBottom = '20px';
    div.style.background = '#f8fafc';
    div.style.padding = '20px';
    div.style.borderRadius = '12px';
    div.style.border = '1px solid #e2e8f0';
    
    if (type === 'family_tree') {
        div.innerHTML = `
            <div class="form-group"><label>Full Name</label><input type="text" name="family_tree_name[]"></div>
            <div class="form-group"><label>Relation</label><input type="text" name="family_tree_relation[]"></div>
            <div class="form-group"><label>CNIC (Optional)</label><input type="text" name="family_tree_cnic[]"></div>
            <div class="form-group"><label>Date of Birth</label><input type="date" name="family_tree_dob[]"></div>
        `;
    } else {
        div.innerHTML = `
            <div class="form-group"><label>Full Name</label><input type="text" name="parents_name[]"></div>
            <div class="form-group"><label>Relation</label><input type="text" name="parents_relation[]"></div>
            <div class="form-group"><label>CNIC</label><input type="text" name="parents_cnic[]"></div>
            <div class="form-group"><label>Contact No</label><input type="text" name="parents_contact[]"></div>
        `;
    }
    container.appendChild(div);
}
</script>

<?php require_once 'includes/footer.php'; ?>
