<?php
// software.php
// Display products of category 'Software' with modern design and animations

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "shop_db";

// Create connection
$conn = new mysqli($servername, $username, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query products in Software category
$category = 'Software';
$sql = "SELECT * FROM product WHERE category = '$category'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Products - Zen Web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        header {
            background: #4b79a1;
            color: white;
            padding: 20px;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 36px;
            letter-spacing: 1px;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
            gap: 20px;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            width: 260px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            opacity: 0;
            transform: translateY(20px);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .product-details {
            padding: 15px;
        }
        .product-name {
            font-size: 20px;
            color: #333;
            margin: 10px 0;
        }
        .product-price {
            font-size: 18px;
            color: #e74c3c;
            margin: 5px 0;
            font-weight: bold;
        }
        .product-discount {
            font-size: 14px;
            color: #888;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 16px;
            transition: background 0.3s;
        }
        .buy-now {
            background: #27ae60;
        }
        .buy-now:hover {
            background: #2ecc71;
        }
        .add-cart {
            background: #2980b9;
        }
        .add-cart:hover {
            background: #3498db;
        }
        /* Animation keyframes */
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .product-card.animate {
            animation: fadeInUp 0.8s ease-in-out forwards;
        }
    </style>
</head>
<body>
    <header>
        <h1>Software Products</h1>
    </header>
    <div class="container">
        <div class="product-grid">
<?php
        if ($result->num_rows > 0) {
            $delay = 0.1;
            while ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row['name']);
                $code = htmlspecialchars($row['code']);
                $price = number_format($row['price'], 2);
                $discount = intval($row['discount']);
                $image = htmlspecialchars($row['image']);
                $final_price = $row['price'] * (1 - $discount / 100);
                
                echo "<div class='product-card animate' style='animation-delay: {$delay}s'>";
                echo "<img src='uploads/{$image}' alt='{$name}'>";
                echo "<div class='product-details'>";
                echo "<div class='product-name'>{$name}</div>";
                echo "<div class='product-price'>৳" . number_format($final_price, 2) . "</div>";
                echo "<div class='product-discount'>Original: ৳{$price} | Discount: {$discount}%</div>";
        echo "<div class='button-group'>";

        // Buy Now (GET)
        echo "<a
        href='cart.php?buy&code=" . urlencode($code) . "'
        class='btn btn-success d-block mb-2 animate__animated animate__zoomIn'
      >
        Buy Now
      </a>";

        // Add to Cart (GET)
        echo "<a
        href='cart.php?add&code=" . urlencode($code) . "'
        class='btn btn-primary d-block animate__animated animate__zoomIn'
      >
        Add to Cart
      </a>";


        echo "</div>"; // .button-group
                echo "</div>"; // .product-details
                echo "</div>"; // .product-card

                $delay += 0.1;
            }
        } else {
            echo "<p style='font-size:18px;color:#555;'>No software products found.</p>";
        }
?>
        </div> <!-- .product-grid -->
    </div> <!-- .container -->

    <script>
        // Add animate class with delay (simulate fade-in animation)
        window.addEventListener("DOMContentLoaded", () => {
            const cards = document.querySelectorAll(".product-card");
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add("animate");
                }, index * 150); // 150ms staggered
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
