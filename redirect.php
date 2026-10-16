<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'));
exit;
