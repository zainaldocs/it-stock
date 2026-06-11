<?php
// logout.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (isset($_SESSION['user_id'])) {
    log_activity($pdo, 'LOGOUT', "User {$_SESSION['username']} logged out.");
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
