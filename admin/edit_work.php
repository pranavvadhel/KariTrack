<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: a_dashboard.php");
  exit();
}
include __DIR__ . '/db.php';

if (!isset($_GET['id'])) {
  echo "No entry ID.";
  exit();
}

$id = intval($_GET['id']);

$result = $conn->query("SELECT * FROM work_entries WHERE id = $id");
$entry = $result->fetch_assoc();

if (!$entry) {
  echo "Entry not found.";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = $_POST['date'];
  $category = $_POST['category'];
  $quantity = $_POST['quantity'];
  $price = $_POST['price'];
  $total = $quantity * $price;

  $conn->query("UPDATE work_entries SET date='$date', category='$category', quantity='$quantity', price='$price', total='$total' WHERE id=$id");
  header("Location: karigar_profile.php?id=" . $entry['karigar_id']);
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="shortcut icon" href="../abc/image/karitrack.png">
  <title>Edit Work Entry</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main>
    <h2>Edit Work Entry</h2>
    <form method="POST">
      <label>Date:</label>
      <input type="date" name="date" value="<?= $entry['date'] ?>" required><br>

      <label>Category:</label>
      <select name="category">
        <option <?= $entry['category'] === 'Cuff' ? 'selected' : '' ?>>Cuff</option>
        <option <?= $entry['category'] === 'Collar' ? 'selected' : '' ?>>Collar</option>
        <option <?= $entry['category'] === 'Shirt' ? 'selected' : '' ?>>Shirt</option>
        <option <?= $entry['category'] === 'Button Patti' ? 'selected' : '' ?>>Button Patti</option>
      </select><br>

      <label>Quantity:</label>
      <input type="number" name="quantity" value="<?= $entry['quantity'] ?>" required><br>

      <label>Price:</label>
      <input type="number" name="price" value="<?= $entry['price'] ?>" required><br>

      <button type="submit">Update</button>
    </form>
  </main>
</body>
</html>
