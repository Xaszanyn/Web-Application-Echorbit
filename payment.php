<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/configuration.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";

try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET);

    $event = \Stripe\Webhook::constructEvent(@file_get_contents('php://input'), $_SERVER['HTTP_STRIPE_SIGNATURE'], STRIPE_WEBHOOK);

    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];

            \Stripe\Invoice::create([
                'customer' => $session['customer'],
                'auto_advance' => true,
            ]);

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
