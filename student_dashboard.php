<?php
session_start();
require 'db.php';

if (($_SESSION['role'] ?? '') !== 'student' || !isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}
$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, fee_type, amount, paid_amount, status, due_date FROM fees WHERE user_id=? ORDER BY due_date ASC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$fees = $stmt->get_result();
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Student Dashboard</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 text-slate-900"><main class="mx-auto max-w-5xl p-6">
<header class="mb-8 flex items-center justify-between"><div><p class="text-sm text-indigo-600">Smart Access Portal</p><h1 class="text-3xl font-bold">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1></div><a class="rounded bg-slate-900 px-4 py-2 text-white" href="logout.php">Log out</a></header>
<?php if ($success): ?><div class="mb-4 rounded bg-green-100 p-3 text-green-800"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-4 rounded bg-red-100 p-3 text-red-800"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<section class="rounded-xl bg-white p-6 shadow"><div class="mb-5 flex items-center justify-between"><h2 class="text-xl font-semibold">Fees</h2><a href="pay_online.php" class="rounded bg-indigo-600 px-4 py-2 text-white">Make a payment</a></div>
<div class="overflow-x-auto"><table class="w-full text-left"><thead><tr class="border-b"><th class="p-3">Fee</th><th class="p-3">Amount</th><th class="p-3">Paid</th><th class="p-3">Balance</th><th class="p-3">Status</th></tr></thead><tbody>
<?php while ($fee = $fees->fetch_assoc()): ?><tr class="border-b"><td class="p-3"><?= htmlspecialchars($fee['fee_type']) ?></td><td class="p-3">KSh <?= number_format((float)$fee['amount'], 2) ?></td><td class="p-3">KSh <?= number_format((float)$fee['paid_amount'], 2) ?></td><td class="p-3">KSh <?= number_format(max(0, (float)$fee['amount'] - (float)$fee['paid_amount']), 2) ?></td><td class="p-3"><?= htmlspecialchars($fee['status']) ?></td></tr><?php endwhile; ?>
</tbody></table></div></section></main></body></html>
