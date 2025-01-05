<?php
ini_set('display_errors', 1);
require '../../stripe/vendor/autoload.php';

\Stripe\Stripe::setApiKey("...");
try {
    $session = \Stripe\Checkout\Session::create([
        "payment_method_types" => ['card'],
        "line_items" => $stripe_items,
        "mode" => 'payment',
        "success_url" => 'https://teszt.andristyak.hu/stripe_check.php?i={CHECKOUT_SESSION_ID}',
        "cancel_url" => 'https://teszt.andristyak.hu/stripe_check.php?i={CHECKOUT_SESSION_ID}',
        "customer_email" => $post['invoiceData']['email'],
        "metadata" => ['order_id' => $order_id]
    ]);
    $ret['stripe_url'] = $session->url;
} catch (Exception $e) {
    // Hiba esetén loggolás
    echo 'Hiba történt: ' . $e->getMessage();
}