<?php
enable_csrf_protection();
session_destroy();
set_route("login");
?>
