<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $karigar_id = $_POST['karigar_id'];
    $category_id = $_POST['category'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];

    // 🔍 Fetch price per item from categories table
    $stmt = $conn->prepare("SELECT price FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($price_per_item);
    $stmt->fetch();
    $stmt->close();

    // ✅ Calculate total
    $total = $price_per_item * $quantity;

    // 📝 Insert into work_entries
    $insert = $conn->prepare("INSERT INTO work_entries (karigar_id, category, quantity, price_per_item, total, date) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("iiidds", $karigar_id, $category_id, $quantity, $price_per_item, $total, $date);

    if ($insert->execute()) {
        header("Location: work_entry.php?success=1");
    } else {
        echo "Error: " . $insert->error;
    }

    $insert->close();
}
?>
