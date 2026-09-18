<?php
require_once __DIR__ . '/db.php';
$admin = require_admin($pdo);
$tickets = $pdo->query('SELECT * FROM tickets ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <div>
        <p class="eyebrow">ADMIN CONSOLE / REQUESTS</p>
        <h1>Service desk workspace</h1>
        <p class="lede">Review customer submissions and keep every request moving toward resolution.</p>
    </div>
</section>

<section class="summary-strip" aria-label="Ticket overview">
    <div><span class="summary-label">All requests</span><strong><?= count($tickets) ?></strong></div>
    <div><span class="summary-label">Open requests</span><strong><?= count(array_filter($tickets, static fn($ticket) => $ticket['status'] === 'open')) ?></strong></div>
    <div><span class="summary-label">Signed in as</span><strong><?= htmlspecialchars($admin['username']) ?></strong></div>
</section>

<section class="section-heading"><div><p class="eyebrow">INBOX</p><h2>Customer requests</h2></div><span class="ticket-meta">Newest first</span></section>

<?php if (!$tickets): ?>
  <div class="empty-state"><span class="empty-icon" aria-hidden="true">+</span><h3>No customer requests</h3><p>New public submissions will appear here.</p></div>
<?php else: ?>
  <div class="ticket-list">
  <?php foreach ($tickets as $ticket): ?>
    <article class="ticket-row">
      <div class="ticket-icon" aria-hidden="true">#</div>
      <div class="ticket-content">
        <a class="ticket-title" href="admin_ticket.php?id=<?= (int)$ticket['id'] ?>"><?= htmlspecialchars($ticket['subject']) ?></a>
        <div class="ticket-meta">Request #<?= (int)$ticket['id'] ?> &middot; <?= htmlspecialchars($ticket['customer_name']) ?> &middot; <?= htmlspecialchars($ticket['customer_email']) ?></div>
      </div>
      <span class="badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
      <a class="row-arrow" href="admin_ticket.php?id=<?= (int)$ticket['id'] ?>" aria-label="View request" title="View request">&rarr;</a>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
