<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/post.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";

$user = post();

switch ($user["action"]) {
    case "favorite":
        $user = user_favorite($user["session"], $user["id"]);
        break;
    case "unfavorite":
        $user = user_unfavorite($user["session"], $user["id"]);
        break;
    case "cart":
        $user = user_cart($user["session"], $user["id"]);
        break;
    case "uncart":
        $user = user_uncart($user["session"], $user["id"]);
        break;
    case "checkout":
        $user = create_order_request($user["session"]);
        break;
    case "information":
        $user = user_information($user["session"], $user["name"], $user["phone"], $user["country"]);
        break;
    case "download":
        $user = user_download($user["session"], $user["id"]);
        break;
}

echo json_encode($user);
