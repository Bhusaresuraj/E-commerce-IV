<?php
declare(strict_types=1);

session_start();

unset(
    $_SESSION['user_id'],
    $_SESSION['user_name'],
    $_SESSION['user_email'],
    $_SESSION['user_avatar'],
    $_SESSION['user_auth_provider']
);

header('Location: google-login.php');
exit;

