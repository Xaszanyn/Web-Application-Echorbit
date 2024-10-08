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

    $query = "INSERT INTO users(email, salt, hash) VALUES (?, ?, ?)";
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

    $query = "SELECT id, email FROM users WHERE email = ? AND hash = MD5(CONCAT(?, salt))";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $email, $password);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $email);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($id)) return ["status" => "success", "id" => $id, "email" => $email, "session" => create_session($id)];
    else return ["status" => "user_invalid"];
}

function create_session($id)
{
    $connection = connect();

    $query = "INSERT INTO sessions(id, date, session) VALUES (?, ?, ?)";
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

    $query = "SELECT users.id, email FROM users, sessions WHERE session = ? AND date < DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $email);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($id)) return ["status" => "success", "id" => $id, "email" => $email];
    else return ["status" => "user_invalid"];
}

function get_products()
{
    $connection = connect();

    $query = "SELECT id, type, name, category, image, price, premium_price, favorite, date, soundcloud, content, feature FROM products";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $type, $name, $category, $image, $price, $premium_price, $favorite, $date, $soundcloud, $content, $feature);

    while (mysqli_stmt_fetch($result)) {
        $products[] = array(

            'id' => $id,
            'type' => $type,
            'name' => $name,
            'category' => $category,
            'image' => $image,
            'price' => $price,
            'premium_price' => $premium_price,
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
