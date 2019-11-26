<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

if (is_upload_authorized()) {
    if (!isset($_FILES["dropped_file"])) {
        echo "test";
        deny_access();
    }
    $file_name = basename($_FILES["dropped_file"]["name"]);
    $new_dir = get_random_token(32);
    $full_target_dir = "$UPLOAD_DIR/$new_dir";
    if (file_exists("$full_target_dir")) {
        error_log("Folder already exists: $full_target_dir");
        fatal_error();
    }
    mkdir("$full_target_dir");
    $target_file = "$full_target_dir/$file_name";

    $move_successful = move_uploaded_file($_FILES["dropped_file"]["tmp_name"],
        $target_file);
    $now = date('Y-m-d H:i:s');
    $status = "dropped";
    $sha_256_hash = get_file_hash($target_file);
    $client_ip = get_client_ip();
    if ($move_successful) {
        $stmt = $mysqli->prepare("UPDATE " . $DB_TABLE_PREFIX . "drops SET
            drop_date = ?,
            drop_status = ?,
            file_name = ?,
            file_path = ?,
            sha_256_hash = ?,
            source_ip = ?  WHERE drop_key = ?");
        $stmt->bind_param("sssssss",
            $now,
            $status,
            $file_name,
            $full_target_dir,
            $sha_256_hash,
            $client_ip,
            $_REQUEST["key"]);
        $stmt->execute();
        $stmt->close();
        set_route("success");
    } else {
        error_log("Failed to upload file: $target_dir/$new_dir/$file_name");
        fatal_error();
    }
} else {
    deny_access();
}
?>