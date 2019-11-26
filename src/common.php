<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}


/** ERRORS ********************************************************************/

function generic_error($title = "Error", $message = "There was an error.") {
    echo <<<EOT
    <!DOCTYPE html>
    <html>
    <head>
        <title>$title</title>
        <link rel="stylesheet" href="style/common.css">
        <link rel="stylesheet" href="style/errors.css">
    </head>
    <body>
        <div class="fatal-error-wrap">
            <div class="fatal-error-title">$title</div>
            <div class="fatal-error-text">$message</div>
        </div>
    </body>
    </html>
EOT;
}


function fatal_error($title = "Fatal Error",
        $message = "There was a fatal error") {
    global $show_error_details;
    generic_error("⚠ $title", $message);
    die();
}


/** USER MANAGEMENT ***********************************************************/

function create_user($username, $password, $is_admin = false) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    $result = false;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO " . $DB_TABLE_PREFIX . "users (
        username,
        password_hash,
        is_admin
    ) VALUES (?, ?, ?)");
    $stmt->bind_param("sss",
        $username,
        $password_hash,
        $is_admin
    );
    $stmt->execute();
    if ($stmt->affected_rows === 1) {
        $result = true;
    }
    $stmt->close();
    return $result;
}

function get_user_by_id($user_id) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX .
        "users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return false;
    } else if ($result->num_rows > 1) {
        error_log("More than one user was found with user_id \"$user_id\"");
        return false;
    }
    return $result->fetch_assoc();
}

function get_user_by_name($username) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX .
        "users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return false;
    } else if ($result->num_rows > 1) {
        error_log("More than one user was found with username \"$username\"");
        return false;
    }
    return $result->fetch_assoc();
}

function get_users() {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX . "users;");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $users = [];
        while ($user = $result->fetch_assoc()) {
            array_push($users, $user);
        }
        return $users;
    }
    return false;
}

function user_exists_by_id($user_id) {
    if (get_user_by_id($user_id)) {
        return true;
    }
    return false;
}

function user_exists_by_name($username) {
    if (get_user_by_name($username)) {
        return true;
    }
    return false;
}

function user_is_admin($user_id) {
    $user = get_user($user_id);
    if ($user && $user["is_admin"] === "true") {
        return true;
    }
    return false;
}

function count_users() {
    $users = get_users();
    if ($users && is_array($users)) {
        return count($users);
    } else {
        return 0;
    }
}


/** DROP MANAGEMENT ***********************************************************/

function get_drop($key, $include_used = false) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    $drop = NULL;
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX .
        "drops WHERE drop_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("No drop was found with this key: $key");
        return false;
    } else if ($result->num_rows > 1) {
        error_log("More than one drop was found with this key: $key");
        return false;
    }
    while ($row = $result->fetch_assoc()) {
        $drop = $row;
        if ($row["drop_status"] !== "new" && !$include_used) {
            error_log("This drop has already been used: $key");
            return false;
        }
    }
    return $drop;
}

function is_upload_authorized() {
    if (isset($_REQUEST["key"])) {
        if (get_drop($_REQUEST["key"])) {
            return true;
        }
    }
    return false;
}


/** CRYPTO ********************************************************************/

function get_file_hash($path) {
    $hash = hash_file("sha256", $path);
    if ($hash) {
        return $hash;
    } else {
        return "[error]";
    }
}

function get_random_token($count = 16) {
    return bin2hex(random_bytes($count));
}


/** PERMISSIONS MANAGEMENT ****************************************************/

function deny_access($title = "Access denied",
        $message = "You are not authorized to access this resource.") {
    generic_error("⛔ $title", $message);
    die();
}

function require_user_permissions() {
    if (isset($_SESSION["username"])) {
        $stored_user = get_user_by_name($_SESSION["username"]);
        if (!$stored_user) {
            deny_access();
        }
    } else {
        deny_access();
    }
}

function require_admin_permissions() {
    if (isset($_SESSION["username"])) {
        $stored_user = get_user_by_name($_SESSION["username"]);
        if ($stored_user) {
            if ($stored_user["is_admin"] !== 1) {
                deny_access();
            }
        } else {
            deny_access();
        }
    } else {
        deny_access();
    }
}

function enable_csrf_protection() {
    global $last_csrf_token;
    if (isset($_POST["csrf_token"]) || isset($_REQUEST["csrf_token"])) {
        $received_token = isset($_POST["csrf_token"]) ? $_POST["csrf_token"] :
            $_REQUEST["csrf_token"];
        if ($received_token === $last_csrf_token) {
            return true;
        }
    }
    deny_access("Access denied",
        "Access denied due to a missing or incorrect CSRF token.");
}


/** MICS FUNCTIONS ************************************************************/

function set_route($route) {
    header("Location: /index.php?route=$route");
}

function get_client_ip() {
    $ip = "N/A";
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }
    return $ip;
}

function short_date($date) {
    return DateTime::createFromFormat("Y-m-d H:i:s", $date)->format('m/d/Y');
}


?>
