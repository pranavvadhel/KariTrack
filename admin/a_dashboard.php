<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../db.php';
?>
<?php
session_start();

// Check if admin session is not set
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../a_dashboard.php");
  exit;
}

$karigar_filter = isset($_GET['karigar_id']) ? intval($_GET['karigar_id']) : 0;
$period = $_GET['period'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$karigar_name = '';

if ($karigar_filter) {
  $karigar_stmt = $conn->prepare("SELECT name FROM karigars WHERE id = ?");
  $karigar_stmt->bind_param("i", $karigar_filter);
  $karigar_stmt->execute();
  $karigar_stmt->bind_result($karigar_name);
  $karigar_stmt->fetch();
  $karigar_stmt->close();
}

// Date filter condition
$date_condition = '';
if ($period === 'weekly') {
  $date_condition = "date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($period === 'monthly') {
  $date_condition = "date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
} elseif ($period === 'custom' && $from_date && $to_date) {
  $from = $conn->real_escape_string($from_date);
  $to = $conn->real_escape_string($to_date);
  $date_condition = "date BETWEEN '$from' AND '$to'";
}

$where_parts = [];
if ($karigar_filter) $where_parts[] = "karigar_id = $karigar_filter";
if ($date_condition) $where_parts[] = $date_condition;
$where = count($where_parts) ? "WHERE " . implode(' AND ', $where_parts) : '';

// Total amount earned
$total_query = "SELECT SUM(total) AS total_amount FROM work_entries $where";
$total_result = $conn->query($total_query);
$total_data = $total_result->fetch_assoc();
$total_amount = $total_data['total_amount'] ?? 0;

// Item breakdown
$item_breakdown = [];
$breakdown_query = "
  SELECT c.name AS category_name, SUM(w.quantity) as count
  FROM work_entries w
  JOIN categories c ON w.category = c.id
  $where
  GROUP BY w.category
";

$breakdown_result = $conn->query($breakdown_query);
while ($row = $breakdown_result->fetch_assoc()) {
  $item_breakdown[] = $row;
}

// Overall stats
$total_payments = 0;
$total_items = 0;
$active_karigars = [];
$result_all = $conn->query("SELECT * FROM work_entries");
while ($row = $result_all->fetch_assoc()) {
  $total_payments += $row['total'];
  $total_items += $row['quantity'];
  $active_karigars[$row['karigar_id']] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>KariTrack - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    .card {
      margin-bottom: 20px;
    }
    
  .clickable-card:hover {
    background-color: #f1f1f1;
    transition: background-color 0.2s ease-in-out;
  }

  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
  <h3>Dashboard</h3>


  <div class="row">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5>Total Payments</h5>
          <h2>₹<?= number_format($total_payments, 2) ?></h2>
          <p class="text-muted small">Total payments in the database</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm clickable-card" data-bs-toggle="modal" data-bs-target="#itemsModal">
        <div class="card-body">
          <h5 class="d-flex justify-content-between align-items-center">
            Items Completed 
            <i class="bi bi-chevron-down text-primary" style="color: black;"></i>
          </h5>
          <h2><?= $total_items ?></h2>
          <p class="text-muted small">Click to view breakdown</p>
        </div>
      </div>
    </div>



    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5>Active Karigars</h5>
          <h2><?= count($active_karigars) ?></h2>
          <p class="text-muted small">Unique karigars with entries</p>
        </div>
      </div>
    </div>
  </div>
<form method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
      <label for="karigar_id" class="form-label">Filter by Karigar</label>
      <select name="karigar_id" id="karigar_id" class="form-select" onchange="this.form.submit()">
        <option value="">All</option>
        <?php
        $karigars = $conn->query("SELECT * FROM karigars");
        while ($k = $karigars->fetch_assoc()) {
          $selected = ($k['id'] == $karigar_filter) ? 'selected' : '';
          echo "<option value='{$k['id']}' $selected>" . htmlspecialchars($k['name']) . "</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="period" class="form-label">Time Period</label>
      <select name="period" id="period" class="form-select" onchange="toggleCustomDate(this.value); this.form.submit()">
        <option value="all" <?= $period == 'all' ? 'selected' : '' ?>>All Time</option>
        <option value="weekly" <?= $period == 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="monthly" <?= $period == 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="custom" <?= $period == 'custom' ? 'selected' : '' ?>>Custom</option>
      </select>
    </div>

    <div class="col-md-3" id="custom_dates" style="display: <?= $period == 'custom' ? 'block' : 'none' ?>;">
      <label for="from_date" class="form-label">From Date</label>
      <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>" onchange="this.form.submit()">
    </div>

    <div class="col-md-3" id="custom_dates_to" style="display: <?= $period == 'custom' ? 'block' : 'none' ?>;">
      <label for="to_date" class="form-label">To Date</label>
      <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>" onchange="this.form.submit()">
    </div>
  </form>
  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <h5>Total Amount Earned <?= $karigar_filter ? 'by: ' . htmlspecialchars($karigar_name) : '' ?></h5>
      <h2 class="text-success">₹<?= number_format($total_amount, 2) ?></h2>
    </div>
  </div>

  <div class="card mt-4 shadow-sm">
    <div class="card-body">
      <h5>Overview</h5>
      <canvas id="overviewChart" height="100"></canvas>
    </div>
  </div>
</div>

<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemsModalLabel">Item Breakdown</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($item_breakdown)): ?>
          <ul class="list-group">
            <?php foreach ($item_breakdown as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($item['category_name']) ?>
                <span class="badge bg-primary rounded-pill"><?= $item['count'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted">No item data available.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function toggleCustomDate(value) {
  const customDates = document.getElementById('custom_dates');
  const customDatesTo = document.getElementById('custom_dates_to');
  if (value === 'custom') {
    customDates.style.display = 'block';
    customDatesTo.style.display = 'block';
  } else {
    customDates.style.display = 'none';
    customDatesTo.style.display = 'none';
  }
}

const ctx = document.getElementById('overviewChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [
      { label: 'Items', data: [120, 130, 90, 80], backgroundColor: '#00b894' },
      { label: 'Payment (₹)', data: [8000, 4500, 4000, 6300], backgroundColor: '#6366f1' }
    ]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
