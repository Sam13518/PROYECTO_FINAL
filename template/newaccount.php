<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ÉCLÉ — Create Account</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
        <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#Bag">Bag</a></li>
        
        <li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
  <?php echo $_SESSION['user_name'] ?? 'User'; ?>
</a>
          <ul class="dropdown-menu" aria-labelledby="navbarUserDropdown">
            <li><a class="dropdown-item" href="index.php#Account">Login / Sign In</a></li> 
            <li><a class="dropdown-item" href="./user.php">View Profile</a></li>
          </ul></li>
        <li class="nav-item"><a class="nav-link" href="index.php#lookbook">Lookbook</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
      </ul>
</div></div></nav>

<section id="Register" class="section bg-warning bg-opacity-10 section-full-height">
  <div class="container">
    <h2 class="section-title text-center mb-5 mt-5">Create Your ÉCLÉ Account</h2>
    <div class="row g-4 justify-content-center align-items-stretch">
      
      <div class="col-md-5 d-none d-md-flex"> <img src="assets/acc2.jpg" class="img-fluid rounded-3 shadow-sm w-100 h-100" >  </div>

      <div class="col-md-5">
        <div class="card product-card h-100">
          <div class="card-body text-start p-4">
            <h3 class="card-title text-center mb-4">Register Now</h3>
            
  <form id="registerForm" onsubmit="handleRegister(event)">
  <div class="mb-3">
    <label class="form-label small text-muted">Full Name</label>
    <input type="text" class="form-control form-control-lg" id="regName" required>
  </div>
  <div class="mb-3">
    <label class="form-label small text-muted">Email</label>
    <input type="email" class="form-control form-control-lg" id="regEmail" required>
  </div>
  <div class="mb-3">
    <label class="form-label small text-muted">Password</label>
    <input type="password" class="form-control form-control-lg" id="regPass" required>
  </div>
  <div class="mb-3">    <label class="form-label small text-muted">Birth Date</label>
    <input type="date" class="form-control form-control-lg" id="regBirthDate" required>
  </div>
  <div class="mb-3">
     <label class="form-label small text-muted">Shipping Address</label>
       <textarea class="form-control form-control-lg" id="regAddress" rows="2" required></textarea>   </div>
<div class="mb-3">
 <label class="form-label small text-muted">Card Number</label>
<input type="text" class="form-control form-control-lg" id="regCardNumber" placeholder="XXXX XXXX XXXX XXXX" required>
 </div>
  <div class="text-center mt-5">
    <button type="submit" class="btn btn-cta w-100 btn-lg">Create Account</button>
    <p id="regError" class="text-danger small mt-2"></p>
  </div>  </div></div> </div></div></div>
</section>

<footer class="footer text-center mt-0"><div class="container">  <p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence of elegance</p> </div> </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
<script>
function handleRegister(e) {
    e.preventDefault();
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const pass = document.getElementById('regPass').value;
    const birthDate = document.getElementById('regBirthDate').value;
    const address = document.getElementById('regAddress').value; 
    const cardNumber = document.getElementById('regCardNumber').value;
    const errorMsg = document.getElementById('regError');
    
    const formData = new FormData();
    formData.append('action', 'register');
    formData.append('name', name);
    formData.append('email', email);
    formData.append('password', pass); 
    formData.append('birth_date', birthDate);
    formData.append('address', address); 
    formData.append('card_number', cardNumber);

    errorMsg.textContent = "Processing...";
    errorMsg.classList.remove('text-danger', 'text-success');

    fetch('./auth.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            errorMsg.textContent = data.message + " Redirecting to login...";
            errorMsg.classList.add('text-success');
            setTimeout(() => {
                window.location.href = "index.php#Account";
            }, 1500);
        } else {
            errorMsg.textContent = "Error: " + data.message;
            errorMsg.classList.add('text-danger');
        }
    })
    .catch(error => {
        errorMsg.textContent = "Error: Could not connect to the server.";
        errorMsg.classList.add('text-danger');
    });
}
</script>
</html>
