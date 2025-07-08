<?php
include __DIR__ . '/../db.php';

$name = $_POST['karigar_name'];
$phone = $_POST['karigar_phone'];
$email = $_POST['karigar_email'];

// Check if phone or email already exists
$check = $conn->prepare("SELECT * FROM karigars WHERE mobile = ? OR email = ?");
$check->bind_param("ss", $phone, $email);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Phone number or email already exists!'); window.history.back();</script>";
    exit();
}

// Insert new karigar
$password = password_hash('default123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO karigars (name, mobile, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $phone, $email, $password);
$stmt->execute();

header("Location: view_karigars.php");
exit();
?>
