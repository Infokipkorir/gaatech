<?php
session_start();
require_once "db.php";
include "loader.php";

// Check if user is logged in (admin/vendor)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch existing shop settings
$shop = $conn->query("SELECT * FROM shop_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Fetch staff list
$staff_result = $conn->query("SELECT * FROM staff");

// Fetch items
$item_result = $conn->query("SELECT * FROM shop_items");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Shop Settings
    if (isset($_POST['update_shop'])) {
        $shop_name = $_POST['shop_name'];
        $tagline = $_POST['tagline'];
        $branch = $_POST['branch'];
        $tax_rate = $_POST['tax_rate'];
        $tax_type = $_POST['tax_type'];
        $counter_no = $_POST['counter_no'];

        // Handle logo upload
        $logo_path = $shop['logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $logo_path = 'uploads/' . basename($_FILES['logo']['name']);
            move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path);
        }

        if ($shop) {
            $stmt = $conn->prepare("UPDATE shop_settings SET shop_name=?, tagline=?, branch=?, logo=?, tax_rate=?, tax_type=?, counter_no=? WHERE id=?");
            $stmt->bind_param("ssssdsdi", $shop_name, $tagline, $branch, $logo_path, $tax_rate, $tax_type, $counter_no, $shop['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO shop_settings (shop_name, tagline, branch, logo, tax_rate, tax_type, counter_no) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssdsd", $shop_name, $tagline, $branch, $logo_path, $tax_rate, $tax_type, $counter_no);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: shop_settings.php");
        exit;
    }

    // Add staff
    if (isset($_POST['add_staff'])) {
        $staff_name = $_POST['staff_name'];
        $counter_no = $_POST['staff_counter'];
        $stmt = $conn->prepare("INSERT INTO staff (name, counter_no) VALUES (?, ?)");
        $stmt->bind_param("ss", $staff_name, $counter_no);
        $stmt->execute();
        $stmt->close();
        header("Location: shop_settings.php");
        exit;
    }

    // Add item
    if (isset($_POST['add_item'])) {
        $item_name = $_POST['item_name'];
        $unit = $_POST['unit'];
        $price = $_POST['price'];
        $stmt = $conn->prepare("INSERT INTO shop_items (name, unit, price) VALUES (?,?,?)");
        $stmt->bind_param("ssd", $item_name, $unit, $price);
        $stmt->execute();
        $stmt->close();
        header("Location: shop_settings.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Settings - Gaatech QR</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-center">Shop Settings</h2>

    <!-- Shop Settings Form -->
    <div class="card mb-4 p-3 shadow-sm">
        <h4>Shop Details</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3"><input type="text" name="shop_name" class="form-control" placeholder="Shop Name" value="<?= $shop['shop_name'] ?? '' ?>" required></div>
            <div class="mb-3"><input type="text" name="tagline" class="form-control" placeholder="Tagline" value="<?= $shop['tagline'] ?? '' ?>"></div>
            <div class="mb-3"><input type="text" name="branch" class="form-control" placeholder="Branch" value="<?= $shop['branch'] ?? '' ?>"></div>
            <div class="mb-3"><input type="file" name="logo" class="form-control"></div>
            <div class="mb-3 d-flex gap-2">
                <input type="number" name="tax_rate" class="form-control" placeholder="Tax %" step="0.01" value="<?= $shop['tax_rate'] ?? 0 ?>">
                <select name="tax_type" class="form-select">
                    <option value="add" <?= ($shop['tax_type']??'add')=='add'?'selected':'' ?>>Add to Price</option>
                    <option value="include" <?= ($shop['tax_type']??'add')=='include'?'selected':'' ?>>Include in Price</option>
                </select>
            </div>
            <div class="mb-3"><input type="text" name="counter_no" class="form-control" placeholder="Counter No" value="<?= $shop['counter_no'] ?? '' ?>"></div>
            <button type="submit" name="update_shop" class="btn btn-primary w-100">Save Shop Settings</button>
        </form>
    </div>

    <!-- Staff -->
    <div class="card mb-4 p-3 shadow-sm">
        <h4>Staff</h4>
        <form method="POST" class="d-flex gap-2 mb-3">
            <input type="text" name="staff_name" class="form-control" placeholder="Staff Name" required>
            <input type="text" name="staff_counter" class="form-control" placeholder="Counter No" required>
            <button type="submit" name="add_staff" class="btn btn-success">Add</button>
        </form>
        <ul class="list-group">
            <?php while($staff = $staff_result->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($staff['name']) ?> (<?= htmlspecialchars($staff['counter_no']) ?>)
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Items -->
    <div class="card mb-4 p-3 shadow-sm">
        <h4>Items</h4>
        <form method="POST" class="row g-2 mb-3">
            <div class="col-md-6"><input type="text" name="item_name" class="form-control" placeholder="Item Name" required></div>
            <div class="col-md-3">
                <select name="unit" class="form-select">
                    <option value="pcs">pcs</option>
                    <option value="Kg">Kg</option>
                    <option value="g">g</option>
                    <option value="L">L</option>
                    <option value="ml">ml</option>
                </select>
            </div>
            <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
            <div class="col-12"><button type="submit" name="add_item" class="btn btn-success w-100">Add Item</button></div>
        </form>
        <ul class="list-group">
            <?php while($item = $item_result->fetch_assoc()): ?>
                <li class="list-group-item"><?= htmlspecialchars($item['name']) ?> - <?= htmlspecialchars($item['unit']) ?> - kes<?= number_format($item['price'],2) ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

</body>
</html>
