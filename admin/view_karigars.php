<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Karigars</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    .sidebar a:hover, .sidebar a.active {
      background-color: #e0e0e0;
      border-left: 4px solid #0d6efd;
    }
    .content {
      margin-left: 220px;
      padding: 20px;
    }
    .table th, .table td {
      vertical-align: middle;
    }
    .search-box {
      margin-bottom: 20px;
    }
    a.text-link {
      text-decoration: none;
      color: #0d6efd;
    }
    a.text-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
  <h3>All Karigars</h3>

  <!-- 🔍 Search Form -->
  <form method="GET" class="search-box">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or email..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
        <a href="view_karigars.php" class="btn btn-secondary">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>S.No</th>
            <!-- <th>ID</th> -->
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
              $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
              $query = "SELECT * FROM karigars";
              if ($search !== '') {
                $query .= " WHERE name LIKE '%$search%' OR mobile LIKE '%$search%' OR email LIKE '%$search%'";
              }
              $query .= " ORDER BY id DESC";

              $result = $conn->query($query);
              $serial = 1;
              if (!$result) {
                die("Query failed: " . $conn->error);
              }
              // Check if there are results
              if ($result === false) {
                die("Error executing query: " . $conn->error);
              } 
              // Fetch results
              $serial = 1;
              // If there are results, display them
              if ($result->num_rows === 0 && $search !== '') {
                echo "<tr><td colspan='6' class='text-center'>No results found for '<strong>" . htmlspecialchars($search) . "</strong>'</td></tr>";
              }
             
          if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td><?= $serial++ ?></td>
            <!-- <td><?= htmlspecialchars($row['id']) ?></td> -->
            <td>
              <a class="text-link" href="karigar_profile.php?id=<?= $row['id'] ?>">
                <?= htmlspecialchars($row['name']) ?>
              </a>
            </td>
            <td><?= htmlspecialchars($row['mobile']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <a href="edit_karigar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="delete_karigar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
          </tr>
          <?php endwhile; else: ?>
          
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
