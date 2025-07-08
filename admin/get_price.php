<?php
include __DIR__ . '/../db.php';

if (isset($_POST['category_id'])) {
    $category_id = intval($_POST['category_id']);
    $query = $conn->query("SELECT price FROM categories WHERE id = $category_id");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        echo $data['price'];
    } else {
        echo 0;
    }
}
?>
