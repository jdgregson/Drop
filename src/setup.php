<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

if (count_users() > 0) {
    enable_csrf_protection();
    require_admin_permissions();
}

$submit_route = "index.php?route=setup&submit";
$success_route = "index.php?route=login";
$username_default = isset($_POST["username"]) ? $_POST["username"] : "";
$username_error = "";
$password1_default = isset($_POST["password1"]) ? $_POST["password1"] : "";
$password1_error = "";
$password2_default = isset($_POST["password2"]) ? $_POST["password2"] : "";
$password2_error = "";

if (isset($_REQUEST["submit"])) {
    $username_errors = [];
    $password1_errors = [];
    $password2_errors = [];
    if (!isset($_POST["username"]) || $_POST["username"] == "") {
        array_push($username_errors, "the username cannot be blank");
    }
    if (!isset($_POST["password1"]) || $_POST["password1"] == "") {
        array_push($password1_errors, "the password cannot be blank");
    }
    if (!isset($_POST["password2"]) || $_POST["password2"] == "") {
        array_push($password2_errors,
            "the password confirmation cannot be blank");
    }
    if (isset($_POST["password1"]) && isset($_POST["password2"]) &&
            $_POST["password1"] !== $_POST["password2"]) {
        array_push($password1_errors, "the passwords do not match");
        array_push($password2_errors, "the passwords do not match");
    }
    if (isset($_POST["username"]) && strpos($_POST["username"], "\"") > -1) {
        array_push($username_errors, "the username cannot contain: \"");
        $username_default = "";
    }
    if (isset($_POST["password1"]) && strpos($_POST["password1"], "\"") > -1) {
        array_push($password1_errors, "the password cannot contain: \"");
        $password1_default = "";
    }
    if (isset($_POST["password2"]) && strpos($_POST["password2"], "\"") > -1) {
        array_push($password2_errors,
            "the password confirmation cannot contain: \"");
        $password2_default = "";
    }
    if (isset($_POST["username"]) && strlen($_POST["username"]) > 64) {
        array_push($username_errors,
            "the username cannot exceed 64 characters");
    }
    if (isset($_POST["username"]) && user_exists_by_name($_POST["username"])) {
        array_push($username_errors,
            "a user with this username already exists");
    }

    if (count($username_errors) > 0 || count($password1_errors) > 0 ||
            count($password2_errors) > 0) {
        if (count($username_errors) > 1) {
            $username_error = join("<br>", $username_errors);
        } else if (count($username_errors) === 1) {
            $username_error = $username_errors[0];
        }
        if (count($password1_errors) > 1) {
            $password1_error = join("<br>", $password1_errors);
        } else if (count($password1_errors) === 1) {
            $password1_error = $password1_errors[0];
        }
        if (count($password2_errors) > 1) {
            $password2_error = join("<br>", $password2_errors);
        } else if (count($password2_errors) === 1) {
            $password2_error = $password2_errors[0];
        }
    } else {
        $initial_admin = count_users() === 0 ? true : false;
        $result = create_user($_POST["username"], $_POST["password1"],
            $initial_admin);
        if ($result) {
            set_route("login");
        } else {
            fatal_error("Account errro",
                "There was an error setting up your account.");
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Drop Setup</title>
    <link rel="stylesheet" href="style/common.css">
</head>
<body>
    <div class="form-outer-wrap">
        <h1>Drop Setup</h1>
        <span>Set up your drop account below.</span>
        <div class="form-inner-wrap">
            <form action="<?php echo $submit_route; ?>" method="post">
                <input type="text" name="username" value="<?php echo $username_default; ?>">
                <label for="username">username</label>
                <span class="validation-error"><?php echo $username_error; ?></span>
                <br>
                <input type="password" name="password1" value="<?php echo $password1_default; ?>">
                <label for="password1">password</label>
                <span class="validation-error"><?php echo $password1_error; ?></span>
                <br>
                <input type="password" name="password2" value="<?php echo $password2_default; ?>">
                <label for="password2">confirm password</label>
                <span class="validation-error"><?php echo $password2_error; ?></span>
                <br>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                <input type="submit" name="submit">
            </form>
        </div>
    </div>
</body>
</html>