<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — Child In Tech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/admin.css"/>
</head>
<body class="admin-login-page">
<?php
// ── Session hardening ────────────────────────────────────────
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Brute-force protection ────────────────────────────────────
$lockFile   = sys_get_temp_dir() . '/cit_login_attempts.json';
$maxAttempts = 5;
$lockWindow  = 15 * 60; // 15 minutes
$clientIP    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$attempts    = json_decode(@file_get_contents($lockFile), true) ?? [];

// Clean up old entries
foreach ($attempts as $ip => $data) {
    if (time() - $data['first'] > $lockWindow) unset($attempts[$ip]);
}

$locked = isset($attempts[$clientIP]) && $attempts[$clientIP]['count'] >= $maxAttempts;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif ($locked) {
        $error = 'Too many failed attempts. Please wait 15 minutes.';
    } else {
        require_once '../db/connect.php';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Clear failed attempts
                unset($attempts[$clientIP]);
                file_put_contents($lockFile, json_encode($attempts));

                session_regenerate_id(true); // prevent session fixation
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $admin['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                // Record failed attempt
                if (!isset($attempts[$clientIP])) {
                    $attempts[$clientIP] = ['count' => 0, 'first' => time()];
                }
                $attempts[$clientIP]['count']++;
                file_put_contents($lockFile, json_encode($attempts));

                $remaining = $maxAttempts - $attempts[$clientIP]['count'];
                $error = $remaining > 0
                    ? "Incorrect username or password. ($remaining attempts left)"
                    : 'Account locked for 15 minutes due to too many failed attempts.';
            }
        } else {
            $error = 'Please enter both username and password.';
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

  <div class="login-wrapper">
    <div class="login-card">
      <!-- Logo -->
      <div class="login-logo">
        <img src="../assets/image/logo.png" alt="Child In Tech"/>
      </div>

      <div class="login-header">
        <h1>Admin Portal</h1>
        <p>Sign in to the Innoventure management dashboard</p>
      </div>

      <?php if ($error): ?>
        <div class="admin-alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="login-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"/>
        <div class="admin-field">
          <label>Username</label>
          <input type="text" name="username" placeholder="admin" required autocomplete="username"/>
        </div>
        <div class="admin-field">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password"/>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%">
          Sign In →
        </button>
      </form>

      <a href="../index.html" class="login-back">← Back to website</a>
    </div>

    <!-- Decorative spheres -->
    <div class="login-sphere s1"></div>
    <div class="login-sphere s2"></div>
    <div class="login-sphere s3"></div>
  </div>
</body>
</html>
