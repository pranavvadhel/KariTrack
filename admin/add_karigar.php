<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Karigar</title>
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
    <h3>Add Karigar</h3>
    <div class="card shadow-sm">
      <div class="card-body">
        <form action="add_karigar_process.php" method="POST">
          <div class="mb-3">
            <label for="karigar_name" class="form-label">Karigar Name</label>
            <input type="text" class="form-control" id="karigar_name" name="karigar_name" required>
          </div>
          <div class="mb-3">
            <label for="karigar_phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="karigar_phone" name="karigar_phone" required>
          </div>
          <div class="mb-3">
            <label for="karigar_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="karigar_email" name="karigar_email">
          </div>
          <button type="submit" class="btn btn-primary">Add Karigar</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
