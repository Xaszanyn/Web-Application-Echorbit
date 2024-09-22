<?php

require "../stripe/init.php";
require "../utilities/configuration.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);


if ($_GET["request"]) {
    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Echorbit Audio Product Name',
                            'description' => 'Echorbit Audio product description. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                            'images' => ['https://picsum.photos/1024']
                        ],
                        'unit_amount' => 10000,  // Amount in cents ($100.00)
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => 'https://echorbitaudio.com',
            'cancel_url' => 'https://ekin.codes',
        ]);

        header("Location: " . $session->url);

    } catch (\Stripe\Exception\ApiErrorException $error) {
        echo $error->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>

<body>
    <h1>Stripe Test Payment</h1>
    <hr>
    <p>Click to make payment.</p>
    <form method="get" action="https://echorbitaudio.com/services/test/index.php">
        <input type="hidden" name="request" value="payment">
        <button type="submit">$4.5 Checkout</button>
    </form>
    <hr>
</body>

</html>