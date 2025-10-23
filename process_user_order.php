<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    if (empty($shipping_address) || empty($payment_method)) {
        throw new Exception('Shipping address and payment method are required');
    }

    // Start transaction
    $conn->beginTransaction();

    // Get cart items with current prices
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.discount,
               (CASE 
                    WHEN p.discount > 0 
                    THEN p.price - (p.price * p.discount / 100)
                    ELSE p.price 
                END) as actual_price,
               (CASE 
                    WHEN p.discount > 0 
                    THEN (p.price - (p.price * p.discount / 100)) * c.quantity
                    ELSE p.price * c.quantity 
                END) as subtotal,
               p.name as name
        FROM cart c
        JOIN product p ON c.product_code = p.code
        WHERE c.user_id = ?
    ");
    
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Calculate total
    $total_amount = array_sum(array_column($cart_items, 'subtotal'));

    // Get user details for order
    $stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        throw new Exception('Could not find user details for the current session.');
    }

    // Create order
    $stmt = $conn->prepare("
        INSERT INTO user_orders (
            user_id,
            full_name,
            email,
            phone,
            shipping_address,
            total_amount,
            payment_method,
            order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([
        $user_id,
        $user_data['full_name'],
        $user_data['email'],
        $user_data['phone'],
        $shipping_address,
        $total_amount,
        $payment_method
    ]);
    
    $order_id = $conn->lastInsertId();

    // Add order items
    $stmt = $conn->prepare("
        INSERT INTO user_order_items (
            order_id,
            product_code,
            product_name,
            quantity,
            price
        ) VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_code'],
            $item['name'],
            $item['quantity'],
            $item['actual_price']
        ]);
    }

    // Clear user's cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'total' => $total_amount
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    error_log("Order processing error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}