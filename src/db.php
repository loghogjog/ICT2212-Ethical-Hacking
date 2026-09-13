<?php
try {
  $dbPath = getenv('DB_DATABASE') ?: '/var/www/db/database.sqlite';
  $pdo = new PDO('sqlite:' . $dbPath);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'user',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS tickets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL, subject TEXT NOT NULL, body TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'open',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS replies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_id INTEGER NOT NULL, user_id INTEGER NOT NULL, body TEXT NOT NULL,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP);
");
session_start();
function current_user($pdo) {
    if (!isset($_SESSION['uid'])) return null;
    $s = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $s->execute([$_SESSION['uid']]);
    return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
function require_login($pdo) {
    $u = current_user($pdo);
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}
