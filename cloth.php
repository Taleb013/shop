<?php
// cloth.php - Display all cloth category products from shop_db

// Database connection settings
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "shop_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products in 'Cloth' category
$sql    = "SELECT * FROM product WHERE category = 'Cloth'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cloth Products - Shop</title>
    <!-- Bootstrap CSS -->
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  rel="stylesheet"
/>
<!-- Animate.css -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
/>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .product-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-10px);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeInDown" href="index.php">My Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse animate__animated animate__fadeInDown"
                 id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item active"><a class="nav-link" href="cloth.php">Clothes</a></li>
                    <li class="nav-item"><a class="nav-link" href="shoes.php">Shoes</a></li>
                    <li class="nav-item"><a class="nav-link" href="accessories.php">Accessories</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main container -->
    <div class="container my-5">
        <h1 class="mb-4 text-center animate__animated animate__fadeIn">Cloth Collection</h1>
        <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()):
                $code      = htmlspecialchars($row['code']);
                $name      = htmlspecialchars($row['name']);
                $price     = floatval($row['price']);
                $discount  = intval($row['discount']);
                $imageFile = htmlspecialchars($row['image']);
                // Calculate discounted price
                $finalPrice = $discount > 0
                    ? $price - ($price * ($discount / 100))
                    : $price;
            ?>
            <div class="col-md-4">
                <div class="card product-card animate__animated animate__zoomIn">
                    <img src="assets/uploads/<?php echo $imageFile; ?>"
                         class="card-img-top product-image"
                         alt="<?php echo $name; ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $name; ?></h5>
                        <p class="card-text">
                            <?php if ($discount > 0): ?>
                                <span class="text-danger fw-bold">
                                    $<?php echo number_format($finalPrice, 2); ?>
                                </span>
                                <small class="text-muted">
                                    <del>$<?php echo number_format($price, 2); ?></del>
                                </small>
                                <br>
                                <span class="badge bg-success">
                                    <?php echo $discount; ?>% OFF
                                </span>
                            <?php else: ?>
                                <span class="fw-bold">
                                    $<?php echo number_format($price, 2); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        <div class="d-grid gap-2">
                            <a href="cart.php?buy&code=<?php echo urlencode($code); ?>"
                               class="btn btn-success">
                                Buy Now
                            </a>
                            <a href="cart.php?add&code=<?php echo urlencode($code); ?>"
                               class="btn btn-primary">
                                Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">No cloth products found.</p>
            </div>
        <?php endif; ?>
        <?php $conn->close(); ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-auto py-4">
        <div class="container">
            <p class="text-center mb-0">&copy; 2025 My Shop. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
