<?php
session_start();
// Database credentials
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "shop_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Courses - Zen Web Store</title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Animate.css -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
  />

  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      animation: fadeIn 1s ease-in-out;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #444;
      font-size: 2.5em;
      animation: fadeIn 1s ease-in-out;
    }
    .search-bar {
      margin-bottom: 20px;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    /* Responsive table for small screens */
    @media (max-width: 768px) {
      table thead { display: none; }
      table, table tbody, table tr, table td { display: block; width: 100%; }
      table tr { margin-bottom: 1rem; }
      table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
      }
      table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 45%;
        padding-left: 1rem;
        font-weight: bold;
        text-align: left;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="animate__animated animate__fadeInDown">All Courses</h1>

    <div class="search-bar">
      <form class="d-flex" method="GET" action="">
        <input
          class="form-control me-2"
          type="text"
          name="q"
          placeholder="Search courses..."
        />
        <button class="btn btn-primary" type="submit">🔍</button>
      </form>
    </div>

    <?php
    $searchQuery = isset($_GET['q'])
        ? $conn->real_escape_string($_GET['q'])
        : '';
    if ($searchQuery !== '') {
        $sql = "SELECT * FROM product
                WHERE category='Course'
                  AND (name LIKE '%$searchQuery%'
                       OR code LIKE '%$searchQuery%')";
    } else {
        $sql = "SELECT * FROM product WHERE category='Course'";
    }
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0):
    ?>
      <div class="table-responsive animate__animated animate__fadeInUp">
        <table class="table table-bordered align-middle">
          <thead class="table-primary text-uppercase">
            <tr>
              <th>Name</th>
              <th>Code</th>
              <th>Price</th>
              <th>Discount</th>
              <th>Image</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = $result->fetch_assoc()):
            $original    = $row['price'];
            $discountPct = intval($row['discount']);
            $finalPrice  = $original * (1 - $discountPct/100);
          ?>
            <tr class="animate__animated animate__fadeInUp">
              <td data-label="Name"><?php echo htmlspecialchars($row['name']); ?></td>
              <td data-label="Code"><?php echo htmlspecialchars($row['code']); ?></td>
              <td data-label="Price">৳<?php echo number_format($finalPrice,2); ?></td>
              <td data-label="Discount"><?php echo $discountPct; ?>%</td>
              <td data-label="Image">
                <img
                  src="uploads/<?php echo htmlspecialchars($row['image']); ?>"
                  alt="<?php echo htmlspecialchars($row['name']); ?>"
                  style="max-width:80px; border-radius:4px;"
                />
              </td>
              <td data-label="Actions">
                <a
                  href="cart.php?buy&code=<?php echo urlencode($row['code']); ?>"
                  class="btn btn-success btn-sm mb-1"
                >
                  Buy Now
                </a>
                <a
                  href="cart.php?add&code=<?php echo urlencode($row['code']); ?>"
                  class="btn btn-primary btn-sm"
                >
                  Add to Cart
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center fst-italic">No courses found.</p>
    <?php
    endif;
    $conn->close();
    ?>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
