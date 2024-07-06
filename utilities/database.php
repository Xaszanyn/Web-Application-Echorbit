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

    if (!empty($id)) {
        return ["id" => $id, "email" => $email];
    }
}

function get_musics()
{
    $connection = connect();

    $query = "SELECT id, name, picture, sample, standart_price, premium_price FROM musics";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $name, $picture, $sample, $standart_price, $premium_price);

    while (mysqli_stmt_fetch($result)) {
        $musics[] = array(
            'id' => $id,
            'name' => $name,
            'picture' => $picture,
            'sample' => $sample,
            'standart_price' => $standart_price,
            'premium_price' => $premium_price
        );
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $musics;
}

function get_sfxs()
{
    $connection = connect();

    $query = "SELECT id, name, picture, sample, standart_price, premium_price FROM sfxs";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $name, $picture, $sample, $standart_price, $premium_price);

    while (mysqli_stmt_fetch($result)) {
        $sfxs[] = array(
            'id' => $id,
            'name' => $name,
            'picture' => $picture,
            'sample' => $sample,
            'standart_price' => $standart_price,
            'premium_price' => $premium_price
        );
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $sfxs;
}