<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $mobile = trim($_POST['mobile']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    $stmt = $conn->prepare("SELECT id FROM karigars WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Email already registered.";
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $role = 'karigar';

      $stmt = $conn->prepare("INSERT INTO karigars (name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $name, $email, $mobile, $hashed_password, $role);

      if ($stmt->execute()) {
        $_SESSION['karigar_id'] = $stmt->insert_id;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $role;

        header("Location: karigar/k_dashboard.php");
        exit;
      } else {
        $error = "Something went wrong. Please try again.";
      }
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="shortcut icon" href="../abc/image/karitrack.png">
  <meta charset="UTF-8">
  <title>Signup - KariTrack</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <h4 class="text-center mb-4">🧵 KariTrack Signup</h4>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mobile Number</label>
            <input type="text" name="mobile" class="form-control" pattern="[0-9]{10}" maxlength="10" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
          </div>
          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="showPasswordToggle">
            <label class="form-check-label" for="showPasswordToggle">Show Password</label>
          </div>
          <button type="submit" class="btn btn-success w-100">Sign Up</button>
        </form>
        <div class="text-center mt-3">
          <a href="index.php">Already have an account? Login here</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    const toggle = document.getElementById("showPasswordToggle");
    toggle.addEventListener("change", function () {
      const password = document.getElementById("password");
      const confirm = document.getElementById("confirm_password");
      const type = this.checked ? "text" : "password";
      password.type = type;
      confirm.type = type;
    });
  </script>
</body>
</html>
