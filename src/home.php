<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}
require_user_permissions();

function add_drop_list_rows($user_id) {
    global $mysqli;
    global $DB_TABLE_PREFIX;
    global $ROOT_URL;
    $drop_url = "/index.php?route=upload&key=";
    $get_url = "/index.php?route=get&key=";
    $stmt = $mysqli->prepare("SELECT * FROM " . $DB_TABLE_PREFIX .
        "drops WHERE owning_user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

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
        ?>
        <tr class='drop-list-row'>
            <td>
                <span title='status'><?php echo $display_status; ?></span>
            </td>
            <td>
                <span title='drop link'><a href='<?php echo $link_url; ?>' target='_blank'><?php echo $link_title; ?></a></span>
            </td>
            <td>
                <span title='created: <?php echo $create_date; ?>'><?php echo short_date($create_date); ?></span>
            </td>
            <td>
                <span title='dropped: <?php echo $drop_date; ?>'><?php echo $drop_date ? short_date($drop_date) : ""; ?></span></td>
            <td>
                <input type='text' title='sha256 hash: <?php echo $sha_256_hash; ?>' onclick='this.select()' value='<?php echo $sha_256_hash; ?>' readonly>
            </td>
            <td>
                <span title='source ip'><?php echo $source_ip; ?></span>
            </td>
            <td class='actions'>
                <?php if ($curl_link) { ?>
                    <span class='link' title="copy command to upload with cURL"
                        onclick="copyToClipboard(this.getAttribute('curlurl'))"
                        curlurl='<?php echo $curl_link; ?>'>➿
                    </span>
                <?php }
                if ($wget_link) { ?>
                    <span class='link' title="copy command to download with wget"
                        onclick="copyToClipboard(this.getAttribute('wgeturl'))"
                        wgeturl='<?php echo $wget_link; ?>'>🌐
                    </span>
                <?php } ?>
                <span title='delete'>
                    <a href='/index.php?route=delete&key=<?php echo $drop_key; ?>&csrf_token=<?php echo $_SESSION["csrf_token"]; ?>'>❌</a>
                </span>
            </td>
        </tr>
    <?php
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Drop</title>
    <link rel="stylesheet" href="style/common.css">
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
    <table class='drop-list'>
        <tr class='drop-list-heading'>
            <td>
                <span>status</span>
            </td>
            <td>
                <span>drop link</span>
            </td>
            <td>
                <span>created</span>
            </td>
            <td>
                <span>dropped</span>
            </td>
            <td>
                <span>sha256 hash</span>
            </td>
            <td>
                <span>source ip</span>
            </td>
            <td>
                <span>actions</span>
            </td>
        </tr>
        <?php echo add_drop_list_rows($_SESSION["user_id"]); ?>
        <tr>
            <td colspan='7' onclick="document.location='/index.php?route=new&csrf_token=<?php echo $_SESSION["csrf_token"]; ?>'">
                <div>+ new</div>
            </td>
        </tr>
    </table>
</div>

<div id="settings-wrap">
    <div id="settings-inner">
        <a href="index.php?route=logout&csrf_token=<?php echo $_SESSION["csrf_token"]; ?>">
            <div class="settings-row">Logout</div>
        </a>
    </div>
</div>

</body>
</html>
