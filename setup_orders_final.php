<?php
// Database connection with error handling
try {
    $conn = new mysqli('localhost', 'root', '', 'shop_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Read and execute the SQL file
try {
    $sql = file_get_contents(__DIR__ . '/assets/orders_setup.sql');
    if ($sql === false) {
        throw new Exception("Could not read the SQL file");
    }

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = true;
    $errors = [];

    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                $success = false;
                $errors[] = "Error in statement: " . substr($statement, 0, 100) . "...\nError message: " . $conn->error;
            }
        }
    }

    header('Content-Type: text/html; charset=utf-8');
    
    if ($success) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Setup Complete</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                .success { color: #28a745; border: 1px solid #28a745; padding: 15px; border-radius: 5px; }
                .next-steps { margin-top: 20px; background: #f8f9fa; padding: 15px; border-radius: 5px; }
                .next-steps h3 { margin-top: 0; }
                .link { color: #007bff; text-decoration: none; }
                .link:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class="success">
                <h2>✅ Setup Complete!</h2>
                <p>Order tables have been created successfully.</p>
            </div>
            <div class="next-steps">
                <h3>Next Steps:</h3>
                <ol>
                    <li>Go to <a href="../cart.php" class="link">your cart</a> to complete your purchase</li>
                    <li>After placing an order, admins can view it in the <a href="../admin.php" class="link">admin panel</a></li>
                </ol>
            </div>
        </body>
        </html>';
    } else {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Setup Error</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                .error { color: #dc3545; border: 1px solid #dc3545; padding: 15px; border-radius: 5px; }
                pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
            </style>
        </head>
        <body>
            <div class='error'>
                <h2>❌ Setup Failed</h2>
                <p>Could not create order tables. Details:</p>
            </div>
            <pre>" . htmlspecialchars(implode("\n", $errors)) . "</pre>
        </body>
        </html>";
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} finally {
    $conn->close();
}