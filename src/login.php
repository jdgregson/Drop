<?php
if (!isset($DROP_INDEX)) {
    die("This page cannot be accessed directly.");
}

$submit_route = "index.php?route=login&submit";
$username_error = "";
$password_error = "";
$generic_error = "";

if (isset($_REQUEST["submit"])) {
    enable_csrf_protection();
    $username_errors = [];
    $password_errors = [];
    $generic_errors = [];
    if (!isset($_POST["username"]) || $_POST["username"] == "") {
        array_push($username_errors, "the username cannot be blank");
    }
    if (!isset($_POST["password"]) || $_POST["password"] == "") {
        array_push($password_errors, "the password cannot be blank");
    }
    if (isset($_POST["username"]) && strlen($_POST["username"]) > 64) {
        array_push($username_errors,
            "the username cannot exceed 64 characters");
    }
    if (isset($_POST["username"]) && !user_exists_by_name($_POST["username"])) {
        array_push($generic_errors,
            "username or password is incorrect");
    }

    if (count($username_errors) > 0 || count($password_errors) > 0 ||
            count($generic_errors) > 0) {
        if (count($username_errors) > 1) {
            $username_error = join("<br>", $username_errors);
        } else if (count($username_errors) === 1) {
            $username_error = $username_errors[0];
        }
        if (count($password_errors) > 1) {
            $password_error = join("<br>", $password_errors);
        } else if (count($password_errors) === 1) {
            $password_error = $password_errors[0];
        }
        if (count($generic_errors) > 1) {
            $generic_error = join("<br>", $generic_errors);
        } else if (count($generic_errors) === 1) {
            $generic_error = $generic_errors[0];
        }
    } else {
        $user = get_user_by_name($_POST["username"]);
        if ($user) {
            $supplied_password = $_POST["password"];
            $stored_hash = $user["password_hash"];
            if (password_verify($supplied_password, $stored_hash)) {
                $_SESSION["username"] = $user["username"];
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["is_admin"] = $user["is_admin"];
                set_route("home");
            } else {
                $generic_error = "username or password is incorrect";
            }
        } else {
            fatal_error("Fatal Error",
                "Validation passed, but could not access user? ¯\_(ツ)_/¯");
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0, height=device-height"/>
    <title>Drop Login</title>
    <link rel="stylesheet" href="style/common.css">
</head>
<body>
    <div class="form-outer-wrap">
        <h1>Drop</h1>
        <span>Log into your drop account below.</span>
        <div class="form-inner-wrap">
            <form action="<?php echo $submit_route; ?>" method="post">
                <?php if ($generic_error !== "") { ?>
                <span class="generic-error"><?php echo $generic_error; ?></span>
                <?php } ?>
                <input type="text" name="username">
                <label for="username">username</label>
                <span class="validation-error"><?php echo $username_error; ?></span>
                <br>
                <input type="password" name="password">
                <label for="password">password</label>
                <span class="validation-error"><?php echo $password_error; ?></span>
                <br><br>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                <input type="submit" name="submit" value="login">
            </form>
        </div>
    </div>
</body>
</html>