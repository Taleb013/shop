<?php
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

    // Create cart table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS cart (
        user_id INT NOT NULL,
        product_code VARCHAR(100) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        PRIMARY KEY (user_id, product_code)
    )";
    
    if (!$conn->query($create_table)) {
        throw new Exception("Could not create cart table: " . $conn->error);
    }

    // Get cart items with product details
    $sql = "SELECT c.*, p.name, p.price, p.discount, p.image 
            FROM cart c 
            JOIN product p ON c.product_code = p.code 
            WHERE c.user_id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $price = $row['price'];
        if ($row['discount'] > 0) {
            $price = $price - ($price * $row['discount'] / 100);
        }
        $subtotal = $price * $row['quantity'];
        $total += $subtotal;
        
        $row['price'] = $price;
        $row['subtotal'] = $subtotal;
        $cart_items[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="assets/css/cart.css">
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="index.php">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>
                                <img src="assets/uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="max-width: 50px;">
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <div class="quantity-controls">
                                    <form action="update_cart.php" method="post" style="display: inline;">
                                        <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($item['product_code']); ?>">
                                        <button type="submit" name="decrease" class="btn btn-sm btn-secondary">-</button>
                                        <span class="mx-2"><?php echo $item['quantity']; ?></span>
                                        <button type="submit" name="increase" class="btn btn-sm btn-secondary">+</button>
                                    </form>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <form action="update_cart.php" method="post" style="display: inline;">
                                    <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($item['product_code']); ?>">
                                    <button type="submit" name="remove" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<!-- Add Bootstrap JS and its dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>