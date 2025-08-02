<?php
session_start();

// If already logged in, send them home
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Grab & trim inputs
    $email    = trim($_POST['loginEmail']    ?? '');
    $password =           $_POST['loginPassword'] ?? '';

    // 2) Basic validation
    if ($email === '' || $password === '') {
        $_SESSION['error'] = "Both fields are required.";
        header('Location: login.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header('Location: login.php');
        exit;
    }

    // 3) Connect to MySQL via PDO
    $host   = 'localhost';
    $db     = 'shop_db';
    $user   = 'root';    // ← adjust if needed
    $pass   = '';        // ← adjust if needed
    $charset= 'utf8mb4';
    $dsn    = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database connection failed.";
        header('Location: login.php');
        exit;
    }

    // 4) Fetch user by email
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM `user` WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Email not found.";
        header('Location: login.php');
        exit;
    }

    // 5) Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = "Incorrect password.";
        header('Location: login.php');
        exit;
    }

    // 6) Login success
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['success']   = "Welcome back, " . htmlspecialchars($user['full_name']) . "!";

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | ShopSmart</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="d-flex flex-column min-vh-100">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">ShopSmart</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
          <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= htmlentities($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success"><?= htmlentities($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
  </div>

  <section class="vh-100 d-flex align-items-center bg-light flex-grow-1">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow p-4">
            <h3 class="mb-4 text-center">Login to Your Account</h3>
            <form id="loginForm" action="login.php" method="POST">
              <div class="form-group mb-3">
                <label for="loginEmail">Email address</label>
                <input
                  type="email"
                  name="loginEmail"
                  id="loginEmail"
                  class="form-control"
                  placeholder="Enter your email"
                  required
                />
              </div>
              <div class="form-group mb-4">
                <label for="loginPassword">Password</label>
                <input
                  type="password"
                  name="loginPassword"
                  id="loginPassword"
                  class="form-control"
                  placeholder="Enter your password"
                  required
                />
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
              <div class="text-center mt-3">
                <small>Don't have an account? <a href="register.php">Register here</a></small>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 ShopSmart. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
