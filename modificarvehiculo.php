<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

if ($rol !== 'admin' && $rol !== 'cliente') {
    header('Location: dashboard.php');
    exit();
}

$id = (int)$_GET['id'];
$vehiculo = obtenerVehiculo($conexion, $id, $rol !== 'admin' ? $id_usuario : 0);

if (!$vehiculo) {
    header('Location: tablavehiculos.php');
    exit();
}

$error = '';

if (!empty($_POST['modificar'])) {
    $marca   = trim($_POST['marca']);
    $modelo  = trim($_POST['modelo']);
    $anio    = (int)$_POST['anio'];
    $patente = strtoupper(trim($_POST['patente']));
    $color   = trim($_POST['color']);

    if (!$marca || !$modelo || !$anio || !$patente || !$color) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $verificar = verificarPatente($conexion, $patente, $id);
        if (mysqli_num_rows($verificar) > 0) {
            $error = 'La patente ya está registrada en otro vehículo.';
        } else {
            if (actualizarVehiculo($conexion, $id, $marca, $modelo, $anio, $patente, $color)) {
                $_SESSION['mensaje'] = 'Vehículo actualizado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablavehiculos.php');
                exit();
            } else {
                $error = 'Error al actualizar el vehículo.';
            }
        }
    }
}

if (!empty($_POST['modificar'])) {
    $datos = $_POST;
} else {
    $datos = $vehiculo;
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Vehículo</h4>
    </div>
    <?php if ($error){ ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php }; ?>
    <div class="card border-0 shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" required value="<?php echo $datos['marca']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" required value="<?php echo $datos['modelo']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" class="form-control" min="1900" max="2026" required value="<?php echo $datos['anio']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Patente</label>
                        <input type="text" name="patente" class="form-control" required style="text-transform:uppercase" value="<?php echo $datos['patente']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" required value="<?php echo $datos['color']; ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" name="modificar" value="1" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="tablavehiculos.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
