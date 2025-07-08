<!-- sidebar.php -->
<div class="sidebar">
  <h4>🧵 KariTrack</h4>
  <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="add_karigar.php" class="<?= basename($_SERVER['PHP_SELF']) == 'add_karigar.php' ? 'active' : '' ?>">Add Karigar</a>
  <a href="view_karigars.php" class="<?= basename($_SERVER['PHP_SELF']) == 'view_karigars.php' ? 'active' : '' ?>">View Karigars</a>
  <a href="work_entry.php" class="<?= basename($_SERVER['PHP_SELF']) == 'work_entry.php' ? 'active' : '' ?>">Add Work</a>
  <a href="edit_category.php" class="<?= basename($_SERVER['PHP_SELF']) == 'edit_category.php' ? 'active' : '' ?>">Edit Category</a>
<a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">Performance</a>
</div>
<style>
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
</style>  