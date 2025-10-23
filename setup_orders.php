<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'shop_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/assets/fix_orders_tables.sql');

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$errors = [];

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            $success = false;
            $errors[] = $conn->error;
        }
    }
}

header('Content-Type: text/plain');

if ($success) {
    echo "Success: Order tables have been created successfully!\n";
    echo "You can now go back and try placing your order again.";
} else {
    echo "Error: Could not create order tables.\n";
    echo "Details:\n";
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
}

$conn->close();