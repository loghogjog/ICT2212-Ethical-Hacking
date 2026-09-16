<?php
require_once __DIR__ . '/db.php';
$u = require_login($pdo);

$msg = ''; // message shown to the user after they submit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    // --- handle the screenshot upload ---
    $savedName = '';
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        $originalName = basename($_FILES['screenshot']['name']);
        $target = $uploadDir . $originalName;
        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $target)) {
            $savedName = $originalName;
        }
    }

    if ($subject && $body) {
        $s = $pdo->prepare('INSERT INTO tickets (user_id, subject, body) VALUES (?, ?, ?)');
        $s->execute([$u['id'], $subject, $body]);
        if ($savedName) {
            $msg = 'Ticket created. Uploaded file: '
                 . '<a href="uploads/' . rawurlencode($savedName) . '">'
                 . htmlspecialchars($savedName) . '</a>';
        } else {
            header('Location: index.php'); exit;
        }
    }
}
?>
<!doctype html><meta charset="utf-8"><title>New ticket</title><h2>New ticket</h2>
<?php if ($msg) echo "<p style='color:green'>$msg</p>"; ?>
<form method="post" enctype="multipart/form-data">
  <p><input name="subject" placeholder="Subject" style="width:100%"></p>
  <p><textarea name="body" placeholder="Describe your issue" rows="6" style="width:100%"></textarea></p>
  <p>Attach a screenshot: <input type="file" name="screenshot"></p>
  <button>Submit</button>
</form>
<p><a href="index.php">Back</a></p>
