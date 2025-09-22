<?php
session_start();
require 'db_connect.php'; // DB connection
include "loader.php";

// ✅ Force login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
    exit;
}

// ✅ Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $qr_data = $_POST['qr_data'] ?? '';
    $qr_image = $_POST['qr_image'] ?? '';

    if (empty($qr_data) || empty($qr_image)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing QR data or image']);
        exit;
    }

    // ✅ Save QR code into the database
    $stmt = $conn->prepare("INSERT INTO my_codes (user_id, qr_data, qr_image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $qr_data, $qr_image);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'QR code saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
<script>
    document.getElementById('savedatabase').addEventListener('click', function () {
    const qrData = document.getElementById('textInput').value; 
    const qrCanvas = document.querySelector('#qrcode canvas');
    if (!qrCanvas || !qrData) {
        alert("Please generate a QR code first.");
        return;
    }

    const qrImage = qrCanvas.toDataURL('image/png');

    fetch('save_qrcode.php', {
        method: 'POST',
        body: new URLSearchParams({
            qr_data: qrData,
            qr_image: qrImage
        }),
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    })
    .catch(err => console.error(err));
});

</script>