<?php
require_once 'includes/header.php';

$search = $_GET['query'] ?? '';
$member = null;

if (!empty($search)) {
    $stmt = $mysqli->prepare('SELECT * FROM members WHERE cnic = ? OR membership_number = ? LIMIT 1');
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Family Tree Search</h1>
        <p>Search by CNIC or Membership Number to view complete family details.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 32px;">
    <h2 style="margin: 0 0 16px; font-size: 1.25rem;">Search Member Directory</h2>
    <form method="GET" style="display: flex; gap: 16px; align-items: center;">
        <div style="flex: 1; position: relative;">
            <input type="text" name="query" value="<?= htmlspecialchars($search) ?>" class="cnic-mask" placeholder="Enter CNIC or Membership Number..." style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; outline: none; transition: border-color 0.2s;">
        </div>
        <button type="submit" class="btn-form" style="margin-top: 0; padding: 14px 32px; border-radius: 12px; font-weight: 700;">Find Member</button>
    </form>
</div>

<?php if ($search && !$member): ?>
    <div class="card" style="text-align: center; padding: 60px;">
        <div style="font-size: 3rem; margin-bottom: 16px;">🔍</div>
        <h3 style="margin: 0; color: var(--text);">No Records Found</h3>
        <p style="color: var(--text-muted); margin-top: 8px;">We couldn't find any member matching "<?= htmlspecialchars($search) ?>".</p>
        <a href="membership.php" style="display: inline-block; margin-top: 24px; color: var(--primary); text-decoration: none; font-weight: 600;">Add New Member instead?</a>
    </div>
<?php elseif ($member): ?>
    <div class="card" id="printableArea">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 32px; margin-bottom: 40px;">
            <div style="display: flex; align-items: center; gap: 24px;">
                <img src="assets/logo.png" alt="Logo" style="width: 80px;">
                <div>
                    <h1 style="margin: 0; font-size: 1.75rem; font-weight: 800; color: var(--text);"><?= htmlspecialchars($member['full_name']) ?></h1>
                    <div style="display: flex; gap: 16px; margin-top: 4px;">
                        <span style="font-size: 0.9rem; color: var(--text-muted);"><strong>ID:</strong> <?= htmlspecialchars($member['membership_number'] ?: 'Pending') ?></span>
                        <span style="font-size: 0.9rem; color: var(--text-muted);"><strong>CNIC:</strong> <?= htmlspecialchars($member['cnic']) ?></span>
                    </div>
                </div>
            </div>
            <button onclick="window.print()" class="btn-form" style="margin-top: 0; background: #fff; color: var(--text); border: 1px solid var(--border); padding: 12px 24px;">Print Certificate</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-bottom: 48px; background: #f9fafb; padding: 32px; border-radius: 16px;">
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Father's Name</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($member['father_name'] ?: '-') ?></div>
            </div>
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Surname</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($member['surname'] ?: '-') ?></div>
            </div>
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Date of Birth</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($member['date_of_birth'] ?: '-') ?></div>
            </div>
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Primary Mobile</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($member['mobile_1'] ?: '-') ?></div>
            </div>
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Native Place</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($member['native_place'] ?: '-') ?></div>
            </div>
            <div class="profile-item">
                <label style="display: block; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">Join Date</label>
                <div style="font-size: 1.1rem; font-weight: 600;"><?= date('F d, Y', strtotime($member['join_date'])) ?></div>
            </div>
        </div>

        <div class="section-title">Immediate Family Tree</div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Member Name</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Relation</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">CNIC</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Birth Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $family = json_decode($member['family_tree'], true) ?: [];
                if (empty($family)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 32px; color: var(--text-muted);">No immediate family records attached.</td></tr>
                <?php else: foreach ($family as $f): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 16px 12px; font-weight: 600;"><?= htmlspecialchars($f['name']) ?></td>
                        <td style="padding: 16px 12px;"><?= htmlspecialchars($f['relation']) ?></td>
                        <td style="padding: 16px 12px;"><?= htmlspecialchars($f['cnic']) ?></td>
                        <td style="padding: 16px 12px;"><?= htmlspecialchars($f['dob']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="section-title">Parents & Sibling Directory</div>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Full Name</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Relation</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Status</th>
                    <th style="padding: 12px; font-weight: 700; color: var(--text-muted);">Membership No</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $parents = json_decode($member['family_details'], true) ?: [];
                if (empty($parents)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 32px; color: var(--text-muted);">No parental or sibling records found.</td></tr>
                <?php else: foreach ($parents as $p): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 16px 12px; font-weight: 600;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding: 16px 12px;"><?= htmlspecialchars($p['relation']) ?></td>
                        <td style="padding: 16px 12px;">
                            <span class="badge" style="background: <?= $p['status'] == 'Alive' ? '#dcfce7' : '#f3f4f6' ?>; color: <?= $p['status'] == 'Alive' ? '#166534' : '#6b7280' ?>;">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 16px 12px;"><?= htmlspecialchars($p['membership_info']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
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
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cnic-mask')) formatCNIC(e.target);
    });
</script>

<style>
    @media print {
        .sidebar, .menu-toggle, form, .page-header, .btn-form { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .section-title { -webkit-print-color-adjust: exact; background-color: #f1f5f9 !important; border-left: 4px solid var(--primary) !important; }
    }
    .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
</style>

<?php require_once 'includes/footer.php'; ?>
