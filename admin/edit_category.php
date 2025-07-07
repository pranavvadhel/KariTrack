<?php
include 'db.php';

// Handle Add
if (isset($_POST['add_category'])) {
  $name = trim($_POST['category_name']);
  $price = floatval($_POST['price']);
  if ($name !== '') {
    $stmt = $conn->prepare("INSERT INTO categories (name, price) VALUES (?, ?)");
    $stmt->bind_param("sd", $name, $price);
    $stmt->execute();
  }
}

// Handle Edit
if (isset($_POST['edit_category'])) {
  $id = intval($_POST['category_id']);
  $name = trim($_POST['category_name']);
  $price = floatval($_POST['price']);
  if ($id && $name !== '') {
    $stmt = $conn->prepare("UPDATE categories SET name=?, price=? WHERE id=?");
    $stmt->bind_param("sdi", $name, $price, $id);
    $stmt->execute();
  }
}

// Handle Delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM categories WHERE id=$id");
}

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="shortcut icon" href="../image/karitrack.png"> 
  <meta charset="UTF-8">
  <title>Edit Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #fff;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      position: fixed;
      width: 220px;
      padding-top: 20px;
    }
    .sidebar h4 {
      text-align: center;
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #333;
      text-decoration: none;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background-color: #e0e0e0;
      border-left: 4px solid #0d6efd;
    }
    .content {
      margin-left: 220px;
      padding: 30px;
    }
    td input {
      width: 100%;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>


<!-- Content Area -->
<div class="content">
  <h3>Edit Categories</h3>

  <!-- Add New Category -->
  <form class="row g-3 mb-4" method="POST">
    <div class="col-md-5">
      <input type="text" class="form-control" name="category_name" placeholder="Enter category name" required>
    </div>
    <div class="col-md-3">
      <input type="number" class="form-control" name="price" step="0.01" placeholder="Price (₹)" required>
    </div>
    <div class="col-md-4">
      <button type="submit" name="add_category" class="btn btn-success w-100">Add Category</button>
    </div>
  </form>

  <!-- Category Table -->
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Category Name</th>
        <th>Price (₹)</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td>
          <form method="POST" class="d-flex">
            <input type="hidden" name="category_id" value="<?= $row['id'] ?>">
            <input type="text" name="category_name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control me-2" required>
        </td>
        <td>
            <input type="number" name="price" step="0.01" value="<?= $row['price'] ?>" class="form-control me-2" required>
        </td>
        <td>
            <button type="submit" name="edit_category" class="btn btn-primary btn-sm me-2">Update</button>
            <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')">Delete</a>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
