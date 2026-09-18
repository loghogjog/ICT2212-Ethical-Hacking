<?php
require_once __DIR__ . '/db.php';

$error = '';
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $s = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $s->execute([$username]);
    $admin = $s->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        header('Location: admin_dashboard.php');
        exit;
    }
    $error = 'The administrator username or password is incorrect.';
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-intro compact-intro">
    <div>
        <p class="eyebrow">NORTHSTAR IT / STAFF ACCESS</p>
        <h1>Administrator sign in</h1>
        <p class="lede">Access the secure service desk workspace to review and manage customer requests.</p>
    </div>
</section>

<div class="form-layout">
  <section class="card form-card">
    <div class="card-heading"><div><p class="eyebrow">SECURE ACCESS</p><h2>Sign in to the admin console</h2></div><span class="step-count">STAFF ONLY</span></div>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label for="username">Username</label><input id="username" type="text" name="username" autocomplete="username" required></div>
        <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button type="submit" class="btn">Sign in <span aria-hidden="true">&rarr;</span></button>
    </form>
  </section>
    <aside class="help-panel"><span class="panel-kicker">PUBLIC SUPPORT</span><h2>Need to raise a request?</h2><p>Customers can submit a support ticket without an account.</p><a class="text-link" href="index.php">Go to submission form <span aria-hidden="true">&nearr;</span></a></aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
