<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Work Entry</title>
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
    .card {
      margin-top: 30px;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
  <h3>Add Work Entry</h3>
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Work entry submitted successfully.</div>
  <?php endif; ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <form action="work_entry_process.php" method="POST">
        <div class="mb-3">
          <label for="karigar_id" class="form-label">Select Karigar</label>
          <select name="karigar_id" id="karigar_id" class="form-select" required>
            <option value="">-- Select --</option>
            <?php
              $karigars = $conn->query("SELECT * FROM karigars");
              while ($k = $karigars->fetch_assoc()) {
                echo "<option value='{$k['id']}'>{$k['name']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="category" class="form-label">Category</label>
          <select name="category" id="category" class="form-select" required>
            <option value="">-- Select Category --</option>
            <?php
              $categories = $conn->query("SELECT * FROM categories");
              while ($c = $categories->fetch_assoc()) {
                echo "<option value='{$c['id']}'>{$c['name']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="price_per_item" class="form-label">Price per Item (₹)</label>
          <input type="number" name="price_per_item" id="price_per_item" class="form-control" readonly required>
        </div>

        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="date" class="form-label">Date</label>
          <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Submit Work Entry</button>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function () {
    $('#category').change(function () {
      var categoryId = $(this).val();
      if (categoryId) {
        $.ajax({
          url: 'get_price.php',
          type: 'POST',
          data: { category_id: categoryId },
          success: function (data) {
            $('#price_per_item').val(data);
          }
        });
      } else {
        $('#price_per_item').val('');
      }
    });
  });
</script>



</body>
</html>
