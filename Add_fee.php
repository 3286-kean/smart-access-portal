<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_id  = intval($_POST['user_id'] ?? 0);
$fee_type = trim($_POST['fee_type'] ?? '');
$amount   = floatval($_POST['amount'] ?? 0);
$status   = in_array($_POST['status'] ?? '', ['Paid', 'Unpaid']) ? $_POST['status'] : 'Unpaid';
$due_date = trim($_POST['due_date'] ?? '');

if ($user_id <= 0 || empty($fee_type) || $amount <= 0 || empty($due_date)) {
    $_SESSION['error'] = "Invalid fee details. Please check all fields.";
    header("Location: admin_dashboard.php");
    exit;
}

if (!strtotime($due_date)) {
    $_SESSION['error'] = "Invalid due date format";
    header("Location: admin_dashboard.php");
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
$check->bind_param("i", $user_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $_SESSION['error'] = "Invalid student selected";
    header("Location: admin_dashboard.php");
    exit;
}

$sql = "INSERT INTO fees (user_id, fee_type, amount, status, due_date)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isdss", $user_id, $fee_type, $amount, $status, $due_date);

if ($stmt->execute()) {
    $_SESSION['success'] = "Fee added successfully";
} else {
    $_SESSION['error'] = "Error adding fee: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: admin_dashboard.php");
exit;
?>
