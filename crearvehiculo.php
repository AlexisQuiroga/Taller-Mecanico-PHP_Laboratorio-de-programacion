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

$clientes = obtenerClientes($conexion);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $rol === 'admin' ? (int)$_POST['id_cliente'] : $id_usuario;
    $marca   = trim($_POST['marca']);
    $modelo  = trim($_POST['modelo']);
    $anio    = (int)$_POST['anio'];
    $patente = strtoupper(trim($_POST['patente']));
    $color   = trim($_POST['color']);

    if (!$id_cliente || !$marca || !$modelo || !$anio || !$patente || !$color) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $verificar = verificarPatente($conexion, $patente);
        if (mysqli_num_rows($verificar) > 0) {
            $error = 'La patente ya está registrada.';
        } else {
            if (insertarVehiculo($conexion, $id_cliente, $marca, $modelo, $anio, $patente, $color)) {
                $_SESSION['mensaje'] = 'Vehículo registrado correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablavehiculos.php');
                exit();
            } else {
                $error = 'Error al registrar el vehículo.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-car-front me-2 text-primary"></i>Nuevo Vehículo</h4>
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
                    <?php if ($rol === 'admin'): ?>
                    <div class="col-12">
                        <label class="form-label">Cliente dueño</label>
                        <select name="id_cliente" class="form-select" required>
                            <option value="">Seleccionar cliente...</option>
                            <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['id_cliente']) && $_POST['id_cliente']==$c['id']) ? 'selected':''; ?>>
                                <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" required
                            value="<?php echo isset($_POST['marca']) ? htmlspecialchars($_POST['marca']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" required
                            value="<?php echo isset($_POST['modelo']) ? htmlspecialchars($_POST['modelo']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" class="form-control" min="1900" max="2026" required
                            value="<?php echo isset($_POST['anio']) ? htmlspecialchars($_POST['anio']) : date('Y'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Patente</label>
                        <input type="text" name="patente" class="form-control" required
                            style="text-transform:uppercase"
                            value="<?php echo isset($_POST['patente']) ? htmlspecialchars($_POST['patente']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" required
                            value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
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
