<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}
require_user_permissions();

if (isset($_REQUEST["key"])) {
    $drop = get_drop($_REQUEST["key"], true);
    if ($drop) {
        $file = $drop["file_path"] . "/" . $drop["file_name"];
        $filename = $drop["file_name"];
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/octet-stream");
        ob_clean();
        readfile($file);
    } else {
        deny_access();
    }
} else {
    deny_access();
}

?>
