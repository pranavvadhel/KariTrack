<?php
session_start();
include '../common/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'karigar') {
  header("Location: ../k_dashboard.php");
  exit;
}

$karigar_id = $_SESSION['karigar_id'];
$karigar_name = $_SESSION['name'] ?? 'Karigar';

// Fetch work entries with category names
$query = "
  SELECT we.*, c.name AS category_name 
  FROM work_entries we 
  JOIN categories c ON we.category = c.id 
  WHERE we.karigar_id = $karigar_id 
  ORDER BY we.date DESC
";
$result = $conn->query($query);

// Calculate grand total
$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="shortcut icon" href="../image/karitrack.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Work</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #fff;
      position: fixed;
      width: 220px;
      padding-top: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
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
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<?php include 'sidebar_karigar.php'; ?>

<div class="content">
  <h3>My Work Records - <?= htmlspecialchars($karigar_name) ?></h3>

  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price/Item (₹)</th>
            <th>Total (₹)</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $i = 1;
          while ($row = $result->fetch_assoc()): 
            $grand_total += $row['total'];
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['category_name']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>₹<?= number_format($row['price_per_item'], 2) ?></td>
              <td>₹<?= number_format($row['total'], 2) ?></td>
              <td><?= $row['date'] ?></td>
            </tr>
          <?php endwhile; ?>
          <tr class="table-success">
            <td colspan="4" class="text-end fw-bold">Grand Total</td>
            <td class="fw-bold">₹<?= number_format($grand_total, 2) ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
