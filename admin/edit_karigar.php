<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: a_dashboard.php");
  exit();
}
include __DIR__ . '/../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Karigar ID.");
}

$karigar_id = intval($_GET['id']);
$name = '';
$error = '';

// Fetch karigar data
$stmt = $conn->prepare("SELECT name FROM karigars WHERE id = ?");
$stmt->bind_param("i", $karigar_id);
$stmt->execute();
$stmt->bind_result($name);
if (!$stmt->fetch()) {
    die("Karigar not found.");
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name'] ?? '');

    if ($new_name === '') {
        $error = "Name cannot be empty.";
    } else {
        $update_stmt = $conn->prepare("UPDATE karigars SET name = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_name, $karigar_id);
        if ($update_stmt->execute()) {
            // Redirect to view page after update
            header("Location: view_karigars.php");
            exit;
        } else {
            $error = "Failed to update karigar: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Karigar - KariTrack</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
  <h2>Edit Karigar</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="name" class="form-label">Karigar Name</label>
      <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
    </div>

    <button type="submit" class="btn btn-primary">Update Karigar</button>
    <a href="view_karigars.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>
</body>
</html>
