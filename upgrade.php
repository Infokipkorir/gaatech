<?php

require_once __DIR__ . '/db.php';   // must define $conn (mysqli)

// ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// AJAX endpoints (same file) ------------------------------------------------
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];
    $user_id = (int)$_SESSION['user_id'];

    // Save user details (Step 2)
    if ($action === 'save_details') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // basic validation
        if ($name === '' || $email === '') {
            echo json_encode(['status'=>'error','message'=>'Name and email required']);
            exit;
        }

        // save into session (so details persist between steps)
        $_SESSION['upgrade_details'] = ['name'=>$name, 'email'=>$email, 'phone'=>$phone];

        // optionally update user's profile in DB
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['status'=>'ok','message'=>'Details saved']);
        exit;
    }

    // Activate free trial (Step 3 for trial)
    if ($action === 'activate_trial') {
        $plan = 'trial';
        $trial_days = 14;
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$trial_days} days"));

        // Insert subscription
        $ins = $conn->prepare("INSERT INTO subscriptions (user_id, plan, started_at, expires_at, payment_method) VALUES (?, ?, NOW(), ?, 'trial')");
        if (!$ins) {
            echo json_encode(['status'=>'error','message'=>'DB prepare failed: '.$conn->error]);
            exit;
        }
        $ins->bind_param("iss", $user_id, $plan, $expires_at);
        if (!$ins->execute()) {
            echo json_encode(['status'=>'error','message'=>'DB execute failed: '.$ins->error]);
            $ins->close();
            exit;
        }
        $ins->close();

        // Update users table
        $u = $conn->prepare("UPDATE users SET plan = ? WHERE user_id = ?");
        if ($u) {
            $u->bind_param("si", $plan, $user_id);
            $u->execute();
            $u->close();
        }

        // store chosen plan in session
        $_SESSION['selected_plan'] = $plan;

        echo json_encode(['status'=>'ok','message'=>"Trial activated until {$expires_at}"]);
        exit;
    }

    // Simulated M-Pesa payment (Step 3 for paid plans) - testing only
    if ($action === 'pay_mpesa_sim') {
        $plan = $_POST['plan'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $prices = ['pro' => 9.99, 'premium' => 19.99];

        if (!isset($prices[$plan])) {
            echo json_encode(['status'=>'error','message'=>'Invalid plan']);
            exit;
        }

        if (!preg_match('/^\+?\d+$/', preg_replace('/\s+/', '', $phone))) {
            echo json_encode(['status'=>'error','message'=>'Invalid phone']);
            exit;
        }

        // SIMULATION: pretend we did STK push and it succeeded immediately.
        // In production you'd initiate Daraja STK push and wait for callback.
        $amount = $prices[$plan];

        // Save a simulated transaction (optional)
        $stmt = $conn->prepare("INSERT INTO mpesa_transactions (user_id, plan, checkout_request_id, merchant_request_id, phone, amount, status, raw_response) VALUES (?, ?, ?, ?, ?, ?, 'success', ?)");
        $checkoutRequestID = 'SIM-'.time();
        $merchantRequestID = 'SIMMER-'.rand(1000,9999);
        $raw = $conn->real_escape_string(json_encode(['sim'=>true,'plan'=>$plan,'phone'=>$phone,'amount'=>$amount]));
        if ($stmt) {
            $stmt->bind_param("isssds", $user_id, $plan, $checkoutRequestID, $merchantRequestID, $phone, $amount, $raw);
            // Note: binding types: i (int), s (string), d (double)
            // Adjust bind order/types if your schema differs. For safety, use simpler insert below if errors.
            $stmt->execute();
            $stmt->close();
        } else {
            // fallback: basic insert
            $q = "INSERT INTO mpesa_transactions (user_id, plan, checkout_request_id, merchant_request_id, phone, amount, status, raw_response) VALUES ($user_id, '".$conn->real_escape_string($plan)."', '".$checkoutRequestID."', '".$merchantRequestID."', '".$conn->real_escape_string($phone)."', ".floatval($amount).", 'success', '".$raw."')";
            $conn->query($q);
        }
        $tx_id = $conn->insert_id;

        // Create subscription
        $ins = $conn->prepare("INSERT INTO subscriptions (user_id, plan, started_at, payment_method, mpesa_transaction_id) VALUES (?, ?, NOW(), 'mpesa_sim', ?)");
        if ($ins) {
            $ins->bind_param("isi", $user_id, $plan, $tx_id);
            $ins->execute();
            $ins->close();
        }

        // Update users.table plan
        $u = $conn->prepare("UPDATE users SET plan = ? WHERE user_id = ?");
        if ($u) {
            $u->bind_param("si", $plan, $user_id);
            $u->execute();
            $u->close();
        }

        // set session chosen plan
        $_SESSION['selected_plan'] = $plan;

        echo json_encode(['status'=>'ok','message'=>'Payment simulated and plan activated']);
        exit;
    }

    // unknown action
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
    exit;
}

// End AJAX endpoints ---------------------------------------------------------

