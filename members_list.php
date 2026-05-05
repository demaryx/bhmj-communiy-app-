<?php
require_once 'includes/header.php';

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$where = " WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (full_name LIKE ? OR cnic LIKE ? OR membership_number LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s, $s];
    $types = "sss";
}

if ($filter === '18plus') {
    $where .= " AND date_of_birth <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR)";
}

$countQuery = "SELECT COUNT(*) as total FROM members" . $where;
$countStmt = $mysqli->prepare($countQuery);
if ($params) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalFound = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

$query = "SELECT id, full_name, cnic, membership_number, mobile_1, join_date FROM members" . $where . " ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$members = $stmt->get_result();
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 24px; margin-bottom: 40px;">
    <div class="page-title">
        <h1 style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em;"><?= $filter === '18plus' ? 'Adult Member Registry (18+)' : 'Member Directory' ?></h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;"><?= $filter === '18plus' ? 'Listing members who have transitioned to adulthood and may require updated credentials.' : 'A total of <strong>' . number_format($totalFound) . '</strong> active members registered in the system.' ?></p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="export_members.php" class="btn-primary" style="background: var(--secondary);">
            📥 Export CSV
        </a>
        <a href="membership.php" class="btn-primary">
            + New Member
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 32px; padding: 24px;">
    <form method="GET" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <div style="flex: 1; min-width: 300px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name, CNIC, or ID..." style="width: 100%; padding: 14px 20px; border-radius: 12px; border: 1px solid var(--border); outline: none; background: #f8fafc; font-size: 1rem;">
        </div>
        <button type="submit" class="btn-primary" style="padding: 14px 28px;">Apply Search</button>
        <?php if ($search || $filter): ?>
            <a href="members_list.php" class="btn-primary" style="background: transparent; color: var(--danger); border: 1px solid var(--danger);">Clear Filters</a>
        <?php endif; ?>
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
                        <tr 
                            onclick="window.location='family_search.php?query=<?= urlencode($m['cnic']) ?>'"
                            style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='#f0f7ff'"
                            onmouseout="this.style.background='transparent'"
                        >
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary); border-radius: 10px; display: grid; place-items: center; font-weight: 800;"><?= strtoupper(substr($m['full_name'], 0, 1)) ?></div>
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
                            <td style="padding: 16px 24px;" onclick="event.stopPropagation()">
                                <div style="display: flex; gap: 8px;">
                                    <a href="family_search.php?query=<?= $m['cnic'] ?>" title="View Profile" style="background: #f1f5f9; color: #475569; padding: 8px; border-radius: 8px; text-decoration: none;">👁️</a>
                                    <a href="edit_member.php?id=<?= $m['id'] ?>" title="Edit Member" style="background: #eff6ff; color: #2563eb; padding: 8px; border-radius: 8px; text-decoration: none;">✏️</a>
                                    <a href="delete_member.php?id=<?= $m['id'] ?>" title="Delete Member" onclick="return confirm('Are you sure?')" style="background: #fee2e2; color: #991b1b; padding: 8px; border-radius: 8px; text-decoration: none;">🗑️</a>
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
