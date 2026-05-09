<?php
require_once 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $data = [
            'membership_number' => trim($_POST['membership_number'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'father_name' => trim($_POST['father_name'] ?? ''),
            'grandfather_name' => trim($_POST['grandfather_name'] ?? ''),
            'surname' => trim($_POST['surname'] ?? ''),
            'native_place' => trim($_POST['native_place'] ?? ''),
            'cnic' => trim($_POST['cnic'] ?? ''),
            'residential_address' => trim($_POST['residential_address'] ?? ''),
            'city_country' => trim($_POST['city_country'] ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
            'mobile_1' => trim($_POST['mobile_1'] ?? ''),
            'mobile_2' => trim($_POST['mobile_2'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'occupation' => trim($_POST['occupation'] ?? ''),
            'marital_status' => trim($_POST['marital_status'] ?? ''),
            'join_date' => date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        // Family Tree (Spouse/Children)
        $ft = [];
        if (isset($_POST['ft_name'])) {
            foreach ($_POST['ft_name'] as $i => $n) {
                if ($n) $ft[] = [
                    'name'     => $n,
                    'relation' => $_POST['ft_rel'][$i],
                    'cnic'     => $_POST['ft_cnic'][$i],
                    'dob'      => $_POST['ft_dob'][$i] ?? '',
                    'status'   => $_POST['ft_status'][$i]
                ];
            }
        }

        // Parents & Siblings
        $ps = [];
        if (isset($_POST['ps_name'])) {
            foreach ($_POST['ps_name'] as $i => $n) {
                if ($n) $ps[] = [
                    'name' => $n,
                    'relation' => $_POST['ps_rel'][$i],
                    'status' => $_POST['ps_status'][$i],
                    'cnic' => $_POST['ps_cnic'][$i],
                    'contact' => $_POST['ps_contact'][$i],
                    'dob' => $_POST['ps_dob'][$i] ?? ''
                ];
            }
        }

        if (!$data['full_name'] || !$data['cnic']) {
            $error = 'Name and CNIC are required.';
        } else {
            $sql = "INSERT INTO members (membership_number, full_name, father_name, grandfather_name, surname, native_place, cnic, residential_address, city_country, date_of_birth, mobile_1, mobile_2, email, occupation, marital_status, join_date, family_tree, family_details, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $ftJ = json_encode($ft);
            $psJ = json_encode($ps);
            $stmt->bind_param('sssssssssssssssssss', $data['membership_number'], $data['full_name'], $data['father_name'], $data['grandfather_name'], $data['surname'], $data['native_place'], $data['cnic'], $data['residential_address'], $data['city_country'], $data['date_of_birth'], $data['mobile_1'], $data['mobile_2'], $data['email'], $data['occupation'], $data['marital_status'], $data['join_date'], $ftJ, $psJ, $data['notes']);
            if ($stmt->execute()) $message = 'Member registered successfully!';
            else $error = 'Error: ' . $mysqli->error;
        }
    }
}
$token = csrfToken();
?>

<div class="card" style="max-width: 1400px; margin: 0 auto; border-top: 6px solid var(--primary);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px;">
        <div>
            <h1 style="font-weight: 800; font-size: 2.2rem; letter-spacing: -0.04em;">Member Registration</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Complete all personal and family details below.</p>
        </div>
        <div style="padding: 15px 25px; background: var(--primary-light); border-radius: 15px; color: var(--primary); font-weight: 800;">ID: AUTO-GEN</div>
    </div>
    
    <?php if ($message): ?><div style="background: var(--success); color: #fff; padding: 20px; border-radius: 15px; margin-bottom: 30px; font-weight: 700; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">✅ <?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div style="background: var(--danger); color: #fff; padding: 20px; border-radius: 15px; margin-bottom: 30px; font-weight: 700; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2);">❌ <?= $error ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        
        <div class="section-title"><i style="margin-right:10px">👤</i> Personal Details</div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required placeholder="Enter Full Name"></div>
            <div class="form-group"><label>CNIC Number *</label><input type="text" name="cnic" required placeholder="xxxxx-xxxxxxx-x"></div>
            <div class="form-group"><label>Father's Name</label><input type="text" name="father_name" placeholder="Father's Name"></div>
            
            <div class="form-group"><label>Grandfather's Name</label><input type="text" name="grandfather_name" placeholder="Grandfather's Name"></div>
            <div class="form-group"><label>Surname / Caste</label><input type="text" name="surname" placeholder="e.g. Shaikh, Qureshi"></div>
            <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth"></div>
            
            <div class="form-group"><label>Mobile 1 (Primary)</label><input type="text" name="mobile_1" placeholder="03xx-xxxxxxx"></div>
            <div class="form-group"><label>Mobile 2</label><input type="text" name="mobile_2" placeholder="03xx-xxxxxxx"></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="email@example.com"></div>
            
            <div class="form-group"><label>Occupation / Employment</label><input type="text" name="occupation" placeholder="Current Work/Job"></div>
            <div class="form-group"><label>Native Place</label><input type="text" name="native_place" placeholder="Origin City"></div>
            <div class="form-group"><label>City / Country</label><input type="text" name="city_country" value="Karachi, Pakistan"></div>
        </div>

        <div class="form-group">
            <label>Residential Address</label>
            <textarea name="residential_address" rows="3" placeholder="Full Home Address"></textarea>
        </div>

        <div class="section-title"><i style="margin-right:10px">🏢</i> Organizational Info</div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <div class="form-group">
                <label>Marital Status</label>
                <select name="marital_status" onchange="document.getElementById('spouse_box').style.display = (this.value == 'Married' ? 'block' : 'none')">
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widow">Widow</option>
                    <option value="Divorced">Divorced</option>
                </select>
            </div>
            <div class="form-group"><label>Membership ID</label><input type="text" name="membership_number" placeholder="BHMJ-XXXX"></div>
        </div>

        <div id="spouse_box" style="display:none; margin-top: 20px; padding: 30px; background: #f8fafc; border-radius: 20px; border: 1px solid var(--border);">
            <div style="font-weight: 800; font-size: 1.1rem; margin-bottom: 20px; color: var(--primary);">Spouse & Children</div>
            <div id="ft-rows"></div>
            <button type="button" onclick="addFT()" class="btn-primary" style="background: var(--secondary); font-size: 0.85rem;">+ Add Family Member</button>
        </div>

        <div class="section-title"><i style="margin-right:10px">👨‍👩‍👦</i> Parents & Siblings Details</div>
        <div id="ps-rows"></div>
        <button type="button" onclick="addPS()" class="btn-primary" style="background: var(--secondary); font-size: 0.85rem; margin-bottom: 40px;">+ Add Parent/Sibling</button>

        <div class="form-group">
            <label>Special Notes</label>
            <textarea name="notes" rows="2" placeholder="Any extra information..."></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 50px;">
            <button type="submit" class="btn-primary" style="padding: 20px 80px; font-size: 1.2rem;">Complete Registration</button>
        </div>
    </form>
</div>

<script>
function addFT() {
    const div = document.createElement('div');
    div.style = "display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 20px; padding: 20px; background: #fff; border-radius: 15px; border: 1px solid var(--border);";
    div.innerHTML = `
        <div class="form-group" style="margin:0"><label>Name</label><input type="text" name="ft_name[]" placeholder="Name"></div>
        <div class="form-group" style="margin:0"><label>Relation</label><input type="text" name="ft_rel[]" placeholder="Spouse/Son/Daughter"></div>
        <div class="form-group" style="margin:0"><label>CNIC</label><input type="text" name="ft_cnic[]" placeholder="xxxxx-xxxxxxx-x"></div>
        <div class="form-group" style="margin:0"><label>Date of Birth</label><input type="date" name="ft_dob[]"></div>
        <div class="form-group" style="margin:0"><label>Status</label><select name="ft_status[]"><option value="Alive">Alive</option><option value="Deceased">Deceased</option></select></div>
    `;
    document.getElementById('ft-rows').appendChild(div);
}

function addPS() {
    const div = document.createElement('div');
    div.style = "display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; padding: 20px; background: #f8fafc; border-radius: 15px; border: 1px solid var(--border);";
    div.innerHTML = `
        <div class="form-group" style="margin:0"><label>Name</label><input type="text" name="ps_name[]" placeholder="Full Name"></div>
        <div class="form-group" style="margin:0"><label>Relation</label><input type="text" name="ps_rel[]" placeholder="e.g. Father, Brother"></div>
        <div class="form-group" style="margin:0"><label>Status</label><select name="ps_status[]"><option value="Alive">Alive</option><option value="Deceased">Deceased</option></select></div>
        <div class="form-group" style="margin:0"><label>CNIC</label><input type="text" name="ps_cnic[]" placeholder="xxxxx-xxxxxxx-x"></div>
        <div class="form-group" style="margin:0"><label>Contact</label><input type="text" name="ps_contact[]" placeholder="Mobile Number"></div>
        <div class="form-group" style="margin:0"><label>Date of Birth</label><input type="date" name="ps_dob[]"></div>
    `;
    document.getElementById('ps-rows').appendChild(div);
}
window.onload = () => { addPS(); };
</script>

<?php require_once 'includes/footer.php'; ?>
