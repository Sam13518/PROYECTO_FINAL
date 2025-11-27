<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php#Account");
    exit(); 
}
include "conex.php";
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// EDIT
if(isset($_POST['edit'])){
    $id       = $_POST['id'];
    $name     = $_POST['name'];
    $desc     = $_POST['description'];
    $price    = $_POST['price'];
    $stock    = $_POST['stock'];
    $material = $_POST['material'];
    $type     = $_POST['type'];
    $manu     = $_POST['manufacturer'];
    $origin   = $_POST['origin'];
// Actualizar producto
if(isset($_FILES["photo"]["tmp_name"]) && $_FILES["photo"]["tmp_name"] !== ""){
    $photo = file_get_contents($_FILES["photo"]["tmp_name"]);

    $stmt = $conn->prepare("
        UPDATE products 
        SET name=?, description=?, photo=?, price=?, stock=?, material=?, type=?, manufacturer=?, origin=? 
        WHERE id_product=?
    ");

    // Aquí pasamos el BLOB directamente con "s", NO null, NO send_long_data
    $stmt->bind_param("sssdiisssi", $name, $desc, $photo, $price, $stock, $material, $type, $manu, $origin, $id);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("
        UPDATE products 
        SET name=?, description=?, price=?, stock=?, material=?, type=?, manufacturer=?, origin=? 
        WHERE id_product=?
    ");
    $stmt->bind_param("ssdiisssi", $name, $desc, $price, $stock, $material, $type, $manu, $origin, $id);
    $stmt->execute();
    $stmt->close();
}


    // Guardar en historial de edición
    $action_type = 1; // Edit
    $stmt = $conn->prepare("INSERT INTO edit_history (product_name, action_type) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $action_type);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

// DELETE
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    // Obtener el nombre antes de borrar
    $result = $conn->query("SELECT name FROM products WHERE id_product=$id");
    $row = $result->fetch_assoc();
    $name_del = $row['name'] ?? "Deleted Product";

    // Guardar en historial de eliminación
    $action_type = 2; // Delete
    $stmt = $conn->prepare("INSERT INTO edit_history (product_name, action_type) VALUES (?, ?)");
    $stmt->bind_param("si", $name_del, $action_type);
    $stmt->execute();
    $stmt->close();

    // Borrar producto
    $conn->query("DELETE FROM products WHERE id_product=$id");

    header("Location: admin.php");
    exit();
}

// LOGOUT
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php#page-top");
    exit(); 
}

// OBTENER HISTORIAL
$history = $conn->query("SELECT id_edit, product_name, action_type, edited_at FROM edit_history ORDER BY edited_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ÉCLÉ Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
<style>
.hero { height: 40vh; display: flex; align-items: center; justify-content: center; text-align: center; background: linear-gradient(135deg, #ECD9B0, #CFA18C); border-radius: 12px; margin: 2rem 0; }
.hero h1 { color: var(--white); font-size: 2.5rem; margin-bottom: 0.5rem; }
.hero p { color: var(--white); font-size: 1.2rem; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand brand" href="index.php#page-top">ÉCLÉ Admin</a>
    <div class="d-flex ms-auto align-items-center gap-3">
      <span class="text-white"><?= htmlspecialchars($admin_name) ?></span>
      <a href="?logout=1" class="btn btn-cta">Logout</a>
    </div>
  </div>
</nav>

<header class="hero">
  <div>
    <h1>Admin Dashboard</h1>
    <p>Manage products quickly and easily</p>
    <a href="upload_products.php" class="btn btn-cta">Add New Product</a>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php
      $result = $conn->query("SELECT * FROM products ORDER BY id_product DESC");
      while($p = $result->fetch_assoc()):
          $img = $p['photo'] ? 'data:image/jpeg;base64,'.base64_encode($p['photo']) : "https://via.placeholder.com/300";
      ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="product-card">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
            <p class="price">$<?= number_format($p['price'],2) ?></p>
            <p class="card-text">Stock: <?= $p['stock'] ?></p>
            <a href="admin.php?delete=<?= $p['id_product'] ?>" class="btn btn-cta mb-1 w-100" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
            <button class="btn btn-cta w-100" data-bs-toggle="modal" data-bs-target="#editModal"
                onclick="loadEdit(
                  '<?= $p['id_product'] ?>',
                  '<?= htmlspecialchars($p['name']) ?>',
                  `<?= htmlspecialchars($p['description']) ?>`,
                  '<?= $p['price'] ?>',
                  '<?= $p['stock'] ?>',
                  '<?= htmlspecialchars($p['material']) ?>',
                  '<?= htmlspecialchars($p['type']) ?>',
                  '<?= htmlspecialchars($p['manufacturer']) ?>',
                  '<?= htmlspecialchars($p['origin']) ?>'
                )">Edit</button>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Historial de Ediciones -->
<section class="section bg-light">
  <div class="container">
    <h3 class="mb-4">Product Edit History</h3>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Action</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while($h = $history->fetch_assoc()): ?>
          <tr>
            <td><?= $h['id_edit'] ?></td>
            <td><?= htmlspecialchars($h['product_name'] ?? 'Deleted Product') ?></td>
            <td><?= ($h['action_type']==1)?'Edit':(($h['action_type']==2)?'Delete':'Create') ?></td>
            <td><?= $h['edited_at'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Edit -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" enctype="multipart/form-data">
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-header"><h5>Edit Product</h5></div>
      <div class="modal-body">
        <input class="form-control mb-2" id="edit_name" name="name" placeholder="Name">
        <textarea class="form-control mb-2" id="edit_description" name="description" placeholder="Description"></textarea>
        <input class="form-control mb-2" type="number" id="edit_price" name="price" placeholder="Price">
        <input class="form-control mb-2" type="number" id="edit_stock" name="stock" placeholder="Stock">
        <input class="form-control mb-2" id="edit_material" name="material" placeholder="Material">
        <input class="form-control mb-2" id="edit_type" name="type" placeholder="Type">
        <input class="form-control mb-2" id="edit_manufacturer" name="manufacturer" placeholder="Manufacturer">
        <input class="form-control mb-2" id="edit_origin" name="origin" placeholder="Origin">
        <input class="form-control mb-2" type="file" name="photo" accept="image/*">
      </div>
      <div class="modal-footer">
        <button class="btn btn-cta w-100" name="edit">Update Product</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadEdit(id, name, desc, price, stock, material, type, manu, origin){
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_name").value = name;
  document.getElementById("edit_description").value = desc;
  document.getElementById("edit_price").value = price;
  document.getElementById("edit_stock").value = stock;
  document.getElementById("edit_material").value = material;
  document.getElementById("edit_type").value = type;
  document.getElementById("edit_manufacturer").value = manu;
  document.getElementById("edit_origin").value = origin;
}
</script>
</body>
</html>
