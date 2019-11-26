<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}
enable_csrf_protection();
require_user_permissions();


function delete_dir($dir) {
    $files = array_diff(scandir($dir), array(".", ".."));
    foreach ($files as $file) {
        if (is_dir("$dir/$file")) {
            delete_dir("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    return rmdir($dir);
}

if (isset($_REQUEST["key"])) {
    $drop = get_drop($_REQUEST["key"], true);
    if ($drop) {
        if ($drop["drop_status"] === "dropped") {
            delete_dir($drop["file_path"]);
        }
        $stmt = $mysqli->prepare("DELETE FROM " . $DB_TABLE_PREFIX .
            "drops WHERE drop_key = ?");
        $stmt->bind_param("s", $_REQUEST["key"]);
        $stmt->execute();
        $stmt->close();
        set_route("home");
    } else {
        deny_access();
    }
} else {
    deny_access();
}

?>
