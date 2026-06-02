<?php
require_once 'auth/check_session.php';
require_once 'config/db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

function badge_estado($estado) {
    $clases = [
        'pendiente' => 'warning',
        'en_proceso' => 'primary',
        'completado' => 'success',
        'cancelado' => 'danger'
    ];
    $labels = [
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En Proceso',
        'completado' => 'Completado',
        'cancelado' => 'Cancelado'
    ];
    $clase = $clases[$estado] ?? 'secondary';
    $label = $labels[$estado] ?? $estado;
    return "<span class=\"badge bg-{$clase}\">{$label}</span>";
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="container-fluid p-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h4>

    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($rol === 'admin'): ?>
    <?php
    $total_usuarios = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM usuarios"))[0];
    $total_vehiculos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vehiculos"))[0];
    $ordenes_pendientes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = 'pendiente'"))[0];
    $ordenes_completadas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = 'completado'"))[0];
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-people fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo $total_usuarios; ?></h3>
                    <p class="text-muted mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-car-front fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo $total_vehiculos; ?></h3>
                    <p class="text-muted mb-0">Total Vehículos</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo $ordenes_pendientes; ?></h3>
                    <p class="text-muted mb-0">Órdenes Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo $ordenes_completadas; ?></h3>
                    <p class="text-muted mb-0">Órdenes Completadas</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Próximas órdenes activas</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Fecha estimada</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
                            CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
                            FROM ordenes_trabajo ot
                            JOIN usuarios u ON ot.id_cliente = u.id
                            JOIN vehiculos v ON ot.id_vehiculo = v.id
                            WHERE ot.estado NOT IN ('completado','cancelado')
                            ORDER BY ot.fecha_estimada ASC LIMIT 5";
                    $res = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                        <td><span class="badge bg-<?php echo $row['tipo'] === 'mecanica' ? 'info text-dark' : 'secondary'; ?>"><?php echo ucfirst($row['tipo']); ?></span></td>
                        <td><?php echo badge_estado($row['estado']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_estimada']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php elseif ($rol === 'mecanico' || $rol === 'electricista'): ?>
    <?php
    $tipo = $rol === 'mecanico' ? 'mecanica' : 'electricidad';
    $pend = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = 'pendiente'"))[0];
    $proc = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = 'en_proceso'"))[0];
    $comp_hoy = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = 'completado' AND DATE(created_at) = CURDATE()"))[0];
    ?>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo $pend; ?></h3>
                    <p class="text-muted mb-0">Mis órdenes pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-tools fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo $proc; ?></h3>
                    <p class="text-muted mb-0">Mis órdenes en proceso</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-check2-circle fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo $comp_hoy; ?></h3>
                    <p class="text-muted mb-0">Completadas hoy</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Mis 5 órdenes más recientes</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>Cliente</th><th>Vehículo</th><th>Estado</th><th>Fecha estimada</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
                            CONCAT(v.marca, ' ', v.modelo) AS vehiculo
                            FROM ordenes_trabajo ot
                            JOIN usuarios u ON ot.id_cliente = u.id
                            JOIN vehiculos v ON ot.id_vehiculo = v.id
                            WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'
                            ORDER BY ot.created_at DESC LIMIT 5";
                    $res = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                        <td><?php echo badge_estado($row['estado']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_estimada']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php elseif ($rol === 'cliente'): ?>
    <?php
    $mis_vehiculos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vehiculos WHERE id_cliente = $id_usuario"))[0];
    $mis_activas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_cliente = $id_usuario AND estado NOT IN ('completado','cancelado')"))[0];
    ?>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-car-front fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo $mis_vehiculos; ?></h3>
                    <p class="text-muted mb-0">Mis vehículos registrados</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-clipboard-check fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo $mis_activas; ?></h3>
                    <p class="text-muted mb-0">Mis órdenes activas</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Todas mis órdenes</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>Vehículo</th><th>Tipo</th><th>Estado</th><th>Fecha ingreso</th><th>Fecha estimada</th></tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT ot.*, CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
                            FROM ordenes_trabajo ot
                            JOIN vehiculos v ON ot.id_vehiculo = v.id
                            WHERE ot.id_cliente = $id_usuario
                            ORDER BY ot.created_at DESC";
                    $res = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                        <td><span class="badge bg-<?php echo $row['tipo'] === 'mecanica' ? 'info text-dark' : 'secondary'; ?>"><?php echo ucfirst($row['tipo']); ?></span></td>
                        <td><?php echo badge_estado($row['estado']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_ingreso']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_estimada']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
