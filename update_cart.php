<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $conn = new mysqli('localhost', 'root', '', 'shop_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $product_code = $_POST['product_code'] ?? '';
    if (empty($product_code)) {
        throw new Exception("No product code provided");
    }

    // Increase quantity
    if (isset($_POST['increase'])) {
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_code = ?";
    }
    // Decrease quantity
    else if (isset($_POST['decrease'])) {
        $sql = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE user_id = ? AND product_code = ?";
    }
    // Remove item
    else if (isset($_POST['remove'])) {
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_code = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("is", $user_id, $product_code);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();

    // Redirect back to cart
    header('Location: cart.php');
    exit;

} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    echo "Error: " . htmlspecialchars($e->getMessage());
}