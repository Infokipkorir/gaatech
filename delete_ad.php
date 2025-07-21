<?php
include '../conn.php';

$id = $_GET['id'];
$conn->query("DELETE FROM ads WHERE id = $id");

header("Location: admin_ads.php?deleted=1");
?>
