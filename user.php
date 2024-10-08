<?php

require "./utilities/post.php";
require "./utilities/database.php";

$user = post();

switch ($user["action"]) {
    case "favorite":
        $user = user_favorite($user["session"], $user["id"]);
        break;
    case "unfavorite":
        $user = user_unfavorite($user["session"], $user["id"]);
        break;
}

echo json_encode($user);
