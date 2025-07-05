<?php
include 'db.php';
$karigar_id = $_POST['karigar_id'];
$date = $_POST['date'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$total = $_POST['total'];
$conn->query("INSERT INTO work_entries (karigar_id, date, category, quantity, price, total) VALUES ('$karigar_id', '$date', '$category', '$quantity', '$price', '$total')");
header("Location: reports.php");