<?php
require 'vendor/autoload.php';
session_start();

// Stripe Secret Key (Test Mode)
\Stripe\Stripe::setApiKey('sk_test_51PoIL7JjFHItPEI8Jz8SgHsqReJ649tIq58t9ne14X913PEKC4u5VPRxHPkbd6KNbBOY5I7WAMtikbTTOFpenCVm00G7PGp6Mj');

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$plan = $_POST['plan'] ?? '';
$prices = [
    "basic"   => 0,
    "pro"     => 9.99,
    "premium" => 19.99
];

if (!isset($prices[$plan])) {
    echo json_encode(["status" => "error", "message" => "Invalid plan"]);
    exit;
}

$amount = $prices[$plan] * 100; // cents

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => ucfirst($plan) . " Plan"],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://locahost.com/payment_success.php?session_id={CHECKOUT_SESSION_ID}&plan=' . urlencode($plan),
        'cancel_url' => 'http://yourdomain.com/upgrade.php',
    ]);

    echo json_encode([
        "status" => "success",
        "id" => $checkout_session->id
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
