<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome - Gaatech</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin:0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #2563eb, #0ea5e9);
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:flex-start;
      min-height:100vh;
      color:#fff;
    }

    /* Hero Section */
    .hero {
      text-align:center;
      padding:50px 20px 20px;
      animation: fadeInDown 1s ease;
    }
    .hero h1 {
      font-size:32px;
      margin-bottom:15px;
      animation: typing 4s steps(30) 1, blink 0.75s step-end infinite alternate;
      white-space:nowrap;
      overflow:hidden;
      border-right:3px solid #facc15;
      display:inline-block;
    }
    .hero p {
      font-size:16px;
      max-width:600px;
      margin:0 auto;
      line-height:1.6;
    }

    /* Cards */
    .container {
      max-width:1000px;
      width:100%;
      padding:20px;
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
      gap:20px;
      margin-top:30px;
    }
    .card {
      background:white;
      border-radius:16px;
      padding:30px 20px;
      text-align:center;
      box-shadow:0 6px 15px rgba(0,0,0,0.1);
      cursor:pointer;
      position:relative;
      overflow:hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-decoration:none;
      color:inherit;
    }
    .card:hover {
      transform: translateY(-8px);
      box-shadow:0 12px 25px rgba(0,0,0,0.2);
    }
    .card i {
      font-size:48px;
      margin-bottom:15px;
      color:#2563eb;
      transition: transform 0.3s ease;
    }
    .card:hover i {
      transform: scale(1.2) rotate(10deg);
    }
    .card h2 {
      font-size:20px;
      margin-bottom:10px;
      color:#1e293b;
    }
    .card p {
      font-size:14px;
      color:#475569;
    }

    /* Cookie Banner */
    .cookie-banner {
      position:fixed;
      bottom:0;
      left:0;
      right:0;
      background:#1e293b;
      color:#f1f5f9;
      padding:15px 20px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:14px;
      z-index:1000;
      animation: fadeInUp 0.8s ease;
    }
    .cookie-banner button {
      background:#facc15;
      border:none;
      padding:8px 16px;
      border-radius:6px;
      font-weight:bold;
      cursor:pointer;
    }

    /* Animations */
    @keyframes fadeInDown {
      from { opacity:0; transform: translateY(-20px); }
      to { opacity:1; transform: translateY(0); }
    }
    @keyframes fadeInUp {
      from { opacity:0; transform: translateY(20px); }
      to { opacity:1; transform: translateY(0); }
    }
    @keyframes typing {
      from { width:0; }
      to { width:100%; }
    }
    @keyframes blink {
      50% { border-color: transparent; }
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <div class="hero">
    <h1>Welcome to Gaatech<span style="color: yellow;">|Solution</span></h1>
    <p>All-in-one platform for generating <b>QR Codes</b>, creating <b>Barcodes</b>, and managing <b>Digital Receipts</b>. 
       Simple, fast, and built for modern businesses.</p>
  </div>

  <!-- Cards -->
  <div class="container">
    <!-- QR Code -->
    <a href="index.php" class="card">
      <i class="fas fa-qrcode"></i>
      <h2>QR Code</h2>
      <p>Create and manage your QR codes with ease.</p>
    </a>

    <!-- Barcode -->
    <a href="barcode.php" class="card">
      <i class="fas fa-barcode"></i>
      <h2>Barcode</h2>
      <p>Generate and scan barcodes for products or inventory.</p>
    </a>

    <!-- Receipt Marker -->
    <a href="receipt.php" class="card">
      <i class="fas fa-receipt"></i>
      <h2>Receipt Marker</h2>
      <p>Track and manage receipts efficiently.</p>
    </a>
  </div>

  <!-- Cookie Consent -->
  <div class="cookie-banner" id="cookieBanner">
    <span>We use cookies to improve your experience. By continuing, you accept our cookie policy.</span>
    <button onclick="acceptCookies()">Accept</button>
  </div>

  <script>
    function acceptCookies() {
      document.getElementById('cookieBanner').style.display = 'none';
      localStorage.setItem('cookiesAccepted', 'true');
    }
    window.onload = () => {
      if(localStorage.getItem('cookiesAccepted')) {
        document.getElementById('cookieBanner').style.display = 'none';
      }
    }
  </script>

</body>
</html>
