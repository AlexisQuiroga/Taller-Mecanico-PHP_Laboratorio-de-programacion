<?php
require_once '../auth/check_session.php';
require_once '../config/db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

$id = (int)$_GET['id'];

if ($rol === 'admin') {
    $orden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ordenes_trabajo WHERE id = $id"));
} elseif ($rol === 'mecanico') {
    $orden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'mecanica'"));
} elseif ($rol === 'electricista') {
    $orden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'electricidad'"));
} else {
    header('Location: ../dashboard.php');
    exit();
}

if (!$orden) {
    header('Location: listar.php');
    exit();
}

$error = '';
$estados_validos = ['pendiente','en_proceso','completado','cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($rol === 'admin') {
        $id_vehiculo = (int)$_POST['id_vehiculo'];
        $id_cliente = (int)$_POST['id_cliente'];
        $id_trabajador = (int)$_POST['id_trabajador'];
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        $descripcion = mysqli_real_escape_string($conn, trim($_POST['descripcion']));
        $estado = mysqli_real_escape_string($conn, $_POST['estado']);
        $fecha_ingreso = mysqli_real_escape_string($conn, $_POST['fecha_ingreso']);
        $fecha_estimada = mysqli_real_escape_string($conn, $_POST['fecha_estimada']);

        if ($fecha_estimada < $fecha_ingreso) {
            $error = 'La fecha estimada no puede ser anterior a la fecha de ingreso.';
        } elseif (!in_array($estado, $estados_validos)) {
            $error = 'Estado no válido.';
        } else {
            $sql = "UPDATE ordenes_trabajo SET id_vehiculo=$id_vehiculo, id_cliente=$id_cliente, id_trabajador=$id_trabajador,
                    tipo='$tipo', descripcion='$descripcion', estado='$estado',
                    fecha_ingreso='$fecha_ingreso', fecha_estimada='$fecha_estimada' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['mensaje'] = 'Orden actualizada correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: listar.php');
                exit();
            } else {
                $error = 'Error al actualizar la orden.';
            }
        }
    } else {
        $estado = mysqli_real_escape_string($conn, $_POST['estado']);
        if (!in_array($estado, $estados_validos)) {
            $error = 'Estado no válido.';
        } else {
            if (mysqli_query($conn, "UPDATE ordenes_trabajo SET estado='$estado' WHERE id = $id")) {
                $_SESSION['mensaje'] = 'Estado de la orden actualizado.';
                $_SESSION['tipo'] = 'success';
                header('Location: listar.php');
                exit();
            } else {
                $error = 'Error al actualizar el estado.';
            }
        }
    }
}

if ($rol === 'admin') {
    $clientes = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'cliente' ORDER BY nombre");
    $vehiculos_cliente = mysqli_query($conn, "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = {$orden['id_cliente']}");
    $mecanicos = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'mecanico' ORDER BY nombre");
    $electricistas = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'electricista' ORDER BY nombre");
}

$datos = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $orden;
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Orden #<?php echo $id; ?></h4>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <?php if ($rol === 'admin'): ?>
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <select name="id_cliente" class="form-select" required id="sel_cliente">
                            <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $datos['id_cliente']==$c['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehículo</label>
                        <select name="id_vehiculo" class="form-select" required id="sel_vehiculo">
                            <?php while ($v = mysqli_fetch_assoc($vehiculos_cliente)): ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo $datos['id_vehiculo']==$v['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($v['marca'].' '.$v['modelo'].' ('.$v['patente'].')'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required id="sel_tipo">
                            <option value="mecanica" <?php echo $datos['tipo']==='mecanica'?'selected':''; ?>>Mecánica</option>
                            <option value="electricidad" <?php echo $datos['tipo']==='electricidad'?'selected':''; ?>>Electricidad</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trabajador</label>
                        <select name="id_trabajador" class="form-select" required id="sel_trabajador">
                            <?php
                            $todos_trabajadores = mysqli_query($conn, "SELECT id, nombre, apellido, rol FROM usuarios WHERE rol IN ('mecanico','electricista') ORDER BY nombre");
                            while ($t = mysqli_fetch_assoc($todos_trabajadores)):
                            ?>
                            <option value="<?php echo $t['id']; ?>" data-rol="<?php echo $t['rol']; ?>"
                                <?php echo $datos['id_trabajador']==$t['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($t['nombre'].' '.$t['apellido'].' ('.ucfirst($t['rol']).')'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($datos['descripcion']); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="pendiente" <?php echo $datos['estado']==='pendiente'?'selected':''; ?>>Pendiente</option>
                            <option value="en_proceso" <?php echo $datos['estado']==='en_proceso'?'selected':''; ?>>En Proceso</option>
                            <option value="completado" <?php echo $datos['estado']==='completado'?'selected':''; ?>>Completado</option>
                            <option value="cancelado" <?php echo $datos['estado']==='cancelado'?'selected':''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control" required value="<?php echo htmlspecialchars($datos['fecha_ingreso']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" required value="<?php echo htmlspecialchars($datos['fecha_estimada']); ?>">
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <strong>Vehículo:</strong>
                            <?php
                            $v_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT marca, modelo, patente FROM vehiculos WHERE id = {$orden['id_vehiculo']}"));
                            echo htmlspecialchars($v_info['marca'].' '.$v_info['modelo'].' ('.$v_info['patente'].')');
                            ?>
                            &nbsp;|&nbsp;
                            <strong>Tipo:</strong> <?php echo ucfirst($orden['tipo']); ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <p class="form-control-plaintext border rounded p-2 bg-light"><?php echo htmlspecialchars($orden['descripcion']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cambiar estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="pendiente" <?php echo $orden['estado']==='pendiente'?'selected':''; ?>>Pendiente</option>
                            <option value="en_proceso" <?php echo $orden['estado']==='en_proceso'?'selected':''; ?>>En Proceso</option>
                            <option value="completado" <?php echo $orden['estado']==='completado'?'selected':''; ?>>Completado</option>
                            <option value="cancelado" <?php echo $orden['estado']==='cancelado'?'selected':''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <?php endif; ?>
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
<?php if ($rol === 'admin'): ?>
<script>
document.getElementById('sel_cliente').addEventListener('change', function() {
    const clienteId = this.value;
    const selVehiculo = document.getElementById('sel_vehiculo');
    if (!clienteId) return;
    fetch('../vehiculos/ajax_vehiculos.php?id_cliente=' + clienteId)
        .then(r => r.json())
        .then(data => {
            selVehiculo.innerHTML = '';
            data.forEach(v => {
                selVehiculo.innerHTML += `<option value="${v.id}">${v.marca} ${v.modelo} (${v.patente})</option>`;
            });
        });
});
</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
