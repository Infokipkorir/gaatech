<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';
    $base64Image = $_POST['image'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!empty($data) && !empty($base64Image)) {
        // Validate and decode image
        if (preg_match('/^data:image\/png;base64,/', $base64Image)) {
            $base64Image = str_replace('data:image/png;base64,', '', $base64Image);
            $base64Image = str_replace(' ', '+', $base64Image);
            $imageData = base64_decode($base64Image);

            // Save image
            $filename = 'gaatech_qr_' . uniqid() . '.png';
            $imagePath = 'qrcodes/' . $filename;

            if (!file_exists('qrcodes')) {
                mkdir('qrcodes', 0777, true);
            }

            if (file_put_contents($imagePath, $imageData)) {
                // Save to database
                $stmt = $conn->prepare("INSERT INTO qrcodes (user_id, data, image, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $user_id, $data, $imagePath);
                if ($stmt->execute()) {
                    header("Location: my_account.php?success=1");
                    exit();
                } else {
                    echo "❌ Failed to save to database.";
                }
            } else {
                echo "❌ Failed to save image.";
            }
        } else {
            echo "❌ Invalid image format.";
        }
    } else {
        echo "❌ QR data or image missing.";
    }
} else {
    echo "❌ Invalid request.";
}