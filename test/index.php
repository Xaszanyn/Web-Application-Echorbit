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
                        'name' => 'Echorbit Audio Product Name',
                        'description' => 'Echorbit Audio product description. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                        'images' => ['https://picsum.photos/512', 'https://picsum.photos/1024/512', 'https://picsum.photos/1024/2048']
                    ],
                    'unit_amount' => 450,  // Amount in cents ($4.50)
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