<?php
ob_start();
// config.php
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

$host = 'localhost';
$dbName = 'bhmj_membership';
$dbUser = 'root';
$dbPass = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $dbUser, $dbPass);
$mysqli->set_charset('utf8mb4');
$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($dbName);

$mysqli->query("CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  membership_number VARCHAR(100) DEFAULT NULL,
  full_name VARCHAR(180) NOT NULL,
  father_name VARCHAR(180) DEFAULT NULL,
  grandfather_name VARCHAR(180) DEFAULT NULL,
  surname VARCHAR(120) DEFAULT NULL,
  native_place VARCHAR(180) DEFAULT NULL,
  cnic VARCHAR(30) DEFAULT NULL,
  residential_address VARCHAR(300) DEFAULT NULL,
  city_country VARCHAR(180) DEFAULT NULL,
  date_of_birth DATE DEFAULT NULL,
  mobile_1 VARCHAR(40) DEFAULT NULL,
  mobile_2 VARCHAR(40) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  occupation VARCHAR(140) DEFAULT NULL,
  marital_status VARCHAR(80) DEFAULT NULL,
  father_or_brother_name VARCHAR(180) DEFAULT NULL,
  father_or_brother_membership_no VARCHAR(120) DEFAULT NULL,
  membership_type VARCHAR(80) NOT NULL DEFAULT 'Standard',
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  join_date DATE DEFAULT NULL,
  family_tree TEXT DEFAULT NULL,
  family_details TEXT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Robust schema migration for updates
function ensureColumn($mysqli, $table, $column, $definition) {
    $res = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res->num_rows == 0) {
        $mysqli->query("ALTER TABLE `$table` ADD `$column` $definition");
    }
}

ensureColumn($mysqli, 'users', 'role', "VARCHAR(40) NOT NULL DEFAULT 'admin'");
ensureColumn($mysqli, 'members', 'cnic', "VARCHAR(30) DEFAULT NULL");
ensureColumn($mysqli, 'members', 'membership_number', "VARCHAR(100) DEFAULT NULL");
ensureColumn($mysqli, 'members', 'native_place', "VARCHAR(180) DEFAULT NULL");

// Initialize default accounts
$adminPass = '$2y$10$2TXuvD.MycAESPVUc9G/vOZRlkmJcemJxrxH/lZrZt7M1/NZprPOC'; // BHMJ2026!
$mysqli->query("INSERT INTO users (name,email,password_hash,role) VALUES ('BHMJ Admin','admin@bhmj.com','$adminPass','admin') ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role);");

// Create hammad account if not exists
$checkHammad = $mysqli->query("SELECT id FROM users WHERE email = 'hammad@bhmj.com' OR name = 'hammad'");
if ($checkHammad->num_rows == 0) {
    $hammadPass = password_hash('hammad', PASSWORD_DEFAULT);
    $mysqli->query("INSERT INTO users (name,email,password_hash,role) VALUES ('Hammad','hammad@bhmj.com','$hammadPass','operator')");
}

function secureSessionStart()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrfToken()
{
    secureSessionStart();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    secureSessionStart();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
