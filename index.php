<?php
declare(strict_types=1);
session_start();

/**
 * index.php — Light futuristic homepage
 * - External CSS: assets/css/style.css
 * - External JS:  assets/js/main.js
 * - Categories: Cloth, Book, Software, Course, Medicine
 * - Shows up to 3 products per category (with add-to-cart POST + CSRF)
 * - Shows up to 3 review previews per product (schema-tolerant)
 */

// --- CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];

// --- DB connection settings ---
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "shop_db";

// Enable mysqli exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

// --- Handle Add to Cart (POST) ---
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $incomingToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals((string)$csrf, (string)$incomingToken)) {
        $msg = "<div class='alert alert-danger text-center'>Invalid request (CSRF).</div>";
    } else {
        $code = trim((string)($_POST['product_code'] ?? ''));
        $qty  = max(1, intval($_POST['quantity'] ?? 1));
        if ($code === '') {
            $msg = "<div class='alert alert-danger text-center'>Invalid product code.</div>";
        } else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$code])) {
                $_SESSION['cart'][$code] += $qty;
            } else {
                $_SESSION['cart'][$code] = $qty;
            }
            $msg = "<div class='alert alert-success text-center'>Product added to cart.</div>";
        }
    }
}

// --- Helpers ---
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tableExists(mysqli $conn, string $table): bool {
    try {
        $sql = "SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'";
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) { $res->free(); return true; }
    } catch (Throwable $t) { /* ignore */ }
    return false;
}

function getTableColumns(mysqli $conn, string $table): array {
    $cols = [];
    try {
        $sql = "SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cols[] = strtolower($row['Field']);
            }
            $res->free();
        }
    } catch (Throwable $t) { /* ignore */ }
    return $cols;
}

/**
 * Fetch reviews in schema-tolerant way.
 * Returns array of rows with keys: user_name|null, rating|null, comment|null, created_at|null
 */
function fetchReviewsPreview(mysqli $conn, string $productCode, int $limit = 3): array {
    $out = [];
    if (!tableExists($conn, 'reviews')) return $out;

    $colsPresent = getTableColumns($conn, 'reviews');
    $wanted = ['user_name','rating','comment','created_at','id'];
    $selectCols = [];
    foreach ($wanted as $w) {
        if (in_array($w, $colsPresent)) $selectCols[] = $w;
    }
    if (empty($selectCols)) return $out;

    $orderBy = '';
    if (in_array('created_at', $colsPresent)) $orderBy = 'ORDER BY created_at DESC';
    elseif (in_array('id', $colsPresent)) $orderBy = 'ORDER BY id DESC';

    $colsSql = implode(', ', array_map(function($c){ return "`$c`"; }, $selectCols));
    $sql = "SELECT $colsSql FROM `reviews` WHERE `product_code` = ? $orderBy LIMIT ?";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $productCode, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $r = [
                    'user_name'  => $row['user_name'] ?? null,
                    'rating'     => isset($row['rating']) ? (int)$row['rating'] : null,
                    'comment'    => $row['comment'] ?? null,
                    'created_at' => $row['created_at'] ?? null,
                ];
                $out[] = $r;
            }
            $res->free();
        }
        $stmt->close();
    } catch (Throwable $t) {
        // ignore and return what we have
    }
    return $out;
}

function fetchReviewsFull(mysqli $conn, string $productCode, int $limit = 10): array {
    return fetchReviewsPreview($conn, $productCode, $limit);
}

// --- Categories (including Medicine) ---
$cats = [
    'Cloth'    => 'cloth.php',
    'Book'     => 'book.php',
    'Software' => 'software.php',
    'Course'   => 'course.php',
    'Medicine' => 'medicine.php',
];

// Cart count
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_map('intval', $_SESSION['cart']));
}

// --- Output start ---
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MyOnlineShop — Futuristic Light</title>

  <!-- Inter font + Boxicons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <!-- External CSS (user placed) -->
  <link rel="stylesheet" href="assets/css/style.css?v=1.4">

  <!-- External JS (deferred) -->
  <script src="assets/js/main.js?v=1.4" defer></script>
