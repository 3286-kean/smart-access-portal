<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$success_msg = $_SESSION['success'] ?? '';
$error_msg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle fee status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_id']) && isset($_POST['new_status'])) {
    $fee_id = intval($_POST['fee_id']);
    $new_status = $_POST['new_status'];
    $update_stmt = $conn->prepare("UPDATE fees SET status=? WHERE id=?");
    $update_stmt->bind_param("si", $new_status, $fee_id);
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Fee status updated successfully";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

$status_filter = $_GET['status_filter'] ?? '';
$sql = "SELECT fees.*, users.full_name 
        FROM fees 
        JOIN users ON fees.user_id = users.id";

if ($status_filter) {
    $sql .= " WHERE fees.status=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_filter);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$fees_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <div class="w-64 bg-indigo-700 text-white flex flex-col">

        <div class="p-6 bg-indigo-800">
            <h1 class="text-xl font-bold">Smart Access</h1>
            <p class="text-sm text-indigo-200">Admin Panel</p>
        </div>

        <div class="p-4 border-b border-indigo-600">
            <p class="font-semibold"><?= htmlspecialchars($_SESSION['full_name']); ?></p>
            <p class="text-xs text-indigo-200">Role: Admin</p>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <button onclick="showSection('dashboard')" 
                class="w-full text-left px-4 py-2 rounded hover:bg-indigo-600">
                Dashboard
            </button>

            <button onclick="showSection('fees')" 
                class="w-full text-left px-4 py-2 rounded hover:bg-indigo-600">
                Manage Fees & Add New Student
            </button>

            <button onclick="showSection('access')" 
                class="w-full text-left px-4 py-2 rounded hover:bg-indigo-600">
                Manage Student Access
            </button>
        </nav>

        <div class="p-4">
            <a href="logout.php" 
               class="block text-center bg-red-600 py-2 rounded hover:bg-red-700">
                Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 p-8">

        <?php if ($success_msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>


        <!-- DASHBOARD SECTION -->
        <div id="dashboard" class="section">
            <h2 class="text-2xl font-bold mb-6">Dashboard Overview</h2>

            <?php
            $total_students_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student'");
            $total_students = $total_students_result ? $total_students_result->fetch_assoc()['total'] : 0;
            
            $paid_fees_result = $conn->query("SELECT COUNT(*) as total FROM fees WHERE status='Paid'");
            $paid_fees = $paid_fees_result ? $paid_fees_result->fetch_assoc()['total'] : 0;
            
            $unpaid_fees_result = $conn->query("SELECT COUNT(*) as total FROM fees WHERE status='Unpaid'");
            $unpaid_fees = $unpaid_fees_result ? $unpaid_fees_result->fetch_assoc()['total'] : 0;
            ?>

            <div class="grid grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded shadow">
                    <h3 class="text-lg font-semibold">Total Students</h3>
                    <p class="text-3xl font-bold mt-2"><?= $total_students ?></p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <h3 class="text-lg font-semibold text-green-600">Paid Fees</h3>
                    <p class="text-3xl font-bold mt-2"><?= $paid_fees ?></p>
                </div>

                <div class="bg-white p-6 rounded shadow">
                    <h3 class="text-lg font-semibold text-red-600">Unpaid Fees</h3>
                    <p class="text-3xl font-bold mt-2"><?= $unpaid_fees ?></p>
                </div>
            </div>
        </div>


        <!-- FEES SECTION -->
        <div id="fees" class="section hidden">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Assign Fee</h2>
                <button onclick="openModal()" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    + Add New Student
                </button>
            </div>

            <form action="Add_fee.php" method="POST" class="grid grid-cols-3 gap-4 mb-6">

                <select name="user_id" required class="border p-2 rounded">
                    <option value="">Select Student</option>
                    <?php 
                    $students = $conn->query("SELECT id, full_name FROM users WHERE role='student'");
                    while ($s = $students->fetch_assoc()): ?>
                        <option value="<?= $s['id']; ?>">
                            <?= htmlspecialchars($s['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="fee_type" placeholder="Fee Type" required class="border p-2 rounded">

                <input type="number" step="0.01" name="amount" placeholder="Amount" required class="border p-2 rounded">

                <input type="date" name="due_date" required class="border p-2 rounded">

                <select name="status" class="border p-2 rounded">
                    <option value="Unpaid">Unpaid</option>
                    <option value="Paid">Paid</option>
                </select>

                <button class="col-span-3 bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">
                    Add Fee
                </button>
            </form>

            <!-- FILTER SECTION -->
            <div class="mb-6 flex gap-4 items-center">
                <form method="GET" class="flex gap-2 items-center">
                    <label class="font-semibold">Filter by Status:</label>
                    <select name="status_filter" class="border p-2 rounded" onchange="this.form.submit()">
                        <option value="">All Fees</option>
                        <option value="Paid" <?= $status_filter === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Unpaid" <?= $status_filter === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                    </select>
                </form>
            </div>

            <!-- FEES TABLE -->
            <table class="w-full border bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 border">Student</th>
                        <th class="p-2 border">Fee Type</th>
                        <th class="p-2 border">Amount</th>
                        <th class="p-2 border">Status</th>
                        <th class="p-2 border">Due Date</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $fees_result->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td class="border p-2"><?= htmlspecialchars($row['full_name']); ?></td>
                            <td class="border p-2"><?= htmlspecialchars($row['fee_type']); ?></td>
                            <td class="border p-2">KSH<?= number_format($row['amount'],2); ?></td>
                            <td class="border p-2 <?= $row['status']=='Paid'?'text-green-600':'text-red-600'; ?>">
                                <?= $row['status']; ?>
                            </td>
                            <td class="border p-2"><?= date("d M Y", strtotime($row['due_date'])); ?></td>
                            <td class="border p-2 space-x-2">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="fee_id" value="<?= $row['id']; ?>">
                                    <select name="new_status" class="border p-1 rounded text-sm" onchange="if(confirm('Update status?')) this.form.submit();">
                                        <option value="">Change Status</option>
                                        <option value="Paid">Mark Paid</option>
                                        <option value="Unpaid">Mark Unpaid</option>
                                    </select>
                                </form>
                                <form action="delete_fee.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="fee_id" value="<?= $row['id']; ?>">
                                    <button type="submit" onclick="return confirm('Delete this fee?')" class="bg-red-600 text-white px-2 py-1 rounded text-sm hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>

        <!-- ACCESS SECTION -->
        <div id="access" class="section hidden">
            <h2 class="text-2xl font-bold mb-6">Manage Student Access</h2>

            <form action="update_access.php" method="POST" class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-lg font-semibold mb-4">Grant Temporary Access</h3>

                <select name="user_id" required class="border p-2 rounded w-full mb-4">
                    <option value="">Select Student</option>
                    <?php
                    $students_access = $conn->query("SELECT id, full_name FROM users WHERE role='student'");
                    while ($s = $students_access->fetch_assoc()): ?>
                        <option value="<?= $s['id']; ?>">
                            <?= htmlspecialchars($s['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label class="block mb-2">Temporary Access Until:</label>
                <input type="date" name="temp_access_until" required class="border p-2 rounded w-full mb-4">

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Grant Access
                </button>
            </form>

            <h3 class="text-lg font-semibold mb-4">Students with Temporary Access</h3>
            <table class="w-full border bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 border">Student</th>
                        <th class="p-2 border">Temporary Access Until</th>
                        <th class="p-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $temp_access_stmt = $conn->prepare("SELECT id, full_name, temp_access_until FROM users WHERE role='student' AND temp_access_until IS NOT NULL ORDER BY temp_access_until DESC");
                    $temp_access_stmt->execute();
                    $temp_result = $temp_access_stmt->get_result();
                    while ($row = $temp_result->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td class="border p-2"><?= htmlspecialchars($row['full_name']); ?></td>
                            <td class="border p-2"><?= $row['temp_access_until'] ? date("d M Y", strtotime($row['temp_access_until'])) : 'N/A'; ?></td>
                            <td class="border p-2">
                                <form action="revoke_access.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                    <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                        Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>


<!-- MODAL -->
<div id="studentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Add New Student</h2>

        <form action="add_student.php" method="POST" class="space-y-4">
            <input type="text" name="full_name" placeholder="Full Name" required class="w-full border p-2 rounded">
            <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded">
            <input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded">
            <input type="text" name="program" placeholder="Program" required class="w-full border p-2 rounded">
            <input type="tel" name="phone" placeholder="Phone" required class="w-full border p-2 rounded">
            <input type="hidden" name="role" value="student">

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="border px-4 py-2 rounded">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
                    Add Student
                </button>
            </div>
        </form>
    </div>
</div>
<script>
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });
    document.getElementById(sectionId).classList.remove('hidden');
}

function openModal() {
    document.getElementById('studentModal').classList.remove('hidden');
    document.getElementById('studentModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('studentModal').classList.remove('flex');
    document.getElementById('studentModal').classList.add('hidden');
}
</script>
</body>
</html>



