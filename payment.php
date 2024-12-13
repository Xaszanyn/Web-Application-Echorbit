<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/configuration.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/debug.php";
    log_text("payment.php try {}");

    \Stripe\Stripe::setApiKey(STRIPE_SECRET);

    log_text("payment.php setApiKey()");

    $event = \Stripe\Webhook::constructEvent(@file_get_contents('php://input'), $_SERVER['HTTP_STRIPE_SIGNATURE'], STRIPE_WEBHOOK);

    log_text("payment.php event");

    log_text("payment.php webhook");

    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];

            \Stripe\Invoice::create([
                'customer' => $session['customer'],
                'auto_advance' => true,
            ]);

            log_text("payment.php checkout.session.completed -> " . $session->id);

            complete_order($session->id);

            break;
    }

    http_response_code(200);
} catch (\UnexpectedValueException $error) {
    http_response_code(400);
    exit();
} catch (\Stripe\SignatureVerificationException $error) {
    http_response_code(400);
    exit();
}
