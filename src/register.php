<?php
require_once __DIR__ . '/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') { $err = 'Username and password required.'; }
    else {
        try {
            $s = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $s->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php'); exit;
        } catch (PDOException $e) { $err = 'Username already taken.'; }
    }
}
?>
<!doctype html><meta charset="utf-8"><title>Register</title><h2>Register</h2>
<?php if ($err) echo "<p style='color:red'>" . htmlspecialchars($err) . "</p>"; ?>
<form method="post">
  <p><input name="username" placeholder="Username"></p>
  <p><input type="password" name="password" placeholder="Password"></p>
  <button>Register</button>
</form>
<p><a href="login.php">Log in</a></p>
