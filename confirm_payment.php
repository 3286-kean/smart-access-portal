<?php
session_start();
require "db.php";
require_once "twilio_config.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in first";
    header("Location: index.php");
    exit;
}

$reference = isset($_GET['ref']) ? trim($_GET['ref']) : '';
if (!$reference || !preg_match('/^PAY_\d+_\d+$/', $reference)) {
    $_SESSION['error'] = "Invalid payment reference";
    header("Location: student_dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE payments SET status='success' WHERE reference=? AND user_id=?");
$stmt->bind_param("si", $reference, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $_SESSION['error'] = "Payment not found";
    header("Location: student_dashboard.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, fee_id FROM payments WHERE reference=? AND user_id=?");
$stmt->bind_param("si", $reference, $_SESSION['user_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    $_SESSION['error'] = "Payment verification failed";
    header("Location: student_dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE fees SET status='Paid' WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $payment['fee_id'], $_SESSION['user_id']);
$stmt->execute();

$stmt2 = $conn->prepare(
    "SELECT users.phone, users.full_name, fees.fee_type
     FROM fees
     JOIN users ON fees.user_id = users.id
     WHERE fees.id = ? AND users.id = ?"
);
$stmt2->bind_param("ii", $payment['fee_id'], $_SESSION['user_id']);
$stmt2->execute();
$user = $stmt2->get_result()->fetch_assoc();

if ($user && !empty($user['phone']) && $twilioClient) {
    $to = $user['phone'];
    $name = $user['full_name'];
    $fee_name = $user['fee_type'];
    $message = "Hi $name, your payment for $fee_name has been received successfully. Thank you!";

    try {
        $twilioClient->messages->create($to, ['from' => $twilio_number, 'body' => $message]);
        $_SESSION['success'] = "Payment successful and SMS sent.";
    } catch (Exception $e) {
        error_log("Twilio SMS error: " . $e->getMessage());
        $_SESSION['warning'] = "Payment recorded but SMS failed to send.";
    }
} else {
    $_SESSION['success'] = "Payment successful!";
}

if (isset($stmt2)) $stmt2->close();

header("Location: student_dashboard.php");
exit;
