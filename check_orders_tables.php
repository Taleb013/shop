<?php
// Diagnostic script to check orders tables
$conn = new mysqli('localhost', 'root', '', 'shop_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database Table Check:\n\n";

// Check orders table
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "✓ 'orders' table exists\n";
    $result = $conn->query("SHOW CREATE TABLE orders");
    $row = $result->fetch_assoc();
    echo "Structure:\n" . $row['Create Table'] . "\n\n";
} else {
    echo "✗ 'orders' table does not exist!\n\n";
}

// Check order_items table
$result = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($result->num_rows > 0) {
    echo "✓ 'order_items' table exists\n";
    $result = $conn->query("SHOW CREATE TABLE order_items");
    $row = $result->fetch_assoc();
    echo "Structure:\n" . $row['Create Table'] . "\n\n";
} else {
    echo "✗ 'order_items' table does not exist!\n\n";
}

$conn->close();
?>