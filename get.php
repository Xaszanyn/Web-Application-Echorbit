<?php

require "./utilities/database.php";

switch ($_GET["target"]) {
    case "products":
        echo json_encode(get_products());
        break;
    case "music_categories":
        echo json_encode(get_music_categories());
        break;
    case "sfx_categories":
        echo json_encode(get_sfx_categories());
        break;
}
