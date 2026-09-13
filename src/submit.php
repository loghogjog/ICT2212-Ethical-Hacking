<?php
require_once __DIR__ . '/db.php';
$u = require_login($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    if ($subject && $body) {
        $s = $pdo->prepare('INSERT INTO tickets (user_id, subject, body) VALUES (?, ?, ?)');
        $s->execute([$u['id'], $subject, $body]);
        header('Location: index.php'); exit;
    }
}
?>
<!doctype html><meta charset="utf-8"><title>New ticket</title><h2>New ticket</h2>
<form method="post">
  <p><input name="subject" placeholder="Subject" style="width:100%"></p>
  <p><textarea name="body" placeholder="Describe your issue" rows="6" style="width:100%"></textarea></p>
  <button>Submit</button>
</form>
<p><a href="index.php">Back</a></p>
