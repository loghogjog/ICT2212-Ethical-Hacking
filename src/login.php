<?php
require_once __DIR__ . '/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $s->execute([trim($_POST['username'] ?? '')]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        $_SESSION['uid'] = $u['id'];
        header('Location: index.php'); exit;
    }
    $err = 'Invalid credentials.';
}
?>
<!doctype html><meta charset="utf-8"><title>Login</title><h2>Login</h2>
<?php if ($err) echo "<p style='color:red'>" . htmlspecialchars($err) . "</p>"; ?>
<form method="post">
  <p><input name="username" placeholder="Username"></p>
  <p><input type="password" name="password" placeholder="Password"></p>
  <button>Log in</button>
</form>
<p><a href="register.php">Register</a></p>
