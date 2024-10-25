<?php

require "./utilities/post.php";
require "./utilities/database.php";
require "./utilities/mail.php";

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
    send_mail_text($_SESSION["email"], "VERIFICATION CODE", "VERIFICATION_CODE: <b>" . $_SESSION["code"] . "</b>.");
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

    send_mail_text($_SESSION["email"], "REGISTRATION", "REGISTRATION_COMPLETE: TRUE");
    register_user($password, $guest);
    session_destroy();

    return json_encode(["status" => "success"]);
}
