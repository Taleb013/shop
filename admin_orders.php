<?php
session_start();

// Check if admin is logged in
const ADMIN_USER = 'Taleb';
const ADMIN_PASS = 't@leb';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'shop_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper: check which order tables exist and choose the appropriate query
$orders_table = 'orders';
$order_items_table = 'order_items';
$use_user_orders = false;

$res = $conn->query("SHOW TABLES LIKE 'orders'");
if (!$res || $res->num_rows === 0) {
    // if `orders` doesn't exist, fall back to `user_orders`
    $res2 = $conn->query("SHOW TABLES LIKE 'user_orders'");
    if ($res2 && $res2->num_rows > 0) {
        $orders_table = 'user_orders';
        $order_items_table = 'user_order_items';
        $use_user_orders = true;
    }
}

// Update order status if requested
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $status = $_POST['new_status'];
    $order_id = $_POST['order_id'];

    if ($use_user_orders) {
        // user_orders uses `order_status` and `order_id` as primary key
        $stmt = $conn->prepare("UPDATE user_orders SET order_status = ? WHERE order_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $order_id);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // default orders table
        $stmt = $conn->prepare("UPDATE orders SET status = ?, order_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $status, $status, $order_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch all orders with user details
$result = false;

if ($use_user_orders) {
    // user_orders schema: alias columns so the rest of the admin UI can use the same keys (id, status, customer_name, customer_email)
    $sql = "SELECT
                o.order_id AS id,
                o.user_id,
                o.full_name AS customer_name,
                o.email AS customer_email,
                o.phone,
                o.shipping_address,
                o.total_amount,
                o.order_status AS status,
                o.created_at,
                COUNT(oi.id) AS total_items,
                GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.quantity, ' × ৳', oi.price, ')') SEPARATOR ', ') AS products
            FROM user_orders o
            JOIN user_order_items oi ON o.order_id = oi.order_id
            GROUP BY o.order_id
            ORDER BY o.created_at DESC";

    $result = $conn->query($sql);
} else {
    // original query for `orders` table
    $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
               COUNT(oi.id) as total_items,
               GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' × ৳', oi.price, ')') SEPARATOR ', ') as products
            FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN product p ON (oi.product_code = p.code OR oi.product_code = CAST(p.id AS CHAR))
            GROUP BY o.id
            ORDER BY o.created_at DESC";

    $result = $conn->query($sql);
}

// If the selected source returned no rows, but user_orders has data, fall back to it
if (($result === false || $result->num_rows === 0) && table_exists($conn, 'user_orders')) {
    $use_user_orders = true;
    $sql = "SELECT
                o.order_id AS id,
                o.user_id,
                o.full_name AS customer_name,
                o.email AS customer_email,
                o.phone,
                o.shipping_address,
                o.total_amount,
                o.order_status AS status,
                o.created_at,
                COUNT(oi.id) AS total_items,
                GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.quantity, ' × ৳', oi.price, ')') SEPARATOR ', ') AS products
            FROM user_orders o
            JOIN user_order_items oi ON o.order_id = oi.order_id
            GROUP BY o.order_id
            ORDER BY o.created_at DESC";

    $result = $conn->query($sql);
}

// Final data source label for the UI
$data_source = $use_user_orders ? 'user_orders' : 'orders';

// Helper to check if a table exists
function table_exists($conn, $tableName) {
    $escaped = $conn->real_escape_string($tableName);
    $res = $conn->query("SHOW TABLES LIKE '" . $escaped . "'");
    return ($res && $res->num_rows > 0);
}

// Count total number of records in each table (for admin dashboard) safely
$order_count = 0;
$order_item_count = 0;
$user_order_count = 0;
$user_order_item_count = 0;

if (table_exists($conn, 'orders')) {
    $r = $conn->query("SELECT COUNT(*) AS count FROM orders");
    if ($r) $order_count = $r->fetch_assoc()['count'] ?? 0;
}
if (table_exists($conn, 'order_items')) {
    $r = $conn->query("SELECT COUNT(*) AS count FROM order_items");
    if ($r) $order_item_count = $r->fetch_assoc()['count'] ?? 0;
}
if (table_exists($conn, 'user_orders')) {
    $r = $conn->query("SELECT COUNT(*) AS count FROM user_orders");
    if ($r) $user_order_count = $r->fetch_assoc()['count'] ?? 0;
}
if (table_exists($conn, 'user_order_items')) {
    $r = $conn->query("SELECT COUNT(*) AS count FROM user_order_items");
    if ($r) $user_order_item_count = $r->fetch_assoc()['count'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet">
    <style>
        .order-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .status-pending { color: #ffc107; }
        .status-processing { color: #17a2b8; }
        .status-shipped { color: #007bff; }
        .status-delivered { color: #28a745; }
        .status-cancelled { color: #dc3545; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="admin.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin_orders.php">Orders</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h1 class="mb-4">Order Management</h1>
    <p class="text-muted">Data source: <code><?php echo htmlspecialchars($data_source); ?></code></p>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="col-12">
                    <div class="card order-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title">
                                        Order #<?php echo $order['id']; ?> 
                                        <span class="badge bg-<?php 
                                            echo match($order['status']) {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </h5>
                                    <p class="mb-1">
                                        <strong>Customer:</strong> 
                                        <?php echo htmlspecialchars($order['customer_name']); ?> 
                                        (<?php echo htmlspecialchars($order['customer_email']); ?>)
                                    </p>
                                    <p class="mb-1">
                                        <strong>Date:</strong> 
                                        <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Total Amount:</strong> 
                                        ৳<?php echo number_format($order['total_amount'], 2); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Shipping Address:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Order Items (<?php echo $order['total_items']; ?>):</h6>
                                    <p><?php echo htmlspecialchars($order['products']); ?></p>
                                    
                                    <form method="POST" class="mt-3">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <div class="input-group">
                                            <select name="new_status" class="form-select">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary">
                                                Update Status
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>