<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_id'])) {
    if ($conn->connect_error) {
        $_SESSION['error'] = "Database Error";
    } else {
        $fee_id = intval($_POST['fee_id']);
        $stmt = $conn->prepare("DELETE FROM fees WHERE id=?");
        $stmt->bind_param("i", $fee_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Fee deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete fee";
        }
        $stmt->close();
        $conn->close();
    }
}

header("Location: admin_dashboard.php");
exit;
