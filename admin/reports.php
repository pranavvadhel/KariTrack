<?php
include '../common/db.php';

$karigar_filter = isset($_GET['karigar']) ? intval($_GET['karigar']) : 0;
$time_filter = $_GET['time'] ?? 'weekly';

// Default date range
$from_date = date('Y-m-d', strtotime('-7 days'));
$to_date = date('Y-m-d');

if ($time_filter === 'monthly') {
  $from_date = date('Y-m-01');
} elseif ($time_filter === 'custom' && isset($_GET['from']) && isset($_GET['to'])) {
  $from_date = $_GET['from'];
  $to_date = $_GET['to'];
}

$where = "we.date >= '$from_date' AND we.date <= '$to_date'";
if ($karigar_filter > 0) {
  $where .= " AND we.karigar_id = $karigar_filter";
}

$query = "
  SELECT we.*, c.name AS category_name, k.name AS karigar_name
  FROM work_entries we
  JOIN karigars k ON we.karigar_id = k.id
  JOIN categories c ON we.category = c.id
  WHERE $where
  ORDER BY we.date DESC
";

$entries = $conn->query($query);
$karigars = $conn->query("SELECT * FROM karigars");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Performance Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: #fff;
      width: 220px;
      position: fixed;
      padding-top: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #000;
      text-decoration: none;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #e0e0e0;
      border-left: 4px solid #0d6efd;
    }
    .content {
      margin-left: 220px;
      padding: 30px;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
  <h3>Performance Report</h3>

  <form method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-md-4">
      <label class="form-label">Karigar</label>
      <select name="karigar" class="form-select">
        <option value="0">All</option>
        <?php while($k = $karigars->fetch_assoc()): ?>
          <option value="<?= $k['id'] ?>" <?= $karigar_filter == $k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Time Period</label>
      <select name="time" class="form-select" onchange="toggleCustomDate(this.value)">
        <option value="weekly" <?= $time_filter == 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="monthly" <?= $time_filter == 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="custom" <?= $time_filter == 'custom' ? 'selected' : '' ?>>Custom</option>
      </select>
    </div>

    <div class="col-md-2" id="customFrom" style="display: <?= $time_filter === 'custom' ? 'block' : 'none' ?>;">
      <label class="form-label">From</label>
      <input type="date" name="from" class="form-control" value="<?= $from_date ?>">
    </div>

    <div class="col-md-2" id="customTo" style="display: <?= $time_filter === 'custom' ? 'block' : 'none' ?>;">
      <label class="form-label">To</label>
      <input type="date" name="to" class="form-control" value="<?= $to_date ?>">
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Apply</button>
    </div>
  </form>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>Karigar</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price/Item</th>
            <th>Total</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($entries->num_rows > 0): ?>
            <?php while ($row = $entries->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['karigar_name']) ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>₹<?= number_format($row['price_per_item'], 2) ?></td>
                <td>₹<?= number_format($row['total'], 2) ?></td>
                <td><?= $row['date'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No data found for this selection.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  function toggleCustomDate(val) {
    const show = val === 'custom';
    document.getElementById('customFrom').style.display = show ? 'block' : 'none';
    document.getElementById('customTo').style.display = show ? 'block' : 'none';
  }
</script>

</body>
</html>
