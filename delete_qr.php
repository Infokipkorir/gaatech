<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $qr_id = (int) $_GET['id'];

    // First get the image path
    $stmt = $conn->prepare("SELECT image FROM qrcodes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $qr_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($imagePath);
        $stmt->fetch();
        $stmt->close();

        // Delete image file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM qrcodes WHERE id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $qr_id, $user_id);
        if ($deleteStmt->execute()) {
            header("Location: my_account.php?deleted=1");
            exit();
        } else {
            header("Location: my_account.php?error=delete-failed");
            exit();
        }

    } else {
        // QR code doesn't belong to user or doesn't exist
        header("Location: my_account.php?error=not-found");
        exit();
    }

} else {
    header("Location: my_account.php?error=invalid-id");
    exit();
}
?>
