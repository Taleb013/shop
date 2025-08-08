<?php
// add_to_cart.php
declare(strict_types=1);
session_start();

// --- DB connection (adjust if you use config.php) ---
$host = '127.0.0.1';
$db   = 'shop_db';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "DB connect error: " . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

// --- Logging helper (creates folder if missing) ---
function log_action(string $line): void {
    $logdir = __DIR__ . '/assets/logs';
    if (!is_dir($logdir)) @mkdir($logdir, 0755, true);
    $file = $logdir . '/cart_actions.log';
    $t = date('Y-m-d H:i:s');
    @file_put_contents($file, "[$t] " . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// --- helper to escape output ---
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// --- Resolve current user id (accept numeric id or email in session) ---
if (!isset($_SESSION['user_id'])) {
    // not logged in
    log_action("NO_SESSION -- incoming=" . json_encode($_REQUEST));
    header("Location: login.php");
    exit;
}

$session_user = $_SESSION['user_id'];
$user_id = null;

if (is_int($session_user) || (is_string($session_user) && ctype_digit($session_user))) {
    $user_id = (int)$session_user;
} else {
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

// final fallback
if ($user_id === null && isset($_SESSION['uid']) && (is_int($_SESSION['uid']) || ctype_digit((string)$_SESSION['uid']))) {
    $user_id = (int)$_SESSION['uid'];
}

// If still null -> abort with debug log
if ($user_id === null) {
    log_action("UNRESOLVED_USER -- session=" . json_encode($_SESSION['user_id']));
    echo "Unable to resolve your user account (session). Please login again.";
    exit;
}

// --- Get incoming product identifier (id or code) ---
$prodId = null;
$prodCode = null;

if (isset($_REQUEST['id']) && ctype_digit((string)$_REQUEST['id'])) {
    $prodId = (int)$_REQUEST['id'];
} elseif (!empty($_REQUEST['code'])) {
    $prodCode = trim((string)$_REQUEST['code']);
}

// If no product info, log and go back
if ($prodId === null && $prodCode === null) {
    log_action("MISSING_PRODUCT -- user_id={$user_id} request=" . json_encode($_REQUEST));
    // safe redirect back
    $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $back");
    exit;
}

// Resolve product id if we have code
if ($prodId === null && $prodCode !== null) {
    $stmt = $mysqli->prepare("SELECT id, code FROM product WHERE code = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $prodCode);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $prodId = (int)$row['id'];
            $prodCode = (string)$row['code'];
        }
        $stmt->close();
    }
    if ($prodId === null) {
        log_action("PRODUCT_NOT_FOUND_BY_CODE -- code={$prodCode} user_id={$user_id}");
        $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header("Location: $back");
        exit;
    }
}

// --- Insert or update cart row (safe prepared statements) ---
try {
    $mysqli->begin_transaction();

    // check existing
    $sel = $mysqli->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1");
    $sel->bind_param('ii', $user_id, $prodId);
    $sel->execute();
    $r = $sel->get_result();
    if ($row = $r->fetch_assoc()) {
        $newQty = (int)$row['quantity'] + 1;
        $upd = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $upd->bind_param('iii', $newQty, $user_id, $prodId);
        $upd->execute();
        $upd->close();
        log_action("UPDATED_CART user_id={$user_id} product_id={$prodId} new_qty={$newQty}");
    } else {
        $ins = $mysqli->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $q = 1;
        $ins->bind_param('iii', $user_id, $prodId, $q);
        $ins->execute();
        $ins->close();
        log_action("INSERTED_CART user_id={$user_id} product_id={$prodId} qty=1");
    }
    $sel->close();
    $mysqli->commit();

} catch (Throwable $t) {
    $mysqli->rollback();
    $err = $mysqli->error ?: $t->getMessage();
    log_action("DB_ERROR user_id={$user_id} product_id={$prodId} error=" . str_replace(["\r","\n"], [' ',' '], $err));
    // for safety do not show raw error to user
    $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $back");
    exit;
}

// Redirect back where the request came from (or to cart)
$back = $_SERVER['HTTP_REFERER'] ?? 'cart.php';
header("Location: $back");
exit;
