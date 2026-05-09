<?php
require_once 'includes/header.php';

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: members_list.php');
    exit;
}

// Total Members — safe fallback if query fails
$_tmRes = $mysqli->query("SELECT COUNT(*) as total FROM members");
$totalMembers = ($_tmRes !== false) ? (int)$_tmRes->fetch_assoc()['total'] : 0;

// Growth Data (Last 6 Months)
$growthLabels = [];
$growthValues = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $_gRes = $mysqli->query("SELECT COUNT(*) as count FROM members WHERE join_date LIKE '$month%'");
    $count = ($_gRes !== false) ? (int)$_gRes->fetch_assoc()['count'] : 0;
    $growthLabels[] = $label;
    $growthValues[] = $count;
}

// Membership Type Distribution via marital_status
$typeLabels = [];
$typeValues = [];
$_colCheck = $mysqli->query("SHOW COLUMNS FROM `members` LIKE 'marital_status'");
if ($_colCheck !== false && $_colCheck->num_rows > 0) {
    $typeResult = $mysqli->query("SELECT marital_status, COUNT(*) as count FROM members GROUP BY marital_status");
    if ($typeResult !== false) {
        while ($row = $typeResult->fetch_assoc()) {
            $typeLabels[] = $row['marital_status'] ?: 'Unknown';
            $typeValues[] = (int)$row['count'];
        }
    }
}

// Recent Members — safe check for 'cnic' column
$_cnicCheck = $mysqli->query("SHOW COLUMNS FROM `members` LIKE 'cnic'");
$_hasCnic = ($_cnicCheck !== false && $_cnicCheck->num_rows > 0);
$_selectCols = $_hasCnic 
    ? "full_name, cnic, membership_number, join_date" 
    : "full_name, '' AS cnic, membership_number, join_date";

$recentMembers = $mysqli->query("SELECT {$_selectCols} FROM members ORDER BY id DESC LIMIT 6");
if ($recentMembers === false) { $recentMembers = null; }
?>

<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2.4rem; font-weight: 800; letter-spacing: -0.04em; margin-bottom: 8px;">Executive Dashboard
    </h1>
    <p style="color: var(--text-muted); font-size: 1.05rem;">Real-time overview of BHMJ membership and organization
        status.</p>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px;">
    <div
        style="background:#fff; padding:28px; border-radius:20px; border:1px solid var(--border); display:flex; align-items:center; gap:20px;">
        <div
            style="width:55px; height:55px; background:var(--primary-light); border-radius:15px; display:grid; place-items:center; font-size:1.6rem;">
            👥</div>
        <div>
            <div
                style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">
                Total Members</div>
            <div style="font-size:2.4rem; font-weight:800; line-height:1.1;"><?= number_format($totalMembers) ?></div>
            <div style="font-size:0.8rem; color:var(--success); font-weight:700;">Active Registry</div>
        </div>
    </div>
    <div
        style="background:#fff; padding:28px; border-radius:20px; border:1px solid var(--border); display:flex; align-items:center; gap:20px;">
        <div
            style="width:55px; height:55px; background:#f0fdf4; border-radius:15px; display:grid; place-items:center; font-size:1.6rem;">
            🔒</div>
        <div>
            <div
                style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">
                System Status</div>
            <div style="font-size:2.4rem; font-weight:800; line-height:1.1; color:var(--success);">Online</div>
            <div style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Encrypted & Secure</div>
        </div>
    </div>
    <div
        style="background:#fff; padding:28px; border-radius:20px; border:1px solid var(--border); display:flex; align-items:center; gap:20px;">
        <div
            style="width:55px; height:55px; background:#fef2f2; border-radius:15px; display:grid; place-items:center; font-size:1.6rem;">
            🔔</div>
        <div>
            <div
                style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">
                Alerts</div>
            <div style="font-size:2.4rem; font-weight:800; line-height:1.1; color:var(--danger);">
                <?= $notificationCount ?></div>
            <div style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Require Attention</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: 60% 40%; gap: 24px; margin-bottom: 32px; min-width: 0;">
    <!-- Growth Chart -->
    <div
        style="background:#fff; padding:30px; border-radius:20px; border:1px solid var(--border); min-width:0; overflow:hidden;">
        <div style="font-size:1rem; font-weight:800; margin-bottom:25px; color:var(--text);">📈 Membership Growth — Last
            6 Months</div>
        <div style="position:relative; height:260px; width:100%;">
            <canvas id="growthChart"></canvas>
        </div>
    </div>
    <!-- Donut Chart -->
    <div
        style="background:#fff; padding:30px; border-radius:20px; border:1px solid var(--border); display:flex; flex-direction:column; min-width:0; overflow:hidden;">
        <div style="font-size:1rem; font-weight:800; margin-bottom:20px; color:var(--text);">🍩 Marital Distribution
        </div>
        <div style="position:relative; height:220px; width:100%;">
            <canvas id="donutChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Members Table -->
<div style="background:#fff; padding:30px; border-radius:20px; border:1px solid var(--border);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <div style="font-size:1rem; font-weight:800;">🕒 Recently Registered Members</div>
        <a href="members_list.php" class="btn-primary" style="font-size:0.85rem; padding:10px 20px;">View All →</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-radius:10px;">
                    <th
                        style="padding:14px 20px; text-align:left; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">
                        Member</th>
                    <th
                        style="padding:14px 20px; text-align:left; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">
                        CNIC</th>
                    <th
                        style="padding:14px 20px; text-align:left; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">
                        Membership ID</th>
                    <th
                        style="padding:14px 20px; text-align:left; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">
                        Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentMembers): while ($m = $recentMembers->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:16px 20px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div
                                    style="width:36px; height:36px; background:var(--primary-light); color:var(--primary); border-radius:10px; display:grid; place-items:center; font-weight:800;">
                                    <?= strtoupper(substr($m['full_name'], 0, 1)) ?></div>
                                <span style="font-weight:700;"><?= htmlspecialchars($m['full_name']) ?></span>
                            </div>
                        </td>
                        <td style="padding:16px 20px; color:var(--text-muted);"><?= htmlspecialchars($m['cnic'] ?: '—') ?></td>
                        <td style="padding:16px 20px;"><span
                                style="background:var(--primary-light); color:var(--primary); padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:700;"><?= htmlspecialchars($m['membership_number'] ?: 'N/A') ?></span>
                        </td>
                        <td style="padding:16px 20px; color:var(--text-muted); font-size:0.9rem;">
                            <?= $m['join_date'] ? date('d M Y', strtotime($m['join_date'])) : '—' ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">No members registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Growth Line Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($growthLabels) ?>,
            datasets: [{
                label: 'New Members',
                data: <?= json_encode($growthValues) ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Outfit', size: 13 },
                    bodyFont: { family: 'Outfit', size: 13 },
                    padding: 12,
                    cornerRadius: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { family: 'Outfit', size: 12 }, color: '#94a3b8', stepSize: 1 }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 12 }, color: '#94a3b8' }
                }
            }
        }
    });

    // Donut Chart
    const donutCtx = document.getElementById('donutChart').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($typeLabels ?: ['No Data']) ?>,
            datasets: [{
                data: <?= json_encode($typeValues ?: [1]) ?>,
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Outfit', size: 12 }, padding: 15, usePointStyle: true } },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Outfit' },
                    bodyFont: { family: 'Outfit' },
                    padding: 12,
                    cornerRadius: 10
                }
            },
            cutout: '70%'
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>