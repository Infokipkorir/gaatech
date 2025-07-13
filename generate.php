<?php
session_start();
require_once 'db.php';
require_once 'phpqrcode/qrlib.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$data = trim($_POST['qrData'] ?? '');
$size = $_POST['qrSize'] ?? 200;

if (!empty($data)) {
    $dir = 'qrcodes/';
    if (!file_exists($dir)) mkdir($dir);

    $filename = $dir . 'qr_' . uniqid() . '.png';
    QRcode::png($data, $filename, QR_ECLEVEL_L, 4);

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO qrcodes (user_id, data, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $data, $filename);
    $stmt->execute();
    $stmt->close();

    header("Location: my_account.php?msg=QR+Generated");
    exit;
} else {
    header("Location: dashboard.php?error=No+data");
    exit;
}
