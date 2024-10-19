<?php

require "configuration.php";

function connect()
{
    $connection = mysqli_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE);

    mysqli_set_charset($connection, "UTF8");

    if (mysqli_connect_errno() > 0)
        die("Hata");

    return $connection;
}

function register_email_control($email)
{
    $connection = connect();

    $query = "SELECT EXISTS(SELECT * FROM users WHERE email = ?)";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $email);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $exists);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return !$exists;
}

function register_user($password)
{
    $connection = connect();

    $query = "INSERT INTO users(email, salt, hash, cart, favorites) VALUES (?, ?, ?, '[]', '[]')";
    $result = mysqli_prepare($connection, $query);
    $salt = bin2hex(random_bytes(16));
    $hash = md5($password . $salt);
    mysqli_stmt_bind_param($result, "sss", $_SESSION["email"], $salt, $hash);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);
}

function login_user($email, $password)
{
    $connection = connect();

    $query = "SELECT id FROM users WHERE email = ? AND hash = MD5(CONCAT(?, salt))";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $email, $password);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($id)) return ["status" => "success", "session" => create_session($id)];
    else return ["status" => "user_invalid"];
}

function create_session($id)
{
    $connection = connect();

    $query = "INSERT INTO sessions(user, date, session) VALUES (?, ?, ?)";
    $result = mysqli_prepare($connection, $query);
    $session = bin2hex(random_bytes(32));
    $date = date("Y-m-d");
    mysqli_stmt_bind_param($result, "sss", $id, $date, $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $session;
}

function login_user_session($session)
{
    $connection = connect();

    $query = "SELECT email, cart, favorites FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $email, $cart, $favorites);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($email)) return ["status" => "success", "email" => $email, "cart" => $cart, "favorites" => $favorites];
    else return ["status" => "user_invalid"];
}

function get_products()
{
    $connection = connect();

    $query = "SELECT id, type, name, category, image, price, favorite, date, soundcloud, content, feature FROM products";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $type, $name, $category, $image, $price, $favorite, $date, $soundcloud, $content, $feature);

    while (mysqli_stmt_fetch($result)) {
        $products[] = array(
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'category' => $category,
            'image' => $image,
            'price' => $price,
            'favorite' => $favorite,
            'date' => $date,
            'soundcloud' => $soundcloud,
            'content' => $content,
            'feature' => $feature
        );
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $products;
}

function get_categories()
{
    $connection = connect();

    $query = "SELECT id, type, name, image FROM categories";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $type, $name, $image);

    while (mysqli_stmt_fetch($result)) {
        $categories[] = array(
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'image' => $image,
        );
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $categories;
}

function user_favorite($session, $id)
{
    $connection = connect();

    $query = "SELECT user, favorites FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user, $favorites);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (empty($favorites)) return ["status" => "error"];

    $favorites = json_decode($favorites);
    if (!in_array($id, $favorites)) $favorites[] = $id;
    $favorites = json_encode($favorites);

    $connection = connect();

    $query = "UPDATE users SET favorites = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $favorites, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}

function user_unfavorite($session, $id)
{
    $connection = connect();

    $query = "SELECT user, favorites FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user, $favorites);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (empty($favorites)) return ["status" => "error"];

    $favorites = json_decode($favorites);
    $index = array_search($id, $favorites);
    if ($index != false) {
        unset($favorites[$index]);
        $favorites = array_values($favorites);
    }
    $favorites = json_encode($favorites);

    $connection = connect();

    $query = "UPDATE users SET favorites = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $favorites, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}

function user_cart($session, $id)
{
    $connection = connect();

    $query = "SELECT user, cart FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user, $cart);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (empty($cart)) return ["status" => "error"];

    $cart = json_decode($cart);
    if (!in_array($id, $cart)) $cart[] = $id;
    $cart = json_encode($cart);

    $connection = connect();

    $query = "UPDATE users SET cart = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $cart, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}

function user_uncart($session, $id)
{
    $connection = connect();

    $query = "SELECT user, cart FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user, $cart);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (empty($cart)) return ["status" => "error"];

    $cart = json_decode($cart);
    $index = array_search($id, $cart);
    if ($index != false) {
        unset($cart[$index]);
        $cart = array_values($cart);
    }
    $cart = json_encode($cart);

    $connection = connect();

    $query = "UPDATE users SET cart = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $cart, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}
