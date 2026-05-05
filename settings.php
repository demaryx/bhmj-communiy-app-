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
        <h1>Portal Management</h1>
        <p>Administrative control panel for system users and access privileges.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: start;" class="settings-grid">
    <section class="card" style="padding: 0; overflow: hidden;">
        <div
            style="padding: 32px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
            <h2 style="margin: 0; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                <span style="background: var(--primary-light); padding: 8px; border-radius: 10px;">👥</span>
                System Access Registry
            </h2>
            <span
                style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"><?= $users->num_rows ?>
                Total Accounts</span>
        </div>
        <div style="padding: 0;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: #fff;">
                            <th
                                style="padding: 18px 24px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                                User Identity</th>
                            <th
                                style="padding: 18px 24px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                                Access Role</th>
                            <th
                                style="padding: 18px 24px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                                Registration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <tr style="border-top: 1px solid var(--border); transition: all 0.2s;"
                                onmouseover="this.style.background='#f8faff'"
                                onmouseout="this.style.background='transparent'">
                                <td style="padding: 20px 24px;">
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <div
                                            style="width: 42px; height: 42px; background: <?= $u['role'] == 'admin' ? 'linear-gradient(135deg, #2563eb, #3b82f6)' : '#e2e8f0' ?>; color: #fff; border-radius: 12px; display: grid; place-items: center; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                            <?= substr($u['name'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">
                                                <?= htmlspecialchars($u['name']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 20px 24px;">
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 6px; background: <?= $u['role'] == 'admin' ? '#dcfce7' : '#f1f5f9' ?>; color: <?= $u['role'] == 'admin' ? '#166534' : '#475569' ?>; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; border: 1px solid <?= $u['role'] == 'admin' ? '#bbf7d0' : '#e2e8f0' ?>;">
                                        <span
                                            style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td
                                    style="padding: 20px 24px; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                                    <?= date('M d, Y', strtotime($u['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="card" style="padding: 40px; background: #fff; position: sticky; top: 120px;">
        <div style="margin-bottom: 32px;">
            <h2 style="margin: 0; font-size: 1.15rem; font-weight: 800;">Provision New User</h2>
            <p style="margin: 6px 0 0; font-size: 0.85rem; color: var(--text-muted);">Create a new management account.
            </p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"
                style="margin-bottom: 24px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"
                style="margin-bottom: 24px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Full
                    Name</label>
                <input type="text" name="name" required placeholder="e.g. Hammad popat"
                    style="width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border); background: #fcfcfc; font-size: 0.95rem; outline: none; transition: all 0.2s;"
                    onfocus="this.style.borderColor=var(--primary); this.style.background='#fff'; this.style.boxShadow='0 0 0 4px var(--primary-light)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.background='#fcfcfc'; this.style.boxShadow='none'">
            </div>

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Email
                    Address</label>
                <input type="email" name="email" required placeholder="hammadpopat@bhmj.com"
                    style="width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border); background: #fcfcfc; font-size: 0.95rem; outline: none; transition: all 0.2s;"
                    onfocus="this.style.borderColor=var(--primary); this.style.background='#fff'; this.style.boxShadow='0 0 0 4px var(--primary-light)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.background='#fcfcfc'; this.style.boxShadow='none'">
            </div>

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Account
                    Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                    style="width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border); background: #fcfcfc; font-size: 0.95rem; outline: none; transition: all 0.2s;"
                    onfocus="this.style.borderColor=var(--primary); this.style.background='#fff'; this.style.boxShadow='0 0 0 4px var(--primary-light)'"
                    onblur="this.style.borderColor='var(--border)'; this.style.background='#fcfcfc'; this.style.boxShadow='none'">
            </div>

            <div style="margin-bottom: 32px;">
                <label
                    style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Access
                    Permission</label>
                <select name="role"
                    style="width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border); background: #fcfcfc; font-size: 0.95rem; outline: none; appearance: none; cursor: pointer; transition: all 0.2s;"
                    onfocus="this.style.borderColor=var(--primary); this.style.background='#fff'"
                    onblur="this.style.borderColor='var(--border)'; this.style.background='#fcfcfc'">
                    <option value="operator">Management Person (Limited)</option>
                    <option value="admin">Administrator (Full Access)</option>
                </select>
            </div>

            <button type="submit" class="btn-form"
                style="width: 100%; margin-top: 0; padding: 16px; border-radius: 12px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); transition: all 0.3s;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(37, 99, 235, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(37, 99, 235, 0.3)'">Initialize
                Account</button>
        </form>
    </section>
</div>

<style>
    @media (max-width: 1024px) {
        .settings-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>