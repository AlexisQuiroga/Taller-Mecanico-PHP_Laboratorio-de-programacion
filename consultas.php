<?php
define('BASE_URL', '/Taller_mecanico');

function conexion() {
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $bd       = 'tallermec';
    $conexion = mysqli_connect($host, $user, $password, $bd);
    mysqli_set_charset($conexion, 'utf8mb4');
    return $conexion;
}

function badge_estado($estado) {
    $clases = ['pendiente'=>'warning','en_proceso'=>'primary','completado'=>'success','cancelado'=>'danger'];
    $labels = ['pendiente'=>'Pendiente','en_proceso'=>'En Proceso','completado'=>'Completado','cancelado'=>'Cancelado'];
    $clase  = $clases[$estado] ?? 'secondary';
    $label  = $labels[$estado] ?? $estado;
    return "<span class=\"badge bg-{$clase}\">{$label}</span>";
}

// ── LOGIN ─────────────────────────────────────────────────────────────────────
function login($conexion, $email, $contrasena) {
    $email = mysqli_real_escape_string($conexion, $email);
    return mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email' AND password = '$contrasena' LIMIT 1");
}

// ── USUARIOS ──────────────────────────────────────────────────────────────────
function mostrarUsuarios($conexion) {
    return mysqli_query($conexion, "SELECT * FROM usuarios ORDER BY created_at DESC");
}

function obtenerUsuario($conexion, $id) {
    return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = $id"));
}

function verificarEmail($conexion, $email, $id = 0) {
    $email = mysqli_real_escape_string($conexion, $email);
    if ($id > 0) {
        return mysqli_query($conexion, "SELECT id FROM usuarios WHERE email = '$email' AND id != $id");
    }
    return mysqli_query($conexion, "SELECT id FROM usuarios WHERE email = '$email'");
}

function insertarUsuario($conexion, $nombre, $apellido, $email, $contrasena, $rol) {
    $nombre   = mysqli_real_escape_string($conexion, $nombre);
    $apellido = mysqli_real_escape_string($conexion, $apellido);
    $email    = mysqli_real_escape_string($conexion, $email);
    $rol      = mysqli_real_escape_string($conexion, $rol);
    return mysqli_query($conexion, "INSERT INTO usuarios (nombre, apellido, email, password, rol)
        VALUES ('$nombre','$apellido','$email','$contrasena','$rol')");
}

function actualizarUsuario($conexion, $id, $nombre, $apellido, $email, $rol, $nueva_contrasena = '') {
    $nombre   = mysqli_real_escape_string($conexion, $nombre);
    $apellido = mysqli_real_escape_string($conexion, $apellido);
    $email    = mysqli_real_escape_string($conexion, $email);
    $rol      = mysqli_real_escape_string($conexion, $rol);
    $extra    = $nueva_contrasena ? ", password = '$nueva_contrasena'" : '';
    return mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', email='$email', rol='$rol' $extra WHERE id = $id");
}

function eliminarUsuario($conexion, $id) {
    return mysqli_query($conexion, "DELETE FROM usuarios WHERE id = $id");
}

function tieneOrdenesActivasUsuario($conexion, $id) {
    $fila = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE (id_cliente = $id OR id_trabajador = $id) AND estado NOT IN ('completado','cancelado')"));
    return $fila[0] > 0;
}

// ── VEHÍCULOS ─────────────────────────────────────────────────────────────────
function mostrarVehiculos($conexion, $rol, $id_usuario) {
    if ($rol === 'admin') {
        $sql = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido) AS duenio
                FROM vehiculos v JOIN usuarios u ON v.id_cliente = u.id ORDER BY v.id DESC";
    } else {
        $sql = "SELECT v.* FROM vehiculos v WHERE v.id_cliente = $id_usuario ORDER BY v.id DESC";
    }
    return mysqli_query($conexion, $sql);
}

function obtenerVehiculo($conexion, $id, $id_usuario = 0) {
    if ($id_usuario > 0) {
        return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM vehiculos WHERE id = $id AND id_cliente = $id_usuario"));
    }
    return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM vehiculos WHERE id = $id"));
}

function verificarPatente($conexion, $patente, $id = 0) {
    $patente = mysqli_real_escape_string($conexion, $patente);
    if ($id > 0) {
        return mysqli_query($conexion, "SELECT id FROM vehiculos WHERE patente = '$patente' AND id != $id");
    }
    return mysqli_query($conexion, "SELECT id FROM vehiculos WHERE patente = '$patente'");
}

