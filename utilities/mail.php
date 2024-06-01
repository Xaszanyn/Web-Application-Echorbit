<?php

function send_mail_text($target, $subject, $message, $name = "")
{
    // $message = ($name ? "Merhaba " . $name . ", " : $name) . $message;

    mail($target, $subject, $message);

    // mail($target, $subject, $message, [
    //     "From" => "Fit Gelsin <no-reply@fitgelsin.com>",
    //     "MIME-Version" => "1.0",
    //     "Content-Type" => "text/html; charset=iso-8859-1"
    // ]);
}