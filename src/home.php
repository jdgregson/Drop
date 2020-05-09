<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}
require_user_permissions();

function get_drop_list_json($user_id) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    global $ROOT_URL;
    $drop_url = "index.php?route=upload&key=";
    $get_url = "index.php?route=get&key=";
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX .
        "drops WHERE owning_user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $i = 0;
    $json = "{";

    while ($row = $result->fetch_assoc()) {
        $drop_key = $row['drop_key'];
        $create_date = $row['create_date'];
        $drop_date = $row['drop_date'];
        $drop_status = $row['drop_status'];
        $drop_note = $row['drop_note'];
        $file_name = $row['file_name'];
        $file_path = $row['file_path'];
        $sha_256_hash = $row['sha_256_hash'];
        $curl_link = "curl -F \"dropped_file=@/home/user/example.log\" \"" .
            $ROOT_URL . "/index.php?route=uploadfile&key=" . $drop_key . "\"";
        $wget_link = "";
        $source_ip = $row['source_ip'];
        $link_url = "$drop_url$drop_key";
        $link_title = $drop_key;
        $display_status = "⏱";
        if ($drop_status === "dropped") {
            $link_url = "$get_url$drop_key";
            $link_title = htmlspecialchars($file_name, ENT_HTML5);
            $display_status = "✔";
            $curl_link = "";
            $wget_link = "wget \"$ROOT_URL/$file_path/$file_name\"";
        }

        $json .= "$i: {" .
            '"dropKey": "'.addslashes($drop_key).'",' .
            '"createDate": "'.addslashes($create_date).'",' .
            '"dropDate": "'.addslashes($drop_date).'",' .
            '"dropStatus": "'.addslashes($drop_status).'",' .
            '"dropNote": "'.addslashes($drop_note).'",' .
            '"fileName": "'.addslashes($file_name).'",' .
            '"filePath": "'.addslashes($file_path).'",' .
            '"sha256hash": "'.addslashes($sha_256_hash).'",' .
            '"curlLink": "'.addslashes($curl_link).'",' .
            '"wgetLink": "'.addslashes($wget_link).'",' .
            '"sourceIp": "'.addslashes($source_ip).'"' .
        "},";
        $i++;
    }
    $stmt->close();
    $json .= "}";
    return $json;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0, height=device-height"/>
    <title>Drop</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/common.css">
    <script>window.csrf_token = '<?php echo $_SESSION["csrf_token"]; ?>';</script>
    <script src="js/home.js"></script>
</head>
<body>

<div id="header">
    <div id="header-inner-wrap">
        <div id="title">drop</div>
        <div id="settings-link">
            <a href="javascript:toggleSettings()">⚙</a>
        </div>
    </div>
</div>

<div id="list-wrap">
</div>
<div id="new-drop-button" class="card card-1">
    <a href="index.php?route=new&csrf_token=<?php echo $_SESSION["csrf_token"]; ?>">+</a>
</div>

<div id="settings-wrap">
    <div id="settings-inner">
        <a href="index.php?route=logout&csrf_token=<?php echo $_SESSION["csrf_token"]; ?>">
            <div class="settings-row">Logout</div>
        </a>
    </div>
</div>

<script>var dropListJSON = <?php echo get_drop_list_json($_SESSION["user_id"]); ?></script>
</body>
</html>
