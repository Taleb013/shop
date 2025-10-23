<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'shop_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute the SQL file
$sql = file_get_contents(__DIR__ . '/assets/update_orders_tables_v3.sql');

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$errors = [];

// Execute each statement separately
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            $success = false;
            $errors[] = "Error in statement: " . substr($statement, 0, 100) . "...\nError message: " . $conn->error;
        }
    }
}

header('Content-Type: text/plain');

if ($success) {
    echo "Success: Order tables have been updated successfully!\n";
    echo "You can now go back and try placing your order again.";
} else {
    echo "Error: Could not update order tables.\n";
    echo "Details:\n";
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
}

$conn->close();