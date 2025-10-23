<?php
// Safe runner for update_orders_safe.sql
$conn = new mysqli('localhost', 'root', '', 'shop_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$sql = file_get_contents(__DIR__ . '/assets/update_orders_safe.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));
$errors = [];
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    if (!$conn->query($stmt)) {
        $errors[] = $conn->error . " (while executing: " . substr($stmt,0,120) . "...)";
    }
}
header('Content-Type: text/plain');
if (empty($errors)) {
    echo "Success: safe orders tables created/updated.\n";
} else {
    echo "Errors encountered:\n" . implode("\n", $errors);
}
$conn->close();
