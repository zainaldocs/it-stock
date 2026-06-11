<?php
// index.php
require_once __DIR__ . '/config/functions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>
