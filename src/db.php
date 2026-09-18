<?php
try {
  $dbPath = getenv('DB_DATABASE') ?: '/var/www/db/database.sqlite';
  $pdo = new PDO('sqlite:' . $dbPath);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die('Database connection failed.');
}
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS tickets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_name TEXT NOT NULL,
  customer_email TEXT NOT NULL,
  subject TEXT NOT NULL,
  body TEXT NOT NULL,
  screenshot_filename TEXT,
  status TEXT NOT NULL DEFAULT 'open',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS replies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_id INTEGER NOT NULL,
  admin_id INTEGER,
  body TEXT NOT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);");

$ticketColumns = $pdo->query('PRAGMA table_info(tickets)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (in_array('user_id', $ticketColumns, true) && !in_array('customer_name', $ticketColumns, true)) {
    $pdo->exec("CREATE TABLE tickets_guest (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      customer_name TEXT NOT NULL,
      customer_email TEXT NOT NULL,
      subject TEXT NOT NULL,
      body TEXT NOT NULL,
      screenshot_filename TEXT,
      status TEXT NOT NULL DEFAULT 'open',
      created_at TEXT DEFAULT CURRENT_TIMESTAMP);
    INSERT INTO tickets_guest (id, customer_name, customer_email, subject, body, status, created_at)
      SELECT t.id, COALESCE(u.username, 'Unknown customer'), '', t.subject, t.body, t.status, t.created_at
      FROM tickets t LEFT JOIN users u ON u.id = t.user_id;
    DROP TABLE tickets;
    ALTER TABLE tickets_guest RENAME TO tickets;");
}

$adminColumns = $pdo->query('PRAGMA table_info(admins)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!$adminColumns) {
    $pdo->exec("CREATE TABLE admins (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password_hash TEXT NOT NULL,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
}

$legacyUsers = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();
if ($legacyUsers) {
    $pdo->exec("INSERT OR IGNORE INTO admins (username, password_hash)
      SELECT username, password_hash FROM users WHERE role IN ('agent', 'admin')");
    $pdo->exec('DROP TABLE users');
}

$ticketColumns = $pdo->query('PRAGMA table_info(tickets)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('screenshot_filename', $ticketColumns, true)) {
    $pdo->exec('ALTER TABLE tickets ADD COLUMN screenshot_filename TEXT');
}

$adminUsername = getenv('ADMIN_USERNAME');
$adminPassword = getenv('ADMIN_PASSWORD');
if ($adminUsername && $adminPassword) {
    $s = $pdo->prepare('INSERT OR IGNORE INTO admins (username, password_hash) VALUES (?, ?)');
    $s->execute([$adminUsername, password_hash($adminPassword, PASSWORD_DEFAULT)]);
}

session_start();
function require_admin($pdo) {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit;
    }
    $s = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
    $s->execute([$_SESSION['admin_id']]);
    $admin = $s->fetch(PDO::FETCH_ASSOC);
    if (!$admin) {
        unset($_SESSION['admin_id']);
        header('Location: admin_login.php');
        exit;
    }
    return $admin;
}
