<?php
require_once 'config.php';

// Must be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// First, ensure all necessary tables exist
try {
    // Create user_preferences if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email_notifications BOOLEAN DEFAULT TRUE,
            sms_notifications BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Create user_addresses if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            address TEXT,
            city VARCHAR(100),
            postal_code VARCHAR(20),
            country VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    // Ensure user has preferences record
    $pdo->prepare("
        INSERT IGNORE INTO user_preferences (user_id, email_notifications, sms_notifications)
        VALUES (?, TRUE, FALSE)
    ")->execute([$_SESSION['user_id']]);

    // Ensure user has address record
    $pdo->prepare("
        INSERT IGNORE INTO user_addresses (user_id)
        VALUES (?)
    ")->execute([$_SESSION['user_id']]);

    // Now fetch user data with all related information
    $stmt = $pdo->prepare("
        SELECT u.*, ua.address, ua.city, ua.postal_code, ua.country,
               up.email_notifications, up.sms_notifications
        FROM users u
        LEFT JOIN user_addresses ua ON u.id = ua.user_id
        LEFT JOIN user_preferences up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found in database
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $msg = "<div class='alert alert-danger'>An error occurred. Please try again later.</div>";
    $user = [];
}

// Initialize message variable
$msg = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        try {
            $full_name = trim($_POST['full_name']); // Corrected to full_name
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $profile_image = $user['profile_image'] ?? null;
            
            // Handle profile image upload
            if (!empty($_FILES['profile_image']['name'])) {
                $upload_dir = __DIR__ . '/uploads/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                        $profile_image = 'profiles/' . $new_filename;
                        
                        // Delete old profile image if exists
                        if (!empty($user['profile_image'])) {
                            $old_image = __DIR__ . '/uploads/' . $user['profile_image'];
                            if (file_exists($old_image)) {
                                unlink($old_image);
                            }
                        }
                    }
                }
            }
            
            // Check if email is already taken by another user
            if ($email !== $user['email']) {
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check->execute([$email, $_SESSION['user_id']]);
                if ($check->rowCount() > 0) {
                    throw new PDOException("This email is already registered.");
                }
            }
            
            // Corrected to full_name in the SQL query
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $profile_image, $_SESSION['user_id']]);
            $_SESSION['user_email'] = $email; // Update session email
            $msg = "<div class='alert alert-success'>Profile updated successfully!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger'>Error updating profile: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Profile update error: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['update_address'])) {
        try {
            $address = trim($_POST['address']);
            $city = trim($_POST['city']);
            $postal_code = trim($_POST['postal_code']);
            $country = trim($_POST['country']);
            
            // Check if address exists for user
            $check = $pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ?");
            $check->execute([$_SESSION['user_id']]);
            
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE user_addresses SET address = ?, city = ?, postal_code = ?, country = ? WHERE user_id = ?");
                $stmt->execute([$address, $city, $postal_code, $country, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, address, city, postal_code, country) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $address, $city, $postal_code, $country]);
            }
            
            $msg = "<div class='alert alert-success'>Address updated successfully!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger'>Error updating address: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Address update error: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $msg = "<div class='alert alert-success'>Password updated successfully!</div>";
            } else {
                $msg = "<div class='alert alert-danger'>New passwords do not match!</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Current password is incorrect!</div>";
        }
    }
    
    if (isset($_POST['update_preferences'])) {
        try {
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
            
            // Check if preferences exist for user
            $check = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
            $check->execute([$_SESSION['user_id']]);
            
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE user_preferences SET email_notifications = ?, sms_notifications = ? WHERE user_id = ?");
                $stmt->execute([$email_notifications, $sms_notifications, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, email_notifications, sms_notifications) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $email_notifications, $sms_notifications]);
            }
            
            $msg = "<div class='alert alert-success'>Preferences updated successfully!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger'>Error updating preferences: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Preferences update error: " . $e->getMessage());
        }
    }
}

