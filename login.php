<?php
require_once 'config.php';
secureSessionStart();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($identifier === '' || $password === '') {
            $error = 'Please enter both email/ID and password.';
        } else {
            $stmt = $mysqli->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ? OR name = ? LIMIT 1');
            $stmt->bind_param('ss', $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
}
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BHMJ Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f3f4f6;
            --text: #111827;
            --text-muted: #6b7280;
            --card-bg: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { 
            margin: 0; 
            min-height: 100vh; 
            background: var(--bg);
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 24px;
            color: var(--text);
        }
        .login-card {
            background: var(--card-bg);
            padding: 48px;
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 25px 50px -12px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo { width: 70px; margin-bottom: 24px; }
        h1 { margin: 0; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.03em; }
        p { margin: 8px 0 32px; color: var(--text-muted); font-size: 1rem; }
        
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 8px; }
        input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            background: #f9fafb;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); background: #fff; }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 12px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3); }
        
        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            margin-bottom: 24px;
            border: 1px solid #fee2e2;
            font-weight: 500;
        }

        .demo-box {
            margin-top: 32px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .demo-box strong { color: var(--text); }
        
        .copyright { margin-top: 32px; font-size: 0.85rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="assets/logo.png" alt="BHMJ Logo" class="logo">
        <h1>BHMJ Portal</h1>
        <p>Organization Membership Login</p>
        
        <?php if ($error): ?><div class="error-box"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="admin@bhmj.com" required placeholder="admin@bhmj.com">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label>Password</label>
                <input type="password" name="password" value="BHMJ2026!" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
        
        <div class="demo-box">
            <strong>Administrator Access:</strong><br>
            Email: admin@bhmj.com | Pass: BHMJ2026!
        </div>
        
        <div class="copyright">
            &copy; 2026 BHMJ Membership System
        </div>
    </div>
</body>
</html>