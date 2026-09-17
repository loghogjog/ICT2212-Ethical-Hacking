<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/waf.php';
$u = require_login($pdo);

$msg = '';
$uploadErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    $savedName = '';
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir    = __DIR__ . '/uploads/';
        $originalName = basename($_FILES['screenshot']['name']);
        $tmp          = $_FILES['screenshot']['tmp_name'];
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (pathinfo($originalName, PATHINFO_FILENAME) === '') {
            $ext = '';
        }

        $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
        $contents = file_get_contents($tmp);

        if ($ext !== '' && !in_array($ext, $allowed, true)) {
            $uploadErr = 'Upload rejected.';
        } elseif ($ext !== '' && !@getimagesize($tmp)) {
            $uploadErr = 'Upload rejected.';
        } elseif (waf_check($contents)) {
            $uploadErr = 'Upload rejected.';
        } elseif (filesize($tmp) > 2 * 1024 * 1024) {
            $uploadErr = 'Upload rejected.';
        } else {
            if (move_uploaded_file($tmp, $uploadDir . $originalName)) {
                $savedName = $originalName;
            }
        }
    }

    if ($subject && $body) {
        $s = $pdo->prepare('INSERT INTO tickets (user_id, subject, body) VALUES (?, ?, ?)');
        $s->execute([$u['id'], $subject, $body]);
        if ($savedName) {
            $msg = 'Ticket created. Uploaded file: <a href="uploads/' . rawurlencode($savedName) . '">' . htmlspecialchars($savedName) . '</a>';
        } elseif (!$uploadErr) {
            header('Location: index.php'); exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-intro compact-intro">
    <div>
        <p class="eyebrow">SERVICE DESK / NEW REQUEST</p>
        <h1>Submit a support ticket</h1>
        <p class="lede">Tell us what is happening and our service desk team will route your request to the right specialist.</p>
    </div>
</section>

<div class="form-layout">
  <section class="card form-card">
    <div class="card-heading"><div><p class="eyebrow">REQUEST DETAILS</p><h2>What do you need help with?</h2></div><span class="step-count">01 / 01</span></div>
    <?php if ($msg) echo "<div class='alert-success'>$msg</div>"; ?>
    <?php if ($uploadErr) echo "<div class='alert-error'>" . htmlspecialchars($uploadErr) . "</div>"; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="field"><label for="subject">Subject <span class="required">Required</span></label><input id="subject" type="text" name="subject" placeholder="Brief summary of the issue" required></div>
        <div class="field"><label for="body">Description <span class="required">Required</span></label><textarea id="body" name="body" placeholder="Describe your issue in detail" rows="7" required></textarea></div>
        <div class="field"><label for="screenshot">Attach a screenshot <span class="optional">Optional</span></label><input type="file" name="screenshot" accept=".jpg,.jpeg,.png,.gif"><span class="field-hint">Accepted formats: JPG, JPEG, PNG, GIF. Maximum size: 2 MB.</span></div>
        <button type="submit" class="btn">Send request <span aria-hidden="true">&rarr;</span></button>
    </form>
  </section>
  <aside class="help-panel"><span class="panel-kicker">NEED A QUICK ANSWER?</span><h2>Browse the support guide</h2><p>Find guidance for common access, device, and software questions before submitting a request.</p><a class="text-link" href="faq.php">View FAQs <span aria-hidden="true">&nearr;</span></a></aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>