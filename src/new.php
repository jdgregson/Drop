<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

enable_csrf_protection();
require_user_permissions();

if (isset($_SESSION["user_id"])) {
    $owning_user_id = $_SESSION["user_id"];
} else {
    fatal_error();
}

$drop_key = get_random_token();
$drop_status = "new";
$drop_note = "";
$file_name = "";
$file_path = "";
$sha_256_hash = "";
$source_ip = "";

$stmt = $mysqli->prepare("INSERT INTO " . $DB_TABLE_PREFIX . "drops (
    owning_user_id,
    drop_key,
    drop_status,
    drop_note,
    file_name,
    file_path,
    sha_256_hash,
    source_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("isssssss",
    $owning_user_id,
    $drop_key,
    $drop_status,
    $drop_note,
    $file_name,
    $file_path,
    $sha_256_hash,
    $source_ip);
$stmt->execute();
$stmt->close();

header("Location: /index.php?route=home");
?>
