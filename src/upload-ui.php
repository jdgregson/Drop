<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

$drop = null;
if (is_upload_authorized()) {
    $drop = get_drop($_REQUEST["key"]);
    $drop_key = $drop["drop_key"];
    $curl_link = "curl -F \"dropped_file=@/home/user/example.log\" \"" .
        $ROOT_URL . "/index.php?route=uploadfile&key=" . $drop_key . "\"";
} else {
    deny_access();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Drop</title>
    <link rel="stylesheet" href="style/common.css">
    <link rel="stylesheet" href="style/upload-ui.css">
</head>
<body>

<div id="page-wrap">
    <h1>Drop</h1>
    <span>Drop a file in the box below, or click "Choose file", and then click "Upload".</span>
    <form action="index.php?route=uploadfile&key=<?php echo $_REQUEST["key"]; ?>" method="post" enctype="multipart/form-data" id="drop-form">
        <input type="file" name="dropped_file" id="dropped_file">
        <input type="submit" value="Upload File" name="submit" id="file_submit">
    </form>

    <span>You can also use this cURL command to upload a file from the command line.</span>
    <input id="curl-url" value='<?php echo $curl_link; ?>' onclick="this.select()">

    <span>Or you can have the server download the file for you from a remote URL.</span>
    <input id="wget-url" value=''>
    <button onclick="handleWgetRedirect()" id="wget-button">Get File</button>
</div>

<script>
    function handleWgetRedirect() {
        let input = document.getElementById('wget-url');
        if (input && input.value) {
            let csrf_token = '<?php echo $_SESSION["csrf_token"]; ?>';
            let drop_key = '<?php echo $drop_key; ?>';
            let base_url = `index.php?route=wgetfile&url=${input.value}`;
            document.location =
                `${base_url}&key=${drop_key}&csrf_token=${csrf_token}`;
        }
    }
</script>
</body>
</html>