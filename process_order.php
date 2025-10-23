<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate required fields
$required_fields = ['firstName', 'email', 'address', 'phone', 'paymentMethod'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Error: " . ucfirst($field) . " is required");
    }
}

try {
    $conn = new mysqli('localhost', 'root', '', 'shop_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Verify orders table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'orders'");
        if ($check_table->num_rows === 0) {
            throw new Exception("Orders table is not set up. Please run the database setup script first.");
        }

        // Verify order_items table exists
        $check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
        if ($check_table->num_rows === 0) {
            throw new Exception("Order items table is not set up. Please run the database setup script first.");
        }

        // Get cart items
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.discount 
                               FROM cart c 
                               JOIN product p ON c.product_code = p.code 
                               WHERE c.user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_items = $stmt->get_result();
        $stmt->close();

        // Calculate total
        $total = 0;
        $items_for_order = [];
        while ($item = $cart_items->fetch_assoc()) {
            $price = $item['price'];
            if ($item['discount'] > 0) {
                $price = $price - ($price * $item['discount'] / 100);
            }
            $subtotal = $price * $item['quantity'];
            $total += $subtotal;
            
            $items_for_order[] = [
                'code' => $item['product_code'],
                'quantity' => $item['quantity'],
                'price' => $price
            ];
        }

        // Create unique order_id (e.g., ORD-20231023-001)
        $order_ref = 'ORD-' . date('Ymd') . '-' . sprintf('%03d', rand(1, 999));
        
        // Create order with explicit column list
        $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, shipping_address, payment_method, 
                                                  total_amount, subtotal, total, status, order_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sissddd", $order_ref, $user_id, $_POST['address'], $_POST['paymentMethod'], 
                                    $total, $total, $total);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order: " . $stmt->error);
        }
        $order_id = $conn->insert_id;
        $stmt->close();

        // Add order items with explicit column list
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_code, quantity, price) 
                               VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($items_for_order as $item) {
            $stmt->bind_param("isid", $order_id, $item['code'], $item['quantity'], $item['price']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to add order item: " . $stmt->error);
            }
        }
        $stmt->close();

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Order processing error: " . $e->getMessage());
    echo "Error processing your order: " . htmlspecialchars($e->getMessage());
}