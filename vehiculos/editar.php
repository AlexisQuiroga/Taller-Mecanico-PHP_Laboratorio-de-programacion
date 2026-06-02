<?php
require_once '../check_session.php';
require_once '../db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

if ($rol !== 'admin' && $rol !== 'cliente') {
    header('Location: ../dashboard.php');
    exit();
}

$id = (int)$_GET['id'];

if ($rol === 'admin') {
    $vehiculo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vehiculos WHERE id = $id"));
} else {
    $vehiculo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vehiculos WHERE id = $id AND id_cliente = $id_usuario"));
}

if (!$vehiculo) {
    header('Location: listar.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = mysqli_real_escape_string($conn, trim($_POST['marca']));
    $modelo = mysqli_real_escape_string($conn, trim($_POST['modelo']));
    $anio = (int)$_POST['anio'];
    $patente = mysqli_real_escape_string($conn, strtoupper(trim($_POST['patente'])));
    $color = mysqli_real_escape_string($conn, trim($_POST['color']));

    if (!$marca || !$modelo || !$anio || !$patente || !$color) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM vehiculos WHERE patente = '$patente' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $error = 'La patente ya está registrada en otro vehículo.';
        } else {
            $sql = "UPDATE vehiculos SET marca='$marca', modelo='$modelo', anio=$anio, patente='$patente', color='$color' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['mensaje'] = 'Vehículo actualizado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: listar.php');
                exit();
            } else {
                $error = 'Error al actualizar el vehículo.';
            }
        }
    }
}

$datos = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $vehiculo;
?>
<?php include '../header.php'; ?>
<?php include '../sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Vehículo</h4>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" required value="<?php echo htmlspecialchars($datos['marca']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" required value="<?php echo htmlspecialchars($datos['modelo']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" class="form-control" min="1900" max="2026" required value="<?php echo htmlspecialchars($datos['anio']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Patente</label>
                        <input type="text" name="patente" class="form-control" required style="text-transform:uppercase" value="<?php echo htmlspecialchars($datos['patente']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" required value="<?php echo htmlspecialchars($datos['color']); ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
