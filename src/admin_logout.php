<?php
require_once __DIR__ . '/db.php';
unset($_SESSION['admin_id']);
session_regenerate_id(true);
header('Location: admin_login.php');
exit;