<?php

require_once "/home/atjufjlwxjd0/public_html/services/utilities/configuration.php";
require_once "/home/atjufjlwxjd0/public_html/services/stripe/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

$products = (\Stripe\Product::all(['limit' => 100]))->data;

for ($index = 0; $index < count($products); $index++) {
    $product = $products[$index];

    if (!empty($products[$index]->default_price))
        $products[$index]->price = (\Stripe\Price::retrieve($products[$index]->default_price))->unit_amount / 100;
}

file_put_contents("/home/atjufjlwxjd0/public_html/services/utilities/stripe.json", json_encode($products, JSON_PRETTY_PRINT));
