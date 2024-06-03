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

    // $orders = ["individual" => [], "company" => []];

    // $query = "SELECT menu_id, date, province_id, district_id, days, time, address FROM orders, order_requests WHERE request_id = order_requests.id AND email = ?";
    // $result = mysqli_prepare($connection, $query);
    // mysqli_stmt_bind_param($result, "s", $email);
    // mysqli_stmt_execute($result);
    // mysqli_stmt_bind_result($result, $menu_id, $date, $province_id, $district_id, $days, $time, $order_address);
    // while (mysqli_stmt_fetch($result)) {
    //     $orders["individual"][] = array(
    //         'menu_id' => $menu_id,
    //         'date' => $date,
    //         'province_id' => $province_id,
    //         'district_id' => $district_id,
    //         'days' => $days,
    //         'time' => $time,
    //         'address' => $order_address
    //     );
    // }
    // mysqli_stmt_close($result);

    // $query = "SELECT menu_id, date, province_id, district_id, days, time, address, allergy, disease, extra, company_name FROM company_orders, company_order_requests WHERE SUBSTRING(request_id, 2) = company_order_requests.id AND email = ?";
    // $result = mysqli_prepare($connection, $query);
    // mysqli_stmt_bind_param($result, "s", $email);
    // mysqli_stmt_execute($result);
    // mysqli_stmt_bind_result($result, $menu_id, $date, $province_id, $district_id, $days, $time, $order_address, $allergy, $disease, $extra, $company_name);
    // while (mysqli_stmt_fetch($result)) {
    //     $orders["company"][] = array(
    //         'menu_id' => $menu_id,
    //         'date' => $date,
    //         'province_id' => $province_id,
    //         'district_id' => $district_id,
    //         'days' => $days,
    //         'time' => $time,
    //         'address' => $order_address,
    //         'allergy' => $allergy,
    //         'disease' => $disease,
    //         'extra' => $extra,
    //         'company_name' => $company_name,
    //     );
    // }
    // mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($id)) {
        return ["id" => $id, "email" => $email];
    }
}