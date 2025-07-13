<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gaatech");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Registration
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: register.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Account created successfully. Please login.";
        header("Location: register.php");
    } else {
        $_SESSION['error'] = "Registration failed.";
        header("Location: register.php");
    }
    $stmt->close();
    exit;
}

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header("Location: dashboard.php"); // Redirect after login
        } else {
            $_SESSION['error'] = "Invalid password.";
            header("Location: login.php");
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: login.php");
    }

    $stmt->close();
    exit;
}

if ($_SESSION['role'] === 'trial') {
    $stmt = $conn->prepare("SELECT trial_started_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($trial_start);
    $stmt->fetch();
    $stmt->close();

    if ($trial_start) {
        $trial_expiry = strtotime($trial_start . ' +7 days');
        if (time() > $trial_expiry) {
            // Expired: downgrade to free
            $update = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $update->bind_param("i", $_SESSION['user_id']);
            $update->execute();
            $_SESSION['role'] = 'user';
        }
    }
}

if ($_SESSION['role'] === 'trial') {
    $stmt = $conn->prepare("SELECT trial_started_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($trial_start);
    $stmt->fetch();
    $stmt->close();

    if ($trial_start) {
        $trial_end = strtotime($trial_start . ' +7 days');
        $now = time();
        $remaining = $trial_end - $now;

        if ($remaining > 0) {
            $days = floor($remaining / 86400);
            $hours = floor(($remaining % 86400) / 3600);
            $minutes = floor(($remaining % 3600) / 60);

            echo "<div class='alert alert-info'>
                    🕒 Your trial ends in <strong>$days days, $hours hrs, $minutes min</strong>.
                  </div>";
        } else {
            echo "<div class='alert alert-warning'>
                    ⚠️ Your trial has ended. Please upgrade to continue using Pro features.
                  </div>";
        }
    }
}

$conn->close();
?>
