<?php

require "./utilities/database.php";

switch ($_GET["target"]) {
    case "musics":
        echo json_encode(get_musics());
        break;
    case "sfxs":
        echo json_encode(get_sfxs());
        break;
    case "music_categories":
        echo json_encode(get_music_categories());
        break;
    case "sfx_categories":
        echo json_encode(get_sfx_categories());
        break;
}