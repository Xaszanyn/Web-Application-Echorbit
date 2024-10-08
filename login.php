<?php

require "./utilities/post.php";
require "./utilities/database.php";

$login = post();

if (isset($login["email"])) $user = login_user($login["email"], $login["password"]);
else $user = login_user_session($login["session"]);

echo json_encode($user);
