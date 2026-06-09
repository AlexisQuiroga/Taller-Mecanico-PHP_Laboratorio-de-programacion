<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion   = conexion();
$rol        = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h4>

    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo']; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($rol === 'admin'): ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-people fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarUsuarios($conexion); ?></h3>
                    <p class="text-muted mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-car-front fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarVehiculos($conexion); ?></h3>
                    <p class="text-muted mb-0">Total Vehículos</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesPorEstado($conexion, 'pendiente'); ?></h3>
                    <p class="text-muted mb-0">Órdenes Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-check-circle fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesPorEstado($conexion, 'completado'); ?></h3>
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
                        <tr><th>Cliente</th><th>Vehículo</th><th>Tipo</th><th>Estado</th><th>Fecha estimada</th></tr>
                    </thead>
                    <tbody>
                    <?php $res = proximasOrdenesActivas($conexion); while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                        <td><span class="badge bg-<?php echo $row['tipo']==='mecanica'?'info text-dark':'secondary'; ?>"><?php echo ucfirst($row['tipo']); ?></span></td>
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
    <?php $tipo = $rol === 'mecanico' ? 'mecanica' : 'electricidad'; ?>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesTrabajador($conexion, $id_usuario, $tipo, 'pendiente'); ?></h3>
                    <p class="text-muted mb-0">Mis órdenes pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-tools fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesTrabajador($conexion, $id_usuario, $tipo, 'en_proceso'); ?></h3>
                    <p class="text-muted mb-0">Mis órdenes en proceso</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-check2-circle fs-1 text-success"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesCompletadasHoy($conexion, $id_usuario, $tipo); ?></h3>
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
                    <?php $res = ultimasOrdenesTrabajador($conexion, $id_usuario, $tipo); while ($row = mysqli_fetch_assoc($res)): ?>
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
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-car-front fs-1 text-primary"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarVehiculosCliente($conexion, $id_usuario); ?></h3>
                    <p class="text-muted mb-0">Mis vehículos registrados</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="bi bi-clipboard-check fs-1 text-warning"></i>
                    <h3 class="fw-bold mt-2"><?php echo contarOrdenesActivasCliente($conexion, $id_usuario); ?></h3>
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
                    <?php $res = todasOrdenesCliente($conexion, $id_usuario); while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                        <td><span class="badge bg-<?php echo $row['tipo']==='mecanica'?'info text-dark':'secondary'; ?>"><?php echo ucfirst($row['tipo']); ?></span></td>
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
<?php include 'footer.php'; ?>
