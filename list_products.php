<?php
// list_products.php - show first 100 products
error_reporting(E_ALL);
ini_set('display_errors', 1);
$host = '127.0.0.1'; $db='shop_db'; $user='root'; $pass='';
$mysqli = new mysqli($host,$user,$pass,$db);
if ($mysqli->connect_errno) { echo 'DB error: '.htmlspecialchars($mysqli->connect_error); exit; }
$mysqli->set_charset('utf8mb4');
$res = $mysqli->query('SELECT id, code, name FROM product LIMIT 100');
if (!$res) { echo 'Query failed: '.htmlspecialchars($mysqli->error); exit; }
$rows = $res->fetch_all(MYSQLI_ASSOC);
if (empty($rows)) { echo '<p>No products found in `product` table.</p>'; exit; }
echo '<table border=1 cellpadding=6><tr><th>id</th><th>code</th><th>name</th></tr>'; foreach ($rows as $r) { echo '<tr><td>'.htmlspecialchars($r['id']).'</td><td>'.htmlspecialchars($r['code']).'</td><td>'.htmlspecialchars($r['name']).'</td></tr>'; } echo '</table>';
$mysqli->close();
?>