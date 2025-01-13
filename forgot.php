<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/post.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/mail.php";

$operation = post();

session_start();

switch ($operation["phase"]) {
    case "forgot":
        echo forgot($operation["email"]);
        break;
    case "confirm":
        echo confirm($operation["code"]);
        break;
    case "change":
        echo change($operation["code"], $operation["password"]);
        break;
}

function forgot($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !registered_email($email))
        return json_encode(["status" => "email_invalid"]);

    $_SESSION["phase"] = "forgot";
    $_SESSION["email"] = $email;
    $_SESSION["code"] = mt_rand(100000, 999999);
    $_SESSION["attempt"] = 3;
    send_mail_text($_SESSION["email"], "Forgot Password Verification Code", "Your verification code is: <b>" . $_SESSION["code"] . "</b>.");
    return json_encode(["status" => "success"]);
}

function confirm($code)
{
    if (!isset($_SESSION["phase"]) || $_SESSION["phase"] != "forgot")
        return json_encode(["status" => "timeout"]);

    if (--$_SESSION["attempt"] < 0) {
        $_SESSION = [];
        return json_encode(["status" => "maximum_attempt"]);
    }

    if ($code != $_SESSION["code"])
        return json_encode(["status" => "code_invalid"]);

    $_SESSION["phase"] = "confirm";
    $_SESSION["code"] = mt_rand(100000, 999999);
    $_SESSION["attempt"] = 3;
    return json_encode(["status" => "success", "code" => $_SESSION["code"]]);
}

function change($code, $password)
{
    if (!isset($_SESSION["phase"]) || $_SESSION["phase"] != "confirm")
        return json_encode(["status" => "timeout"]);

    if (--$_SESSION["attempt"] == 0) {
        $_SESSION = [];
        return json_encode(["status" => "maximum_attempt"]);
    }

    if ($code != $_SESSION["code"])
        return json_encode(["status" => "code_invalid"]);

    // Password Change Mail
    $session = change_registered_users_password($password);
    session_destroy();

    return json_encode(["status" => "success", "session" => $session]);
}
