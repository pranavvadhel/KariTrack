<?php
include __DIR__ . '/../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM karigars WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: view_karigars.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
