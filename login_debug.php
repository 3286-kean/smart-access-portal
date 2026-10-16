<?php
session_start();
require 'db.php';

echo "<pre>";
echo "=== LOGIN DEBUG ===\n\n";

echo "POST Data Received:\n";
var_dump($_POST);

echo "\n\nSession CSRF Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "\n";
echo "POST CSRF Token: " . ($_POST['csrf_token'] ?? 'NOT SET') . "\n";

/* ===== CSRF CHECK ===== */
if (
    !isset($_POST['csrf_token']) ||
    !isset($_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    echo "\n CSRF Token Mismatch!\n";
    die("Invalid CSRF token");
}

echo "\n CSRF Token Valid\n";

/* ===== INPUT ===== */
$email    = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

echo "\nEmail: $email\n";
echo "Password: " . (strlen($password) > 0 ? "***" : "EMPTY") . "\n";
echo "Remember: " . ($remember ? "YES" : "NO") . "\n";

if (!$email || !$password) {
    echo "\n Missing Email or Password!\n";
    $_SESSION['error'] = "All fields are required";
    header("Location: index.php");
    exit;
}

echo "\n Email and Password provided\n";

/* ===== USER QUERY ===== */
echo "\nQuerying database for email: $email\n";
$stmt = $conn->prepare(
    "SELECT id, full_name, password, role FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

echo "Query Result Rows: " . $result->num_rows . "\n";

if ($result->num_rows !== 1) {
    echo "\n User not found in database!\n";
    echo "Users in database:\n";
    $all_users = $conn->query("SELECT email, role FROM users");
    while ($row = $all_users->fetch_assoc()) {
        echo "  - " . $row['email'] . " (" . $row['role'] . ")\n";
    }
    $_SESSION['error'] = "Invalid email or password";
    header("Location: index.php");
    exit;
}

echo " User found!\n";

$user = $result->fetch_assoc();
echo "User ID: " . $user['id'] . "\n";
echo "Full Name: " . $user['full_name'] . "\n";
echo "Role: " . $user['role'] . "\n";
echo "Password Hash: " . substr($user['password'], 0, 20) . "...\n";

/* ===== PASSWORD CHECK ===== */
echo "\nVerifying password...\n";
if (!password_verify($password, $user['password'])) {
    echo " Password verification FAILED!\n";
    $_SESSION['error'] = "Invalid email or password";
    header("Location: index.php");
    exit;
}

echo " Password verification PASSED!\n";

/* ===== LOGIN SUCCESS ===== */
echo "\nSetting session variables...\n";
$_SESSION['user_id']   = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role']      = $user['role'];

echo "  User ID: " . $_SESSION['user_id'] . "\n";
echo "  Full Name: " . $_SESSION['full_name'] . "\n";
echo "  Role: " . $_SESSION['role'] . "\n";

echo "\n Session variables set!\n";

/* ===== REMEMBER ME ===== */
if ($remember) {
    setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/");
    setcookie("role", $_SESSION['role'], time() + (30 * 24 * 60 * 60), "/");
    echo " Remember-me cookies set\n";
}

echo "\n LOGIN SUCCESSFUL!\n";
echo "Redirecting to redirect.php...\n\n";

echo "</pre>";

/* ===== REDIRECT ===== */
header("Location: redirect.php");
exit;
?>
