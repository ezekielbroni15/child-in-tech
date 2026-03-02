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
  session_start();
  if (!empty($_SESSION['admin_logged_in'])) {
      header('Location: dashboard.php');
      exit;
  }

  $error = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      require_once '../db/connect.php';
      $username = trim($_POST['username'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($username && $password) {
          $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
          $stmt->execute([$username]);
          $admin = $stmt->fetch();

          if ($admin && password_verify($password, $admin['password_hash'])) {
              $_SESSION['admin_logged_in'] = true;
              $_SESSION['admin_username']  = $admin['username'];
              header('Location: dashboard.php');
              exit;
          } else {
              $error = 'Incorrect username or password.';
          }
      } else {
          $error = 'Please enter both username and password.';
      }
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
