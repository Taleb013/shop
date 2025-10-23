<?php
// database_mysqli.php
// A robust, centralized mysqli database connection.

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "shop_db";

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    $mysqli->set_charset('utf8mb4');
} catch (Exception $e) {
    // In a production environment, you would log this error and show a generic message.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("Service unavailable. Please try again later.");
}
