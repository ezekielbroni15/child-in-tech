<?php
// admin/auth.php — shared session authentication guard
// Include at the top of every admin page (except index.php)
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
