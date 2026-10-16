<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

echo "=== DATABASE DEBUG ===\n\n";

if ($conn->connect_error) {
    die(" Database connection FAILED: " . $conn->connect_error . "\n");
}

echo " Connected to MySQL database\n\n";
//teting if table are working
echo " Tables in database:\n";
$result = $conn->query("SHOW TABLES");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_row()) {
        echo "  - " . $row[0] . "\n";
    }
} else {
    echo "   No tables found!\n";
}

echo "\n";

// Checking users
echo " Users in database:\n";
$users = $conn->query("SELECT id, email, role, full_name FROM users");
if ($users && $users->num_rows > 0) {
    while ($row = $users->fetch_assoc()) {
        echo "  - ID: {$row['id']}, Email: {$row['email']}, Role: {$row['role']}, Name: {$row['full_name']}\n";
    }
} else {
    echo "   No users found!\n";
}

echo "\n";

//  fees
echo " Fees in database:\n";
$fees = $conn->query("SELECT id, user_id, fee_type, amount, status FROM fees");
if ($fees && $fees->num_rows > 0) {
    echo "  Found {$fees->num_rows} fees\n";
    while ($row = $fees->fetch_assoc()) {
        echo "  - Fee ID: {$row['id']}, User ID: {$row['user_id']}, Type: {$row['fee_type']}, Amount: {$row['amount']}, Status: {$row['status']}\n";
    }
} else {
    echo "  No fees found yet\n";
}

echo "\n=== END DEBUG ===\n";

$conn->close();
?>
