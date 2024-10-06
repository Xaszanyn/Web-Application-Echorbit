<?php

require "./utilities/database.php";

switch ($_GET["target"]) {
    case "products":
        echo json_encode(get_products());
        break;
    case "categories":
        echo json_encode(get_categories());
        break;
}
