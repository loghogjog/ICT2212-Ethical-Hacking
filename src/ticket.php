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
require_once __DIR__ . '/includes/header.php';
?>

<div class="back-link"><a href="index.php">&larr; Back to requests</a></div>
<section class="card ticket-detail">
    <div class="detail-top"><div><p class="eyebrow">REQUEST #<?= (int)$t['id'] ?></p><h1><?= htmlspecialchars($t['subject']) ?></h1></div><span class="badge-<?= htmlspecialchars($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span></div>
    <div class="ticket-meta">Reported by <b><?= htmlspecialchars($t['username']) ?></b></div>
    <div class="ticket-body"><?= htmlspecialchars($t['body']) ?></div>
</section>

<section class="section-heading"><div><p class="eyebrow">CONVERSATION</p><h2>Updates</h2></div></section>

<?php foreach ($replies as $r): ?>
  <article class="reply-card">
      <div class="reply-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($r['username'], 0, 1))) ?></div>
      <div><p class="reply-meta"><b><?= htmlspecialchars($r['username']) ?></b> replied</p><p class="reply-body"><?= htmlspecialchars($r['body']) ?></p></div>
  </article>
<?php endforeach; ?>

<section class="card reply-form">
    <p class="eyebrow">ADD AN UPDATE</p><h2>Reply to this request</h2>
    <form method="post">
        <label for="reply">Your message</label>
        <textarea id="reply" name="reply" rows="4" placeholder="Type your response here..." required></textarea>
        <div class="form-actions"><button type="submit" class="btn">Post reply <span aria-hidden="true">&rarr;</span></button>
        <?php if ($u['role']==='agent' && $t['status']==='open'): ?><button type="submit" name="close" value="1" class="btn btn-secondary">Close ticket</button><?php endif; ?></div>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>