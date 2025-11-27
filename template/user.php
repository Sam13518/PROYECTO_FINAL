<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: index.php#Account");
  exit();}

require_once "conex.php"; 
$userId = $_SESSION["user_id"];
// GET ORDERS
if(isset($_GET['get_orders']) && $_GET['get_orders'] == 1){
    $stmt = $conn->prepare("SELECT id_buy, buy_date, total_amount FROM buy_history WHERE id_user = ? ORDER BY buy_date DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while($row = $result->fetch_assoc()){
        $orders[] = $row;}
    header('Content-Type: application/json');
    echo json_encode($orders);
    exit(); }

// CHECKOUT
if(isset($_GET['checkout']) && $_GET['checkout'] == 1){
    $data = json_decode(file_get_contents('php://input'), true);
    $cart = $data['cart'] ?? [];
    if(empty($cart)){
        echo json_encode(['status'=>'error','msg'=>'El carrito está vacío']);
        exit(); }
    $total = 0;
    foreach($cart as $item){
        $id = (int)($item['id'] ?? 0);
        $qty = (int)($item['qty'] ?? $item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        if($id <= 0 || $qty <= 0 || $price <= 0){
            echo json_encode(['status'=>'error','msg'=>'Datos del carrito inválidos']);
            exit();}
        $total += $price * $qty; }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO buy_history (id_user, total_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $userId, $total);
        $stmt->execute();
        $buyId = $stmt->insert_id;
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO buy_details (id_buy, id_product, quantity, price) VALUES (?, ?, ?, ?)");
        foreach($cart as $item){
            $id = (int)($item['id']);
            $qty = (int)($item['qty'] ?? $item['quantity']);
            $price = (float)($item['price']);
            $stmt->bind_param("iiid", $buyId, $id, $qty, $price);
            $stmt->execute(); }
        $stmt->close();
        $conn->commit();
        echo json_encode(['status'=>'success']);
    } catch(Exception $e){
        $conn->rollback();
        echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);}
    exit(); }

// UPDATE PROFILE
if(isset($_GET['update_profile']) && $_GET['update_profile'] == 1){
    $name      = $_POST["profileName"] ?? "";
    $birthDate = $_POST["profileBirthDate"] ?? "";
    $card      = $_POST["profileCardNumber"] ?? "";
    $address   = $_POST["profileAddress"] ?? "";
    $pass      = $_POST["profilePass"] ?? "";
    if ($pass == "") {
        $stmt = $conn->prepare("UPDATE users SET name=?, birth_date=?, card_number=?, postal_address=? WHERE id_user=?");
        $stmt->bind_param("ssssi", $name, $birthDate, $card, $address, $userId);
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, birth_date=?, card_number=?, postal_address=?, password=? WHERE id_user=?");
        $stmt->bind_param("sssssi", $name, $birthDate, $card, $address, $hashed, $userId);
    }
    if ($stmt->execute()) {
        $_SESSION["user_name"] = $name; // actualizar sesión
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $conn->error;}
    $stmt->close();
    exit();
}

