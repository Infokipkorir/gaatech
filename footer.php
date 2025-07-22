<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<footer style="background-color: #007bff;" class="text-white pt-5 pb-4 mt-5">
  <div class="container text-md-left">
    <div class="row">

      <!-- Column 1 -->
      <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold">Gaatech QR</h6>
        <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #ffffff; height: 2px" />
        <p>
          Your smart way to create, customize, and manage QR codes for business, events, and more. Powered by Gaatech Solutions.
        </p>
      </div>

      <!-- Column 2 -->
      <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold">Quick Links</h6>
        <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #ffffff; height: 2px" />
        <p><a href="dashboard.php" class="text-white text-decoration-none">Dashboard</a></p>
        <p><a href="my_account.php" class="text-white text-decoration-none">My Account</a></p>
        <p><a href="settings.php" class="text-white text-decoration-none">Settings</a></p>
        <p><a href="logout.php" class="text-white text-decoration-none">Logout</a></p>
      </div>
      <!-- Column 3 -->
      <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold">Support</h6>
        <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #ffffff; height: 2px" />
        <p><a href="support.php" class="text-white text-decoration-none">Help Center</a></p>
        <p><a href="upgrade.php" class="text-white text-decoration-none">Upgrade Plan</a></p>
        <p><a href="privacy.php" class="text-white text-decoration-none">Privacy Policy</a></p>
        <p><a href="terms.php" class="text-white text-decoration-none">Terms & Conditions</a></p>
      </div>

      <!-- Column 4 -->
      <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
        <h6 class="text-uppercase fw-bold">Contact</h6>
        <hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #ffffff; height: 2px" />
        <p><i class="fas fa-home me-2"></i> Nairobi, Kenya</p>
        <p><i class="fas fa-envelope me-2"></i> support@gaatech.co.ke</p>
        <p><i class="fas fa-phone me-2"></i> +254 740482738</p>
        <p><i class="fas fa-globe me-2"></i> www.gaatech.co.ke</p>
      </div>

    </div>
  </div>
<!-- Floating Support Button -->
<a href="support.php" class="support-btn" title="Need Help?">
  <i class="fas fa-headset"></i>
</a>

<!-- Button Style -->
<style>
  .support-btn {
    position: fixed;
    bottom: 80px; /* just above footer */
    right: 20px;
    background-color: #dc3545; /* red */
    color: white;
    font-size: 20px;
    padding: 15px;
    border-radius: 50%;
    text-align: center;
    z-index: 9999;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: 0.3s ease;
  }

  .support-btn:hover {
    background-color: #c82333;
    text-decoration: none;
  }
</style>

  <div class="text-center py-3 bg-primary mt-4">
    &copy; <?= date('Y') ?> Gaatech QR Generator. All rights reserved.
  </div>
</footer>
