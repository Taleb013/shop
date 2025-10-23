<?php
session_start();
require_once 'config.php';

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "shop_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get medicine products with prepared statement
$stmt = $conn->prepare("SELECT * FROM product WHERE category = 'Medicine' ORDER BY name");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

// Function to safely output text
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Get cart count for the logged-in user
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    if ($cart_stmt) {
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        if ($row = $cart_result->fetch_assoc()) {
            $cartCount = $row['count'] ?: 0;
        }
        $cart_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Your custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .product-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="book.php">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cloth.php">Clothes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="medicine.php">Medicine</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="software.php">Software</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Cart <?php if($cartCount > 0): ?><span class="badge bg-primary"><?php echo $cartCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Medicine</h1>
            <p class="lead">Browse our selection of medicines</p>
        </div>
    </div>

    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100">
                        <?php if ($row['discount'] > 0): ?>
                            <div class="discount-badge">
                                <?php echo e($row['discount']); ?>% OFF
                            </div>
                        <?php endif; ?>
                        
                        <img src="assets/uploads/<?php echo e($row['image']); ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo e($row['name']); ?>">
                             
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo e($row['name']); ?></h5>
                            
                            <div class="pricing mb-3">
                                <?php if ($row['discount'] > 0): ?>
                                    <?php 
                                        $discounted_price = $row['price'] - ($row['price'] * $row['discount'] / 100);
                                    ?>
                                    <span class="text-decoration-line-through text-muted">
                                        ৳<?php echo number_format($row['price'], 2); ?>
                                    </span>
                                    <span class="ms-2 text-danger fw-bold">
                                        ৳<?php echo number_format($discounted_price, 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="fw-bold">৳<?php echo number_format($row['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <form action="add_to_cart.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="product_code" value="<?php echo e($row['code']); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary flex-grow-1">Add to Cart</button>
                                    <button type="submit" name="buy_now" class="btn btn-success">Buy Now</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No medicine products available at the moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html><?php
// Close database connection
$stmt->close();
$conn->close();
?>