<?php
include '../conn.php';

$title = $_POST['title'];
$content = $_POST['content'];
$link = $_POST['link'];
$plan_target = $_POST['plan_target'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$image = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $image = $imageName;
    }
}

$stmt = $conn->prepare("INSERT INTO ads (title, content, image, link, plan_target, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $title, $content, $image, $link, $plan_target, $start_date, $end_date);

if ($stmt->execute()) {
    header("Location: admin_ads.php?success=1");
} else {
    echo "Error: " . $stmt->error;
}
?>
