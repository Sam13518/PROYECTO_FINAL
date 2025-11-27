<?php
session_start();
require_once "conex.php";
$username = $_SESSION["user_name"] ?? null;
$products = [];
$stmt = $conn->prepare("SELECT * FROM products WHERE type = ? ORDER BY created_at ASC");
$typeFilter = "New Collection";
$stmt->bind_param("s", $typeFilter);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $products[] = $row;
}
$stmt->close();
$conn->close();
?> 

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ÉCLÉ — jewelry</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
<link href="styles.css" rel="stylesheet" />
</head>
<body id="page-top" class="bg-white text-dark">

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container">
    <a class="navbar-brand brand" href="#page-top">ÉCLÉ</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="mainNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="#page-top">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="goToCart()">Bag</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= $username ? htmlspecialchars($username) : "User"; ?>
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarUserDropdown">
            <li><a class="dropdown-item" href="#Account" onclick="document.getElementById('loginEmail').focus()">Login / Sign In</a></li>
            <li><a class="dropdown-item" href="newaccount.php">Create Account</a></li>
            <li><a class="dropdown-item" href="./user.php">View Profile</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="#lookbook">Lookbook</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>

<header class="hero d-flex align-items-center">
  <div class="container text-center">
    <h1 class="display-3 title">ÉCLÉ</h1>
    <p class="lead subtitle">Exquisite Jewelry · Champagne Shine · A Timeless Masterpiece</p>
    <a class="btn btn-primary btn-cta" href="collection.php">View Collection </a>
  </div>
</header>

