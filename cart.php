<?php
// cart.php — robust version: accepts session user id or email, fixes "cart empty" issue
declare(strict_types=1);
session_start();

/* ---------------- DB Connect ---------------- */
$servername = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "shop_db";

$mysqli = new mysqli($servername, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "DB connect error: " . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

/* --------------- Helpers ------------------- */
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/**
 * Resolve product id from either numeric id or product code (string).
 */
function resolveProductId(mysqli $m, $val): ?int {
    if ($val === null) return null;
    $val = trim((string)$val);
    if ($val === '') return null;

    if (ctype_digit($val)) {
        return (int)$val;
    } else {
        $sql = "SELECT id FROM product WHERE code = ? LIMIT 1";
        if ($stmt = $m->prepare($sql)) {
            $stmt->bind_param('s', $val);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return (int)$row['id'];
            }
            $stmt->close();
        }
    }
    return null;
}

/* --------------- Resolve current user ---------------- */
/*
  Your site previously stored different values in $_SESSION['user_id']
  (sometimes numeric id, sometimes email). We'll accept both:
  - if numeric -> use directly
  - else treat as email and look up users.id
*/
if (!isset($_SESSION['user_id'])) {
    // not logged in
    header("Location: login.php");
    exit;
}

$session_user = $_SESSION['user_id'];
$user_id = null;

if (is_int($session_user) || (is_string($session_user) && ctype_digit($session_user))) {
    $user_id = (int)$session_user;
} else {
    // try lookup by email (most common case)
    $maybe_email = trim((string)$session_user);
    if ($maybe_email !== '') {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $maybe_email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $user_id = (int)$row['id'];
            }
            $stmt->close();
        }
    }
}

// If still null, try fallback to another session key (e.g. user_id_int)
if ($user_id === null && isset($_SESSION['uid'])) {
    if (is_int($_SESSION['uid']) || (is_string($_SESSION['uid']) && ctype_digit($_SESSION['uid']))) {
        $user_id = (int)$_SESSION['uid'];
    }
}

// If we cannot resolve, show friendly error
if ($user_id === null) {
    // helpful debug info — remove in production
    echo "<h2>User not resolved from session</h2>";
    echo "<p>Session value: <code>" . e(json_encode($_SESSION['user_id'])) . "</code></p>";
    echo "<p>Please ensure <code>\$_SESSION['user_id']</code> contains the numeric users.id or the user's email.</p>";
    exit;
}

/* --------------- Actions (add/decrease/remove/buy) ---------------- */
function addToCart(mysqli $m, int $user_id, int $product_id, int $amount = 1): void {
    $m->begin_transaction();
    try {
        $sel = $m->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
        $sel->bind_param('ii', $user_id, $product_id);
        $sel->execute();
        $r = $sel->get_result();
        if ($row = $r->fetch_assoc()) {
            $newQ = (int)$row['quantity'] + $amount;
            $upd = $m->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $upd->bind_param('iii', $newQ, $user_id, $product_id);
            $upd->execute();
            $upd->close();
        } else {
            $ins = $m->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $q = max(1, $amount);
            $ins->bind_param('iii', $user_id, $product_id, $q);
            $ins->execute();
            $ins->close();
        }
        $sel->close();
        $m->commit();
    } catch (Throwable $t) {
        $m->rollback();
    }
}

function decreaseCartItem(mysqli $m, int $user_id, int $product_id): void {
    $m->begin_transaction();
    try {
        $sel = $m->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
        $sel->bind_param('ii', $user_id, $product_id);
        $sel->execute();
        $r = $sel->get_result();
        if ($row = $r->fetch_assoc()) {
            $qty = (int)$row['quantity'];
            if ($qty > 1) {
                $newQ = $qty - 1;
                $upd = $m->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $upd->bind_param('iii', $newQ, $user_id, $product_id);
                $upd->execute();
                $upd->close();
            } else {
                $del = $m->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $del->bind_param('ii', $user_id, $product_id);
                $del->execute();
                $del->close();
            }
        }
        $sel->close();
        $m->commit();
    } catch (Throwable $t) {
        $m->rollback();
    }
}

function removeCartItem(mysqli $m, int $user_id, int $product_id): void {
    $stmt = $m->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}

// Accept either numeric id or product code via either ?add=... or ?add&code=...
if (isset($_GET['add'])) {
    $val = $_GET['add'];
    if ($val === '' && isset($_GET['code'])) $val = $_GET['code'];
    $pid = resolveProductId($mysqli, $val);
    if ($pid !== null) addToCart($mysqli, $user_id, $pid, 1);
    header("Location: cart.php");
    exit;
}

