<?php
session_start();

$error = '';

// Static admin credentials
$static_admin_id = 'admin';
$static_admin_password = '12345';

// Auto-login if cookies exist
if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_id']) && $_COOKIE['admin_id'] === $static_admin_id) {
  $_SESSION['admin_id'] = $_COOKIE['admin_id'];
  $_SESSION['admin_name'] = $_COOKIE['admin_name'];
  header("Location: admin/a_dashboard.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if ($email === $static_admin_id && $password === $static_admin_password) {
    $_SESSION['admin_id'] = $static_admin_id;
    $_SESSION['admin_name'] = 'Admin';

    // If "Remember Me" is checked, set cookies for 30 days
    if (isset($_POST['remember'])) {
      setcookie('admin_id', $static_admin_id, time() + (86400 * 30), "/");
      setcookie('admin_name', 'Admin', time() + (86400 * 30), "/");
    }

    header("Location: admin/a_dashboard.php");
    exit;
  } else {
    $error = "Invalid Admin ID or Password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - KariTrack</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <h4 class="text-center mb-4">🛡️ Admin Login</h4>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Admin ID</label>
            <input type="text" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember Me</label>
          </div>
          <button type="submit" class="btn btn-dark w-100">Login as Admin</button>
        </form>
        <div class="text-center mt-3">
          <a href="index.php" style="text-decoration: none;">Back to User Login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
