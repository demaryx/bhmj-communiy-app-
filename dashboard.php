<?php
require_once 'includes/header.php';

// Only admins can access dashboard stats
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: members_list.php');
    exit;
}

$countStmt = $mysqli->prepare('SELECT COUNT(*) AS total FROM members');
$countStmt->execute();
$totalMembers = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

$recentStmt = $mysqli->prepare('SELECT full_name, mobile_1, cnic, membership_number, join_date FROM members ORDER BY id DESC LIMIT 10');
$recentStmt->execute();
$recentMembers = $recentStmt->get_result();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header">
    <div class="page-title">
        <h1>BHMJ Portal Overview</h1>
        <p>Real-time membership statistics and member directory.</p>
    </div>
    <div class="page-actions">
        <a href="membership.php" class="btn-form" style="text-decoration:none; display:inline-block; margin-top:0; border-radius: 10px; background: var(--primary); color: #fff; padding: 12px 24px; font-weight: 600;">+ New Registration</a>
    </div>
</div>

<section class="stats-grid">
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h3>Total Members</h3>
                <div class="value"><?= number_format($totalMembers) ?></div>
            </div>
            <span style="font-size: 1.5rem;">👥</span>
        </div>
        <p style="margin:12px 0 0; font-size:0.85rem; color: #10b981; font-weight: 600;">↑ 12% increase</p>
    </div>
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h3>System Status</h3>
                <div class="value">Secure</div>
            </div>
            <span style="font-size: 1.5rem;">🛡️</span>
        </div>
        <p style="margin:12px 0 0; font-size:0.85rem; color: var(--text-muted);">Last scan: 2 mins ago</p>
    </div>
    <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h3>Portal Speed</h3>
                <div class="value">Fast</div>
            </div>
            <span style="font-size: 1.5rem;">⚡</span>
        </div>
        <p style="margin:12px 0 0; font-size:0.85rem; color: var(--text-muted);">99.9% uptime achieved</p>
    </div>
</section>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 40px;" class="dashboard-charts">
    <div class="card" style="padding: 24px;">
        <h3 style="margin: 0 0 20px; font-size: 1.1rem; font-weight: 700;">Membership Growth</h3>
        <canvas id="growthChart" height="200"></canvas>
    </div>
    <div class="card" style="padding: 24px;">
        <h3 style="margin: 0 0 20px; font-size: 1.1rem; font-weight: 700;">Membership Types</h3>
        <canvas id="typeChart" height="200"></canvas>
    </div>
</div>

<section class="card" style="padding: 32px; overflow: hidden;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;">Member Directory</h2>
        <a href="#" style="font-size: 0.9rem; color: var(--primary); text-decoration: none; font-weight: 600;">View All Records →</a>
    </div>
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 16px 12px; font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Member Name</th>
                    <th style="padding: 16px 12px; font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">CNIC No.</th>
                    <th style="padding: 16px 12px; font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Relation</th>
                    <th style="padding: 16px 12px; font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Join Date</th>
                    <th style="padding: 16px 12px; font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentMembers->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center; padding:60px; color:var(--text-muted);">No members registered yet.</td></tr>
                <?php else: ?>
                    <?php while ($row = $recentMembers->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f9fafb; transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; background: #eef2ff; border-radius: 10px; display: grid; place-items: center; font-weight: 700; color: var(--primary); font-size: 0.85rem;"><?= substr($row['full_name'], 0, 1) ?></div>
                                    <div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($row['full_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">ID: <?= htmlspecialchars($row['membership_number'] ?: 'N/A') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px 12px; color: var(--text-muted); font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($row['cnic'] ?: 'N/A') ?></td>
                            <td style="padding: 16px 12px;"><span style="background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: #475569; border: 1px solid #e2e8f0;">Member</span></td>
                            <td style="padding: 16px 12px; font-size: 0.9rem;"><?= date('M d, Y', strtotime($row['join_date'])) ?></td>
                            <td style="padding: 16px 12px;">
                                <a href="family_search.php?query=<?= $row['cnic'] ?>" style="text-decoration:none; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem;" title="View Profile">👁️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
    // Membership Growth Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Members',
                data: [12, 19, 3, 5, 2, 10], // Mock data
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, display: false }, x: { grid: { display: false } } }
        }
    });

    // Membership Type Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Archived'],
            datasets: [{
                data: [70, 20, 10],
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } },
            cutout: '70%'
        }
    });
</script>

<style>
    @media (max-width: 992px) {
        .dashboard-charts { grid-template-columns: 1fr !important; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 20px; }
        .page-actions { width: 100%; }
        .page-actions a { width: 100%; text-align: center; }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
