<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/database.php";

switch ($_GET["target"]) {
    case "products":
        echo json_encode(get_products());
        break;
    case "categories":
        echo json_encode(get_categories());
        break;
    case "featured-showcase":
        echo json_encode(get_featured_showcase());
}
