<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

$id = (int)$_GET['id'];

if (!in_array($rol, ['admin','mecanico','electricista'])) {
    header('Location: dashboard.php');
    exit();
}

$orden = obtenerOrden($conexion, $id, $rol, $id_usuario);
if (!$orden) {
    header('Location: tablaordenes.php');
    exit();
}

$error = '';
$estados_validos = ['pendiente','en_proceso','completado','cancelado'];

if (!empty($_POST['modificar'])) {
    if ($rol === 'admin') {
        $id_vehiculo    = (int)$_POST['id_vehiculo'];
        $id_cliente     = (int)$_POST['id_cliente'];
        $id_trabajador  = (int)$_POST['id_trabajador'];
        $tipo           = $_POST['tipo'];
        $descripcion    = trim($_POST['descripcion']);
        $estado         = $_POST['estado'];
        $fecha_ingreso  = $_POST['fecha_ingreso'];
        $fecha_estimada = $_POST['fecha_estimada'];

        if ($fecha_estimada < $fecha_ingreso) {
            $error = 'La fecha estimada no puede ser anterior a la fecha de ingreso.';
        } elseif (!in_array($estado, $estados_validos)) {
            $error = 'Estado no válido.';
        } else {
            if (actualizarOrden($conexion, $id, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada)) {
                $_SESSION['mensaje'] = 'Orden actualizada correctamente.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablaordenes.php');
                exit();
            } else {
                $error = 'Error al actualizar la orden.';
            }
        }
    } else {
        $estado = $_POST['estado'];
        if (!in_array($estado, $estados_validos)) {
            $error = 'Estado no válido.';
        } else {
            if (actualizarEstadoOrden($conexion, $id, $estado)) {
                $_SESSION['mensaje'] = 'Estado de la orden actualizado.';
                $_SESSION['tipo'] = 'success';
                header('Location: tablaordenes.php');
                exit();
            } else {
                $error = 'Error al actualizar el estado.';
            }
        }
    }
}

if ($rol === 'admin') {
    $clientes          = obtenerClientes($conexion);
    $vehiculos_cliente = obtenerVehiculosPorCliente($conexion, $orden['id_cliente']);
    $trabajadores      = obtenerTrabajadores($conexion);
}

if (!empty($_POST['modificar'])) {
    $datos = $_POST;
} else {
    $datos = $orden;
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Editar Orden #<?php echo $id; ?></h4>
    </div>
    <?php if ($error){ ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php }; ?>
    <div class="card border-0 shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <?php if ($rol === 'admin'){ ?>
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <select name="id_cliente" class="form-select" required id="sel_cliente">
                            <?php while ($c = mysqli_fetch_array($clientes)) { ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $datos['id_cliente']==$c['id']?'selected':''; ?>>
                                <?php echo $c['nombre'] . ' ' . $c['apellido']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehículo</label>
                        <select name="id_vehiculo" class="form-select" required id="sel_vehiculo">
                            <?php foreach ($vehiculos_cliente as $v) { ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo $datos['id_vehiculo']==$v['id']?'selected':''; ?>>
                                <?php echo $v['marca'].' '.$v['modelo'].' ('.$v['patente'].')'; ?>
                            </option>
                            <?php } ?>
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
                            <?php while ($t = mysqli_fetch_array($trabajadores)) { ?>
                            <option value="<?php echo $t['id']; ?>" data-rol="<?php echo $t['rol']; ?>"
                                <?php echo $datos['id_trabajador']==$t['id']?'selected':''; ?>>
                                <?php echo $t['nombre'].' '.$t['apellido'].' ('.ucfirst($t['rol']).')'; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required><?php echo $datos['descripcion']; ?></textarea>
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
                        <input type="date" name="fecha_ingreso" class="form-control" required value="<?php echo $datos['fecha_ingreso']; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" required value="<?php echo $datos['fecha_estimada']; ?>">
                    </div>
                    <?php } else { ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <strong>Vehículo:</strong>
                            <?php
                            $v_info = obtenerInfoVehiculo($conexion, $orden['id_vehiculo']);
                            echo $v_info['marca'].' '.$v_info['modelo'].' ('.$v_info['patente'].')';
                            ?>
                            &nbsp;|&nbsp;
                            <strong>Tipo:</strong> <?php echo ucfirst($orden['tipo']); ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <p class="form-control-plaintext border rounded p-2 bg-light"><?php echo $orden['descripcion']; ?></p>
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
                    <?php }; ?>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" name="modificar" value="1" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="tablaordenes.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($rol === 'admin'){ ?>
<script>
document.getElementById('sel_cliente').addEventListener('change', function() {
    const idCliente = this.value;
    const selVehiculo = document.getElementById('sel_vehiculo');
    if (!idCliente) return;
    fetch('vehiculosporcliente.php?id_cliente=' + idCliente)
        .then(r => r.json())
        .then(datos => {
            selVehiculo.innerHTML = '';
            datos.forEach(v => {
                selVehiculo.innerHTML += `<option value="${v.id}">${v.marca} ${v.modelo} (${v.patente})</option>`;
            });
        });
});
</script>
<?php }; ?>
<?php include 'footer.php'; ?>
