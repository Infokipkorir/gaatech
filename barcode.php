<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate Barcode - Gaatech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #10b981, #059669);
      margin: 0; padding: 0;
      display: flex; align-items: center; justify-content: center;
      height: 100vh;
    }
    .card {
      background: white; padding: 30px; border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      text-align: center; width: 100%; max-width: 420px;
    }
    h2 { margin-bottom: 20px; color: #059669; }
    .input-group { margin-bottom: 15px; text-align: left; }
    label { font-weight: 600; font-size: 14px; color: #374151; }
    input {
      width: 100%; padding: 12px; margin-top: 6px;
      border: 1px solid #cbd5e1; border-radius: 8px;
      font-size: 14px;
    }
    button {
      width: 100%; padding: 12px; margin-top: 10px;
      border: none; border-radius: 8px; cursor: pointer;
      background: #059669; color: white; font-weight: bold; font-size: 16px;
    }
    button:hover { background: #047857; }
    canvas {
      margin-top: 20px; max-width: 100%; background: #f9fafb; padding: 10px;
      border-radius: 8px; box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
    }
    .download-link {
      display: inline-block; margin-top: 15px; font-size: 14px;
      text-decoration: none; color: #059669; font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Generate Barcode</h2>
    <form id="barcodeForm">
      <div class="input-group">
        <label>Enter Text / Number</label>
        <input type="text" id="barcodeText" placeholder="e.g. 1234567890" required>
      </div>
      <button type="submit">Generate Barcode</button>
    </form>
    <canvas id="barcode"></canvas>
    <a id="downloadLink" class="download-link" style="display:none;">⬇ Download Barcode</a>
  </div>

  <!-- JsBarcode Library -->
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script>
    const form = document.getElementById("barcodeForm");
    const input = document.getElementById("barcodeText");
    const canvas = document.getElementById("barcode");
    const downloadLink = document.getElementById("downloadLink");

    form.addEventListener("submit", function(e) {
      e.preventDefault();

      const value = input.value.trim();
      if (value === "") return;

      // Generate barcode
      JsBarcode(canvas, value, {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 80,
        displayValue: true
      });

      // Enable download
      const dataURL = canvas.toDataURL("image/png");
      downloadLink.href = dataURL;
      downloadLink.download = "barcode.png";
      downloadLink.style.display = "block";
    });
  </script>
</body>
</html>
