<?php
session_start();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prefill form  cookies
$remembered_username = $_COOKIE['username'] ?? '';
$remembered_role     = $_COOKIE['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Access Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="lang-switcher">
  <label for="language-select" class="sr-only" data-i18n="languageLabel">Language</label>
  <select id="language-select" class="lang-switcher-select" aria-label="Select language">
    <option value="en">English</option>
    <option value="sw">Kiswahili</option>
  </select>
</div>

<!-- ===== ROLE SELECTION SCREEN ===== -->
<div id="role-screen" class="fixed inset-0 flex items-center justify-center bg-transparentz-50 transition-opacity duration-500">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md text-center transform transition-transform duration-300">
    <h1 class="text-2xl font-bold mb-6" data-i18n="homeTitle">University Smart Portal</h1>
    <div class="space-y-4">
      <button id="choose-student" class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition" data-i18n="studentBtn">Student</button>
      <button id="choose-admin" class="w-full py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition" data-i18n="adminBtn">Admin</button>
    </div>
  </div>
</div>

<!-- ===== LOGIN SCREEN ===== -->
<div id="auth-screen" class="fixed inset-0 flex items-center justify-center hidden bg-transparent z-40 transition-opacity duration-500">
  <div class="bg-blue-600 p-8 rounded-xl shadow-lg w-full max-w-md transform transition-transform duration-300">
    <h2 id="login-title" class="text-xl font-bold text-center mb-6 text-white" data-i18n="loginTitle">Login</h2>

    <?php if(isset($_SESSION['error'])): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <form id="login-form" action="login.php" method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <input type="hidden" name="role" id="role-input" value="<?= htmlspecialchars($remembered_role) ?>">
      <input type="email" name="username" placeholder="Email" required value="<?= htmlspecialchars($remembered_username) ?>" class="w-full px-4 py-3 border rounded-lg" data-i18n="emailPlaceholder">
      <div class="relative">
        <input type="password" name="password" id="password" placeholder="Password" required class="w-full px-4 py-3 border rounded-lg pr-16" data-i18n="passwordPlaceholder">
        <button type="button" id="togglePassword" class="absolute right-3 top-3 text-sm text-gray-500" data-i18n="showPassword">Show</button>
      </div>

      <label class="flex items-center space-x-2 text-sm">
        <input type="checkbox" name="remember">
        <span data-i18n="rememberMe">Remember me</span>
      </label>

      <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition" data-i18n="signInButton">Sign In</button>
    </form>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>

