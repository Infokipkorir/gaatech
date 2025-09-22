<?php
// stripe_webhook.php
require 'vendor/autoload.php';
require_once "db.php";
require_once "config.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$event = null;

try {
    if (!empty(STRIPE_WEBHOOK_SECRET)) {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);
    } else {
        // If you don't have webhook secret (dev only), decode directly (less secure)
        $event = json_decode($payload);
    }
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;

        // For one-time payments, payment_status may be 'paid'
        if ($session->payment_status === 'paid' || $session->status === 'complete') {
            // Retrieve metadata
            $metadata = $session->display_items[0] ?? null;
            // Better to obtain metadata from session line_items or session itself (depends)
            // Safer: fetch session from Stripe API to get metadata you set
            try {
                $sess = \Stripe\Checkout\Session::retrieve($session->id, ['expand' => ['line_items']]);
                // metadata may be on product or on session; we set metadata in product_data
                $lineItem = $sess->line_items->data[0] ?? null;
                $plan = null;
                $user_id = null;
                if ($lineItem && isset($lineItem->price->product)) {
                    // fetch product to inspect metadata if needed (not necessary)
                }
                // We stored user_id and plan as product_data.metadata — but easier: retrieve session.payment_intent -> charges -> metadata
                // In our create_checkout_session we used product_data.metadata — we can find it in session.line_items.data[0].price.product.metadata
                // As fallback, check session.metadata (if you set there).
                // Try retrieving product metadata robustly:
                if ($lineItem && isset($lineItem->price->product)) {
                    $productId = $lineItem->price->product;
                    $product = \Stripe\Product::retrieve($productId);
                    $plan = $product->metadata->plan ?? null;
                    $user_id = $product->metadata->user_id ?? null;
                }

                // If not found above, check session.metadata
                if (!$plan && !empty($sess->metadata)) {
                    $plan = $sess->metadata->plan ?? $plan;
                    $user_id = $sess->metadata->user_id ?? $user_id;
                }

                // Another fallback: parse from session.customer_details or description if you included it
                // If still missing, you may require storing session id to DB on creation to map to user - recommended.

                if ($plan && $user_id) {
                    // Update users table
                    $stmt = $conn->prepare("UPDATE users SET plan = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $plan, $user_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // If metadata not found, you should link session ID to DB during session creation.
                    // For robust production, when creating a session, store mapping session->user->plan in DB.
                }
            } catch (Exception $e) {
                // log error
            }
        }
        break;

    // ... handle other event types if desired ...
    default:
        // Unexpected event
        break;
}

http_response_code(200);
