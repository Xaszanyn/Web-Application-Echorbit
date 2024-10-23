<?php

require "./stripe/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

// $endpoint_secret;

// $payload = @file_get_contents('php://input');
// $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    $event = \Stripe\Webhook::constructEvent(
        @file_get_contents('php://input'),
        $_SERVER['HTTP_STRIPE_SIGNATURE'],
        STRIPE_WEBHOOK
    );

    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];

            // Write session details to a text file
            $file = 'session_details.txt'; // Specify the filename
            $current_time = date('Y-m-d H:i:s');
            $session_data = "Time: $current_time\n" . print_r($session, true) . "\n\n";

            file_put_contents($file, $session_data, FILE_APPEND);
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
