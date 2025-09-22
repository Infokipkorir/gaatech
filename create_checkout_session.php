<?php
// create_checkout_session.php
session_start();
require_once "db.php";
require_once "config.php"; // STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY, STRIPE_WEBHOOK_SECRET
require 'vendor/autoload.php'; // stripe-php

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$plan = $_POST['plan'] ?? '';

$prices = [
  'basic'   => 0.00,
  'pro'     => 9.99,
  'premium' => 19.99,
];

if (!isset($prices[$plan])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid plan']);
    exit;
}

// For test mode we create a single-line Checkout session with a one-time payment.
// For production you may want to use Stripe Prices and subscriptions.

$amount = intval(round($prices[$plan] * 100)); // in cents (USD)
$currency = 'usd';

// If plan is free (0.00) we can auto-upgrade without stripe (optional).
if ($amount === 0) {
    // update DB directly
    $stmt = $conn->prepare("UPDATE users SET plan = ? WHERE user_id = ?");
    $stmt->bind_param("si", $plan, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['sessionUrl' => 'success_local://free']); // frontend will handle
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update plan']);
    }
    exit;
}

try {
    // Create Checkout Session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => strtoupper($plan) . ' Plan - Gaatech QR',
                    'metadata' => ['plan' => $plan, 'user_id' => $user_id]
                ],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        // After payment success, Stripe will redirect here (you can show message)
        'success_url' => (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/upgrade_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/upgrade_payment.php',
    ]);

    echo json_encode(['sessionId' => $session->id, 'sessionUrl' => $session->url]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
