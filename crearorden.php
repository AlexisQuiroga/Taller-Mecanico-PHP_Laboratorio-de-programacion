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

$error = '';

if ($rol === 'admin') {
    $clientes      = obtenerClientes($conexion);
    $mecanicos     = obtenerMecanicos($conexion);
    $electricistas = obtenerElectricistas($conexion);
} else {
    $mis_vehiculos = obtenerVehiculosDelCliente($conexion, $id_usuario);
}

if (!empty($_POST['aceptar'])) {
    if ($rol === 'admin') {
        $id_cliente    = (int)$_POST['id_cliente'];
        $id_vehiculo   = (int)$_POST['id_vehiculo'];
        $id_trabajador = (int)$_POST['id_trabajador'];
        $tipo          = $_POST['tipo'];
        $estado        = $_POST['estado'];
    } else {
        $id_cliente  = $id_usuario;
        $id_vehiculo = (int)$_POST['id_vehiculo'];
        $tipo        = $_POST['tipo'];
        $estado      = 'pendiente';

        $rol_buscado    = $tipo === 'mecanica' ? 'mecanico' : 'electricista';
        $trabajador_row = buscarTrabajadorPorRol($conexion, $rol_buscado);
        $id_trabajador  = $trabajador_row ? (int)$trabajador_row['id'] : 0;
    }

    $descripcion    = trim($_POST['descripcion']);
    $fecha_ingreso  = $_POST['fecha_ingreso'];
    $fecha_estimada = $_POST['fecha_estimada'];

    if (!$id_cliente || !$id_vehiculo || !$id_trabajador || !$tipo || !$descripcion || !$fecha_ingreso || !$fecha_estimada) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($fecha_estimada < $fecha_ingreso) {
        $error = 'La fecha estimada no puede ser anterior a la fecha de ingreso.';
    } else {
        if (insertarOrden($conexion, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada)) {
            $_SESSION['mensaje'] = 'Orden de trabajo creada correctamente.';
            $_SESSION['tipo'] = 'success';
            header('Location: tablaordenes.php');
            exit();
        } else {
            $error = 'Error al crear la orden de trabajo.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'menulateral.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Nueva Orden de Trabajo</h4>
    </div>
    <?php if ($error) { ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php }; ?>
    <div class="card border-0 shadow-sm" style="max-width:700px;">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <?php if ($rol === 'admin') { ?>
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <select name="id_cliente" class="form-select" required id="sel_cliente">
                            <option value="">Seleccionar cliente...</option>
                            <?php while ($c = mysqli_fetch_array($clientes)) { ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['id_cliente']) && $_POST['id_cliente']==$c['id'])?'selected':''; ?>>
                                <?php echo $c['nombre'] . ' ' . $c['apellido']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehículo del cliente</label>
                        <select name="id_vehiculo" class="form-select" required id="sel_vehiculo">
                            <option value="">Primero seleccione un cliente</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de trabajo</label>
                        <select name="tipo" class="form-select" required id="sel_tipo">
                            <option value="">Seleccionar tipo...</option>
                            <option value="mecanica" <?php echo (isset($_POST['tipo'])&&$_POST['tipo']==='mecanica')?'selected':''; ?>>Mecánica</option>
                            <option value="electricidad" <?php echo (isset($_POST['tipo'])&&$_POST['tipo']==='electricidad')?'selected':''; ?>>Electricidad</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trabajador</label>
                        <select name="id_trabajador" class="form-select" required id="sel_trabajador">
                            <option value="">Primero seleccione tipo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Estado inicial</label>
                        <select name="estado" class="form-select" required>
                            <option value="pendiente" <?php echo (!isset($_POST['estado'])||$_POST['estado']==='pendiente')?'selected':''; ?>>Pendiente</option>
                            <option value="en_proceso" <?php echo (isset($_POST['estado'])&&$_POST['estado']==='en_proceso')?'selected':''; ?>>En Proceso</option>
                            <option value="completado" <?php echo (isset($_POST['estado'])&&$_POST['estado']==='completado')?'selected':''; ?>>Completado</option>
                            <option value="cancelado" <?php echo (isset($_POST['estado'])&&$_POST['estado']==='cancelado')?'selected':''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <?php } else { ?>
                    <div class="col-12">
                        <label class="form-label">Mi vehículo</label>
                        <select name="id_vehiculo" class="form-select" required>
                            <option value="">Seleccionar vehículo...</option>
                            <?php while ($v = mysqli_fetch_array($mis_vehiculos)) { ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo (isset($_POST['id_vehiculo'])&&$_POST['id_vehiculo']==$v['id'])?'selected':''; ?>>
                                <?php echo $v['marca'] . ' ' . $v['modelo'] . ' (' . $v['patente'] . ')'; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tipo de trabajo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="mecanica" <?php echo (isset($_POST['tipo'])&&$_POST['tipo']==='mecanica')?'selected':''; ?>>Mecánica</option>
                            <option value="electricidad" <?php echo (isset($_POST['tipo'])&&$_POST['tipo']==='electricidad')?'selected':''; ?>>Electricidad</option>
                        </select>
                    </div>
                    <?php }; ?>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required><?php echo isset($_POST['descripcion']) ? $_POST['descripcion'] : ''; ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control" required
                            value="<?php echo isset($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" required
                            value="<?php echo isset($_POST['fecha_estimada']) ? $_POST['fecha_estimada'] : ''; ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" name="aceptar" value="1" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                        <a href="tablaordenes.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($rol === 'admin') { ?>
<script>
const mecanicos = <?= json_encode($mecanicos) ?>;
const electricistas = <?= json_encode($electricistas) ?>;

document.getElementById('sel_cliente').addEventListener('change', function() {
    const idCliente = this.value;
    const selVehiculo = document.getElementById('sel_vehiculo');
    selVehiculo.innerHTML = '<option value="">Cargando...</option>';
    if (!idCliente) {
        selVehiculo.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        return;
    }
    fetch('vehiculosporcliente.php?id_cliente=' + idCliente)
        .then(r => r.json())
        .then(datos => {
            selVehiculo.innerHTML = '<option value="">Seleccionar vehículo...</option>';
            datos.forEach(v => {
                selVehiculo.innerHTML += `<option value="${v.id}">${v.marca} ${v.modelo} (${v.patente})</option>`;
            });
        });
});

document.getElementById('sel_tipo').addEventListener('change', function() {
    const tipo = this.value;
    const selTrabajador = document.getElementById('sel_trabajador');
    selTrabajador.innerHTML = '<option value="">Seleccionar trabajador...</option>';
    const lista = tipo === 'mecanica' ? mecanicos : (tipo === 'electricidad' ? electricistas : []);
    lista.forEach(t => {
        selTrabajador.innerHTML += `<option value="${t.id}">${t.nombre} ${t.apellido}</option>`;
    });
});
</script>
<?php }; ?>
<?php include 'footer.php'; ?>
