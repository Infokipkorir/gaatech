<!DOCTYPE html>
<html lang="en">
<head>
  <head>
  <meta charset="UTF-8">
  <title>Gaatech - Dashboard</title>
  <link rel="icon" type="image/png" href="admin\assets\Gaatech logo2.jpg">
  <link rel="stylesheet" href="assets/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f9ff;
    }
    .hero {
      padding: 60px 0;
      background: linear-gradient(to right, #0d6efd, #0a58ca);
      color: white;
      text-align: center;
    }
    .hero h1 {
      font-weight: bold;
      font-size: 2.8rem;
    }
    .plans-section {
      padding: 60px 0;
    }
    .card {
      border: none;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .btn-primary {
      background-color: #0d6efd;
      border: none;
    }
    footer {
      background: #0d6efd;
      color: white;
      padding: 20px 0;
      text-align: center;
      margin-top: 60px;
    }
  </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <h1>Welcome to Gaatech QR</h1>
    <p class="lead">Fast, secure, and stylish QR code generation with analytics and sharing</p>
    <div class="mt-4">
      <a href="login.php" class="btn btn-light me-2"><i class="fas fa-sign-in-alt"></i> Login</a>
      <a href="register.php" class="btn btn-outline-light"><i class="fas fa-user-plus"></i> Register</a>
    </div>
  </div>
</section>

<!-- Plans Section -->
<section class="plans-section text-center">
  <div class="container">
    <h2 class="text-primary mb-4">Choose Your Plan</h2>
    <div class="row justify-content-center">
      <!-- Free Plan -->
      <div class="col-md-5 mb-4">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title text-primary"><i class="fas fa-leaf"></i> Free Plan</h4>
            <p class="card-text">Perfect for individuals who want to create up to <strong>3 QR codes daily</strong>.</p>
            <ul class="list-unstyled mb-3">
              <li><i class="fas fa-check text-success"></i> QR Code Generator</li>
              <li><i class="fas fa-check text-success"></i> Save History</li>
              <li><i class="fas fa-times text-danger"></i> No Analytics</li>
              <li><i class="fas fa-times text-danger"></i> Limited Downloads</li>
            </ul>
            <a href="register.php" class="btn btn-outline-primary">Get Started</a>
          </div>
        </div>
      </div>

      <!-- Pro Plan -->
      <div class="col-md-5 mb-4">
        <div class="card border-primary">
          <div class="card-body">
            <h4 class="card-title text-primary"><i class="fas fa-rocket"></i> Pro Plan</h4>
            <p class="card-text">Unlimited access to all QR tools, downloads, and advanced features.</p>
            <ul class="list-unstyled mb-3">
              <li><i class="fas fa-check text-success"></i> Unlimited QR Generation</li>
              <li><i class="fas fa-check text-success"></i> PDF & Branding Options</li>
              <li><i class="fas fa-check text-success"></i> Analytics & Tracking</li>
              <li><i class="fas fa-check text-success"></i> Priority Support</li>
            </ul>
            <a href="register.php" class="btn btn-primary">Upgrade Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Cookie Banner -->
<div id="cookieBanner" class="position-fixed bottom-0 start-0 w-100 bg-primary text-white p-3 shadow-lg" style="z-index: 9999; display: none;">
  <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
    <div>
      <strong>We use cookies</strong> to improve your experience on Gaatech. Read our
      <a href="cookies.php" class="text-white text-decoration-underline">Cookies Policy</a>.
    </div>
    <div class="mt-2 mt-md-0">
      <button class="btn btn-light btn-sm me-2" onclick="acceptCookies()">Accept</button>
      <button class="btn btn-outline-light btn-sm" onclick="declineCookies()">Decline</button>
    </div>
  </div>
</div>

<script>
  function setCookie(name, value, days) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = name + "=" + encodeURIComponent(value) + "; expires=" + expires + "; path=/";
  }

  function getCookie(name) {
    return document.cookie.split('; ').find(row => row.startsWith(name + "="))?.split('=')[1];
  }

  function acceptCookies() {
    setCookie('gaatech_cookies', 'accepted', 365);
    document.getElementById('cookieBanner').style.display = 'none';
  }

  function declineCookies() {
    setCookie('gaatech_cookies', 'declined', 365);
    document.getElementById('cookieBanner').style.display = 'none';
  }

  window.onload = function () {
    if (!getCookie('gaatech_cookies')) {
      document.getElementById('cookieBanner').style.display = 'block';
    }
  }

function acceptCookies() {
  localStorage.setItem("cookieAccepted", "true");
  document.getElementById("cookie-banner").style.display = "none";
}

window.onload = () => {
  if (!localStorage.getItem("cookieAccepted")) {
    document.getElementById("cookie-banner").style.display = "block";
  }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
