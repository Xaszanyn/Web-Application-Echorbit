<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/post.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/mail.php";

$registry = post();

session_start();

switch ($registry["phase"]) {
    case "register":
        echo register($registry["email"]);
        break;
    case "confirm":
        echo confirm($registry["code"]);
        break;
    case "create":
        echo create($registry["code"], $registry["password"], $registry["guest"]);
        break;
}

function register($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return json_encode(["status" => "email_invalid"]);

    if (!register_email_control($email))
        return json_encode(["status" => "email_used"]);

    $_SESSION["phase"] = "register";
    $_SESSION["email"] = $email;
    $_SESSION["code"] = mt_rand(100000, 999999);
    $_SESSION["attempt"] = 3;
    send_mail_text($_SESSION["email"], "Verification Code", "Your verification code is: <b>" . $_SESSION["code"] . "</b>.");
    return json_encode(["status" => "success"]);
}

function confirm($code)
{
    if (!isset($_SESSION["phase"]) || $_SESSION["phase"] != "register")
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

function create($code, $password, $guest)
{
    if (!isset($_SESSION["phase"]) || $_SESSION["phase"] != "confirm")
        return json_encode(["status" => "timeout"]);

    if (--$_SESSION["attempt"] == 0) {
        $_SESSION = [];
        return json_encode(["status" => "maximum_attempt"]);
    }

    if ($code != $_SESSION["code"])
        return json_encode(["status" => "code_invalid"]);

    // Register Mail
    $session = register_user($password, $guest);
    session_destroy();

    return json_encode(["status" => "success", "session" => $session]);
}