</head>
<body>
  <!-- HEADER -->
  <header class="site-header">
    <div class="container header-inner">
      <!-- Shop name (top-left) -->
      <a class="brand" href="index.php" aria-label="MyOnlineShop home">
        <span class="brand-mark">My</span><span class="brand-name">OnlineShop</span>
      </a>

      <!-- Nav (top-right) -->
      <nav class="main-nav" aria-label="Main navigation">
        <ul class="nav-list">
          <li><a href="index.php">Home</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="profile.php">Profile</a></li>
          <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="admin.php">Admin</a></li>
          <?php endif; ?>
          <li><a href="cart.php" class="cart-link" title="Cart">
            <i class='bx bx-cart'></i><span class="cart-count"><?= intval($cartCount) ?></span>
          </a></li>
        </ul>
      </nav>
    </div>
  </header>

  <?php if (!empty($msg)): ?>
    <div class="system-msg container"><?= $msg ?></div>
  <?php endif; ?>

  <main class="site-main container">
    <!-- HERO / Headline -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Shop the future — curated goods & modern picks</h1>
        <p class="hero-sub">Cloth, Books, Software, Courses & Medicine — smart shopping with a light futuristic UI.</p>
        <div class="hero-ctas">
          <a href="#categories" class="btn btn-primary">Browse Categories</a>
          <a href="cart.php" class="btn btn-ghost">View Cart</a>
        </div>
      </div>
      <div class="hero-art" aria-hidden="true"></div>
    </section>

    <!-- CATEGORIES ROW (compact icons) -->
    <section id="categories" class="categories-section" aria-label="Categories">
      <div class="categories-row">
        <?php foreach ($cats as $name => $link): ?>
          <a href="<?= e($link) ?>" class="category-chip" title="<?= e($name) ?>">
            <span class="category-icon">
              <?php
                // category-specific boxicon
                switch ($name) {
                  case 'Cloth':    echo '<i class="bx bxs-t-shirt"></i>'; break;
                  case 'Book':     echo '<i class="bx bxs-book"></i>'; break;
                  case 'Software': echo '<i class="bx bxs-desktop"></i>'; break;
                  case 'Course':   echo '<i class="bx bxs-graduation"></i>'; break;
                  case 'Medicine': echo '<i class="bx bxs-droplet"></i>'; break; // droplet as medicine-friendly icon
                  default:         echo '<i class="bx bxs-category"></i>';
                }
              ?>
            </span>
            <span class="category-label"><?= e($name) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- PRODUCT PREVIEWS: show up to 3 per category -->
    <?php foreach ($cats as $category => $page): ?>
      <?php
        // fetch up to 3 products for this category
        $products = [];
        try {
            $stmt = $conn->prepare("SELECT `code`, `name`, `price`, `discount`, `image` FROM `product` WHERE `category` = ? LIMIT 3");
            $stmt->bind_param('s', $category);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($r = $res->fetch_assoc()) $products[] = $r;
                $res->free();
            }
            $stmt->close();
        } catch (Throwable $t) {
            $products = [];
        }
      ?>

      <?php if (!empty($products)): ?>
        <section class="category-section">
          <div class="category-header">
            <h2 class="category-title"><?= e($category) ?></h2>
            <a class="view-all" href="<?= e($page) ?>">View All <?= e($category) ?>s</a>
          </div>

          <div class="product-row">
            <?php foreach ($products as $row):
                $code = (string)$row['code'];
                $pname = (string)$row['name'];
                $orig = number_format((float)$row['price'], 2);
                $final = $row['discount'] > 0 ? number_format((float)$row['price'] - (float)$row['discount'], 2) : $orig;

                // safe image path (basename) and fallback
                $imgRaw = (string)$row['image'];
                $imgPath = $imgRaw !== '' ? 'assets/uploads/' . basename($imgRaw) : '';
                if ($imgPath === '' || !file_exists($imgPath)) {
                    $imgPath = 'assets/uploads/placeholder.png';
                }

                // ID used for JS modal / hidden reviews
                $safeId = 'reviews-' . preg_replace('/[^A-Za-z0-9_-]/', '', $code);

                // reviews preview (tolerant)
                $reviews_preview = fetchReviewsPreview($conn, $code, 3);
            ?>
              <article class="product-card" data-code="<?= e($code) ?>">
                <?php if (!empty($row['discount']) && $row['discount'] > 0): ?>
                  <div class="product-badge">Sale</div>
                <?php endif; ?>

                <div class="product-media">
                  <img src="<?= e($imgPath) ?>" alt="<?= e($pname) ?>" width="800" height="500" loading="lazy" decoding="async">
                </div>

                <div class="product-body">
                  <h3 class="product-name"><?= e($pname) ?></h3>

                  <div class="product-price">
                    <?php if (!empty($row['discount']) && $row['discount'] > 0): ?>
                      <span class="price-old">৳<?= e($orig) ?></span>
                      <span class="price-new">৳<?= e($final) ?></span>
                    <?php else: ?>
                      <span class="price-new">৳<?= e($orig) ?></span>
                    <?php endif; ?>
                  </div>

                  <div class="product-actions">
                    <!-- Add to cart via POST (CSRF token) -->
                    <form method="POST" class="add-cart-form">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                      <input type="hidden" name="add_to_cart" value="1">
                      <input type="hidden" name="product_code" value="<?= e($code) ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                    </form>

                   <a href="add_to_cart.php?code=<?= urlencode($row['code']) ?>&buy=1" class="btn btn-sm btn-success">Buy Now</a>
                    <button class="btn btn-ghost btn-sm btn-quickview" data-product="<?= e($code) ?>">Quick View</button>
                  </div>

                  <div class="reviews-preview">
                    <?php if (!empty($reviews_preview)): ?>
                      <?php foreach ($reviews_preview as $rp): ?>
                        <div class="review-row">
                          <div class="review-head">
                            <strong><?= e($rp['user_name'] ?? 'Customer') ?></strong>
                            <?php if (!empty($rp['rating'])): ?>
                              <span class="rv-stars"><?= str_repeat('★', max(0, min(5, (int)$rp['rating']))) . str_repeat('☆', 5 - max(0, min(5, (int)$rp['rating']))) ?></span>
                            <?php endif; ?>
                          </div>
                          <div class="review-body"><?= e(mb_strimwidth((string)($rp['comment'] ?? ''), 0, 120, '...')) ?></div>
                        </div>
                      <?php endforeach; ?>
                      <button class="btn-link btn-open-reviews" data-product="<?= e($code) ?>">View all reviews</button>
                    <?php else: ?>
                      <div class="no-reviews">Be the first to review!</div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Hidden full reviews block (server-rendered) -->
                <div id="<?= e($safeId) ?>" class="reviews-hidden" style="display:none" aria-hidden="true">
                  <h3>Reviews for <?= e($pname) ?></h3>
                  <?php
                    $fullR = fetchReviewsFull($conn, $code, 10);
                    if (!empty($fullR)):
                      foreach ($fullR as $fr): ?>
                        <div class="review-full">
                          <div class="review-head">
                            <strong><?= e($fr['user_name'] ?? 'Customer') ?></strong>
                            <time class="rv-date"><?= e($fr['created_at'] ?? '') ?></time>
                            <?php if (!empty($fr['rating'])): ?>
                              <span class="rv-stars"><?= str_repeat('★', max(0, min(5, (int)$fr['rating']))) ?></span>
                            <?php endif; ?>
                          </div>
                          <div class="review-body"><?= nl2br(e((string)$fr['comment'])) ?></div>
                        </div>
                      <?php endforeach;
                    else:
                      echo "<div class='no-reviews'>No reviews yet.</div>";
                    endif;
                  ?>
                  <div class="review-form">
                    <form action="submit_review.php" method="POST" class="review-submit-form">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                      <input type="hidden" name="product_code" value="<?= e($code) ?>">
                      <label>Rating
                        <select name="rating" required>
                          <option value="5">5 — Excellent</option>
                          <option value="4">4 — Good</option>
                          <option value="3">3 — Okay</option>
                          <option value="2">2 — Bad</option>
                          <option value="1">1 — Terrible</option>
                        </select>
                      </label>
                      <label>Your name <input type="text" name="user_name" maxlength="50" placeholder="Anonymous"></label>
                      <label>Comment <textarea name="comment" rows="3" maxlength="800"></textarea></label>
                      <div><button type="submit" class="btn btn-primary">Submit Review</button></div>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endforeach; ?>

  </main>

  <!-- FOOTER (light futuristic) -->
  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="footer-col">
        <a class="brand-footer" href="index.php">MyOnlineShop</a>
        <p class="muted">Curated goods — smart shopping</p>
      </div>

      <div class="footer-col">
        <h4>Company</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms & Conditions</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Categories</h4>
        <ul>
          <?php foreach ($cats as $name => $link): ?>
            <li><a href="<?= e($link) ?>"><?= e($name) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom container">
      <p>&copy; <?= date('Y') ?> MyOnlineShop. All rights reserved.</p>
    </div>
  </footer>

  <!-- Modal (Quick View / Reviews) - content populated by assets/js/main.js -->
  <div id="modal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-panel" role="document">
      <button class="modal-close" aria-label="Close" data-modal-close><i class="bx bx-x"></i></button>
      <div id="modal-content"></div>
    </div>
  </div>

</body>
</html>
