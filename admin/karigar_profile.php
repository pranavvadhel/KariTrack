<?php
include '../common/db.php';

$karigar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$karigar = $conn->query("SELECT * FROM karigars WHERE id = $karigar_id")->fetch_assoc();

// Filter handling
$filter = $_GET['filter'] ?? 'all';
$from_date = '';
$where = "we.karigar_id = $karigar_id";

if ($filter == 'weekly') {
  $from_date = date('Y-m-d', strtotime('-7 days'));
} elseif ($filter == 'monthly') {
  $from_date = date('Y-m-01');
}

if ($from_date) {
  $where .= " AND we.date >= '$from_date'";
}

$query = "
  SELECT we.*, c.name AS category_name 
  FROM work_entries we 
  JOIN categories c ON we.category = c.id
  WHERE $where
  ORDER BY we.date DESC
";
$entries = $conn->query($query);

// Totals
$totals = $conn->query("SELECT SUM(quantity) AS total_qty, SUM(total) AS total_earned FROM work_entries WHERE karigar_id = $karigar_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Karigar Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .content { padding: 30px; }
    .card h5 { margin-bottom: 15px; }
  </style>
</head>
<body>



<div class="content">
  <a href="view_karigars.php" class="btn btn-secondary mb-3">← Back to Karigars</a>

  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h4>👷‍♂️ Karigar Profile</h4>
      <p><strong>Name:</strong> <?= htmlspecialchars($karigar['name']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($karigar['mobile']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($karigar['email']) ?></p>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5>📋 Work History</h5>

      <form method="GET" class="mb-3">
        <input type="hidden" name="id" value="<?= $karigar_id ?>">
        <label class="form-label">Filter:</label>
        <select name="filter" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
          <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All Time</option>
          <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Last 7 Days</option>
          <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>This Month</option>
        </select>
      </form>

      <div class="mb-2">
        <a href="export_karigar_pdf.php?id=<?= $karigar_id ?>&filter=<?= $filter ?>" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
      </div>


      <table class="table table-bordered table-striped mt-3">
        <thead>
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
          <?php if ($entries->num_rows > 0): $i = 1; ?>
            <?php while($row = $entries->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['price_per_item'], 2) ?></td>
                <td><?= number_format($row['total'], 2) ?></td>
                <td><?= $row['date'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No work records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <p class="mt-3"><strong>Total Quantity:</strong> <?= $totals['total_qty'] ?? 0 ?></p>
      <p><strong>Total Earned:</strong> ₹<?= number_format($totals['total_earned'] ?? 0, 2) ?></p>
    </div>
  </div>
</div>

</body>
</html>
