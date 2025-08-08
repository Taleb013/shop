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

// Create orders table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255),
    email VARCHAR(255),
    mobile VARCHAR(30),
    location TEXT,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTableSQL);

// Handle form submission
$confirmationMessage = "";
$extraInstruction = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name   = $conn->real_escape_string($_POST['fullName']);
    $email  = $conn->real_escape_string($_POST['email']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $loc    = $conn->real_escape_string($_POST['location']);
    $method = $conn->real_escape_string($_POST['payment_method']);

    // Insert into orders table
    $insertSQL = "INSERT INTO orders (user_name, email, mobile, location, payment_method) 
                  VALUES ('$name', '$email', '$mobile', '$loc', '$method')";
    $conn->query($insertSQL);

    $confirmationMessage = "✅ Thank you, <strong>$name</strong>! Your order has been placed successfully using <strong>$method</strong>. We will contact you at <strong>$email</strong> or <strong>$mobile</strong>.";

    // Optional Payment Instructions
    if ($method == 'bKash') {
        $extraInstruction = "<p><strong>bKash Instruction:</strong> Please send ৳ amount to <strong>017XXXXXXXX</strong>. Your order will be verified upon delivery.</p>";
    } elseif ($method == 'Nagad') {
        $extraInstruction = "<p><strong>Nagad Instruction:</strong> Send your payment to <strong>018XXXXXXXX</strong> and keep your transaction ID ready.</p>";
    } else {
        $extraInstruction = "<p><strong>Cash on Delivery:</strong> Please keep the exact change ready during delivery. Our delivery agent will call before dispatch.</p>";
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
