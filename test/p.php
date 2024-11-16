<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

$data = (\Stripe\Product::all())->data;

print_r($data);
