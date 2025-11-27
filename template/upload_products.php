<?php
session_start();
$admin_name = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'User';

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php#page-top");
    exit();
}

require_once "conex.php";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = $_POST['name'];
    $description  = $_POST['description'];
    $price        = $_POST['price'];
    $stock        = $_POST['stock'];
    $material     = $_POST['material'];
    $type         = $_POST['type'];
    $manufacturer = $_POST['manufacturer'];
    $origin       = $_POST['origin'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $imgData = file_get_contents($_FILES['photo']['tmp_name']);

        // Insertar producto
        $stmt = $conn->prepare("INSERT INTO products (name, description, photo, price, stock, material, type, manufacturer, origin)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
       $stmt->bind_param(
    "ssbdiisss",
    $name,
    $description,
    $imgData, // aquí pones directamente los datos de la imagen
    $price,
    $stock,
    $material,
    $type,
    $manufacturer,
    $origin
);
$stmt->send_long_data(2, $imgData);


        if ($stmt->execute()) {
            $message = "Product '$name' uploaded successfully.";

            // Guardar en historial de creación (action_type = 3 = Create)
            $stmt2 = $conn->prepare("INSERT INTO edit_history (product_name, action_type) VALUES (?, 3)");
            $stmt2->bind_param("s", $name);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Error uploading the image.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ÉCLÉ — Upload Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="styles.css" rel="stylesheet" />
</head>
<body class="bg-white text-dark">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand brand" href="index.php#page-top">ÉCLÉ</a>
    <div class="d-flex ms-auto align-items-center gap-3">
      <span class="text-white"><?= htmlspecialchars($admin_name) ?></span>
      <?php if(isset($_SESSION['admin_id'])): ?>
      <a href="admin.php" class="btn btn-cta">Admin Dashboard</a>
      <?php endif; ?>
      <a href="?logout=1" class="btn btn-cta">Logout</a></div></div>
</nav>

<!-- Form  -->
<section class="section py-5">
  <div class="container">
    <?php if($message): ?>
        <div class="alert alert-info text-center"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width:600px;">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required></div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" required></textarea></div>
        <div class="mb-3">
            <label>Price</label>
            <input type="number" step="0.01" name="price" class="form-control" required></div>
        <div class="mb-3">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" required></div>
        <div class="mb-3">
            <label>Material</label>
            <input type="text" name="material" class="form-control" required></div>
        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="">--Please select a category--</option>
                <option value="New Collection">New Collection</option>
                <option value="Ring">Rings</option>
                <option value="Bracelet">Bracelets</option>
                <option value="Necklace">Necklaces</option>
                <option value="Earrings">Earrings</option>
                <option value="Limited Edition">Limited Edition</option>
            </select></div>
        <div class="mb-3">
            <label>Manufacturer</label>
            <input type="text" name="manufacturer" class="form-control" required></div>
        <div class="mb-3">
            <label>Origin</label>
            <input type="text" name="origin" class="form-control" required></div>
        <div class="mb-3">
            <label>Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*" required></div>
        <button type="submit" class="btn btn-primary w-100">Upload Product</button>
    </form>
  </div>
</section>
<footer class="footer text-center py-3"><div class="container"><p class="mb-0 small">© 2025 ÉCLÉ Jewelry — The essence of elegance</p></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
