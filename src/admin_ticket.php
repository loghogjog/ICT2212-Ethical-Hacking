<?php
require_once __DIR__ . '/db.php';
$admin = require_admin($pdo);
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$s->execute([$id]);
$ticket = $s->fetch(PDO::FETCH_ASSOC);
if (!$ticket) {
    http_response_code(404);
    exit('Ticket not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['close'])) {
        $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute(['closed', $id]);
    } elseif (isset($_POST['reopen'])) {
        $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute(['open', $id]);
    }
    header('Location: admin_ticket.php?id=' . $id);
    exit;
}
require_once __DIR__ . '/includes/header.php';
?>

<div class="back-link"><a href="admin_dashboard.php">&larr; Back to requests</a></div>
<section class="card ticket-detail">
    <div class="detail-top"><div><p class="eyebrow">REQUEST #<?= (int)$ticket['id'] ?></p><h1><?= htmlspecialchars($ticket['subject']) ?></h1></div><span class="badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span></div>
    <div class="ticket-meta">Submitted by <?= htmlspecialchars($ticket['customer_name']) ?> &middot; <?= htmlspecialchars($ticket['customer_email']) ?></div>
    <div class="ticket-body"><?= htmlspecialchars($ticket['body']) ?></div>
</section>

<section class="section-heading"><div><p class="eyebrow">CUSTOMER DETAILS</p><h2>Submission information</h2></div></section>
<div class="info-grid">
    <div class="card"><span class="summary-label">Full name</span><strong><?= htmlspecialchars($ticket['customer_name']) ?></strong></div>
    <div class="card"><span class="summary-label">Email address</span><strong><a href="mailto:<?= htmlspecialchars($ticket['customer_email']) ?>"><?= htmlspecialchars($ticket['customer_email']) ?></a></strong></div>
    <div class="card"><span class="summary-label">Screenshot</span>
    <?php if (!empty($ticket['screenshot_filename'])): ?><a class="file-link" href="uploads/<?= rawurlencode(basename($ticket['screenshot_filename'])) ?>" download>Download screenshot <span aria-hidden="true">&darr;</span></a><?php else: ?><span class="ticket-meta">No screenshot attached</span><?php endif; ?></div>
</div>

<?php if ($ticket['status'] === 'open'): ?>
<section class="card reply-form"><p class="eyebrow">REQUEST ACTIONS</p><h2>Update this request</h2><form method="post"><button type="submit" name="close" value="1" class="btn">Mark as closed <span aria-hidden="true">&check;</span></button></form></section>
<?php elseif ($ticket['status'] === 'closed'): ?>
<section class="card reply-form"><p class="eyebrow">REQUEST ACTIONS</p><h2>Update this request</h2><form method="post"><button type="submit" name="reopen" value="1" class="btn">Reopen ticket <span aria-hidden="true">&rarr;</span></button></form></section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
