<?php
session_start();

// Only process the form on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Grab & trim inputs
    $name     = trim($_POST['registerName']    ?? '');
    $email    = trim($_POST['registerEmail']   ?? '');
    $password =           $_POST['registerPassword'] ?? '';
    $confirm  =           $_POST['confirmPassword']  ?? '';

    // 2) Basic validation
    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $_SESSION['error'] = "All fields are required.";
        header('Location: register.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header('Location: register.php');
        exit;
    }
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: register.php');
        exit;
    }

    // 3) Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 4) Connect to MySQL via PDO
    $host   = 'localhost';
    $db     = 'shop_db';
    $user   = 'root';        // ← adjust if different
    $pass   = '';            // ← adjust if different
    $charset= 'utf8mb4';
    $dsn    = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database connection failed.";
        header('Location: register.php');
        exit;
    }

    // 5) Ensure email is unique
    $stmt = $pdo->prepare("SELECT id FROM `users` WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "That email is already registered.";
        header('Location: register.php');
        exit;
    }

    // 6) Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO `users` (full_name, email, password_hash)
        VALUES (:name, :email, :hash)
    ");
    $stmt->execute([
        ':name'  => $name,
        ':email' => $email,
        ':hash'  => $passwordHash,
    ]);

    $_SESSION['success'] = "Registration successful! You may now log in.";
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register | ShopSmart</title>
  <!-- Bootstrap CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
  />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">ShopSmart</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Flash Messages -->
  <div class="container mt-4">
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger">
        <?= htmlentities($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <?= htmlentities($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
  </div>

  <!-- Register Form -->
  <section class="vh-100 d-flex align-items-center bg-light flex-grow-1">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow p-4">
            <h3 class="mb-4 text-center">Create Your Account</h3>
            <form id="registerForm" action="register.php" method="POST">
              <div class="form-group mb-3">
                <label for="registerName">Full Name</label>
                <input
                  type="text"
                  name="registerName"
                  id="registerName"
                  class="form-control"
                  placeholder="Enter your name"
                  required
                />
              </div>
              <div class="form-group mb-3">
                <label for="registerEmail">Email address</label>
                <input
                  type="email"
                  name="registerEmail"
                  id="registerEmail"
                  class="form-control"
                  placeholder="Enter your email"
                  required
                />
              </div>
              <div class="form-group mb-3">
                <label for="registerPassword">Password</label>
                <input
                  type="password"
                  name="registerPassword"
                  id="registerPassword"
                  class="form-control"
                  placeholder="Create a password"
                  required
                />
              </div>
              <div class="form-group mb-4">
                <label for="confirmPassword">Confirm Password</label>
                <input
                  type="password"
                  name="confirmPassword"
                  id="confirmPassword"
                  class="form-control"
                  placeholder="Re-enter your password"
                  required
                />
              </div>
              <button type="submit" class="btn btn-success w-100">Register</button>
              <div class="text-center mt-3">
                <small>Already have an account? <a href="login.php">Login here</a></small>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <p class="mb-0">&copy; 2025 ShopSmart. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JS -->

</body>
</html>
