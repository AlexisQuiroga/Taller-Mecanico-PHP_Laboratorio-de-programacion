<?php
require_once '../check_session.php';
require_once '../db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

if ($rol !== 'admin' && $rol !== 'cliente') {
    header('Location: ../dashboard.php');
    exit();
}

$error = '';

if ($rol === 'admin') {
    $clientes = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'cliente' ORDER BY nombre");
    $mecanicos = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'mecanico' ORDER BY nombre");
    $electricistas = mysqli_query($conn, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'electricista' ORDER BY nombre");
} else {
    $mis_vehiculos = mysqli_query($conn, "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_usuario ORDER BY marca");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($rol === 'admin') {
        $id_cliente = (int)$_POST['id_cliente'];
        $id_vehiculo = (int)$_POST['id_vehiculo'];
        $id_trabajador = (int)$_POST['id_trabajador'];
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        $estado = mysqli_real_escape_string($conn, $_POST['estado']);
    } else {
        $id_cliente = $id_usuario;
        $id_vehiculo = (int)$_POST['id_vehiculo'];
        $tipo = mysqli_real_escape_string($conn, $_POST['tipo']);
        $estado = 'pendiente';

        $rol_buscado = $tipo === 'mecanica' ? 'mecanico' : 'electricista';
        $trabajador_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM usuarios WHERE rol = '$rol_buscado' LIMIT 1"));
        $id_trabajador = $trabajador_row ? (int)$trabajador_row['id'] : 0;
    }

    $descripcion = mysqli_real_escape_string($conn, trim($_POST['descripcion']));
    $fecha_ingreso = mysqli_real_escape_string($conn, $_POST['fecha_ingreso']);
    $fecha_estimada = mysqli_real_escape_string($conn, $_POST['fecha_estimada']);

    if (!$id_cliente || !$id_vehiculo || !$id_trabajador || !$tipo || !$descripcion || !$fecha_ingreso || !$fecha_estimada) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($fecha_estimada < $fecha_ingreso) {
        $error = 'La fecha estimada no puede ser anterior a la fecha de ingreso.';
    } else {
        $sql = "INSERT INTO ordenes_trabajo (id_vehiculo, id_cliente, id_trabajador, tipo, descripcion, estado, fecha_ingreso, fecha_estimada)
                VALUES ($id_vehiculo, $id_cliente, $id_trabajador, '$tipo', '$descripcion', '$estado', '$fecha_ingreso', '$fecha_estimada')";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['mensaje'] = 'Orden de trabajo creada correctamente.';
            $_SESSION['tipo'] = 'success';
            header('Location: listar.php');
            exit();
        } else {
            $error = 'Error al crear la orden de trabajo.';
        }
    }
}
?>
<?php include '../header.php'; ?>
<?php include '../sidebar.php'; ?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Nueva Orden de Trabajo</h4>
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
                            <option value="">Seleccionar cliente...</option>
                            <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['id_cliente']) && $_POST['id_cliente']==$c['id'])?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                            </option>
                            <?php endwhile; ?>
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
                    <?php else: ?>
                    <div class="col-12">
                        <label class="form-label">Mi vehículo</label>
                        <select name="id_vehiculo" class="form-select" required>
                            <option value="">Seleccionar vehículo...</option>
                            <?php while ($v = mysqli_fetch_assoc($mis_vehiculos)): ?>
                            <option value="<?php echo $v['id']; ?>" <?php echo (isset($_POST['id_vehiculo'])&&$_POST['id_vehiculo']==$v['id'])?'selected':''; ?>>
                                <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo'] . ' (' . $v['patente'] . ')'); ?>
                            </option>
                            <?php endwhile; ?>
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
                    <?php endif; ?>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control" required
                            value="<?php echo isset($_POST['fecha_ingreso']) ? htmlspecialchars($_POST['fecha_ingreso']) : date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha estimada</label>
                        <input type="date" name="fecha_estimada" class="form-control" required
                            value="<?php echo isset($_POST['fecha_estimada']) ? htmlspecialchars($_POST['fecha_estimada']) : ''; ?>">
                    </div>
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
const mecanicos = <?php
    mysqli_data_seek($mecanicos, 0);
    $mec_arr = [];
    while ($m = mysqli_fetch_assoc($mecanicos)) $mec_arr[] = $m;
    echo json_encode($mec_arr);
?>;
const electricistas = <?php
    mysqli_data_seek($electricistas, 0);
    $ele_arr = [];
    while ($e = mysqli_fetch_assoc($electricistas)) $ele_arr[] = $e;
    echo json_encode($ele_arr);
?>;

document.getElementById('sel_cliente').addEventListener('change', function() {
    const clienteId = this.value;
    const selVehiculo = document.getElementById('sel_vehiculo');
    selVehiculo.innerHTML = '<option value="">Cargando...</option>';
    if (!clienteId) {
        selVehiculo.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        return;
    }
    fetch('../vehiculos/ajax_vehiculos.php?id_cliente=' + clienteId)
        .then(r => r.json())
        .then(data => {
            selVehiculo.innerHTML = '<option value="">Seleccionar vehículo...</option>';
            data.forEach(v => {
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
<?php endif; ?>
<?php include '../footer.php'; ?>
