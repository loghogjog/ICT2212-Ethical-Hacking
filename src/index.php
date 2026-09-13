<?php
require_once __DIR__ . '/db.php';
$u = require_login($pdo);
if ($u['role'] === 'agent') {
    $tickets = $pdo->query('SELECT t.*, us.username FROM tickets t JOIN users us ON us.id=t.user_id ORDER BY t.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
} else {
    $s = $pdo->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC');
    $s->execute([$u['id']]);
    $tickets = $s->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html><meta charset="utf-8"><title>Tickets</title>
<p>Logged in as <b><?= htmlspecialchars($u['username']) ?></b> (<?= htmlspecialchars($u['role']) ?>) — <a href="logout.php">Logout</a></p>
<h2>Tickets</h2><p><a href="submit.php">+ New ticket</a></p>
<ul>
<?php foreach ($tickets as $t): ?>
  <li><a href="ticket.php?id=<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['subject']) ?></a>
  [<?= htmlspecialchars($t['status']) ?>]
  <?php if ($u['role']==='agent') echo '— by ' . htmlspecialchars($t['username']); ?></li>
<?php endforeach; ?>
</ul>
