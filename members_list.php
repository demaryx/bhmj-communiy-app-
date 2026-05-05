<?php
require_once 'includes/header.php';

$search = $_GET['search'] ?? '';
$where = '';
if ($search) {
    $s = "%$search%";
    $where = " WHERE full_name LIKE ? OR cnic LIKE ? OR membership_number LIKE ?";
}

$query = "SELECT id, full_name, cnic, membership_number, mobile_1, join_date FROM members" . $where . " ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
if ($search) {
    $stmt->bind_param('sss', $s, $s, $s);
}
$stmt->execute();
$members = $stmt->get_result();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Member Directory</h1>
        <p>Search, view, and manage all registered organization members.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 32px;">
    <form method="GET" style="display: flex; gap: 16px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name, CNIC, or ID..." style="flex: 1; padding: 14px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
        <button type="submit" class="btn-form" style="margin-top: 0; padding: 14px 32px;">Search Records</button>
    </form>
</div>

<section class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; background: #f8fafc; border-bottom: 2px solid var(--border);">
                    <th style="padding: 16px 24px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Member Detail</th>
                    <th style="padding: 16px 24px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">CNIC / ID</th>
                    <th style="padding: 16px 24px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Join Date</th>
                    <th style="padding: 16px 24px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members->num_rows === 0): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 60px; color: var(--text-muted);">No members found in directory.</td></tr>
                <?php else: ?>
                    <?php while($m = $members->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary); border-radius: 10px; display: grid; place-items: center; font-weight: 800;"><?= substr($m['full_name'], 0, 1) ?></div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text);"><?= htmlspecialchars($m['full_name']) ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($m['mobile_1']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px 24px;">
                                <div style="font-size: 0.95rem; font-weight: 600;"><?= htmlspecialchars($m['cnic'] ?: 'No CNIC') ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">ID: <?= htmlspecialchars($m['membership_number'] ?: 'N/A') ?></div>
                            </td>
                            <td style="padding: 16px 24px; font-size: 0.9rem;"><?= date('M d, Y', strtotime($m['join_date'])) ?></td>
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; gap: 8px;">
                                    <a href="family_search.php?query=<?= $m['cnic'] ?>" class="action-btn" title="View Profile" style="background: #f1f5f9; color: #475569; padding: 8px; border-radius: 8px; text-decoration: none;">👁️</a>
                                    <a href="edit_member.php?id=<?= $m['id'] ?>" class="action-btn" title="Edit Member" style="background: #eff6ff; color: #2563eb; padding: 8px; border-radius: 8px; text-decoration: none;">✏️</a>
                                    <a href="delete_member.php?id=<?= $m['id'] ?>" class="action-btn" title="Delete Member" onclick="return confirm('Are you sure you want to delete this member?')" style="background: #fee2e2; color: #991b1b; padding: 8px; border-radius: 8px; text-decoration: none;">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
