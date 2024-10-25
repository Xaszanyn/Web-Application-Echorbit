<?php

require "./stripe/init.php";
require "./utilities/configuration.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

try {
    $event = \Stripe\Webhook::constructEvent(@file_get_contents('php://input'), $_SERVER['HTTP_STRIPE_SIGNATURE'], STRIPE_WEBHOOK);

    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];

            \Stripe\Invoice::create([
                'customer' => $session['customer'],
                'auto_advance' => true,
            ]);

            // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // //
            file_put_contents('STRIPE_WEBHOOK.txt', print_r($session, true) . "\n____________________________________________________________________________________________________
====================================================================================================\n", FILE_APPEND);
            // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // //

            break;
    }

    http_response_code(200);
} catch (\UnexpectedValueException $error) {
    http_response_code(400);
    exit();
} catch (\Stripe\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}
