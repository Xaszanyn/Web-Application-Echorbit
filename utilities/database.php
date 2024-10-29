<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/services/stripe/init.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/services/utilities/configuration.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET);

function add_string_list($list, $item)
{
    $list = json_decode($list);

    if (preg_match("/\[.*\]/", $item)) {
        $item = json_decode($item);
        for ($index = 0; $index < count($item); $index++)
            if (!in_array($item[$index], $list)) $list[] = $item[$index];
    } else if (!in_array($item, $list)) $list[] = $item;

    return json_encode($list);
}

function remove_string_list($list, $item)
{
    $list = json_decode($list);
    $index = array_search($item, $list);

    if ($index !== false)  unset($list[$index]);

    return json_encode(array_values($list));
}

function intersection_string_list($list, $items)
{
    $list = json_decode($list);
    $items = json_decode($items);

    return !empty(array_intersect($list, $items));
}

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

function register_user($password, $guest)
{
    $customer = \Stripe\Customer::create([
        'email' => $_SESSION["email"],
    ]);

    $cart = $favorites = [];

    if ($guest != "-") {
        $guest = json_decode($guest);
        $cart = $guest->cart;
        $favorites = $guest->favorites;
    }

    $cart = json_encode($cart);
    $favorites = json_encode($favorites);

    $connection = connect();

    $query = "INSERT INTO users(customer, name, email, phone, country, salt, hash, inventory, cart, favorites) VALUES (?, '—', ?, '—', '—', ?, ?, '[]', ?, ?)";
    $result = mysqli_prepare($connection, $query);
    $salt = bin2hex(random_bytes(16));
    $hash = md5($password . $salt);
    mysqli_stmt_bind_param($result, "ssssss", $customer->id, $_SESSION["email"], $salt, $hash, $cart, $favorites);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    $connection = connect();

    $query = "SELECT id FROM users WHERE email = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $_SESSION["email"]);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return create_session($id);
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

    $query = "SELECT name, email, phone, country, inventory, cart, favorites FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $name, $email, $phone, $country, $inventory, $cart, $favorites);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!empty($email)) return ["status" => "success", "name" => $name, "email" => $email, "phone" => $phone, "country" => $country, "inventory" => $inventory, "cart" => $cart, "favorites" => $favorites];
    else return ["status" => "user_invalid"];
}

function get_products()
{
    $data = (\Stripe\Product::all())->data;

    $connection = connect();

    $query = "SELECT id, type, stripe, category, image, favorite, date, soundcloud, content, feature FROM products";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $type, $stripe, $category, $image, $favorite, $date, $soundcloud, $content, $feature);

    while (mysqli_stmt_fetch($result)) {
        for ($index = 0; $index < count($data); $index++) {
            $product = $data[$index];
            if ($stripe == $product->id) {
                $name = $product->name;
                $price = (\Stripe\Price::retrieve($product->default_price))->unit_amount / 100;
                break;
            }
        }

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

function get_featured_showcase()
{
    $data = (\Stripe\Product::all())->data;

    $connection = connect();

    $query = "SELECT id, stripe, image FROM products WHERE id IN (1, 2, 4, 5)";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $stripe, $image);

    while (mysqli_stmt_fetch($result)) {
        for ($index = 0; $index < count($data); $index++) {
            $product = $data[$index];
            if ($stripe == $product->id) {
                $name = $product->name;
                break;
            }
        }

        $products[] = array(
            'id' => $id,
            'name' => $name,
            'image' => $image,
        );
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    return $products;
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

    $favorites = add_string_list($favorites, $id);

    $connection = connect();

    $query = "UPDATE users SET favorites = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $favorites, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    $connection = connect();

    $query = "UPDATE products SET favorite = favorite + 1 WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $id);
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

    $favorites = remove_string_list($favorites, $id);

    $connection = connect();

    $query = "UPDATE users SET favorites = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $favorites, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    $connection = connect();

    $query = "UPDATE products SET favorite = favorite - 1 WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $id);
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

    $cart = add_string_list($cart, $id);

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

    $cart = remove_string_list($cart, $id);

    $connection = connect();

    $query = "UPDATE users SET cart = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $cart, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}

function create_order_request($session)
{
    $connection = connect();

    $query = "SELECT user, inventory, cart FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user, $inventory, $cart);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    if (!isset($user) || $cart == "[]") return ["status" => "error"];
    if (intersection_string_list($inventory, $cart)) return ["status" => "intersection"];

    $cart_query = str_replace(['[', ']'], ['(', ')'], $cart);

    $data = (\Stripe\Product::all())->data;

    $line_items = [];

    $connection = connect();

    $query = "SELECT stripe FROM products WHERE id IN $cart_query";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $stripe);

    while (mysqli_stmt_fetch($result)) {
        for ($index = 0; $index < count($data); $index++) {
            $product = $data[$index];
            if ($stripe == $product->id) {
                $line_items[] = [
                    'price' => $product->default_price,
                    'quantity' => 1,
                ];
                break;
            }
        }
    }

    mysqli_stmt_close($result);

    mysqli_close($connection);

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'https://echorbitaudio.com/store?success',
        'cancel_url' => 'https://echorbitaudio.com/store?error',
        'customer_creation' => 'always'
    ]);

    $connection = connect();

    $query = "INSERT INTO orders(user, cart, stripe, date) VALUES (?, ?, ?, NOW())";
    $result = mysqli_prepare($connection, $query);
    $id = $session->id;
    mysqli_stmt_bind_param($result, "sss", $user, $cart, $id);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success", "stripe" => $session->url];
}

function complete_order($stripe)
{
    $connection = connect();

    $query = "SELECT id, user, cart FROM orders WHERE stripe = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $stripe);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $id, $user, $cart);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    $query = "UPDATE orders SET complete = 1 WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $id);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    $query = "SELECT inventory FROM users WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $inventory);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    $inventory = add_string_list($inventory, $cart);

    $query = "UPDATE users SET cart = '[]', inventory = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ss", $inventory, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);
}

function user_information($session, $name, $phone, $country)
{
    $connection = connect();

    $query = "SELECT user FROM users, sessions WHERE session = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND user = users.id";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $session);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $user);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    $query = "SELECT customer FROM users WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "s", $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_bind_result($result, $customer);
    mysqli_stmt_fetch($result);
    mysqli_stmt_close($result);

    \Stripe\Customer::update($customer, [
        'name' => $name,
        'phone' => $phone,
        'address' =>  [
            // 'line1' => $customer->address->line1,
            // 'city' => $customer->address->city,
            // 'state' => $customer->address->state,
            // 'postal_code' => $customer->address->postal_code,
            'country' => $country,
        ],
        'locale' => 'en'
    ]);

    $query = "UPDATE users SET name = ?, phone = ?, country = ? WHERE id = ?";
    $result = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($result, "ssss", $name, $phone, $country, $user);
    mysqli_stmt_execute($result);
    mysqli_stmt_close($result);

    mysqli_close($connection);

    return ["status" => "success"];
}
