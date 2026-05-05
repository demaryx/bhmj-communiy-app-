<?php
require_once __DIR__ . '/../config.php';
secureSessionStart();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
$userName = $_SESSION['user_name'] ?? 'User';

// Notification Logic
$notificationCount = $mysqli->query("SELECT COUNT(*) as total FROM members WHERE date_of_birth <= DATE_SUB(CURDATE(), INTERVAL 18 YEAR)")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BHMJ Portal | Executive</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg: #f8fafc;
            --sidebar-bg: #0f172a;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --sidebar-width: 240px;
            --radius-lg: 24px;
            --radius-md: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg); color: var(--text); 
            display: block;
            min-height: 100vh; overflow-x: hidden;
        }

        /* Sidebar - Glassmorphism touch */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            background-image: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
            padding: 40px 24px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.04em; }

        .sidebar-nav { flex: 1; padding: 30px 16px; }
        .nav-item {
            display: flex; align-items: center; gap: 15px; padding: 16px 20px;
            color: #94a3b8; text-decoration: none; border-radius: 20px;
            margin-bottom: 8px; font-weight: 600; font-size: 1.05rem;
            transition: 0.2s;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(5px); }
        .nav-item.active { background: var(--primary); color: #fff; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3); }

        .sidebar-footer { padding: 24px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 12px; }

        /* Top Bar */
        .top-bar {
            height: 80px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: fixed;
            top: 0; left: var(--sidebar-width); right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            transition: left 0.3s;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 100px 32px 32px;
            min-height: 100vh;
            min-width: 0;
            transition: margin-left 0.3s;
        }

        /* Premium Form Elements */
        .card {
            background: #fff;
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .section-title {
            background: #f1f5f9; padding: 16px 24px; border-radius: 12px;
            border-left: 6px solid var(--primary); font-weight: 800;
            font-size: 0.85rem; text-transform: uppercase; margin: 40px 0 24px;
            color: var(--text); letter-spacing: 0.05em; display: flex; align-items: center; gap: 10px;
        }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }

        .form-group { margin-bottom: 24px; position: relative; }
        .form-group label { 
            display: block; font-weight: 700; font-size: 0.85rem; 
            margin-bottom: 8px; color: var(--secondary);
            transition: 0.2s;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 14px 18px; border: 2px solid var(--border);
            border-radius: 14px; font-size: 1rem; outline: none; background: #fff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text);
        }
        .form-group input:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 5px var(--primary-light); transform: translateY(-2px); }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff; border: none; padding: 16px 40px;
            border-radius: 16px; font-weight: 700; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px; font-size: 1rem;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); transition: 0.3s;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3); }

        .menu-toggle { display: none; background: var(--primary-light); color: var(--primary); border: none; padding: 12px; border-radius: 12px; cursor: pointer; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .top-bar { left: 0; padding: 0 20px; }
            .main-content { margin-left: 0; padding: 90px 20px 20px; max-width: 100%; }
            .menu-toggle { display: block; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/logo.png" alt="Logo" style="width: 45px; height: auto;">
            <div class="sidebar-brand">BHMJ PORTAL</div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="membership.php" class="nav-item <?= $currentPage == 'membership.php' ? 'active' : '' ?>">📝 Registration</a>
            <a href="members_list.php" class="nav-item <?= $currentPage == 'members_list.php' ? 'active' : '' ?>">👥 Directory</a>
            <a href="family_search.php" class="nav-item <?= $currentPage == 'family_search.php' ? 'active' : '' ?>">🌳 Search</a>
            <a href="settings.php" class="nav-item <?= $currentPage == 'settings.php' ? 'active' : '' ?>">⚙️ Settings</a>
        </nav>
        <div class="sidebar-footer">
            <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 12px; display: grid; place-items: center; font-weight: 800;"><?= substr($userName, 0, 1) ?></div>
            <div>
                <div style="font-weight: 700; font-size: 0.9rem; color: #fff;"><?= htmlspecialchars($userName) ?></div>
                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 800;"><?= strtoupper($_SESSION['user_role'] ?? 'User') ?></div>
            </div>
        </div>
    </aside>

    <header class="top-bar">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div style="font-weight: 700; color: var(--secondary); font-size: 0.85rem;"><?= strtoupper(str_replace('.php', '', $currentPage)) ?></div>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <?php if ($notificationCount > 0): ?>
            <a href="members_list.php?filter=18plus" style="background: var(--primary-light); color: var(--primary); padding: 10px 20px; border-radius: 30px; text-decoration: none; font-weight: 800; font-size: 0.85rem; border: 1px solid #dbeafe;">
                🔔 <?= $notificationCount ?>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="btn-primary" style="background: var(--danger); font-size: 0.85rem; padding: 12px 24px;">Sign Out</a>
        </div>
    </header>

    <script>
        function toggleSidebar() { 
            const sb = document.getElementById('sidebar');
            sb.classList.toggle('open');
            if (sb.classList.contains('open')) {
                const overlay = document.createElement('div');
                overlay.id = 'sb-overlay';
                overlay.style = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.3);z-index:1500;backdrop-filter:blur(2px);';
                overlay.onclick = toggleSidebar;
                document.body.appendChild(overlay);
            } else {
                const ov = document.getElementById('sb-overlay');
                if (ov) ov.remove();
            }
        }
    </script>

    <main class="main-content">