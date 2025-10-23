<?php
// cart_debug.php — debug helper. Save in shop root and open in browser while logged in.
declare(strict_types=1);
session_start();

$host = '127.0.0.1';
$db   = 'shop_db';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("DB connect error: " . htmlspecialchars($mysqli->connect_error));
}
$mysqli->set_charset('utf8mb4');

echo "<h2>Session</h2><pre>" . htmlspecialchars(json_encode($_SESSION, JSON_PRETTY_PRINT)) . "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:crimson'><strong>No session user_id set — please login first.</strong></p>";
    exit;
}

// Show resolved user_id logic (same method as cart.php)
$session_user = $_SESSION['user_id'];
$resolved = null;
if (is_int($session_user) || (is_string($session_user) && ctype_digit($session_user))) {
    $resolved = (int)$session_user;
} else {
    $maybe_email = trim((string)$session_user);
    if ($maybe_email !== '') {
        $stmt = $mysqli->prepare("SELECT id, email, full_name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $maybe_email);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($row = $r->fetch_assoc()) $resolved = (int)$row['id'];
        $stmt->close();
    }
}
echo "<h2>Resolved user_id</h2><pre>" . htmlspecialchars((string)$resolved) . "</pre>";

echo "<h2>Cart rows (raw)</h2>";
$stmt = $mysqli->prepare("SELECT * FROM cart WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $resolved);
$stmt->execute();
$res = $stmt->get_result();
echo "<table border='1' cellpadding='6' cellspacing='0'><tr><th>cart_id</th><th>user_id</th><th>product_id</th><th>quantity</th><th>created_at</th></tr>";
while ($r = $res->fetch_assoc()) {
    echo "<tr><td>{$r['cart_id']}</td><td>{$r['user_id']}</td><td>{$r['product_id']}</td><td>{$r['quantity']}</td><td>{$r['created_at']}</td></tr>";
}
echo "</table>";
$stmt->close();

echo "<h2>Cart JOIN product</h2>";
$stmt = $mysqli->prepare("
  SELECT c.cart_id, c.quantity, c.created_at, p.id AS pid, p.code, p.name, p.price, p.image
  FROM cart c
  LEFT JOIN product p ON c.product_id = p.id
  WHERE c.user_id = ? ORDER BY c.created_at DESC
");
$stmt->bind_param('i', $resolved);
$stmt->execute();
$res = $stmt->get_result();
echo "<table border='1' cellpadding='6' cellspacing='0'><tr><th>cart_id</th><th>product_id</th><th>code</th><th>name</th><th>qty</th><th>created_at</th></tr>";
while ($r = $res->fetch_assoc()) {
    $code = htmlspecialchars((string)$r['code']);
    $name = htmlspecialchars((string)$r['name']);
    echo "<tr><td>{$r['cart_id']}</td><td>{$r['pid']}</td><td>{$code}</td><td>{$name}</td><td>{$r['quantity']}</td><td>{$r['created_at']}</td></tr>";
}
echo "</table>";
$stmt->close();

// Optional: product lookup helper (pass ?code=XYZ or ?id=123)
if (isset($_GET['code']) || isset($_GET['id'])) {
    echo "<h2>Product lookup</h2>";
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $s = $mysqli->prepare("SELECT id, code, name FROM product WHERE id = ? LIMIT 1");
        $s->bind_param('i', $id);
    } else {
        $code = $_GET['code'];
        $s = $mysqli->prepare("SELECT id, code, name FROM product WHERE code = ? LIMIT 1");
        $s->bind_param('s', $code);
    }
    $s->execute();
    $rr = $s->get_result();
    if ($row = $rr->fetch_assoc()) {
        echo "<pre>" . htmlspecialchars(json_encode($row, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p style='color:crimson'>No product found for that lookup.</p>";
    }
    $s->close();
}

// Optional: insert a test row if you pass ?test_insert=1&id=PRODUCT_ID
if (isset($_GET['test_insert']) && isset($_GET['id'])) {
    $pid = intval($_GET['id']);
    $ins = $mysqli->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $ins->bind_param('ii', $resolved, $pid);
    if ($ins->execute()) {
        echo "<p style='color:green'>Inserted test row for product_id={$pid} user_id={$resolved}</p>";
    } else {
        echo "<p style='color:crimson'>Insert failed: " . htmlspecialchars($mysqli->error) . "</p>";
    }
    $ins->close();
}

echo "<hr><p>After testing, remove this file for security.</p>";
$mysqli->close();
