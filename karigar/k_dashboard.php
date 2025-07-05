<?php
session_start();
include '../common/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'karigar') {
  header("Location: ../k_dashboard.php");
  exit;
}

$karigar_id = $_SESSION['karigar_id'];
$karigar_name = $_SESSION['name'] ?? 'Karigar';

// Handle filter
$filter = $_GET['filter'] ?? 'weekly';
$from_date = '';
$where = "karigar_id = $karigar_id";

if ($filter == 'weekly') {
  $from_date = date('Y-m-d', strtotime('-7 days'));
} elseif ($filter == 'monthly') {
  $from_date = date('Y-m-01');
}

if ($from_date) {
  $where .= " AND date >= '$from_date'";
}

// Fetch totals
$total_query = $conn->query("SELECT SUM(total) AS total_amount, SUM(quantity) AS total_items FROM work_entries WHERE $where");
$total_data = $total_query->fetch_assoc();
$total_amount = $total_data['total_amount'] ?? 0;
$total_items = $total_data['total_items'] ?? 0;

// Fetch recent entries with category name
$entries = $conn->query("
  SELECT we.*, c.name AS category_name 
  FROM work_entries we 
  JOIN categories c ON we.category = c.id 
  WHERE $where 
  ORDER BY date DESC 
  LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Karigar Dashboard</title>
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
  <h3>Welcome, <?= htmlspecialchars($karigar_name) ?></h3>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5>Total Amount Earned</h5>
          <h2 class="text-success">₹<?= number_format($total_amount, 2) ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5>Total Items Completed</h5>
          <h2><?= $total_items ?></h2>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Dropdown -->
  <div class="mb-3">
    <form method="GET">
      <select name="filter" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
        <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Time</option>
      </select>
    </form>
  </div>

  <!-- Recent Entries -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">Recent Work Entries</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price/Item</th>
            <th>Total</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($row = $entries->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['category_name']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>₹<?= number_format($row['price_per_item'], 2) ?></td>
              <td>₹<?= number_format($row['total'], 2) ?></td>
              <td><?= $row['date'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
