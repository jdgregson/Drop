<?php
session_start();
$DROP_INDEX = true;
require "config.php";
require "common.php";

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = get_random_token(32);
    header("Location: " . $_SERVER["REQUEST_URI"]);
}
$last_csrf_token = $_SESSION["csrf_token"];
$_SESSION["csrf_token"] = get_random_token(32);

$mysqli = new mysqli($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB_DBNAME);
$mysqli->set_charset("utf8mb4");
if ($mysqli->connect_error) {
    $title = "Fatal Error: Unable to connect to MySQL.";
    $message = "Error " . mysqli_connect_errno() . ": " . mysqli_connect_error();
    fatal_error($title, $message);
}

if (isset($_REQUEST["route"])) {
    if ($_REQUEST["route"] === "setup") {
        require "setup.php";
    } else if ($_REQUEST["route"] === "login") {
        require "login.php";
    } else if ($_REQUEST["route"] === "logout") {
        require "logout.php";
    } else if ($_REQUEST["route"] === "home") {
        require "home.php";
    } else if ($_REQUEST["route"] === "upload") {
        require "upload-ui.php";
    } else if ($_REQUEST["route"] === "uploadfile") {
        require "upload.php";
    } else if ($_REQUEST["route"] === "wgetfile") {
        require "wget.php";
    } else if ($_REQUEST["route"] === "get") {
        require "get.php";
    } else if ($_REQUEST["route"] === "delete") {
        require "delete.php";
    } else if ($_REQUEST["route"] === "success") {
        require "success.php";
    } else if ($_REQUEST["route"] === "new") {
        require "new.php";
    } else {
        set_route("login");
    }
} else {
    set_route("login");
}

mysqli_close($mysqli);

?>
