<?php

require "../stripe/init.php";
require "../utilities/configuration.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Custom Payment Name',
                    ],
                    'unit_amount' => 100,  // Amount in cents ($1.00)
                ],
                'quantity' => 1,
            ]
        ],
        'mode' => 'payment',
        'success_url' => 'https://echorbitaudio.com',
        'cancel_url' => 'https://ekin.codes',
    ]);

    echo $session->url;

} catch (\Stripe\Exception\ApiErrorException $error) {
    echo $error->getMessage();
}



// try {
//     $paymentIntent = \Stripe\PaymentIntent::create([
//         'amount' => 1,
//         'currency' => 'usd',
//         'payment_method_types' => ['card'],
//         'description' => 'Custom payment without product',
//     ]);

//     echo json_encode([
//         'client_secret' => $paymentIntent->client_secret,
//     ]);

// } catch (\Stripe\Exception\ApiErrorException $error) {
//     echo json_encode(['error' => $error->getMessage()]);
// }




// // Create a product
// $product = \Stripe\Product::create([
//     'name' => 'Sample Product',
// ]);

// // Create a price for the product
// $price = \Stripe\Price::create([
//     'product' => $product->id,
//     'unit_amount' => 1000, // 1000 cents = $10
//     'currency' => 'usd',
// ]);

// // Create a payment link
// $paymentLink = \Stripe\PaymentLink::create([
//     'line_items' => [
//         [
//             'price' => $price->id,
//             'quantity' => 1,
//         ],
//     ],
// ]);

// // Output the payment link URL
// echo "Payment URL: " . $paymentLink->url;