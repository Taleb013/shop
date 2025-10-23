<?php
// dev_test.php — quick diagnostics for cart/product data
// Usage: open in browser while XAMPP running: http://localhost/shop-master/dev_test.php?user=1

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$db = 'shop_db';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "DB connect error: " . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

$uid = isset($_GET['user']) ? intval($_GET['user']) : 1;

echo "<h2>dev_test: cart diagnostics for user_id={$uid}</h2>";

// Show cart rows
$stmt = $mysqli->prepare('SELECT * FROM cart WHERE user_id = ?');
if ($stmt === false) {
    echo "Failed to prepare cart query: " . htmlspecialchars($mysqli->error);
    exit;
}
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$cartRows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartRows)) {
    echo "<p><strong>No cart rows found for user_id={$uid}.</strong></p>";
} else {
    echo "<h3>Cart rows</h3>";
    echo "<table border=1 cellpadding=6><tr>";
    foreach (array_keys($cartRows[0]) as $h) echo "<th>" . htmlspecialchars($h) . "</th>";
    echo "</tr>";
    foreach ($cartRows as $r) {
        echo "<tr>";
        foreach ($r as $v) echo "<td>" . htmlspecialchars((string)$v) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// If cart rows exist, fetch product info
if (!empty($cartRows)) {
    echo "<h3>Products referenced by cart</h3>";
    echo "<table border=1 cellpadding=6><tr><th>product_id</th><th>found</th><th>code</th><th>name</th></tr>";
    $ps = $mysqli->prepare('SELECT id, code, name FROM product WHERE id = ? LIMIT 1');
    foreach ($cartRows as $r) {
        $pid = intval($r['product_id']);
        $ps->bind_param('i', $pid);
        $ps->execute();
        $res = $ps->get_result();
        $found = $res && $res->num_rows > 0;
        $row = $found ? $res->fetch_assoc() : null;
        echo "<tr><td>".htmlspecialchars($pid)."</td><td>".($found?"yes":"no")."</td><td>".htmlspecialchars($row['code'] ?? '-') ."</td><td>".htmlspecialchars($row['name'] ?? '-') ."</td></tr>";
    }
    $ps->close();
    echo "</table>";
}

// Helpful SQL suggestions
echo "<h3>Quick SQL checks (run in phpMyAdmin)</h3>";
echo "<pre>SELECT * FROM cart WHERE user_id = {$uid};\nSELECT id, code, name FROM product WHERE id IN (SELECT product_id FROM cart WHERE user_id = {$uid});\nSHOW CREATE TABLE cart;\nSHOW CREATE TABLE product;</pre>";

$mysqli->close();

?>