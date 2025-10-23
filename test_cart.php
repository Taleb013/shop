<?php
// test_cart.php - Diagnostic script to check cart functionality
session_start();

echo "Cart Diagnostic Results:\n\n";

// 1. Check database connection
$conn = new mysqli('localhost', 'root', '', 'shop_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "✓ Database connection successful\n";

// 2. Check if cart table exists and its structure
$result = $conn->query("SHOW TABLES LIKE 'cart'");
if ($result->num_rows > 0) {
    echo "✓ Cart table exists\n";
    
    $result = $conn->query("SHOW CREATE TABLE cart");
    $row = $result->fetch_assoc();
    echo "Cart table structure:\n" . $row['Create Table'] . "\n\n";
} else {
    echo "✗ Cart table does not exist!\n";
}

// 3. Check if product table exists and has necessary columns
$result = $conn->query("SHOW COLUMNS FROM product");
if ($result->num_rows > 0) {
    echo "Product table columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    echo "\n";
} else {
    echo "✗ Cannot read product table structure!\n";
}

// 4. Check session
echo "Session status:\n";
echo "- Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";
echo "- User ID in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "No") . "\n";
echo "- CSRF token: " . (isset($_SESSION['csrf_token']) ? "Yes" : "No") . "\n\n";

// 5. Check for any items in cart
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT c.*, p.name FROM cart c JOIN product p ON c.product_code = p.code WHERE c.user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "Current cart contents:\n";
            while ($row = $result->fetch_assoc()) {
                echo "- " . $row['name'] . " (Quantity: " . $row['quantity'] . ")\n";
            }
        } else {
            echo "Cart is empty for user_id=" . $_SESSION['user_id'] . "\n";
        }
        $stmt->close();
    }
}

$conn->close();
?>