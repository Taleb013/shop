<?php
session_start();  // Assume user session is already valid

// 1. Connect to MySQL database `shop_db` using root and no password
$conn = mysqli_connect("localhost", "root", "", "shop_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 2. Query the product table for category 'Book'
$sql = "SELECT * FROM product WHERE category='Book'";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Hover/scale animation for cards and buttons */
        .card:hover { transform: scale(1.03); transition: transform 0.3s; }
        .btn:hover  { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Books</h1>
    <div class="row">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="assets/uploads/<?php echo htmlspecialchars($row['image']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text">$<?php echo number_format($row['price'], 2); ?></p>
                            <?php if (!empty($row['discount']) && $row['discount'] > 0): ?>
                                <span class="badge bg-success mb-2">
                                  <?php echo (int)$row['discount']; ?>% off
                                </span>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <!-- Add to Cart and Buy Now buttons -->
                                <a href="cart.php?add&code=<?php echo urlencode($row['code']); ?>"
                                   class="btn btn-primary">
                                  Add to Cart
                                </a>
                                <a href="cart.php?buy&code=<?php echo urlencode($row['code']); ?>"
                                   class="btn btn-success ms-2">
                                  Buy Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-muted">No books found in the Book category.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
//  Close the connection
mysqli_close($conn);
?>
