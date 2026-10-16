<?php
session_start();
require "db.php";

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
    $_SESSION['error'] = "No unpaid fees found";
    header("Location: student_dashboard.php");
    exit;
}

$remaining_amount = $fee['amount'] - $fee['paid_amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Money Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0f4ff 0%, #b3e5fc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <i class="fas fa-mobile-alt text-4xl text-blue-600 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800">Mobile Money Payment</h1>
                <p class="text-gray-500 text-sm mt-2">Pay securely via M-Pesa or other mobile money</p>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                <p class="text-blue-700 font-semibold mb-2">
                    <i class="fas fa-info-circle mr-2"></i> Outstanding Balance
                </p>
                <p class="text-blue-900 text-2xl font-bold">
                    Ksh <?= number_format($remaining_amount, 2) ?>
                </p>
            </div>

            <form method="POST" action="initiate_payment.php" class="space-y-4">
                <div>
                    <label for="amount" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-money-bill mr-2 text-green-600"></i> Amount to Pay (Ksh)
                    </label>
                    <input type="number" id="amount" name="amount" placeholder="Enter amount" step="0.01" min="1" max="<?= $remaining_amount ?>" required class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-600" value="">
                    <?php if ($remaining_amount > 0): ?>
                        <p class="text-gray-600 text-sm mt-2">
                            Max: Ksh <?= number_format($remaining_amount, 2) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="phone" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-phone mr-2 text-green-600"></i> Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone" placeholder="254712345678" required class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-600">
                    <p class="text-gray-600 text-sm mt-2">Format: 254XXXXXXXXX</p>
                </div>

                <div class="bg-yellow-50 border border-yellow-300 p-4 rounded-lg">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Instructions:</strong> You will receive a prompt on your phone to enter your M-Pesa PIN.
                    </p>
                </div>
                
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:shadow-lg transition transform hover:-translate-y-1 font-semibold">
                    <i class="fas fa-check-circle mr-2"></i> Send Payment Request
                </button>
            </form>

            <a href="student_dashboard.php" class="block w-full px-6 py-3 mt-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition text-center font-semibold">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
