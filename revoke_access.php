<?php
session_start();
require 'db.php';

if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: index.php'); exit; }
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) { $_SESSION['error'] = 'Invalid student.'; header('Location: admin_dashboard.php'); exit; }
$stmt = $conn->prepare("UPDATE users SET temp_access_until=NULL, access_status='Inactive' WHERE id=? AND role='student'");
$stmt->bind_param('i', $user_id);
$ok = $stmt->execute();
$_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Temporary access revoked.' : 'Unable to revoke access.';
header('Location: admin_dashboard.php'); exit;
