<?php
session_start();
require "db.php";
require_once "twilio_config.php";

// Validate session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in first";
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch unpaid fee
$stmt = $conn->prepare("
    SELECT id, amount, paid_amount 
    FROM fees 
    WHERE user_id=? AND status='Unpaid' 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();

if (!$fee) {
    die("No unpaid fees found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $paying_amount = floatval($_POST['amount'] ?? 0);

    if ($paying_amount <= 0) {
        $_SESSION['error'] = "Invalid payment amount";
        header("Location: student_dashboard.php?page=fee&payment");
        exit;
    }

    $remaining = max(0, (float) $fee['amount'] - (float) $fee['paid_amount']);
    if ($paying_amount > $remaining) {
        $_SESSION['error'] = 'Payment cannot exceed the remaining balance.';
        header("Location: student_dashboard.php?page=fee&payment");
        exit;
    }

    // Calculate new values
    $new_paid = (float) $fee['paid_amount'] + $paying_amount;
    $balance = (float) $fee['amount'] - $new_paid;

    if ($balance <= 0) {
        $balance = 0;
        $status = 'Paid';
    } else {
        $status = 'Unpaid';
    }

    // Generate transaction code
    $transaction_code = "TXN" . date('YmdHis') . rand(1000, 9999);

    // Update fee record
    $update = $conn->prepare("
        UPDATE fees 
        SET paid_amount=?, status=?, transaction_code=? 
        WHERE id=?
    ");
    $update->bind_param("dssi", $new_paid, $status, $transaction_code, $fee['id']);

    if ($update->execute()) {

        // Insert payment record
        $insert_payment = $conn->prepare("
            INSERT INTO payments (user_id, fee_id, amount, method, reference, status) 
            VALUES (?, ?, ?, 'manual', ?, 'success')
        ");
        $insert_payment->bind_param("iids", $user_id, $fee['id'], $paying_amount, $transaction_code);
        $insert_payment->execute();

        // Restore access if fully paid
        if ($status === 'Paid') {
            $restore = $conn->prepare("
                UPDATE users 
                SET access_status='Active' 
                WHERE id=?
            ");
            $restore->bind_param("i", $user_id);
            $restore->execute();
        }

        // Send SMS if fully paid
        if ($status === 'Paid') {
            $stmt2 = $conn->prepare("
                SELECT users.phone, users.full_name, fees.fee_type 
                FROM fees 
                JOIN users ON fees.user_id = users.id 
                WHERE fees.id = ?
            ");
            $stmt2->bind_param("i", $fee['id']);
            $stmt2->execute();
            $user = $stmt2->get_result()->fetch_assoc();

            if ($user && !empty($user['phone']) && isset($twilioClient)) {
                $to = $user['phone'];
                $name = $user['full_name'];
                $fee_name = $user['fee_type'];

                $message = "Hi $name, your payment for $fee_name has been received successfully. Thank you!";

                try {
                    $twilioClient->messages->create($to, [
                        'from' => $twilio_number,
                        'body' => $message
                    ]);
                    $_SESSION['success'] = "Payment recorded and SMS sent.";
                } catch (Exception $e) {
                    $_SESSION['success'] = "Payment recorded.";
                }
            } else {
                $_SESSION['success'] = "Payment recorded.";
            }
        } else {
            $_SESSION['success'] = "Payment recorded.";
        }

        header("Location: student_dashboard.php?page=fee");
        exit;
    } else {
        $_SESSION['error'] = "Payment failed.";
        header("Location: student_dashboard.php?page=fee&payment");
        exit;
    }
}

// If not POST, perhaps show form, but since it's backend, maybe not needed.
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pay Fees</title>
</head>
<body>

<h2>Enter Payment Amount</h2>

<p>Total Fee: <?php echo $fee['amount']; ?></p>
<p>Already Paid: <?php echo $fee['paid_amount']; ?></p>
<p>Remaining Balance: <?php echo $fee['amount'] - $fee['paid_amount']; ?></p>

<form method="POST">
    <label>Enter Amount You Are Paying:</label><br>
    <input type="number" name="amount" step="0.01" required><br><br>
    <button type="submit">Submit Payment</button>
</form>
</body>
</html>