<section id="collection" class="section">
  <div class="container">
    <div class="row align-items-center mb-5">
      <div class="col-lg-6">
        <h2 class="section-title">New Collection</h2>
        <p class="section-desc">Exquisite fine jewelry, crafted with precision and refined sophistication.</p>
            <div class="text-center mt-3"><a class="btn btn-outline-dark btn-sm" href="collection.php">Buy the collection</a></div>
      </div>
      <div class="col-lg-6 text-end d-none d-lg-block">
        <img src="assets/NC0.jpg" alt="joya hero" class="img-fluid rounded-3 shadow-sm" style="max-width:420px;">
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <?php foreach($products as $product): ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card product-card">
          <?php if(!empty($product['photo'])): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($product['photo']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="card-img-top">
          <?php else: ?>
            <img src="assets/default.jpg" alt="No image" class="card-img-top">
          <?php endif; ?>
          <div class="card-body text-center">
            <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
            <p class="price">$<?= number_format($product['price'],2); ?> MXN</p>
            <a href="collection.php#newcollection" class="btn btn-primary btn-cta w-100">
  View Collection 🛍️
</a>

          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(count($products) === 0): ?>
        <p class="text-center">No products available in New Collection yet.</p>
      <?php endif; ?>
    </div>
  </div>
</section>



<!-- About Section -->
<section id="about" class="section bg-warning bg-opacity-10">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h2 class="section-title text-center text-lg-start">About Us</h2>
        <p class="section-desc text-center text-lg-start">
          ÉCLÉ creates jewelry with soul: ethical materials, local workshops, and champagne gold finishes. Each piece is handcrafted by skilled artisans, designed to endure the test of time.
        </p>   </div>
      <div class="col-lg-6 text-center text-lg-end">
        <img src="assets/about.jpg" alt="Artisan at work" class="img-fluid rounded-3 shadow-sm" style="max-width:420px;">
      </div> </div></div></section>

<!-- Lookbook  -->
<section id="lookbook" class="section"> <div class="container">
    <h2 class="section-title text-center mb-4">Lookbook</h2>
    <div id="lookbookCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner"> <div class="carousel-item active">  <div class="row g-0">  <div class="col-4">
              <img src="assets/look1.jpg" class="look-img d-block w-100" alt=""></div>
            <div class="col-4"> <img src="assets/look2.jpg" class="look-img d-block w-100" alt=""> </div>
            <div class="col-4"> <img src="assets/look3.jpg" class="look-img d-block w-100" alt="">
            </div></div>        </div>
        <div class="carousel-item">   <div class="row g-0"> <div class="col-4">
            <img src="assets/look4.jpg" class="look-img d-block w-100" alt=""> </div>
            <div class="col-4"> <img src="assets/look5.jpg" class="look-img d-block w-100" alt="">  </div>
            <div class="col-4">  <img src="assets/look6.jpg" class="look-img d-block w-100" alt=""></div></div></div>  
          </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#lookbookCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#lookbookCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>  </div>
</section>

<!-- Account -->  
<section id="Account" class="section bg-warning bg-opacity-10">
  <div class="container">
    <h2 class="section-title text-center mb-4">Account</h2>
    <div class="row justify-content-center"> 
        
        <div id="user-dashboard" class="text-center d-none">
            <h2 class="section-title">Welcome Back</h2>
            <p class="section-desc">Hello, <span id="display-username" style="font-weight:700;">User</span>. Your personal gallery awaits.</p>
            <div class="mt-4">
              <button class="btn btn-outline-dark me-2">My Orders</button>
              <button onclick="logoutUser()" class="btn btn-cta">Log Out</button>
            </div>  </div>

        <div id="auth-forms" class="row g-4 justify-content-center w-100 align-items-stretch">  <div class="col-md-5 d-none d-md-flex"> 
                <img src="assets/acc.jpg" alt="Illustration of jewelry and user account" class="img-fluid rounded-3 shadow-sm w-100 h-100" 
                    style="object-fit: cover; border: none; max-width: none;"> </div>

            <div class="col-md-5"> <div class="card product-card h-100">  <div class="card-body text-start p-4">
                  <h3 class="card-title text-center mb-4">Sign In</h3>
                  
                  <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="mb-3">
                      <label class="form-label small text-muted">Email</label>
                      <input type="email" class="form-control form-control-lg" id="loginEmail" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label small text-muted">Password</label>
                      <input type="password" class="form-control form-control-lg" id="loginPass" required>
                    </div>       
                    <div class="text-center mt-4">
                      <button type="submit" class="btn btn-outline-dark w-100 btn-lg">Sign In</button>
                      <p id="loginError" class="text-danger small mt-2"></p>
                    </div>
                  </form>

                  <hr class="my-4">
                  <div class="text-center">
                      <p class="small text-muted mb-3">Don't have an account?</p>
                      <a href="newaccount.php" class="btn btn-cta w-100 btn-lg">Create Account</a>
                  </div></div></div></div></div></div>
  </div>
</section>
    
<!-- Contact -->
<section id="contact" class="section">
  <div class="container text-center">
    <h2 class="section-title">Contact</h2>
    <p class="section-desc"> · Bespoke Commissions & Inquiries: contact@ecle-jewelry.com · </p>
    <p class="section-desc"> ·  +52 55 1234 5678 ·  </p>
    <a href="mailto:contacto@ecle-jewelry.com" class="btn btn-cta">Send a mail</a>
  </div> </section>
<!-- footer -->
<footer class="footer text-center">
  <div class="container">  <p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence of elegance</p> </div> </footer>
<script>
function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("loginEmail").value.trim();
  const pass = document.getElementById("loginPass").value.trim();
  const errorText = document.getElementById("loginError");

  const formData = new FormData();
  formData.append("action", "login");
  formData.append("email", email);
  formData.append("password", pass);

  fetch("auth.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // 🔹 Redirige según rol
      if (data.role === "admin") {
        window.location.href = "admin.php";
      } else {
        window.location.href = "user.php";
      }
    } else {
      errorText.textContent = data.message;
    }
  })
  .catch(() => {
    errorText.textContent = "Connection error. Please try again.";
  });
}
</script>

<script>
function goToCart() {
  fetch("auth.php?checkSession=true")
    .then(res => res.json())
    .then(data => {
      if (data.logged) {
        new bootstrap.Modal(document.getElementById("cartModal")).show();
      } else {
        alert("Debes iniciar sesión para ver el carrito.");
        window.location.href = "#Account";
      }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
