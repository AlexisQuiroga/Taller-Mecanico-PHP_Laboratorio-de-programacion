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

$vehiculos = mostrarVehiculos($conexion, $rol, $id_usuario);
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-car-front me-2 text-primary"></i>
            <?php echo $rol === 'admin' ? 'Vehículos' : 'Mis Vehículos'; ?>
        </h4>
        <a href="crearvehiculo.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nuevo Vehículo</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])){ ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php }; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <?php if ($rol === 'admin'){ ?><th>Dueño</th><?php }; ?>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Patente</th>
                            <th>Color</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($v = mysqli_fetch_array($vehiculos)) { ?>
                    <?php
                    $tiene_activas = tieneOrdenesActivasVehiculo($conexion, $v['id']);
                    ?>
                    <tr>
                        <?php if ($rol === 'admin'){ ?><td><?php echo $v['duenio']; ?></td><?php }; ?>
                        <td><?php echo $v['marca']; ?></td>
                        <td><?php echo $v['modelo']; ?></td>
                        <td><?php echo $v['anio']; ?></td>
                        <td><strong><?php echo $v['patente']; ?></strong></td>
                        <td><?php echo $v['color']; ?></td>
                        <td>
                            <a href="modificarvehiculo.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (!$tiene_activas){ ?>
                            <a href="eliminarvehiculo.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar este vehículo?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php } else { ?>
                            <button class="btn btn-sm btn-outline-danger" disabled title="Tiene órdenes activas">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php }; ?>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