if (isset($_GET['buy'])) {
    $val = $_GET['buy'];
    if ($val === '' && isset($_GET['code'])) $val = $_GET['code'];
    $pid = resolveProductId($mysqli, $val);
    if ($pid !== null) {
        addToCart($mysqli, $user_id, $pid, 1);
        // optionally redirect to checkout; here we go to cart
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['decrease'])) {
    $val = $_GET['decrease'];
    if ($val === '' && isset($_GET['code'])) $val = $_GET['code'];
    $pid = resolveProductId($mysqli, $val);
    if ($pid !== null) decreaseCartItem($mysqli, $user_id, $pid);
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove'])) {
    $val = $_GET['remove'];
    if ($val === '' && isset($_GET['code'])) $val = $_GET['code'];
    $pid = resolveProductId($mysqli, $val);
    if ($pid !== null) removeCartItem($mysqli, $user_id, $pid);
    header("Location: cart.php");
    exit;
}

/* --------------- Fetch cart items for this resolved user ---------------- */
$sql = "
 SELECT c.product_id AS pid, c.quantity,
        p.id AS p_id, p.name, p.code, p.price, p.discount, p.image
 FROM cart c
 JOIN product p ON c.product_id = p.id
 WHERE c.user_id = ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* --------------- Render page ------------------ */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Cart — MyOnlineShop</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="assets/css/cart.css?v=1">
  <style>
    /* tiny fallback */
    .cart-img{max-width:100%;height:140px;object-fit:cover;border-radius:6px}
  </style>
</head>
<body>
  <nav class="navbar navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="index.php">MyOnlineShop</a>
      <div>
        <a href="index.php" class="btn btn-outline-primary btn-sm">Home</a>
        <a href="profile.php" class="btn btn-outline-secondary btn-sm">Profile</a>
      </div>
    </div>
  </nav>

  <main class="container my-4">
    <h2>Your Cart</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="row">
        <?php
          $total = 0.0;
          while ($row = $result->fetch_assoc()):
            $price = (float)$row['price'];
            $discount = (float)$row['discount'];
            $discounted = $price * (1 - ($discount / 100));
            $qty = (int)$row['quantity'];
            $subtotal = $discounted * $qty;
            $total += $subtotal;
            $img = 'assets/uploads/placeholder.png';
            if (!empty($row['image'])) {
              $candidate = 'assets/uploads/' . basename($row['image']);
              if (file_exists($candidate)) $img = $candidate;
            }
        ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="<?= e($img) ?>" class="card-img-top cart-img" alt="<?= e($row['name']) ?>">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= e($row['name']) ?></h5>
                <p class="mb-1"><small class="text-muted">Code: <?= e($row['code']) ?></small></p>

                <div class="mb-2">
                  <?php if ($discount > 0): ?>
                    <div><small class="text-muted"><del>৳<?= number_format($price,2) ?></del></small></div>
                    <div class="h5 text-success">৳<?= number_format($discounted,2) ?></div>
                  <?php else: ?>
                    <div class="h5">৳<?= number_format($price,2) ?></div>
                  <?php endif; ?>
                </div>

                <div class="mt-auto">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <a href="?decrease=<?= urlencode((string)$row['pid']) ?>" class="btn btn-sm btn-warning">-</a>
                      <span class="mx-2 font-weight-bold"><?= $qty ?></span>
                      <a href="?add=<?= urlencode((string)$row['pid']) ?>" class="btn btn-sm btn-success">+</a>
                    </div>
                    <div class="text-right">
                      <div class="font-weight-bold">৳<?= number_format($subtotal,2) ?></div>
                      <a href="?remove=<?= urlencode((string)$row['pid']) ?>" class="btn btn-sm btn-outline-danger mt-1">Remove</a>
                    </div>
                  </div>

                  <div class="text-right">
                    <a href="?add=<?= urlencode((string)$row['pid']) ?>" class="btn btn-primary btn-sm">Add One</a>
                    <a href="?buy=<?= urlencode((string)$row['pid']) ?>" class="btn btn-success btn-sm">Buy Now</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Order Summary</h5>
            <small class="text-muted">Includes discounts where applicable</small>
          </div>
          <div class="text-right">
            <div class="h4 mb-1">৳<?= number_format($total,2) ?></div>
            <a href="checkout.php" class="btn btn-lg btn-primary">Proceed to Payment</a>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="text-center py-5">
        <p class="lead">Your cart is empty.</p>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>

        <!-- Helpful debug hint -->
        <div class="mt-4 text-left small text-muted">
          <p><strong>Debug hint:</strong></p>
          <ul>
            <li>Current resolved <code>user_id</code>: <code><?= e((string)$user_id) ?></code></li>
            <li>To inspect cart rows run this SQL in phpMyAdmin or MySQL CLI:
              <pre>SELECT * FROM cart WHERE user_id = <?= (int)$user_id ?>;</pre>
            </li>
            <li>If rows exist but belong to another user id, check what value you set in <code>$_SESSION['user_id']</code>.</li>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
