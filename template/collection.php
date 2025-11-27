<?php
session_start();
require_once "conex.php";

$userId = $_SESSION["user_id"] ?? null;
$username = $_SESSION["user_name"] ?? null;

// Traer productos
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at ASC");
while($row = $result->fetch_assoc()){
    $products[] = $row;
}

// Agrupar por categoría
$productsByType = [];
foreach($products as $product){
    $type = $product['type'] ?: "Other";
    $productsByType[$type][] = $product;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ÉCLÉ — Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
<link href="styles.css" rel="stylesheet" />
</head>
<body id="page-top" class="bg-white text-dark">

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container">
    <a class="navbar-brand brand" href="index.php#page-top">ÉCLÉ</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="mainNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php#page-top">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="goToCart()">Bag</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" data-bs-toggle="dropdown">
            <?= $username ? htmlspecialchars($username) : "User"; ?>
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarUserDropdown">
            <li><a class="dropdown-item" href="user.php">View Profile</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<section id="collection" class="section mt-5 pt-5">
  <div class="container">
    <h2 class="section-title text-center mb-4">Collection</h2>

    <?php foreach($productsByType as $type => $typeProducts): ?>
      <h3 class="mt-5 mb-3"><?= htmlspecialchars($type); ?></h3>
      <div class="row g-4 justify-content-center">
        <?php foreach($typeProducts as $product): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card product-card h-100">
            <?php if(!empty($product['photo'])): ?>
              <img src="data:image/jpeg;base64,<?= base64_encode($product['photo']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="card-img-top">
            <?php else: ?>
              <img src="assets/default.jpg" alt="No image" class="card-img-top">
            <?php endif; ?>
            <div class="card-body text-center">
              <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
              <p class="price">$<?= number_format($product['price'],2); ?> MXN</p>
              <!-- Botón con id incluido -->
              <button class="btn btn-primary btn-cta w-100"
                onclick="addToCart('<?= addslashes($product['name']); ?>', <?= $product['price']; ?>, <?= $product['stock']; ?>, <?= $product['id_product']; ?>)">
                Add to Bag
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Carrito Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Your Bag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="cartItems"></div>
        <p class="mt-3 fw-bold" id="cartTotal"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-dark" data-bs-dismiss="modal">Continue Shopping</button>
        <button class="btn btn-primary" onclick="completePurchase()" <?= $userId ? '' : 'disabled title="You must log in to checkout"' ?>>Checkout</button>
      </div>
    </div>
  </div>
</div>

<footer class="footer text-center mt-5">
  <div class="container">
    <p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence of elegance</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Variable para saber si el usuario está logueado
const isLoggedIn = <?= $userId ? 'true' : 'false'; ?>;
</script>

<script>
let cart = JSON.parse(localStorage.getItem("cart")) || [];

// Guardar carrito
function saveCart() {
  localStorage.setItem("cart", JSON.stringify(cart));
}

// Renderizar carrito
function renderCart() {
  const container = document.getElementById("cartItems");
  const totalElem = document.getElementById("cartTotal");
  container.innerHTML = "";
  let total = 0;

  if(cart.length === 0){
    container.innerHTML = "<p>Your bag is empty.</p>";
    totalElem.textContent = "";
    return;
  }

  cart.forEach(item => total += item.price * item.qty);
  cart.forEach((item, index) => {
    container.innerHTML += `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span>${item.name} x ${item.qty}</span>
        <span>$${(item.price * item.qty).toFixed(2)}</span>
        <button class="btn btn-sm btn-danger ms-2" onclick="removeFromCart(${index})">X</button>
      </div>`;
  });

  totalElem.textContent = "Total: $" + total.toFixed(2);
}

// Añadir al carrito con id
function addToCart(name, price, stock, id){
  const existing = cart.find(item => item.id === id);
  if(existing) existing.qty++;
  else cart.push({id, name, price, qty:1});
  saveCart();
  renderCart();
  new bootstrap.Modal(document.getElementById("cartModal")).show();
}

// Eliminar del carrito
function removeFromCart(index){
  cart.splice(index,1);
  saveCart();
  renderCart();
}

// Mostrar carrito
function goToCart(){
  renderCart();
  new bootstrap.Modal(document.getElementById("cartModal")).show();
}

// Completar compra guardando en BD
function completePurchase(){
  if(cart.length === 0){
    return alert("Your bag is empty");
  }

  if(!isLoggedIn){
    return alert("You must be logged in to complete the purchase.");
  }

  fetch('user.php?checkout=1', {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify({cart})
  })
  .then(res => res.json())
  .then(data => {
      if(data.status === 'success'){
          cart = [];
          saveCart();
          renderCart();
          alert("Purchase completed and saved in database!");
      } else {
          alert("Error: " + data.msg);
      }
  })
  .catch(err => alert("Error: " + err.message));
}


// Inicializar carrito al cargar
renderCart();
</script>
</body>
</html>
