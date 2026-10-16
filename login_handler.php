<?php
session_start();
require 'db.php';

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo "Invalid request";
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === 'adminname@kabaka.com' && $password === 'admin123') {
    $_SESSION['role'] = 'admin';
    $_SESSION['full_name'] = 'Admin User';
    header("Location: admin_dashboard.php");
    exit;
} elseif ($username === 'studentname@kabaka.com' && $password === 'student123') {
    $_SESSION['role'] = 'student';
    $_SESSION['full_name'] = 'Student User';
    header("Location: student_dashboard.php");
    exit;
} else {
    echo "Invalid username or password";
}
