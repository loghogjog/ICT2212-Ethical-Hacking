<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Enterprise IT Helpdesk support portal">
  <title>Northstar IT Helpdesk</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <nav class="navbar">
    <div class="nav-inner">
      <?php if (isset($_SESSION['admin_id'])): ?>
        <a class="brand" href="admin_dashboard.php"><span class="brand-mark">N</span><span>Northstar <strong>IT</strong></span></a>
        <div class="nav-links">
          <a href="admin_dashboard.php">Dashboard</a>
          <a href="admin_logout.php">Sign out</a>
        </div>
      <?php else: ?>
        <a class="brand" href="index.php"><span class="brand-mark">N</span><span>Northstar <strong>IT</strong></span></a>
        <div class="nav-links">
          <a href="index.php">Submit Ticket</a>
          <a href="faq.php">FAQ</a>
          <a href="contact.php">Contact</a>
        </div>
      <?php endif; ?>
    </div>
  </nav>
  <main class="container">