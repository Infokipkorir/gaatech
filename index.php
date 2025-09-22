<?php
session_start();
require_once "db.php";
include "loader.php";

// Handle save to database request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_image') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['qr_data'], $input['qr_image'])) {
        echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
        exit;
    }

    if (empty($_SESSION['user_id'])) {
        echo json_encode(['ok' => false, 'message' => 'Not logged in']);
        exit;
    }

    $user_id  = $_SESSION['user_id'];
    $qr_data  = $input['qr_data'];
    $qr_image = $input['qr_image'];

    $stmt = $conn->prepare("INSERT INTO my_codes (user_id, qr_data, qr_image, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['ok' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iss", $user_id, $qr_data, $qr_image);

    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'message' => 'QR code saved successfully']);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
$_SESSION['message'] = "Account sign up successfully.";
    header("Location: login.php");
    exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaatech QR Generator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root { --accent:#2563eb; }
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f8fafc; color:#1e293b; }

        /* Navbar */
        .navbar {
            display:flex; align-items:center; justify-content:space-between;
            background: linear-gradient(90deg,#0ea5e9 0%, #2563eb 100%);
            padding:10px 20px; color:white;
        }
        .navbar .left { display:flex; align-items:center; gap:20px; }
        .navbar a { color:white; text-decoration:none; font-weight:500; }
        .navbar .right { display:flex; align-items:center; gap:15px; }
        .btn-upgrade {
            background:#facc15; color:#1e293b; padding:6px 12px; border-radius:6px;
            font-weight:600; display:flex; align-items:center; gap:5px;
        }
        .dropdown { position:relative; }
        .dropdown-menu {
            position:absolute; top:100%; right:0; background:white; color:black;
            border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,0.1);
            display:none; flex-direction:column; min-width:160px;
        }
        .dropdown-menu a {
            padding:10px; text-decoration:none; color:#1e293b; font-size:14px;
        }
        .dropdown:hover .dropdown-menu { display:flex; }

        /* Container */
        .container {
            max-width:900px; margin:30px auto; background:white; padding:20px;
            border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.05);
        }
        h1 { font-size:22px; margin-bottom:15px; }
        .row { margin-bottom:12px; }
        input[type=text] {
            width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;
        }
        .button-group { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:15px; }
        button {
            background:var(--accent); color:white; padding:10px 12px; border:0; border-radius:8px; cursor:pointer;
        }
        button:hover { opacity:0.9; }
        .preview { display:flex; flex-direction:column; align-items:center; gap:12px; padding:12px; border:1px dashed #cbd5e1; border-radius:8px; }
        .muted { font-size:13px; color:#64748b; }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="left">
        <a href="#">Gaatech QR</a>
        <a href="my_codes.php">My Codes</a>
    </div>
    <div class="right">
        <a href="upgrade.php" class="btn-upgrade"><i class="fas fa-crown"></i> Upgrade</a>
        <div class="dropdown">
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <div class="dropdown-menu">
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="settings.php"><i class="fas fa-sliders-h"></i> Preferences</a>
            </div>
        </div>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    <h1>QR Code Generator</h1>

    <div class="row">
        <label for="textInput">Enter text or URL:</label>
        <input type="text" id="textInput" placeholder="Type something...">
    </div>

    <div class="button-group">
        <button id="generateBtn">Generate QR</button>
        <button id="downloadPng">Download PNG</button>
        <button id="downloadSvg">Download SVG</button>
        <button id="downloadPdf">Download PDF</button>
        <button id="savedatabase">Save</button>
        <button id="clearBtn">Clear</button>
    </div>

    <div class="preview">
        <canvas id="qrCanvas"></canvas>
        <div class="muted">Your QR code will appear here</div>
    </div>
</div>

<script>
let currentQRContent = "";
let currentQRImage = "";

// Generate QR
document.getElementById('generateBtn').addEventListener('click', () => {
    const text = document.getElementById('textInput').value.trim();
    if (!text) { alert("Please enter text or URL"); return; }
    currentQRContent = text;
    QRCode.toCanvas(document.getElementById('qrCanvas'), text, { width: 256 }, err => { if (err) console.error(err); });
    QRCode.toDataURL(text, { width: 256 }, (err, url) => { if (!err) currentQRImage = url; });
});

// Download PNG
document.getElementById('downloadPng').addEventListener('click', () => {
    if (!currentQRImage) return alert("Generate a QR code first");
    const a = document.createElement('a');
    a.href = currentQRImage; a.download = 'qrcode.png'; a.click();
});

// Download SVG
document.getElementById('downloadSvg').addEventListener('click', () => {
    const text = currentQRContent;
    if (!text) return alert("Generate a QR code first");
    QRCode.toString(text, { type: 'svg' }, (err, svg) => {
        const blob = new Blob([svg], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'qrcode.svg'; a.click();
    });
});

// Download PDF
document.getElementById('downloadPdf').addEventListener('click', () => {
    if (!currentQRImage) return alert("Generate a QR code first");
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();
    pdf.addImage(currentQRImage, 'PNG', 15, 40, 180, 160);
    pdf.save('qrcode.pdf');
});

// Save to DB
document.getElementById('savedatabase').addEventListener('click', () => {
    if (!currentQRContent || !currentQRImage) return alert("Generate a QR code first");
    fetch('index.php?action=save_image', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ qr_data: currentQRContent, qr_image: currentQRImage })
    })
    .then(res => res.json())
    .then(data => { alert(data.message); })
    .catch(err => console.error(err));
});

// Clear
document.getElementById('clearBtn').addEventListener('click', () => {
    document.getElementById('textInput').value = "";
    const ctx = document.getElementById('qrCanvas').getContext('2d');
    ctx.clearRect(0, 0, 300, 300);
    currentQRContent = ""; currentQRImage = "";
});
</script>

</body>
</html>
