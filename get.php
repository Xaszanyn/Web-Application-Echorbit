<?php

require "./utilities/database.php";

switch ($_GET["target"]) {
    case "musics":
        echo json_encode(get_musics());
        break;
    case "sfxs":
        echo json_encode(get_sfxs());
        break;
}