// GET USER
$stmt = $conn->prepare("SELECT name, email, birth_date, card_number, postal_address, created_at FROM users WHERE id_user = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userName   = $user["name"];
$userEmail  = $user["email"];
$birthDate  = $user["birth_date"];
$cardNumber = $user["card_number"];
$address    = $user["postal_address"];
$createdAt  = $user["created_at"];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ÉCLÉ — User Profile</title>
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
        <li class="nav-item"><a class="nav-link" href="user.php#Bag">Bag</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle active" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown">
            <?php echo htmlspecialchars($userName); ?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="user.php">View Profile</a></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="logoutUser()">Logout</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="index.php#lookbook">Lookbook</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>

<section id="UserProfile" class="section bg-warning bg-opacity-10 section-full-height">
  <div class="container">
    <h2 class="section-title text-center mb-5 mt-5">My Account Dashboard</h2>
    <div class="row g-4 justify-content-center"> <div class="col-md-3">
        <div class="card product-card h-100 p-3">
            <h4 class="h5 mb-3">Hello, <span id="userNameDisplay"><?php echo $userName; ?></span></h4>
            <div class="nav flex-column nav-pills">
                <a class="nav-link active" data-bs-toggle="pill" data-bs-target="#v-pills-profile">Profile Details</a>
              <button class="nav-link btn btn-light w-100 text-center mb-2" onclick="showOrders()"> 📦 My Orders 📦 </button>
              <button class="nav-link btn btn-light w-100 text-center text-danger" onclick="logoutUser()"> 👋 Logout</button>
            </div>  </div> </div>

      <div class="col-md-7">  <div class="card product-card h-100">
          <div class="card-body p-4">   <div class="tab-content">
                <div class="tab-pane fade show active" id="v-pills-profile">
                    <h3 class="card-title mb-4">Account Information</h3>
                    <form id="profileForm" onsubmit="updateProfile(event)">
                        <div class="mb-3">
                            <label class="form-label small text-muted">User ID</label>
                            <input type="text" class="form-control" value="<?php echo $userId; ?>" disabled>
                        </div>
                        <div class="mb-3">
                          <label class="form-label small text-muted">Name</label>
                          <input type="text" class="form-control" id="profileName" value="<?php echo $userName; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label small text-muted">Email</label>
                          <input type="email" class="form-control" value="<?php echo $userEmail; ?>" disabled>
                        </div>
                        <div class="mb-3">
                          <label class="form-label small text-muted">Birth Date</label>
                          <input type="date" class="form-control" id="profileBirthDate" value="<?php echo $birthDate; ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label small text-muted">Password</label>
                          <input type="password" class="form-control" id="profilePass" placeholder="Enter new password to change"> </div>
                        <div class="mb-3">
                          <label class="form-label small text-muted">Card Number</label>
                          <input type="text" class="form-control" id="profileCardNumber" value="<?php echo $cardNumber; ?>"> </div>                   
                        <div class="mb-3">
                          <label class="form-label small text-muted">Shipping Address</label>
                          <textarea class="form-control" id="profileAddress" rows="3"><?php echo $address; ?></textarea> </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted">Account Creation Date</label>
                            <input type="text" class="form-control" value="<?php echo $createdAt; ?>" disabled>
                        </div>
                        <button type="submit" class="btn btn-cta">Save Changes</button>
                    </form>
                </div> </div> </div> </div> </div> </div> </div>
</section>

<section id="Bag" class="container mt-4">
  <h2>🛍 My Bag 🛍</h2>
  <div id="cartItems"></div>
  <p class="mt-3 fw-bold" id="cartTotal"></p>
  <button class="btn btn-success mt-3" onclick="checkout()">Checkout</button>
</section>

<section id="Orders" class="container mt-4">
  <h2>📦 My Orders 📦</h2>
  <div id="ordersItems"><p class="text-muted">Cargando pedidos...</p></div>
</section>
<script>
// Bag
function loadCart() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  let html = "";
  let total = 0;

  if (cart.length === 0) {
    document.getElementById("cartItems").innerHTML = "<p>Tu carrito está vacío.</p>";
    document.getElementById("cartTotal").innerHTML = "";
    return;  }

  cart.forEach(item => {
    const qty = item.quantity ?? item.qty ?? 1;
    const price = parseFloat(item.price) || 0;
    html += `<div class="d-flex justify-content-between border-bottom py-2">
      <span>${item.name} x ${qty}</span>
      <span>$${(price * qty).toFixed(2)}</span>
    </div>`;
    total += price * qty;  });

  document.getElementById("cartItems").innerHTML = html;
  document.getElementById("cartTotal").innerHTML = "Total: $" + total.toFixed(2);
} loadCart();

// LOGOUT
function logoutUser() {
  fetch("auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=logout"
  }).then(() => {
      localStorage.removeItem("cart");
      window.location.href = "index.php#Account";  });
}

// CHECKOUT
function checkout() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  if(cart.length === 0) return alert("Tu carrito está vacío.");

  const cartData = cart.map(item => ({
    id: item.id,
    qty: item.quantity ?? item.qty ?? 1,
    price: parseFloat(item.price) || 0   }));

  fetch('user.php?checkout=1', {
    method: 'POST',
    headers: { 'Content-Type':'application/json' },
    body: JSON.stringify({cart: cartData})  })
  .then(res => res.json())
  .then(data => {
    if(data.status === 'success'){
      localStorage.removeItem('cart');
      alert("Compra realizada con éxito!");
      loadCart();
      showOrders();
    } else { alert("Error: " + data.msg);
    }  });  }

// ORDERS
function showOrders() {
  const ordersContainer = document.getElementById("ordersItems");
  fetch('user.php?get_orders=1')
    .then(res => res.json())
    .then(data => {
      if(data.length === 0){
        ordersContainer.innerHTML = "<p>No recent orders found.</p>";
      } else {
        let html = '<div class="list-group">';
        data.forEach(order => {
          html += `<div class="list-group-item d-flex justify-content-between align-items-center mb-2">
            <div>
              <h6 class="mb-1">Order #${order.id_buy}</h6>
              <p class="mb-0 small text-muted">Date: ${order.buy_date} | Total: $${parseFloat(order.total_amount).toFixed(2)}</p>
            </div>
          </div>`;
        });
        html += '</div>';
        ordersContainer.innerHTML = html;  }
      document.getElementById("Orders").scrollIntoView({behavior: "smooth"});
    });
}
document.addEventListener("DOMContentLoaded", () => {
  showOrders();
});


// UPDATE PROFILE
function updateProfile(event) {
  event.preventDefault();
  const formData = new FormData();
  formData.append("profileName", document.getElementById("profileName").value);
  formData.append("profileBirthDate", document.getElementById("profileBirthDate").value);
  formData.append("profileCardNumber", document.getElementById("profileCardNumber").value);
  formData.append("profileAddress", document.getElementById("profileAddress").value);
  formData.append("profilePass", document.getElementById("profilePass").value);
  fetch("user.php?update_profile=1", {
    method: "POST",
    body: formData   })
  .then(res => res.text())
  .then(response => {
    alert("Profile updated successfully!");
    location.reload();  })
  .catch(error => console.error("Error:", error)); }
</script>

<footer class="footer text-center mt-0">
  <div class="container">  
    <p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence de la elegancia</p> 
  </div> 
</footer>

</body>
</html>
