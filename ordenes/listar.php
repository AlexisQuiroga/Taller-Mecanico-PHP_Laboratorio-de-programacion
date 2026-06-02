<?php
require_once '../auth/check_session.php';
require_once '../config/db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

$filtro_estado = isset($_GET['estado']) ? mysqli_real_escape_string($conn, $_GET['estado']) : '';

$estados_validos = ['pendiente','en_proceso','completado','cancelado'];

function badge_estado($estado) {
    $clases = ['pendiente'=>'warning','en_proceso'=>'primary','completado'=>'success','cancelado'=>'danger'];
    $labels = ['pendiente'=>'Pendiente','en_proceso'=>'En Proceso','completado'=>'Completado','cancelado'=>'Cancelado'];
    $clase = $clases[$estado] ?? 'secondary';
    $label = $labels[$estado] ?? $estado;
    return "<span class=\"badge bg-{$clase}\">{$label}</span>";
}

if ($rol === 'admin') {
    $where = $filtro_estado && in_array($filtro_estado, $estados_validos) ? "WHERE ot.estado = '$filtro_estado'" : '';
    $sql = "SELECT ot.*,
            CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
            CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
            CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
            FROM ordenes_trabajo ot
            JOIN usuarios uc ON ot.id_cliente = uc.id
            JOIN vehiculos v ON ot.id_vehiculo = v.id
            JOIN usuarios ut ON ot.id_trabajador = ut.id
            $where ORDER BY ot.created_at DESC";
} elseif ($rol === 'mecanico') {
    $tipo = 'mecanica';
    $where = "WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'";
    if ($filtro_estado && in_array($filtro_estado, $estados_validos)) $where .= " AND ot.estado = '$filtro_estado'";
    $sql = "SELECT ot.*,
            CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
            CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
            FROM ordenes_trabajo ot
            JOIN usuarios uc ON ot.id_cliente = uc.id
            JOIN vehiculos v ON ot.id_vehiculo = v.id
            $where ORDER BY ot.created_at DESC";
} elseif ($rol === 'electricista') {
    $tipo = 'electricidad';
    $where = "WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'";
    if ($filtro_estado && in_array($filtro_estado, $estados_validos)) $where .= " AND ot.estado = '$filtro_estado'";
    $sql = "SELECT ot.*,
            CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
            CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
            FROM ordenes_trabajo ot
            JOIN usuarios uc ON ot.id_cliente = uc.id
            JOIN vehiculos v ON ot.id_vehiculo = v.id
            $where ORDER BY ot.created_at DESC";
} elseif ($rol === 'cliente') {
    $where = "WHERE ot.id_cliente = $id_usuario";
    if ($filtro_estado && in_array($filtro_estado, $estados_validos)) $where .= " AND ot.estado = '$filtro_estado'";
    $sql = "SELECT ot.*,
            CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
            CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
            FROM ordenes_trabajo ot
            JOIN vehiculos v ON ot.id_vehiculo = v.id
            JOIN usuarios ut ON ot.id_trabajador = ut.id
            $where ORDER BY ot.created_at DESC";
} else {
    header('Location: ../dashboard.php');
    exit();
}

$ordenes = mysqli_query($conn, $sql);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>
            <?php echo ($rol === 'admin') ? 'Órdenes de Trabajo' : 'Mis Órdenes'; ?>
        </h4>
        <?php if ($rol === 'admin' || $rol === 'cliente'): ?>
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nueva Orden</a>
        <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="" class="d-flex gap-2 flex-wrap align-items-end">
                <div>
                    <label class="form-label mb-1 small">Filtrar por estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="pendiente" <?php echo $filtro_estado==='pendiente'?'selected':''; ?>>Pendiente</option>
                        <option value="en_proceso" <?php echo $filtro_estado==='en_proceso'?'selected':''; ?>>En Proceso</option>
                        <option value="completado" <?php echo $filtro_estado==='completado'?'selected':''; ?>>Completado</option>
                        <option value="cancelado" <?php echo $filtro_estado==='cancelado'?'selected':''; ?>>Cancelado</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                <a href="listar.php" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <?php if ($rol === 'admin'): ?>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Trabajador</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>F. Ingreso</th>
                            <th>F. Estimada</th>
                            <th>Acciones</th>
                            <?php elseif ($rol === 'mecanico' || $rol === 'electricista'): ?>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>F. Estimada</th>
                            <th>Acción</th>
                            <?php else: ?>
                            <th>Vehículo</th>
                            <th>Tipo</th>
                            <th>Trabajador</th>
                            <th>Estado</th>
                            <th>F. Estimada</th>
                            <th>Acción</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($o = mysqli_fetch_assoc($ordenes)): ?>
                    <tr>
                        <?php if ($rol === 'admin'): ?>
                        <td><?php echo htmlspecialchars($o['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($o['vehiculo']); ?></td>
                        <td><?php echo htmlspecialchars($o['trabajador']); ?></td>
                        <td><span class="badge bg-<?php echo $o['tipo']==='mecanica'?'info text-dark':'secondary'; ?>"><?php echo ucfirst($o['tipo']); ?></span></td>
                        <td><?php echo badge_estado($o['estado']); ?></td>
                        <td><?php echo htmlspecialchars($o['fecha_ingreso']); ?></td>
                        <td><?php echo htmlspecialchars($o['fecha_estimada']); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminar.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar esta orden de trabajo?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                        <?php elseif ($rol === 'mecanico' || $rol === 'electricista'): ?>
                        <td><?php echo htmlspecialchars($o['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($o['vehiculo']); ?></td>
                        <td><?php echo htmlspecialchars(substr($o['descripcion'], 0, 50)) . (strlen($o['descripcion'])>50?'...':''); ?></td>
                        <td><?php echo badge_estado($o['estado']); ?></td>
                        <td><?php echo htmlspecialchars($o['fecha_estimada']); ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Estado
                            </a>
                        </td>
                        <?php else: ?>
                        <td><?php echo htmlspecialchars($o['vehiculo']); ?></td>
                        <td><span class="badge bg-<?php echo $o['tipo']==='mecanica'?'info text-dark':'secondary'; ?>"><?php echo ucfirst($o['tipo']); ?></span></td>
                        <td><?php echo htmlspecialchars($o['trabajador']); ?></td>
                        <td><?php echo badge_estado($o['estado']); ?></td>
                        <td><?php echo htmlspecialchars($o['fecha_estimada']); ?></td>
                        <td>
                            <?php if ($o['estado'] === 'pendiente'): ?>
                            <a href="eliminar.php?id=<?php echo $o['id']; ?>&cancelar=1" class="btn btn-sm btn-outline-warning"
                               onclick="return confirm('¿Cancelar esta orden?')">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
