<?php
session_start();
if (!isset($_SESSION['role'])) {
  header("Location: ../index.php");
  exit();
}

// Optional role enforcement
function requireRole($role) {
  if ($_SESSION['role'] !== $role) {
    header("Location: ../index.php");
    exit();
  }
}
