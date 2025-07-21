<?php
include '../conn.php';

$id = $_GET['id'];
$ad = $conn->query("SELECT is_active FROM ads WHERE id = $id")->fetch_assoc();
$newStatus = $ad['is_active'] ? 0 : 1;

$conn->query("UPDATE ads SET is_active = $newStatus WHERE id = $id");
header("Location: admin_ads.php?toggled=1");
?>
