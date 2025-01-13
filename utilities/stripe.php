<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/configuration.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

/* =========================================*/
function log_text($message)
{
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/log.txt", date("Y/m/d | H:i:s") . " | " . $message . "\n", FILE_APPEND);
}
log_text("stripe.php V2 initiated.");
/* =========================================*/

$products = (\Stripe\Product::all(['limit' => 100]))->data;

for ($index = 0; $index < count($products); $index++) {
    $product = $products[$index];

    if (!empty($products[$index]->default_price))
        $products[$index]->price = (\Stripe\Price::retrieve($products[$index]->default_price))->unit_amount / 100;
}

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/utilities/stripe.json", json_encode($products, JSON_PRETTY_PRINT));
