<?php
require_once '../auth/check_session.php';
require_once '../config/db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

if ($rol !== 'admin' && $rol !== 'cliente') {
    header('Location: ../dashboard.php');
    exit();
}

if ($rol === 'admin') {
    $sql = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido) AS duenio
            FROM vehiculos v JOIN usuarios u ON v.id_cliente = u.id ORDER BY v.id DESC";
} else {
    $sql = "SELECT v.* FROM vehiculos v WHERE v.id_cliente = $id_usuario ORDER BY v.id DESC";
}

$vehiculos = mysqli_query($conn, $sql);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-car-front me-2 text-primary"></i>
            <?php echo $rol === 'admin' ? 'Vehículos' : 'Mis Vehículos'; ?>
        </h4>
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nuevo Vehículo</a>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <?php if ($rol === 'admin'): ?><th>Dueño</th><?php endif; ?>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Patente</th>
                            <th>Color</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($v = mysqli_fetch_assoc($vehiculos)): ?>
                    <?php
                    $tiene_activas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_vehiculo = {$v['id']} AND estado NOT IN ('completado','cancelado')"))[0];
                    ?>
                    <tr>
                        <?php if ($rol === 'admin'): ?><td><?php echo htmlspecialchars($v['duenio']); ?></td><?php endif; ?>
                        <td><?php echo htmlspecialchars($v['marca']); ?></td>
                        <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($v['anio']); ?></td>
                        <td><strong><?php echo htmlspecialchars($v['patente']); ?></strong></td>
                        <td><?php echo htmlspecialchars($v['color']); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (!$tiene_activas): ?>
                            <a href="eliminar.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar este vehículo?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-danger" disabled title="Tiene órdenes activas">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