// Refresh user data after updates
try {
    $stmt = $pdo->prepare("
        SELECT u.*, ua.address, ua.city, ua.postal_code, ua.country, 
               up.email_notifications, up.sms_notifications
        FROM users u
        LEFT JOIN user_addresses ua ON u.id = ua.user_id
        LEFT JOIN user_preferences up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error refreshing user data: " . $e->getMessage());
}

// Check if orders table exists
$stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$orderTableExists = $stmt->rowCount() > 0;

// Initialize $recent_orders as empty array
$recent_orders = [];

if ($orderTableExists) {
    // Fetch order history if table exists
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(p.name SEPARATOR ', ') as products
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN product p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    
    try {
        $stmt->execute([$_SESSION['user_id']]);
        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error and continue
        error_log("Error fetching orders: " . $e->getMessage());
        $recent_orders = [];
    }
}

// Fetch wishlist
$stmt = $pdo->prepare("
    SELECT p.*
    FROM product p
    JOIN wishlist w ON p.id = w.product_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MyOnlineShop</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        .order-card {
            transition: transform 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .profile-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">MyOnlineShop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Profile Header -->
    <header class="profile-header">
        <div class="container text-center">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;" alt="Profile Picture">
            <?php else: ?>
                <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($user['email'] ?? ''))) ?>?s=120&d=mp" class="rounded-circle mb-3" alt="Profile Picture">
            <?php endif; ?>
            <!-- Corrected to full_name -->
            <h1 class="display-5 mb-0"><?= htmlspecialchars($user['full_name'] ?? 'User Profile') ?></h1>
            <p class="lead"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        </div>
    </header>
    <!-- Main Content -->
    <div class="container">
        <?php if (!empty($msg)) echo $msg; ?>
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="profile-section">
                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active mb-2" data-bs-toggle="pill" href="#profile">Profile Info</a>
                        <a class="nav-link mb-2" data-bs-toggle="pill" href="#orders">Order History</a>
                        <a class="nav-link mb-2" data-bs-toggle="pill" href="#wishlist">Wishlist</a>
                        <a class="nav-link mb-2" data-bs-toggle="pill" href="#preferences">Preferences</a>
                        <a class="nav-link mb-2" data-bs-toggle="pill" href="#change-password">Change Password</a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9 mb-4">
                <div class="tab-content">
                    <!-- Profile Info Tab -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="profile-section">
                            <h2 class="mb-4">Update Profile Info</h2>
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <!-- Corrected to full_name for both input name and value -->
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                        
                        <!-- Address section here -->
                        <div class="profile-section mt-4">
                            <h2 class="mb-4">Update Address</h2>
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="update_address" value="1">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Address</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Order History Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="profile-section">
                            <h2 class="mb-4">Recent Orders</h2>
                            <?php if (!empty($recent_orders)): ?>
                                <div class="list-group">
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mb-2 order-card shadow-sm">
                                            <div>
                                                <h5 class="mb-1">Order #<?= htmlspecialchars($order['id']) ?></h5>
                                                <p class="mb-1 text-muted">
                                                    Products: <?= htmlspecialchars($order['products'] ?? 'N/A') ?><br>
                                                    Total: ৳<?= number_format($order['total_amount'], 2) ?><br>
                                                    Status: <span class="badge bg-primary"><?= htmlspecialchars($order['order_status']) ?></span>
                                                </p>
                                            </div>
                                            <small><?= date("M d, Y", strtotime($order['created_at'])) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">You have no recent orders.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Wishlist Tab -->
                    <div class="tab-pane fade" id="wishlist">
                        <div class="profile-section">
                            <h2 class="mb-4">My Wishlist</h2>
                            <?php if (!empty($wishlist)): ?>
                                <div class="row">
                                    <?php foreach ($wishlist as $item): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body d-flex align-items-center">
                                                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" class="me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
                                                    <div>
                                                        <h5 class="card-title mb-0"><?= htmlspecialchars($item['name']) ?></h5>
                                                        <p class="card-text text-muted mb-0">৳<?= number_format($item['price'], 2) ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">Your wishlist is empty.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Preferences Tab -->
                    <div class="tab-pane fade" id="preferences">
                        <div class="profile-section">
                            <h2 class="mb-4">Notification Preferences</h2>
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="update_preferences" value="1">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" <?= ($user['sms_notifications'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="sms_notifications">
                                        SMS Notifications
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Preferences</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="change-password">
                        <div class="profile-section">
                            <h2 class="mb-4">Change Password</h2>
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="update_password" value="1">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> MyOnlineShop. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