function insertarVehiculo($conexion, $id_cliente, $marca, $modelo, $anio, $patente, $color) {
    $marca   = mysqli_real_escape_string($conexion, $marca);
    $modelo  = mysqli_real_escape_string($conexion, $modelo);
    $patente = mysqli_real_escape_string($conexion, $patente);
    $color   = mysqli_real_escape_string($conexion, $color);
    return mysqli_query($conexion, "INSERT INTO vehiculos (id_cliente, marca, modelo, anio, patente, color)
        VALUES ($id_cliente,'$marca','$modelo',$anio,'$patente','$color')");
}

function actualizarVehiculo($conexion, $id, $marca, $modelo, $anio, $patente, $color) {
    $marca   = mysqli_real_escape_string($conexion, $marca);
    $modelo  = mysqli_real_escape_string($conexion, $modelo);
    $patente = mysqli_real_escape_string($conexion, $patente);
    $color   = mysqli_real_escape_string($conexion, $color);
    return mysqli_query($conexion, "UPDATE vehiculos SET marca='$marca', modelo='$modelo', anio=$anio, patente='$patente', color='$color' WHERE id = $id");
}

function eliminarVehiculo($conexion, $id) {
    return mysqli_query($conexion, "DELETE FROM vehiculos WHERE id = $id");
}

function tieneOrdenesActivasVehiculo($conexion, $id) {
    $fila = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_vehiculo = $id AND estado NOT IN ('completado','cancelado')"));
    return $fila[0] > 0;
}

function obtenerVehiculosPorCliente($conexion, $id_cliente) {
    $res = mysqli_query($conexion, "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_cliente ORDER BY marca");
    $vehiculos = [];
    while ($v = mysqli_fetch_assoc($res)) $vehiculos[] = $v;
    return $vehiculos;
}

function obtenerInfoVehiculo($conexion, $id) {
    return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT marca, modelo, patente FROM vehiculos WHERE id = $id"));
}

// ── ÓRDENES ───────────────────────────────────────────────────────────────────
function mostrarOrdenes($conexion, $rol, $id_usuario, $filtro_estado = '') {
    $estados_validos = ['pendiente','en_proceso','completado','cancelado'];
    if ($rol === 'admin') {
        $where = ($filtro_estado && in_array($filtro_estado, $estados_validos)) ? "WHERE ot.estado = '$filtro_estado'" : '';
        $sql = "SELECT ot.*,
                CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
                CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
                FROM ordenes_trabajo ot
                JOIN usuarios uc ON ot.id_cliente = uc.id
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                JOIN usuarios ut  ON ot.id_trabajador = ut.id
                $where ORDER BY ot.created_at DESC";
    } elseif ($rol === 'mecanico' || $rol === 'electricista') {
        $tipo  = $rol === 'mecanico' ? 'mecanica' : 'electricidad';
        $where = "WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'";
        if ($filtro_estado && in_array($filtro_estado, $estados_validos)) $where .= " AND ot.estado = '$filtro_estado'";
        $sql = "SELECT ot.*,
                CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
                FROM ordenes_trabajo ot
                JOIN usuarios uc ON ot.id_cliente = uc.id
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                $where ORDER BY ot.created_at DESC";
    } else {
        $where = "WHERE ot.id_cliente = $id_usuario";
        if ($filtro_estado && in_array($filtro_estado, $estados_validos)) $where .= " AND ot.estado = '$filtro_estado'";
        $sql = "SELECT ot.*,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
                CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
                FROM ordenes_trabajo ot
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                JOIN usuarios ut  ON ot.id_trabajador = ut.id
                $where ORDER BY ot.created_at DESC";
    }
    return mysqli_query($conexion, $sql);
}

function obtenerOrden($conexion, $id, $rol = 'admin', $id_usuario = 0) {
    if ($rol === 'admin') {
        $sql = "SELECT * FROM ordenes_trabajo WHERE id = $id";
    } elseif ($rol === 'mecanico') {
        $sql = "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'mecanica'";
    } elseif ($rol === 'electricista') {
        $sql = "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'electricidad'";
    } else {
        return null;
    }
    return mysqli_fetch_assoc(mysqli_query($conexion, $sql));
}

function obtenerOrdenPendienteCliente($conexion, $id, $id_usuario) {
    return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM ordenes_trabajo
        WHERE id = $id AND id_cliente = $id_usuario AND estado = 'pendiente'"));
}

function insertarOrden($conexion, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada) {
    $tipo           = mysqli_real_escape_string($conexion, $tipo);
    $descripcion    = mysqli_real_escape_string($conexion, $descripcion);
    $estado         = mysqli_real_escape_string($conexion, $estado);
    $fecha_ingreso  = mysqli_real_escape_string($conexion, $fecha_ingreso);
    $fecha_estimada = mysqli_real_escape_string($conexion, $fecha_estimada);
    return mysqli_query($conexion, "INSERT INTO ordenes_trabajo
        (id_vehiculo, id_cliente, id_trabajador, tipo, descripcion, estado, fecha_ingreso, fecha_estimada)
        VALUES ($id_vehiculo, $id_cliente, $id_trabajador, '$tipo', '$descripcion', '$estado', '$fecha_ingreso', '$fecha_estimada')");
}

function actualizarOrden($conexion, $id, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada) {
    $tipo           = mysqli_real_escape_string($conexion, $tipo);
    $descripcion    = mysqli_real_escape_string($conexion, $descripcion);
    $estado         = mysqli_real_escape_string($conexion, $estado);
    $fecha_ingreso  = mysqli_real_escape_string($conexion, $fecha_ingreso);
    $fecha_estimada = mysqli_real_escape_string($conexion, $fecha_estimada);
    return mysqli_query($conexion, "UPDATE ordenes_trabajo
        SET id_vehiculo=$id_vehiculo, id_cliente=$id_cliente, id_trabajador=$id_trabajador,
            tipo='$tipo', descripcion='$descripcion', estado='$estado',
            fecha_ingreso='$fecha_ingreso', fecha_estimada='$fecha_estimada'
        WHERE id = $id");
}

function actualizarEstadoOrden($conexion, $id, $estado) {
    $estado = mysqli_real_escape_string($conexion, $estado);
    return mysqli_query($conexion, "UPDATE ordenes_trabajo SET estado='$estado' WHERE id = $id");
}

function eliminarOrden($conexion, $id) {
    return mysqli_query($conexion, "DELETE FROM ordenes_trabajo WHERE id = $id");
}

function cancelarOrden($conexion, $id) {
    return mysqli_query($conexion, "UPDATE ordenes_trabajo SET estado = 'cancelado' WHERE id = $id");
}

// ── SELECTORES ────────────────────────────────────────────────────────────────
function obtenerClientes($conexion) {
    return mysqli_query($conexion, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'cliente' ORDER BY nombre");
}

function obtenerMecanicos($conexion) {
    $res = mysqli_query($conexion, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'mecanico' ORDER BY nombre");
    $arr = [];
    while ($m = mysqli_fetch_assoc($res)) $arr[] = $m;
    return $arr;
}

function obtenerElectricistas($conexion) {
    $res = mysqli_query($conexion, "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'electricista' ORDER BY nombre");
    $arr = [];
    while ($e = mysqli_fetch_assoc($res)) $arr[] = $e;
    return $arr;
}

function obtenerTrabajadores($conexion) {
    return mysqli_query($conexion, "SELECT id, nombre, apellido, rol FROM usuarios WHERE rol IN ('mecanico','electricista') ORDER BY nombre");
}

function buscarTrabajadorPorRol($conexion, $rol) {
    $rol = mysqli_real_escape_string($conexion, $rol);
    return mysqli_fetch_assoc(mysqli_query($conexion, "SELECT id FROM usuarios WHERE rol = '$rol' LIMIT 1"));
}

function obtenerVehiculosDelCliente($conexion, $id_usuario) {
    return mysqli_query($conexion, "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_usuario ORDER BY marca");
}

// ── DASHBOARD ─────────────────────────────────────────────────────────────────
function contarUsuarios($conexion) {
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM usuarios"))[0];
}

function contarVehiculos($conexion) {
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM vehiculos"))[0];
}

function contarOrdenesPorEstado($conexion, $estado) {
    $estado = mysqli_real_escape_string($conexion, $estado);
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = '$estado'"))[0];
}

function proximasOrdenesActivas($conexion) {
    return mysqli_query($conexion, "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
        FROM ordenes_trabajo ot
        JOIN usuarios u ON ot.id_cliente = u.id
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.estado NOT IN ('completado','cancelado')
        ORDER BY ot.fecha_estimada ASC LIMIT 5");
}

function contarOrdenesTrabajador($conexion, $id_usuario, $tipo, $estado) {
    $tipo   = mysqli_real_escape_string($conexion, $tipo);
    $estado = mysqli_real_escape_string($conexion, $estado);
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = '$estado'"))[0];
}

function contarOrdenesCompletadasHoy($conexion, $id_usuario, $tipo) {
    $tipo = mysqli_real_escape_string($conexion, $tipo);
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = 'completado' AND DATE(created_at) = CURDATE()"))[0];
}

function ultimasOrdenesTrabajador($conexion, $id_usuario, $tipo) {
    $tipo = mysqli_real_escape_string($conexion, $tipo);
    return mysqli_query($conexion, "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        CONCAT(v.marca, ' ', v.modelo) AS vehiculo
        FROM ordenes_trabajo ot
        JOIN usuarios u ON ot.id_cliente = u.id
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'
        ORDER BY ot.created_at DESC LIMIT 5");
}

function contarVehiculosCliente($conexion, $id_usuario) {
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM vehiculos WHERE id_cliente = $id_usuario"))[0];
}

function contarOrdenesActivasCliente($conexion, $id_usuario) {
    return mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_cliente = $id_usuario AND estado NOT IN ('completado','cancelado')"))[0];
}

function todasOrdenesCliente($conexion, $id_usuario) {
    return mysqli_query($conexion, "SELECT ot.*, CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
        FROM ordenes_trabajo ot
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.id_cliente = $id_usuario
        ORDER BY ot.created_at DESC");
}
