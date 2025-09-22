<?php
require 'vendor/autoload.php';

session_start();

\Stripe\Stripe::setApiKey(sk_test_51PoIL7JjFHItPEI8Jz8SgHsqReJ649tIq58t9ne14X913PEKC4u5VPRxHPkbd6KNbBOY5I7WAMtikbTTOFpenCVm00G7PGp6Mj); //Stripe Secret Key

$plan = $_POST['plan'] ?? '';
$prices = [
    "basic" => 0,
    "pro" => 9.99,
    "premium" => 19.99
];

if (!isset($prices[$plan])) {
    die("Invalid plan");
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
        'success_url' => 'http://yourdomain.com/payment_success.php?plan=' . urlencode($plan),
        'cancel_url' => 'http://yourdomain.com/upgrade_payment.php',
    ]);

    header("Location: " . $checkout_session->url);
    exit;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
