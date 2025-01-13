<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/configuration.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

function log_text($message)
{
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/log.txt", date("Y/m/d | H:i:s") . " | " . $message . "\n", FILE_APPEND);
}

log_text("stripe.php initiated.");

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/utilities/stripe.json", json_encode((\Stripe\Product::all(['limit' => 100]))->data, JSON_PRETTY_PRINT));
