<?php
 session_start();
 require 'db.php';

 /* ===== CSRF CHECK ===== */
 if (
     !isset($_POST['csrf_token']) ||
     !isset($_SESSION['csrf_token']) ||
     $_POST['csrf_token'] !== $_SESSION['csrf_token']
 ) {
     die("Invalid CSRF token");
 }

 /* ===== INPUT ===== */
 $email    = trim($_POST['username'] ?? '');
 $password = $_POST['password'] ?? '';
 $remember = isset($_POST['remember']);

 if (!$email || !$password) {
     $_SESSION['error'] = "All fields are required";
     header("Location: index.php");
     exit;
 }

 /* ===== USER QUERY ===== */
 $stmt = $conn->prepare(
     "SELECT id, full_name, password, role FROM users WHERE email = ? LIMIT 1"
 );
 $stmt->bind_param("s", $email);
 $stmt->execute();
 $result = $stmt->get_result();

 if ($result->num_rows !== 1) {
     $_SESSION['error'] = "Invalid email or password";
     header("Location: index.php");
     exit;
 }

 $user = $result->fetch_assoc();

 /* ===== PASSWORD CHECK ===== */
 if (!password_verify($password, $user['password'])) {
     $_SESSION['error'] = "Invalid email or password";
     header("Location: index.php");
     exit;
 }

 /* ===== LOGIN SUCCESS ===== */
 $_SESSION['user_id']   = $user['id'];
 $_SESSION['full_name'] = $user['full_name'];
 $_SESSION['role']      = $user['role'];

 /* ===== REMEMBER ME ===== */
 if ($remember) {
     setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/");
 }

 /* ===== REDIRECT ===== */
 header("Location: redirect.php");
 exit;

