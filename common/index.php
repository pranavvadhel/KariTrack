<?php
session_start();
include 'db.php';

$error = '';

// Auto-login if cookie is set
if (!isset($_SESSION['karigar_id']) && isset($_COOKIE['karigar_id'])) {
  $_SESSION['karigar_id'] = $_COOKIE['karigar_id'];
  $_SESSION['name'] = $_COOKIE['karigar_name'];
  $_SESSION['role'] = 'karigar';
  header("Location: karigar/k_dashboard.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, name, password FROM karigars WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      $_SESSION['karigar_id'] = $user['id'];
      $_SESSION['name'] = $user['name'];
      $_SESSION['role'] = 'karigar';

      // Set cookie if "Remember Me" is checked
      if (isset($_POST['remember'])) {
        setcookie("karigar_id", $user['id'], time() + (86400 * 30), "/"); // 30 days
        setcookie("karigar_name", $user['name'], time() + (86400 * 30), "/");
      }

      header("Location: karigar/k_dashboard.php");
      exit;
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "User not found.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - KariTrack</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      padding: 30px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }
    .no-underline {
      text-decoration: none;
    }
    @media (max-width: 575.98px) {
      .login-card {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="login-card">
      <h4 class="text-center mb-4">🧵 KariTrack Login</h4>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="remember" id="remember">
          <label class="form-check-label" for="remember">Remember Me</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <div class="text-center mt-4">
        <a href="signup.php" class="no-underline me-3">Don't have an account? Sign up</a><br>
        <a href="admin_login.php" class="no-underline mt-2 d-inline-block" style="color: black;">🛡️ Admin Login</a>
      </div>
    </div>
  </div>

</body>
</html>
