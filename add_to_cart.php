<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get product code from POST or GET
$product_code = isset($_POST['product_code']) ? $_POST['product_code'] : (isset($_GET['code']) ? $_GET['code'] : null);
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$product_code) {
    die('No product code provided');
}

try {
    $conn = new mysqli('localhost', 'root', '', 'shop_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // First, get the product details
    $stmt = $conn->prepare("SELECT * FROM product WHERE code = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $product_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_code = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("is", $user_id, $product_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // Update quantity
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_code = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iis", $new_quantity, $user_id, $product_code);
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_code, quantity) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isi", $user_id, $product_code, $quantity);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // If this was a "Buy Now" click, redirect to cart
    if (isset($_POST['buy_now']) || isset($_GET['buy'])) {
        header('Location: cart.php');
        exit;
    }

    // For regular add to cart, return to previous page
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'));
    exit;

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    echo "Error: " . htmlspecialchars($e->getMessage());
}