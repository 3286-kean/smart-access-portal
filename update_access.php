<?php
session_start();
require 'db.php';

if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: index.php'); exit; }
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$date = $_POST['temp_access_until'] ?? '';
$date_ok = DateTime::createFromFormat('Y-m-d', $date);
if (!$user_id || !$date_ok || $date_ok->format('Y-m-d') !== $date) {
    $_SESSION['error'] = 'Invalid access details.';
    header('Location: admin_dashboard.php'); exit;
}
$stmt = $conn->prepare("UPDATE users SET temp_access_until=?, access_status='Active' WHERE id=? AND role='student'");
$stmt->bind_param('si', $date, $user_id);
$ok = $stmt->execute();
$_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Temporary access granted.' : 'Unable to grant access.';
header('Location: admin_dashboard.php'); exit;
