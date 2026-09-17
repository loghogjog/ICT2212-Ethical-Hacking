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
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div>
        <p class="eyebrow">SERVICE DESK / HOME</p>
        <h1>How can we help today?</h1>
        <p class="lede">Raise a request, check an existing case, or find a quick answer in our support centre.</p>
    </div>
    <a href="submit.php" class="btn">Submit a ticket <span aria-hidden="true">&rarr;</span></a>
</section>

<section class="summary-strip" aria-label="Support overview">
    <div><span class="summary-label">Open requests</span><strong><?= count(array_filter($tickets, static fn($ticket) => $ticket['status'] === 'open')) ?></strong></div>
    <div><span class="summary-label">Average response</span><strong>Under 4 hrs</strong></div>
    <div><span class="summary-label">Service coverage</span><strong>24 / 7</strong></div>
</section>

<section class="section-heading">
    <div><p class="eyebrow">CASE MANAGEMENT</p><h2>Recent requests</h2></div>
    <a class="text-link" href="submit.php">New request <span aria-hidden="true">&nearr;</span></a>
</section>

<?php if (!$tickets): ?>
  <div class="empty-state">
      <span class="empty-icon" aria-hidden="true">+</span>
      <h3>No requests yet</h3>
      <p>Your submitted tickets will appear here for easy tracking.</p>
      <a class="btn btn-secondary" href="submit.php">Create your first request</a>
  </div>
<?php else: ?>
  <div class="ticket-list">
  <?php foreach ($tickets as $t): ?>
    <article class="ticket-row">
        <div class="ticket-icon" aria-hidden="true">#</div>
        <div class="ticket-content">
            <a class="ticket-title" href="ticket.php?id=<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['subject']) ?></a>
            <div class="ticket-meta">Request #<?= (int)$t['id'] ?><?php if ($u['role']==='agent') echo ' &middot; Raised by ' . htmlspecialchars($t['username']); ?></div>
        </div>
        <span class="badge-<?= htmlspecialchars($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span>
        <a class="row-arrow" href="ticket.php?id=<?= (int)$t['id'] ?>" aria-label="View ticket" title="View ticket">&rarr;</a>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>