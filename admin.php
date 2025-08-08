<?php
session_start();

// Part 1: Authentication
const ADMIN_USER = 'Taleb';
const ADMIN_PASS = 't@leb';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Handle login submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        if ($u === ADMIN_USER && $p === ADMIN_PASS) {
            $_SESSION['is_admin'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }
    // Login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Admin Login</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
      <style>
        body { background: #f0f2f5; }
        .card { max-width: 400px; margin: 100px auto; }
      </style>
    </head>
    <body>
        <a href="index.php" class="btn btn-secondary position-absolute top-0 start-0 m-3">Back to Home</a>
      <div class="card shadow animate__animated animate__fadeInDown">
        <div class="card-body">
          <h3 class="card-title text-center mb-4">Admin Login</h3>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="POST" action="admin.php">
            <div class="mb-3">
              <input name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
              <input name="password" type="password" class="form-control" placeholder="Password" required>
            </div>
            <button name="login" class="btn btn-primary w-100">Login</button>
          </form>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Part 2: Database Connection & Handlers
$servername = "localhost";
$dbuser     = "root";
$dbpass     = "";
$dbname     = "shop_db";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name     = $conn->real_escape_string($_POST['product_name']);
    $code     = $conn->real_escape_string($_POST['product_code']);
    $category = $conn->real_escape_string($_POST['category']);
    $price    = floatval($_POST['price']);
    $discount = intval($_POST['discount']);

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $targetName = uniqid('img_') . '.' . $ext;
        $targetPath = __DIR__ . 'assets/uploads/' . $targetName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $msg = "<div class='alert alert-danger'>Image upload failed.</div>";
        } else {
            $conn->query(
                "INSERT INTO product (name, code, category, price, discount, image)
                 VALUES ('$name','$code','$category',$price,$discount,'$targetName')"
            );
            $msg = "<div class='alert alert-success'>Product added.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>Please select an image.</div>";
    }
}

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delCode = $conn->real_escape_string($_POST['product_code']);
    $res     = $conn->query("SELECT image FROM product WHERE code='$delCode'");
    if ($res && $row = $res->fetch_assoc()) {
        @unlink(__DIR__ . '/uploads/' . $row['image']);
    }
    $conn->query("DELETE FROM product WHERE code='$delCode'");
    $msg = "<div class='alert alert-info'>Product deleted.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <style>
    body { overflow-x: hidden; background: #f8f9fa; }
    .sidebar {
      width: 240px;
      position: fixed; top: 0; bottom: 0;
      background: #343a40; color: #ccc; padding-top: 1rem;
    }
    .sidebar a {
      color: #ccc; padding: .75rem 1rem; display: block; text-decoration: none;
    }
    .sidebar a:hover { background: #495057; color: #fff; }
    .main { margin-left: 240px; padding: 2rem; }
    .card-header { background: #4b79a1; color: #fff; }
  </style>
</head>
<body>

<nav class="sidebar animate__animated animate__fadeInLeft">
  <h4 class="text-center mb-4">Admin Panel</h4>
  <a href="#add">Add Product</a>
  <a href="#delete">Delete Product</a>
  <a href="#view">View Products</a>
  <a href="admin_logout.php">Logout</a>
</nav>

<div class="main">
  <?php if (!empty($msg)) echo $msg; ?>

  <!-- Add Product Section -->
  <section id="add" class="mb-5">
    <div class="card shadow animate__animated animate__fadeInUp">
      <div class="card-header">Add New Product</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
              <option>Cloth</option>
              <option>Book</option>
              <option>Software</option>
              <option>Course</option>
            </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Product Name</label>
              <input name="product_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Product Code</label>
              <input name="product_code" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Price</label>
              <input name="price" type="number" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Discount (%)</label>
              <input name="discount" type="number" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Product Image</label>
              <input name="image" type="file" class="form-control" accept="image/*" required>
            </div>
          </div>
          <button name="add_product" class="btn btn-success mt-3">Add Product</button>
        </form>
      </div>
    </div>
  </section>
  <!-- Delete Product Section -->
  <section id="delete" class="mb-5">
    <div class="card shadow animate__animated animate__fadeInUp" style="animation-delay: .2s;">
      <div class="card-header">Delete Product</div>
      <div class="card-body">
        <form method="POST" action="admin.php#delete">
          <div class="input-group">
            <input
              name="product_code"
              type="text"
              class="form-control"
              placeholder="Enter product code to delete"
              required
            />
            <button
              name="delete_product"
              class="btn btn-danger"
              type="submit"
            >
              Delete
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- View All Products Section -->
  <section id="view">
    <div class="card shadow animate__animated animate__fadeInUp" style="animation-delay: .4s;">
      <div class="card-header">All Products</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Category</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Image</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $all = $conn->query("SELECT * FROM product ORDER BY name");
              while ($row = $all->fetch_assoc()):
                  $imgPath = 'uploads/' . htmlspecialchars($row['image']);
              ?>
                <tr>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['code']) ?></td>
                  <td><?= htmlspecialchars($row['category']) ?></td>
                  <td>৳<?= number_format($row['price'], 2) ?></td>
                  <td><?= intval($row['discount']) ?>%</td>
                  <td>
                    <img
                      src="<?= $imgPath ?>"
                      alt="<?= htmlspecialchars($row['name']) ?>"
                      width="50"
                      class="rounded"
                    />
                  </td>
                  <td>
                    <a
                      href="edit_product.php?id=<?= urlencode($row['code']) ?>"
                      class="btn btn-sm btn-warning"
                    >
                      Edit
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

</div><!-- /.main -->

<!-- Smooth Scroll Script -->
<script>
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
