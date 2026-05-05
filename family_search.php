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

<div class="page-header" style="margin-bottom: 40px;">
    <h1 style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;">Family Registry Search</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">Access complete family trees and historical records by ID or CNIC.</p>
</div>

<div class="card" style="margin-bottom: 32px;">
    <form method="GET" style="display: flex; gap: 16px; align-items: center;">
        <div style="flex: 1;">
            <input type="text" name="query" value="<?= htmlspecialchars($search) ?>" placeholder="Enter CNIC (xxxxx-xxxxxxx-x) or Membership Number..." style="width: 100%; padding: 14px 20px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; outline: none; background: #f8fafc;">
        </div>
        <button type="submit" class="btn-primary" style="padding: 14px 32px;">Search Registry</button>
    </form>
</div>

<?php if ($search && !$member): ?>
    <div class="card" style="text-align: center; padding: 60px;">
        <div style="font-size: 3rem; margin-bottom: 16px;">🔍</div>
        <h2 style="margin: 0; color: var(--text);">Record Not Found</h2>
        <p style="color: var(--text-muted); margin-top: 8px;">No member exists with identity "<?= htmlspecialchars($search) ?>".</p>
        <a href="membership.php" class="btn-primary" style="margin-top: 24px; background: var(--secondary);">Register New Member</a>
    </div>
<?php elseif ($member): ?>
    <div class="card" id="printableArea" style="padding: 60px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 50px; border-bottom: 2px solid #f1f5f9; padding-bottom: 40px;">
            <div style="display: flex; align-items: center; gap: 30px;">
                <img src="assets/logo.png" alt="Logo" style="width: 100px;">
                <div>
                    <h1 style="margin: 0; font-size: 2.2rem; font-weight: 800; color: var(--text);"><?= htmlspecialchars($member['full_name']) ?></h1>
                    <div style="display: flex; gap: 20px; margin-top: 8px;">
                        <span style="font-size: 1rem; color: var(--text-muted);"><strong>ID:</strong> <?= htmlspecialchars($member['membership_number'] ?: 'N/A') ?></span>
                        <span style="font-size: 1rem; color: var(--text-muted);"><strong>CNIC:</strong> <?= htmlspecialchars($member['cnic']) ?></span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="window.print()" class="btn-primary" style="background: #fff; color: var(--text); border: 1px solid var(--border);">🖨️ Print Record</button>
                <a href="edit_member.php?id=<?= $member['id'] ?>" class="btn-primary">✏️ Edit Details</a>
            </div>
        </div>

        <div class="grid" style="margin-bottom: 60px; background: #f8fafc; padding: 40px; border-radius: 20px;">
            <div class="form-group">
                <label>Father's Name</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($member['father_name'] ?: '-') ?></div>
            </div>
            <div class="form-group">
                <label>Surname / Caste</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($member['surname'] ?: '-') ?></div>
            </div>
            <div class="form-group">
                <label>Native Place</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($member['native_place'] ?: '-') ?></div>
            </div>
            <div class="form-group">
                <label>Join Date</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= date('d M, Y', strtotime($member['join_date'])) ?></div>
            </div>
            <div class="form-group">
                <label>Membership Type</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><span style="color: var(--primary);"><?= htmlspecialchars($member['membership_type']) ?></span></div>
            </div>
            <div class="form-group">
                <label>Primary Mobile</label>
                <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($member['mobile_1'] ?: '-') ?></div>
            </div>
        </div>

        <div class="section-title">Immediate Family Tree (Spouse & Children)</div>
        <div style="overflow-x: auto; margin-bottom: 50px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 16px;">Full Name</th>
                        <th style="padding: 16px;">Relation</th>
                        <th style="padding: 16px;">CNIC</th>
                        <th style="padding: 16px;">Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $family = json_decode($member['family_tree'], true) ?: [];
                    if (empty($family)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);">No immediate family data recorded.</td></tr>
                    <?php else: foreach ($family as $f): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px; font-weight: 700;"><?= htmlspecialchars($f['name']) ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($f['relation']) ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($f['cnic'] ?: '-') ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($f['dob'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title">Parents & Sibling Registry</div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 16px;">Full Name</th>
                        <th style="padding: 16px;">Relation</th>
                        <th style="padding: 16px;">CNIC</th>
                        <th style="padding: 16px;">Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $parents = json_decode($member['family_details'], true) ?: [];
                    if (empty($parents)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);">No parental or sibling data recorded.</td></tr>
                    <?php else: foreach ($parents as $p): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px; font-weight: 700;"><?= htmlspecialchars($p['name']) ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($p['relation']) ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($p['cnic'] ?: '-') ?></td>
                            <td style="padding: 16px;"><?= htmlspecialchars($p['contact'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($member['notes']): ?>
        <div class="section-title">Administrative Notes</div>
        <div style="padding: 24px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; color: #92400e; line-height: 1.8;">
            <?= nl2br(htmlspecialchars($member['notes'])) ?>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<style>
@media print {
    /* Hide all navigation and UI chrome */
    .sidebar,
    .top-bar,
    .menu-toggle,
    form,
    .btn-primary,
    button,
    #sb-overlay {
        display: none !important;
    }

    /* Reset layout - full page width */
    body {
        display: block !important;
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Outfit', Arial, sans-serif !important;
        color: #000 !important;
        font-size: 12pt;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 15mm 15mm 10mm 15mm !important;
        width: 100% !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
        page-break-inside: avoid;
    }

    /* Tables - clean borders */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 10pt !important;
        margin-bottom: 15px !important;
    }

    th, td {
        border: 1px solid #ccc !important;
        padding: 8px 10px !important;
        text-align: left !important;
    }

    thead tr {
        background: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Section titles */
    .section-title {
        background: #eef2ff !important;
        border-left: 5px solid #2563eb !important;
        padding: 8px 12px !important;
        margin: 15px 0 10px !important;
        font-size: 9pt !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        page-break-after: avoid;
    }

    /* Member header block */
    #printableArea > div:first-child {
        border-bottom: 2px solid #000 !important;
        padding-bottom: 10px !important;
        margin-bottom: 15px !important;
    }

    h1 { font-size: 18pt !important; }

    /* Page settings */
    @page {
        size: A4 portrait;
        margin: 15mm;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
