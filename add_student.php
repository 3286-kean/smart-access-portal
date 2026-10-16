<?php
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Validate and sanitize inputs
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$program   = trim($_POST['program'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$role      = $_POST['role'] ?? 'student';

// Validate required fields
if (empty($full_name) || empty($email) || empty($password) || empty($program) || empty($phone)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: admin_dashboard.php");
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: admin_dashboard.php");
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $_SESSION['error'] = "Email already exists";
    header("Location: admin_dashboard.php");
    exit;
}

// Hash password
$password = password_hash($password, PASSWORD_DEFAULT);

// Auto-generate Student ID
$prefix = "ST";
$latest = $conn->query("SELECT id FROM users ORDER BY id DESC LIMIT 1")->fetch_assoc();
$newNumber = $latest ? $latest['id'] + 1 : 1;
$student_id = $prefix . str_pad($newNumber, 5, "0", STR_PAD_LEFT);

// Insert student with phone field
$stmt = $conn->prepare("INSERT INTO users (student_id, full_name, email, password, program, role, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $student_id, $full_name, $email, $password, $program, $role, $phone);

if ($stmt->execute()) {
    $_SESSION['success'] = "Student added successfully";
} else {
    $_SESSION['error'] = "Error adding student: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: admin_dashboard.php");
exit;
?>
