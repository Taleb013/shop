<?php
// logout.php
require 'config.php';

// 1) Clear session
$_SESSION = [];

// 2) Destroy cookie
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $p['path'], $p['domain'],
        $p['secure'], $p['httponly']
    );
}

// 3) End session
session_destroy();

// 4) Redirect home
header('Location: index.php');
exit();
