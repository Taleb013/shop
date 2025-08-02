<?php
// profile.php
session_start();
// Redirect to login if user not authenticated
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection settings
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'shop_db';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo 'Database connection failed: ' . $e->getMessage();
    exit;
}

// Fetch user data
$stmt = $pdo->prepare('SELECT username, email, full_name, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // Invalid session, user removed
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle profile update
$errors = [];
success:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($fullName)) {
        $errors[] = 'Full Name cannot be empty.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
        $update->execute([$fullName, $email, $_SESSION['user_id']]);
        // Reload data
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $message = 'Profile updated successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - MyOnlineShop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="index.php">MyOnlineShop</a>
  <div class="collapse navbar-collapse justify-content-end">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>
<div class="container mt-5">
    <h2>Your Profile</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" action="profile.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="form-group">
            <label>Member Since</label>
            <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<footer class="bg-dark text-white text-center py-3 mt-5">
    &copy; 2025 MyOnlineShop. All rights reserved.
</footer>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>