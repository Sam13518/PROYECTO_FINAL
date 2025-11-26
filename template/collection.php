<?php
session_start();
require_once "conex.php";

$userLoggedIn = isset($_SESSION["user_id"]);
$userName = $_SESSION["user_name"] ?? "";

// Traer todos los productos
$products = [];
if ($result = $conn->query("SELECT * FROM products ORDER BY created_at DESC")) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Agrupar productos por categoría
$productsByType = [];
foreach ($products as $product) {
    $type = $product['type'] ?: "All"; 
    $productsByType[$type][] = $product;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ÉCLÉ — Colección</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand brand" href="index.php">ÉCLÉ</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#Bag">Bag</a></li>
        <?php if($userLoggedIn): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" data-bs-toggle="dropdown">
            <?php echo htmlspecialchars($userName); ?>
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarUserDropdown">
            <li><a class="dropdown-item" href="user.php">View Profile</a></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="logoutUser()">Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="index.php#Account">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<section class="section">
  <div class="container">
    <section class="section text-center">
      <h2 class="section-title">Collection</h2>
    </section>

    <?php foreach ($productsByType as $type => $typeProducts): ?>
    <h3 class="mt-5 mb-3"><?php echo htmlspecialchars($type); ?></h3>
    <div class="row g-4 justify-content-center">
      <?php foreach ($typeProducts as $product): ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="product-card">
          <?php
          if(!empty($product['photo'])) {
              $imgBase64 = base64_encode($product['photo']);
              echo "<img src='data:image/jpeg;base64,$imgBase64' alt='".htmlspecialchars($product['name'])."'>";
          } else {
              echo "<img src='assets/default.jpg' alt='No image'>";
          }
          ?>
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
            <p class="price">$<?php echo number_format($product['price'],2); ?> MXN</p>
            <button class="btn-cta w-100">Agregar al carrito</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función de logout
function logoutUser() {
  fetch("auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=logout"
  }).then(() => {
      localStorage.removeItem("cart");
      window.location.href = "index.php#Account";
  });
}
</script>

<footer class="footer text-center">
  <div class="container">
    <p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence of elegance</p>
  </div>
</footer>
</body>
</html>
