<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: a_dashboard.php");
  exit();
}
include __DIR__ . '/../db.php';

if (!isset($_GET['id']) || !isset($_GET['karigar_id'])) {
  echo "Missing parameters.";
  exit();
}

$id = intval($_GET['id']);
$karigar_id = intval($_GET['karigar_id']);

$conn->query("DELETE FROM work_entries WHERE id = $id");
header("Location: karigar_profile.php?id=$karigar_id");
exit();
