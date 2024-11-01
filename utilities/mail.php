<?php

function send_mail($target, $subject, $mail)
{
    mail($target, $subject, $mail, [
        "From" => "Echorbit <no-reply@echorbit.com>",
        "MIME-Version" => "1.0",
        "Content-Type" => "text/html; charset=UTF-8"
    ]);
}

function send_mail_text($target, $subject, $message)
{
    send_mail($target, $subject, $message);
}
