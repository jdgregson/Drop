<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

if (is_upload_authorized()) {
    enable_csrf_protection();
    $drop = get_drop($_REQUEST["key"]);
    if ($drop && $drop["status"] !== "dropped") {
        if (isset($_REQUEST["url"])) {
            $url = $_REQUEST["url"];
            $content = file_get_contents($url);
            if ($content) {
                $new_dir = get_random_token(32);
                $full_target_dir = "$UPLOAD_DIR/$new_dir";
                $file_name = basename($url);
                $file_name = (preg_split("/\?|#/", $file_name))[0];
                if (!file_exists("$full_target_dir")) {
                    mkdir("$full_target_dir");
                    $target_file = "$full_target_dir/$file_name";
                    if (file_put_contents($target_file, $content) !== false) {
                        $now = date('Y-m-d H:i:s');
                        $status = "dropped";
                        $sha_256_hash = get_file_hash($target_file);
                        $client_ip = get_client_ip();
                        $stmt = $mysqli->prepare("UPDATE " . $DB_TABLE_PREFIX .
                            "drops SET
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
                        fatal_error("Error saving file.", "There was an error" .
                            " when saving the file.");
                    }
                } else {
                    fatal_error("Directory already exists", "The directory " .
                        "already exists (this should never happen).");
                }
            } else {
                fatal_error("No content",
                    "There was no content from the server.");
            }
        } else {
            fatal_error("Nothing to get",
                "No URL was provided for me to download.");
        }
    } else {
        fatal_error();
    }
}

?>
