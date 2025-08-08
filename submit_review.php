<?php
// submit_review.php - basic secure receiver for review form
declare(strict_types=1);
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// simple redirect helper
function redirect_back(string $fallback = 'index.php') {
    $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
    header('Location: ' . $ref);
    exit;
}

$csrf = $_SESSION['csrf_token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_back();

$incoming = $_POST;
$token = (string)($incoming['csrf_token'] ?? '');
if (!hash_equals((string)$csrf, $token)) {
    // invalid token
    redirect_back();
}

$product_code = trim((string)($incoming['product_code'] ?? ''));
$rating = intval($incoming['rating'] ?? 0);
$user_name = trim((string)($incoming['user_name'] ?? ''));
$comment = trim((string)($incoming['comment'] ?? ''));

if ($product_code === '' || $rating < 1 || $rating > 5) {
    redirect_back();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');

    // insert review (prepared)
    $stmt = $conn->prepare("INSERT INTO reviews (product_code, user_name, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $user_name_db = $user_name === '' ? 'Anonymous' : mb_substr($user_name, 0, 50);
    $comment_db = mb_substr($comment, 0, 800);
    $stmt->bind_param('ssis', $product_code, $user_name_db, $rating, $comment_db);
    $stmt->execute();
} catch (Throwable $e) {
    // in production: log $e->getMessage() to file; show friendly message
    redirect_back();
}

redirect_back();
