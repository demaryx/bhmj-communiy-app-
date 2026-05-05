<?php
require_once 'includes/header.php';

// Only admins can access settings
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-error">Access Denied. Only Administrators can access this page.</div>';
    require_once 'includes/footer.php';
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'operator';

        if (!$name || !$email || !$password) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $email, $passHash, $role);
            
            try {
                if ($stmt->execute()) {
                    $message = 'User added successfully.';
                }
            } catch (Exception $e) {
                $error = 'Error: Email already exists or system error.';
            }
        }
    }
}

$users = $mysqli->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
$token = csrfToken();
?>

<div class="page-header">
    <div class="page-title">
        <h1>Portal Settings</h1>
        <p>Manage system users and access permissions.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 400px; gap: 32px; align-items: start;" class="settings-grid">
    <section class="card">
        <h2 style="margin: 0 0 24px; font-size: 1.25rem;">System Users</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Name</th>
                        <th style="padding: 12px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Email</th>
                        <th style="padding: 12px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Role</th>
                        <th style="padding: 12px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = $users->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f9fafb;">
                            <td style="padding: 16px 12px; font-weight: 600;"><?= htmlspecialchars($u['name']) ?></td>
                            <td style="padding: 16px 12px; color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                            <td style="padding: 16px 12px;">
                                <span style="background: <?= $u['role'] == 'admin' ? '#dcfce7' : '#f1f5f9' ?>; color: <?= $u['role'] == 'admin' ? '#166534' : '#475569' ?>; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td style="padding: 16px 12px; font-size: 0.85rem;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2 style="margin: 0 0 24px; font-size: 1.25rem;">Add New User</h2>
        <?php if ($message): ?><div class="alert alert-success" style="margin-bottom: 20px;"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error" style="margin-bottom: 20px;"><?= $error ?></div><?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Full Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Initial Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Access Level</label>
                <select name="role" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                    <option value="operator">Operator (Add/Edit Members Only)</option>
                    <option value="admin">Administrator (Full System Access)</option>
                </select>
            </div>
            <button type="submit" class="btn-form" style="width: 100%; margin-top: 0;">Create Account</button>
        </form>
    </section>
</div>

<style>
    @media (max-width: 1024px) {
        .settings-grid { grid-template-columns: 1fr !important; }
    }
</style>

<?php require_once 'includes/footer.php'; ?>
