<?php
require_once 'db.php';

$email = 'support@gaatech.co.ke';
$name = 'Admin';
$password = 'admin123'; // Change this later for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $name, $email, $hashedPassword, $role);
    if ($insert->execute()) {
        echo "✅ Admin user created successfully.";
    } else {
        echo "❌ Failed to create admin: " . $insert->error;
    }
    $insert->close();
} else {
    echo "⚠️ Admin already exists.";
}
$conn->close();
?>