// If not AJAX, render the HTML wizard UI below.
// Prefill details from session or DB
$user_id = (int)$_SESSION['user_id'];
$profile_name = $_SESSION['username'] ?? '';
$profile_email = '';
$profile_phone = '';

// Try to fetch from DB if values missing
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($n, $e, $p);
    if ($stmt->fetch()) {
        if (!$profile_name) $profile_name = $n;
        if (!$profile_email) $profile_email = $e;
        if (!$profile_phone) $profile_phone = $p;
    }
    $stmt->close();
}

// If session has saved details, prefer them
if (!empty($_SESSION['upgrade_details'])) {
    $d = $_SESSION['upgrade_details'];
    $profile_name = $d['name'] ?? $profile_name;
    $profile_email = $d['email'] ?? $profile_email;
    $profile_phone = $d['phone'] ?? $profile_phone;
}

// HTML page -----------------------------------------------------------------
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Upgrade Wizard — Gaatech QR</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{--accent:#2563eb}
    body{font-family:Inter,system-ui,Arial;background:#f7fafc;color:#0f172a}
    .container{max-width:1000px;margin:28px auto}
    .card-plan{border-radius:12px; padding:18px; cursor:pointer; transition:transform .12s ease, box-shadow .12s ease; user-select:none}
    .card-plan:hover{transform:translateY(-6px); box-shadow:0 10px 30px rgba(16,24,40,0.08)}
    .card-plan.selected{box-shadow:0 12px 36px rgba(16,24,40,0.12); border:2px solid rgba(37,99,235,0.18)}
    .plan-ico{font-size:28px}
    .step{display:none}
    .step.active{display:block}
    .wizard-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
    #loaderOverlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(255,255,255,0.85);z-index:9999}
    #loaderOverlay .spinner-border{width:3rem;height:3rem}
  </style>
</head>
<body>

  <div class="card p-3 mb-3">
    <div class="d-flex gap-3 align-items-center">
      <div class="badge bg-white text-primary">Step <span id="stepNum">1</span> / 4</div>
      <div id="stepTitle" class="fw-semibold">Choose a plan</div>
    </div>
  </div>

  <!-- STEP 1: Choose Plan -->
  <div id="step1" class="step active">
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <div class="card-plan bg-white h-100 border" data-plan="trial" onclick="selectPlan(this)">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-gift plan-ico text-secondary"></i>
            <div>
              <div class="fw-bold">Free Trial</div>
              <small class="text-muted">14-day free trial</small>
            </div>
          </div>
          <div class="mt-3"><strong class="text-success">Free</strong></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card-plan bg-white h-100 border" data-plan="pro" onclick="selectPlan(this)">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-gem plan-ico text-primary"></i>
            <div>
              <div class="fw-bold">Pro</div>
              <small class="text-muted">Unlimited QR + analytics</small>
            </div>
          </div>
          <div class="mt-3"><strong>$9.99 / month</strong></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card-plan bg-white h-100 border" data-plan="premium" onclick="selectPlan(this)">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-crown plan-ico text-warning"></i>
            <div>
              <div class="fw-bold">Premium</div>
              <small class="text-muted">Priority support & branding</small>
            </div>
          </div>
          <div class="mt-3"><strong>$19.99 / month</strong></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card-plan bg-white h-100 border" data-plan="basic" onclick="selectPlan(this)">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-seam plan-ico text-muted"></i>
            <div>
              <div class="fw-bold">Basic</div>
              <small class="text-muted">Up to 50 QR codes</small>
            </div>
          </div>
          <div class="mt-3"><strong>Free</strong></div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end">
      <button class="btn btn-primary" id="toStep2Btn" onclick="gotoStep(2)" disabled>Next →</button>
    </div>
  </div>

  <!-- STEP 2: Details -->
  <div id="step2" class="step">
    <div class="card p-3 mb-3">
      <h5>Enter your details</h5>
      <form id="detailsForm" onsubmit="return saveDetails();">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Full name</label>
            <input class="form-control" id="inpName" required value="<?php echo htmlspecialchars($profile_name); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input class="form-control" id="inpEmail" type="email" required value="<?php echo htmlspecialchars($profile_email); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input class="form-control" id="inpPhone" value="<?php echo htmlspecialchars($profile_phone); ?>">
          </div>
        </div>

        <div class="mt-3 d-flex">
          <button type="button" class="btn btn-secondary" onclick="gotoStep(1)">← Back</button>
          <button type="submit" class="btn btn-primary ms-auto">Save & Continue →</button>
        </div>
      </form>
    </div>
  </div>

  <!-- STEP 3: Payment (Mpesa simulated) -->
  <div id="step3" class="step">
    <div class="card p-3 mb-3">
      <h5>Payment</h5>
      <p class="text-muted" id="selectedPlanLabel">Selected plan: —</p>

      <div id="paymentMpesa" style="display:none">
        <label class="form-label">Phone number (for M-Pesa prompt)</label>
        <input class="form-control mb-2" id="mpesaPhone" placeholder="e.g., 0712345678">
        <div class="d-flex">
          <button class="btn btn-secondary" onclick="gotoStep(2)">← Back</button>
          <button class="btn btn-success ms-auto" onclick="payMpesaSim()">Pay (Simulated)</button>
        </div>
      </div>

      <div id="paymentTrial" style="display:none">
        <div class="alert alert-info">This plan is free / trial. No payment required.</div>
        <div class="d-flex">
          <button class="btn btn-secondary" onclick="gotoStep(2)">← Back</button>
          <button class="btn btn-success ms-auto" onclick="activateTrial()">Activate Trial</button>
        </div>
      </div>

    </div>
  </div>

  <!-- STEP 4: Confirmation -->
  <div id="step4" class="step">
    <div class="card p-3">
      <h5>Confirmation</h5>
      <div id="confirmationArea">
        <div class="text-muted">No action yet</div>
      </div>
      <div class="mt-3 d-flex justify-content-end">
        <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
      </div>
    </div>
  </div>

</div>

<!-- loader overlay (simple) -->
<div id="loaderOverlay" class="d-flex" role="status" aria-hidden="true">
  <div class="text-center">
    <div class="spinner-border text-primary" role="status"></div>
    <div id="loaderMsg" class="mt-2">Processing...</div>
  </div>
</div>

<script>
let selectedPlan = null;

function setLoader(show, message = 'Processing...') {
  document.getElementById('loaderOverlay').style.display = show ? 'flex' : 'none';
  document.getElementById('loaderMsg').textContent = message;
}

function selectPlan(el) {
  document.querySelectorAll('.card-plan').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedPlan = el.getAttribute('data-plan');
  document.getElementById('toStep2Btn').disabled = false;
}

function gotoStep(n) {
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');
  document.getElementById('stepNum').textContent = n;
  // step-specific
  if (n === 3) {
    document.getElementById('selectedPlanLabel').textContent = 'Selected plan: ' + (selectedPlan || '—');
    if (selectedPlan === 'trial' || selectedPlan === 'basic') {
      document.getElementById('paymentMpesa').style.display = 'none';
      document.getElementById('paymentTrial').style.display = 'block';
    } else {
      document.getElementById('paymentMpesa').style.display = 'block';
      document.getElementById('paymentTrial').style.display = 'none';
      // prefill mpesa phone from details if available
      const p = document.getElementById('inpPhone').value || '';
      document.getElementById('mpesaPhone').value = p;
    }
  }
  if (n === 4) {
    // nothing here - confirmation area gets updated after activate/pay
  }
}

async function saveDetails() {
  const name = document.getElementById('inpName').value.trim();
  const email = document.getElementById('inpEmail').value.trim();
  const phone = document.getElementById('inpPhone').value.trim();
  if (!name || !email) { alert('Name and email required'); return false; }

  setLoader(true, 'Saving details...');
  const data = new URLSearchParams();
  data.append('name', name);
  data.append('email', email);
  data.append('phone', phone);

  const res = await fetch(window.location.pathname + '?action=save_details', { method:'POST', body: data });
  const j = await res.json();
  setLoader(false);
  if (j.status === 'ok') {
    gotoStep(3);
  } else {
    alert(j.message || 'Error saving details');
  }
  return false;
}

async function activateTrial() {
  setLoader(true, 'Activating trial...');
  const res = await fetch(window.location.pathname + '?action=activate_trial', { method:'POST' });
  const j = await res.json();
  setLoader(false);
  if (j.status === 'ok') {
    document.getElementById('confirmationArea').innerHTML = '<div class="alert alert-success">'+j.message+'</div>';
    gotoStep(4);
  } else {
    alert(j.message || 'Failed to activate');
  }
}

async function payMpesaSim() {
  const phone = document.getElementById('mpesaPhone').value.trim();
  if (!phone) { alert('Enter phone for M-Pesa prompt'); return; }
  if (!selectedPlan) { alert('Choose a plan'); return; }

  setLoader(true, 'Simulating M-Pesa payment...');
  const data = new URLSearchParams();
  data.append('plan', selectedPlan);
  data.append('phone', phone);

  const res = await fetch(window.location.pathname + '?action=pay_mpesa_sim', { method:'POST', body: data });
  const j = await res.json();
  setLoader(false);
  if (j.status === 'ok') {
    document.getElementById('confirmationArea').innerHTML = '<div class="alert alert-success">'+j.message+'</div>';
    gotoStep(4);
  } else {
    document.getElementById('confirmationArea').innerHTML = '<div class="alert alert-danger">'+(j.message || 'Payment failed')+'</div>';
    gotoStep(4);
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', function(){
  // restore plan from session if available
  <?php if (!empty($_SESSION['selected_plan'])): ?>
    selectedPlan = '<?php echo addslashes($_SESSION['selected_plan']); ?>';
    // highlight the card
    const el = document.querySelector('.card-plan[data-plan="<?php echo addslashes($_SESSION['selected_plan']); ?>"]');
    if (el) el.classList.add('selected');
    document.getElementById('toStep2Btn').disabled = false;
  <?php endif; ?>
});
</script>

</body>
</html>
