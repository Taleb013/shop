<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "shop_db";

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$confirmationMessage = "";
$extraInstruction = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $loc    = $conn->real_escape_string($_POST['location']);
    $method = $conn->real_escape_string($_POST['payment_method']);

    // Calculate total amount from cart. Join product either by product_code or product_id to be resilient.
    $cartQuery = "SELECT c.quantity, p.price, p.discount 
                  FROM cart c 
                  JOIN product p ON (c.product_code = p.code OR c.product_id = p.id) 
                  WHERE c.user_id = ?";

    $stmt = $conn->prepare($cartQuery);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_amount = 0;
    while ($row = $result->fetch_assoc()) {
        $discount = isset($row['discount']) ? (float)$row['discount'] : 0.0;
        $price = (float)$row['price'] * (1 - $discount/100);
        $total_amount += $price * (int)$row['quantity'];
    }

    // Insert into orders table
    $insertSQL = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) 
                  VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($insertSQL);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("idss", $user_id, $total_amount, $loc, $method);
    if (!$stmt->execute()) {
        die("Order creation failed: " . $stmt->error);
    }

    $order_id = $conn->insert_id;

    // Insert order items: select product_code (from cart or product) and price from product join
    $insertItemsSQL = "INSERT INTO order_items (order_id, product_code, quantity, price) 
                       SELECT ?, COALESCE(c.product_code, p.code) as product_code, c.quantity, p.price 
                       FROM cart c 
                       JOIN product p ON (c.product_code = p.code OR c.product_id = p.id) 
                       WHERE c.user_id = ?";

    $stmt = $conn->prepare($insertItemsSQL);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $order_id, $user_id);
    if (!$stmt->execute()) {
        die("Order items creation failed: " . $stmt->error);
    }

    // Clear the user's cart
    $clearCartSQL = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($clearCartSQL);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        die("Cart clearing failed: " . $stmt->error);
    }

    // Get user details for confirmation
    $userQuery = "SELECT name, email, mobile FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
    
    $confirmationMessage = "✅ Thank you, <strong>" . htmlspecialchars($userData['name']) . "</strong>! Your order #" . $order_id . " has been placed successfully using <strong>" . htmlspecialchars($method) . "</strong>. Total amount: <strong>৳" . number_format($total_amount, 2) . "</strong>. We will contact you at <strong>" . htmlspecialchars($userData['email']) . "</strong> or <strong>" . htmlspecialchars($userData['mobile']) . "</strong>.";

    // Optional Payment Instructions
    if ($method == 'bKash') {
        $extraInstruction = "<p><strong>bKash Instruction:</strong> Please send ৳" . number_format($total_amount, 2) . " to <strong>017XXXXXXXX</strong>. Your order will be verified upon delivery.</p>";
    } elseif ($method == 'Nagad') {
        $extraInstruction = "<p><strong>Nagad Instruction:</strong> Send your payment of ৳" . number_format($total_amount, 2) . " to <strong>018XXXXXXXX</strong> and keep your transaction ID ready.</p>";
    } else {
        $extraInstruction = "<p><strong>Cash on Delivery:</strong> Please keep the exact amount of ৳" . number_format($total_amount, 2) . " ready during delivery. Our delivery agent will call before dispatch.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Order - Zen Web</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 700px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #34495e;
        }
        .confirmation {
            background-color: #e9ffe9;
            padding: 20px;
            border-left: 5px solid #28a745;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .instructions {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 5px solid #ffc107;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Payment Confirmation</h2>

    <?php if ($confirmationMessage != ""): ?>
        <div class="confirmation">
            <?= $confirmationMessage ?>
        </div>
        <div class="instructions">
            <?= $extraInstruction ?>
        </div>
        <a href="index.php" class="btn btn-primary mt-4">Back to Home</a>
    <?php else: ?>
        <form method="post" action="payment_process.php">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="fullName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email (Gmail):</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mobile Number:</label>
                <input type="text" name="mobile" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Delivery Location:</label>
                <textarea name="location" class="form-control" rows="2" required></textarea>
            </div>
            <label>Choose Payment Method:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="payment_method" value="bKash" id="bkash" checked>
                <label class="form-check-label" for="bkash">bKash</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="payment_method" value="Nagad" id="nagad">
                <label class="form-check-label" for="nagad">Nagad</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="payment_method" value="Cash on Delivery" id="cod">
                <label class="form-check-label" for="cod">Cash on Delivery</label>
            </div>
            <br><br>
            <button type="submit" class="btn btn-success btn-block">Submit Order</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
