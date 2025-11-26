<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: index.php#Account");
    exit();
}

include "conex.php";

// =================== AGREGAR PRODUCTO ===================
if(isset($_POST['add'])){
    $name   = $_POST['name'];
    $desc   = $_POST['description'];
    $price  = $_POST['price'];
    $stock  = $_POST['stock'];
    $material = $_POST['material'];
    $type   = $_POST['type'];
    $manu   = $_POST['manufacturer'];
    $origin = $_POST['origin'];

    $photo = NULL;
    if(isset($_FILES["photo"]["tmp_name"]) && $_FILES["photo"]["tmp_name"] !== ""){
        $photo = file_get_contents($_FILES["photo"]["tmp_name"]);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, photo, price, stock, material, type, manufacturer, origin)
                            VALUES (?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param("ssbdiisss", $name, $desc, $photo, $price, $stock, $material, $type, $manu, $origin);
    $stmt->send_long_data(2, $photo);

    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// =================== EDITAR PRODUCTO ===================
if(isset($_POST['edit'])){
    $id     = $_POST['id'];
    $name   = $_POST['name'];
    $desc   = $_POST['description'];
    $price  = $_POST['price'];
    $stock  = $_POST['stock'];
    $material = $_POST['material'];
    $type   = $_POST['type'];
    $manu   = $_POST['manufacturer'];
    $origin = $_POST['origin'];

    if(isset($_FILES["photo"]["tmp_name"]) && $_FILES["photo"]["tmp_name"] !== ""){
        $photo = file_get_contents($_FILES["photo"]["tmp_name"]);
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, photo=?, price=?, stock=?, material=?, type=?, manufacturer=?, origin=? WHERE id_product=?");
        $stmt->bind_param("ssbdiisssi", $name, $desc, $photo, $price, $stock, $material, $type, $manu, $origin, $id);
        $stmt->send_long_data(2, $photo);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, material=?, type=?, manufacturer=?, origin=? WHERE id_product=?");
        $stmt->bind_param("ssdiisssi", $name, $desc, $price, $stock, $material, $type, $manu, $origin, $id);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// =================== BORRAR ===================
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id_product=$id");
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard — ÉCLÉ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<h2>Administrador — Productos</h2>

<button class="btn btn-dark my-3" data-bs-toggle="modal" data-bs-target="#addModal">Agregar Producto</button>

<table class="table table-bordered table-striped text-center">
<tr>
  <th>ID</th>
  <th>Foto</th>
  <th>Nombre</th>
  <th>Precio</th>
  <th>Stock</th>
  <th>Acciones</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM products ORDER BY id_product DESC");
while($p = $result->fetch_assoc()):
    $img = $p['photo'] ? 'data:image/jpeg;base64,'.base64_encode($p['photo']) : "https://via.placeholder.com/80";
?>
<tr>
  <td><?= $p['id_product'] ?></td>
  <td><img src="<?= $img ?>" width="80"></td>
  <td><?= $p['name'] ?></td>
  <td>$<?= $p['price'] ?></td>
  <td><?= $p['stock'] ?></td>
  <td>
    <button class="btn btn-primary btn-sm"
      data-bs-toggle="modal" data-bs-target="#editModal"
      onclick="loadEdit(
        '<?= $p['id_product'] ?>',
        '<?= $p['name'] ?>',
        `<?= $p['description'] ?>`,
        '<?= $p['price'] ?>',
        '<?= $p['stock'] ?>',
        '<?= $p['material'] ?>',
        '<?= $p['type'] ?>',
        '<?= $p['manufacturer'] ?>',
        '<?= $p['origin'] ?>'
      )">Editar</button>

    <a class="btn btn-danger btn-sm" href="admin.php?delete=<?= $p['id_product'] ?>"
       onclick="return confirm('Seguro deseas borrar este producto?')">
       Borrar
    </a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<!-- ========= MODAL AGREGAR ========= -->
<div class="modal fade" id="addModal">
<div class="modal-dialog modal-lg">
<form class="modal-content" method="POST" enctype="multipart/form-data">
  <div class="modal-header"><h5>Agregar Producto</h5></div>
  <div class="modal-body">
    <input class="form-control mb-2" name="name" placeholder="Nombre" required>
    <textarea class="form-control mb-2" name="description" placeholder="Descripción"></textarea>
    <input class="form-control mb-2" type="number" name="price" placeholder="Precio" required>
    <input class="form-control mb-2" type="number" name="stock" placeholder="Stock" required>
    <input class="form-control mb-2" name="material" placeholder="Material">
    <input class="form-control mb-2" name="type" placeholder="Tipo">
    <input class="form-control mb-2" name="manufacturer" placeholder="Fabricante">
    <input class="form-control mb-2" name="origin" placeholder="Origen">
    <input class="form-control mb-2" type="file" name="photo" accept="image/*" required>
  </div>
  <div class="modal-footer">
    <button class="btn btn-dark" name="add">Guardar</button>
  </div>
</form>
</div>
</div>

<!-- ========= MODAL EDITAR ========= -->
<div class="modal fade" id="editModal">
<div class="modal-dialog modal-lg">
<form class="modal-content" method="POST" enctype="multipart/form-data">
<input type="hidden" id="edit_id" name="id">
<div class="modal-header"><h5>Editar Producto</h5></div>
<div class="modal-body">
  <input class="form-control mb-2" id="edit_name" name="name">
  <textarea class="form-control mb-2" id="edit_description" name="description"></textarea>
  <input class="form-control mb-2" type="number" id="edit_price" name="price">
  <input class="form-control mb-2" type="number" id="edit_stock" name="stock">
  <input class="form-control mb-2" id="edit_material" name="material">
  <input class="form-control mb-2" id="edit_type" name="type">
  <input class="form-control mb-2" id="edit_manufacturer" name="manufacturer">
  <input class="form-control mb-2" id="edit_origin" name="origin">
  <input class="form-control mb-2" type="file" name="photo" accept="image/*">
</div>
<div class="modal-footer">
  <button class="btn btn-primary" name="edit">Actualizar</button>
</div>
</form>
</div>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
