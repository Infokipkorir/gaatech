<?php
require_once 'db.php';
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE ads SET clicks = clicks + 1 WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}
