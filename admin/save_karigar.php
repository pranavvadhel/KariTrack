<?php
include 'db.php';
$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$conn->query("INSERT INTO karigars (name, phone, address) VALUES ('$name', '$phone', '$address')");
header("Location: view_karigars.php");