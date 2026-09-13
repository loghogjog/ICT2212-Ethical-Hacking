<?php
require_once __DIR__ . '/db.php';
$u = require_login($pdo);
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT t.*, us.username FROM tickets t JOIN users us ON us.id=t.user_id WHERE t.id = ?');
$s->execute([$id]);
$t = $s->fetch(PDO::FETCH_ASSOC);
if (!$t) { die('Ticket not found'); }
if ($u['role'] !== 'agent' && $t['user_id'] != $u['id']) { die('Not authorised'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($_POST['reply'] ?? '') !== '') {
        $r = $pdo->prepare('INSERT INTO replies (ticket_id, user_id, body) VALUES (?, ?, ?)');
        $r->execute([$id, $u['id'], trim($_POST['reply'])]);
    }
    if (isset($_POST['close']) && $u['role'] === 'agent') {
        $pdo->prepare('UPDATE tickets SET status=? WHERE id=?')->execute(['closed', $id]);
    }
    header('Location: ticket.php?id=' . $id); exit;
}
$rs = $pdo->prepare('SELECT r.*, us.username FROM replies r JOIN users us ON us.id=r.user_id WHERE ticket_id = ? ORDER BY r.created_at');
$rs->execute([$id]);
$replies = $rs->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><meta charset="utf-8"><title>Ticket</title>
<p><a href="index.php">&larr; Back</a></p>
<h2><?= htmlspecialchars($t['subject']) ?> [<?= htmlspecialchars($t['status']) ?>]</h2>
<p><i>by <?= htmlspecialchars($t['username']) ?></i></p>
<p><?= nl2br(htmlspecialchars($t['body'])) ?></p><hr>
<?php foreach ($replies as $r): ?>
  <p><b><?= htmlspecialchars($r['username']) ?>:</b> <?= nl2br(htmlspecialchars($r['body'])) ?></p>
<?php endforeach; ?>
<hr>
<form method="post">
  <p><textarea name="reply" rows="3" style="width:100%" placeholder="Reply"></textarea></p>
  <button>Reply</button>
  <?php if ($u['role']==='agent' && $t['status']==='open'): ?>
    <button name="close" value="1">Close ticket</button>
  <?php endif; ?>
</form>
