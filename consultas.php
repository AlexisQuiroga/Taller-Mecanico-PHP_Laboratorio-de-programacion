<?php
define('BASE_URL', '/Taller_mecanico');

function conexion() {
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $bd       = 'tallermec';
    $conexion = mysqli_connect($host, $user, $password, $bd);
    return $conexion;
}

function badge_estado($estado) {
    $clases = ['pendiente'=>'warning','en_proceso'=>'primary','completado'=>'success','cancelado'=>'danger'];
    $labels = ['pendiente'=>'Pendiente','en_proceso'=>'En Proceso','completado'=>'Completado','cancelado'=>'Cancelado'];
    if (isset($clases[$estado])) {
        $clase = $clases[$estado];
    } else {
        $clase = 'secondary';
    }
    if (isset($labels[$estado])) {
        $label = $labels[$estado];
    } else {
        $label = $estado;
    }
    return "<span class=\"badge bg-{$clase}\">{$label}</span>";
}

function login($conexion, $email, $contrasena) {
    $SQL = "SELECT * FROM usuarios WHERE email = '$email' AND password = '$contrasena' LIMIT 1";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function mostrarUsuarios($conexion) {
    $SQL = "SELECT * FROM usuarios ORDER BY created_at DESC";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function obtenerUsuario($conexion, $id) {
    $SQL = "SELECT * FROM usuarios WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function verificarEmail($conexion, $email, $id = 0) {
    if ($id > 0) {
        $SQL = "SELECT id FROM usuarios WHERE email = '$email' AND id != $id";
    } else {
        $SQL = "SELECT id FROM usuarios WHERE email = '$email'";
    }
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function insertarUsuario($conexion, $nombre, $apellido, $email, $contrasena, $rol) {
    $SQL = "INSERT INTO usuarios (nombre, apellido, email, password, rol)
        VALUES ('$nombre','$apellido','$email','$contrasena','$rol')";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function actualizarUsuario($conexion, $id, $nombre, $apellido, $email, $rol, $nueva_contrasena = '') {
    if ($nueva_contrasena) {
        $extra = ", password = '$nueva_contrasena'";
    } else {
        $extra = '';
    }
    $SQL = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', email='$email', rol='$rol' $extra WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function eliminarUsuario($conexion, $id) {
    $SQL = "DELETE FROM usuarios WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function tieneOrdenesActivasUsuario($conexion, $id) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE (id_cliente = $id OR id_trabajador = $id) AND estado NOT IN ('completado','cancelado')";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0] > 0;
}

function mostrarVehiculos($conexion, $rol, $id_usuario) {
    if ($rol === 'admin') {
        $SQL = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido) AS duenio
                FROM vehiculos v JOIN usuarios u ON v.id_cliente = u.id ORDER BY v.id DESC";
    } else {
        $SQL = "SELECT v.* FROM vehiculos v WHERE v.id_cliente = $id_usuario ORDER BY v.id DESC";
    }
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function obtenerVehiculo($conexion, $id, $id_usuario = 0) {
    if ($id_usuario > 0) {
        $SQL = "SELECT * FROM vehiculos WHERE id = $id AND id_cliente = $id_usuario";
    } else {
        $SQL = "SELECT * FROM vehiculos WHERE id = $id";
    }
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function verificarPatente($conexion, $patente, $id = 0) {
    if ($id > 0) {
        $SQL = "SELECT id FROM vehiculos WHERE patente = '$patente' AND id != $id";
    } else {
        $SQL = "SELECT id FROM vehiculos WHERE patente = '$patente'";
    }
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function insertarVehiculo($conexion, $id_cliente, $marca, $modelo, $anio, $patente, $color) {
    $SQL = "INSERT INTO vehiculos (id_cliente, marca, modelo, anio, patente, color)
        VALUES ($id_cliente,'$marca','$modelo',$anio,'$patente','$color')";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function actualizarVehiculo($conexion, $id, $marca, $modelo, $anio, $patente, $color) {
    $SQL = "UPDATE vehiculos SET marca='$marca', modelo='$modelo', anio=$anio, patente='$patente', color='$color' WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function eliminarVehiculo($conexion, $id) {
    $SQL = "DELETE FROM vehiculos WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function tieneOrdenesActivasVehiculo($conexion, $id) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_vehiculo = $id AND estado NOT IN ('completado','cancelado')";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0] > 0;
}

function obtenerVehiculosPorCliente($conexion, $id_cliente) {
    $SQL = "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_cliente ORDER BY marca";
    $rs = mysqli_query($conexion, $SQL);
    $vehiculos = [];
    while ($v = mysqli_fetch_array($rs)) { $vehiculos[] = $v; }
    return $vehiculos;
}

function obtenerInfoVehiculo($conexion, $id) {
    $SQL = "SELECT marca, modelo, patente FROM vehiculos WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function mostrarOrdenes($conexion, $rol, $id_usuario, $filtro_estado = '') {
    $estados_validos = ['pendiente','en_proceso','completado','cancelado'];
    if ($rol === 'admin') {
        if ($filtro_estado && in_array($filtro_estado, $estados_validos)) {
            $where = "WHERE ot.estado = '$filtro_estado'";
        } else {
            $where = '';
        }
        $SQL = "SELECT ot.*,
                CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
                CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
                FROM ordenes_trabajo ot
                JOIN usuarios uc ON ot.id_cliente = uc.id
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                JOIN usuarios ut  ON ot.id_trabajador = ut.id
                $where ORDER BY ot.created_at DESC";
    } elseif ($rol === 'mecanico' || $rol === 'electricista') {
        if ($rol === 'mecanico') {
            $tipo = 'mecanica';
        } else {
            $tipo = 'electricidad';
        }
        $where = "WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'";
        if ($filtro_estado && in_array($filtro_estado, $estados_validos)) {
            $where .= " AND ot.estado = '$filtro_estado'";
        }
        $SQL = "SELECT ot.*,
                CONCAT(uc.nombre, ' ', uc.apellido) AS cliente,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
                FROM ordenes_trabajo ot
                JOIN usuarios uc ON ot.id_cliente = uc.id
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                $where ORDER BY ot.created_at DESC";
    } else {
        $where = "WHERE ot.id_cliente = $id_usuario";
        if ($filtro_estado && in_array($filtro_estado, $estados_validos)) {
            $where .= " AND ot.estado = '$filtro_estado'";
        }
        $SQL = "SELECT ot.*,
                CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo,
                CONCAT(ut.nombre, ' ', ut.apellido) AS trabajador
                FROM ordenes_trabajo ot
                JOIN vehiculos v  ON ot.id_vehiculo = v.id
                JOIN usuarios ut  ON ot.id_trabajador = ut.id
                $where ORDER BY ot.created_at DESC";
    }
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function obtenerOrden($conexion, $id, $rol = 'admin', $id_usuario = 0) {
    if ($rol === 'admin') {
        $SQL = "SELECT * FROM ordenes_trabajo WHERE id = $id";
    } elseif ($rol === 'mecanico') {
        $SQL = "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'mecanica'";
    } elseif ($rol === 'electricista') {
        $SQL = "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_trabajador = $id_usuario AND tipo = 'electricidad'";
    } else {
        return null;
    }
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function obtenerOrdenPendienteCliente($conexion, $id, $id_usuario) {
    $SQL = "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_cliente = $id_usuario AND estado = 'pendiente'";
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function insertarOrden($conexion, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada) {
    $SQL = "INSERT INTO ordenes_trabajo
        (id_vehiculo, id_cliente, id_trabajador, tipo, descripcion, estado, fecha_ingreso, fecha_estimada)
        VALUES ($id_vehiculo, $id_cliente, $id_trabajador, '$tipo', '$descripcion', '$estado', '$fecha_ingreso', '$fecha_estimada')";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function actualizarOrden($conexion, $id, $id_vehiculo, $id_cliente, $id_trabajador, $tipo, $descripcion, $estado, $fecha_ingreso, $fecha_estimada) {
    $SQL = "UPDATE ordenes_trabajo
        SET id_vehiculo=$id_vehiculo, id_cliente=$id_cliente, id_trabajador=$id_trabajador,
            tipo='$tipo', descripcion='$descripcion', estado='$estado',
            fecha_ingreso='$fecha_ingreso', fecha_estimada='$fecha_estimada'
        WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function actualizarEstadoOrden($conexion, $id, $estado) {
    $SQL = "UPDATE ordenes_trabajo SET estado='$estado' WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function eliminarOrden($conexion, $id) {
    $SQL = "DELETE FROM ordenes_trabajo WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function cancelarOrden($conexion, $id) {
    $SQL = "UPDATE ordenes_trabajo SET estado = 'cancelado' WHERE id = $id";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function obtenerClientes($conexion) {
    $SQL = "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'cliente' ORDER BY nombre";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function obtenerMecanicos($conexion) {
    $SQL = "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'mecanico' ORDER BY nombre";
    $rs = mysqli_query($conexion, $SQL);
    $arr = [];
    while ($m = mysqli_fetch_array($rs)) { $arr[] = $m; }
    return $arr;
}

function obtenerElectricistas($conexion) {
    $SQL = "SELECT id, nombre, apellido FROM usuarios WHERE rol = 'electricista' ORDER BY nombre";
    $rs = mysqli_query($conexion, $SQL);
    $arr = [];
    while ($e = mysqli_fetch_array($rs)) { $arr[] = $e; }
    return $arr;
}

function obtenerTrabajadores($conexion) {
    $SQL = "SELECT id, nombre, apellido, rol FROM usuarios WHERE rol IN ('mecanico','electricista') ORDER BY nombre";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function buscarTrabajadorPorRol($conexion, $rol) {
    $SQL = "SELECT id FROM usuarios WHERE rol = '$rol' LIMIT 1";
    $rs = mysqli_query($conexion, $SQL);
    $data = mysqli_fetch_array($rs);
    return $data;
}

function obtenerVehiculosDelCliente($conexion, $id_usuario) {
    $SQL = "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_usuario ORDER BY marca";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function contarUsuarios($conexion) {
    $SQL = "SELECT COUNT(*) FROM usuarios";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function contarVehiculos($conexion) {
    $SQL = "SELECT COUNT(*) FROM vehiculos";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function contarOrdenesPorEstado($conexion, $estado) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = '$estado'";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function proximasOrdenesActivas($conexion) {
    $SQL = "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
        FROM ordenes_trabajo ot
        JOIN usuarios u ON ot.id_cliente = u.id
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.estado NOT IN ('completado','cancelado')
        ORDER BY ot.fecha_estimada ASC LIMIT 5";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function contarOrdenesTrabajador($conexion, $id_usuario, $tipo, $estado) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = '$estado'";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function contarOrdenesCompletadasHoy($conexion, $id_usuario, $tipo) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_trabajador = $id_usuario AND tipo = '$tipo' AND estado = 'completado' AND DATE(created_at) = CURDATE()";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function ultimasOrdenesTrabajador($conexion, $id_usuario, $tipo) {
    $SQL = "SELECT ot.*, CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        CONCAT(v.marca, ' ', v.modelo) AS vehiculo
        FROM ordenes_trabajo ot
        JOIN usuarios u ON ot.id_cliente = u.id
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.id_trabajador = $id_usuario AND ot.tipo = '$tipo'
        ORDER BY ot.created_at DESC LIMIT 5";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}

function contarVehiculosCliente($conexion, $id_usuario) {
    $SQL = "SELECT COUNT(*) FROM vehiculos WHERE id_cliente = $id_usuario";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function contarOrdenesActivasCliente($conexion, $id_usuario) {
    $SQL = "SELECT COUNT(*) FROM ordenes_trabajo
        WHERE id_cliente = $id_usuario AND estado NOT IN ('completado','cancelado')";
    $rs = mysqli_query($conexion, $SQL);
    $fila = mysqli_fetch_array($rs);
    return $fila[0];
}

function todasOrdenesCliente($conexion, $id_usuario) {
    $SQL = "SELECT ot.*, CONCAT(v.marca, ' ', v.modelo, ' (', v.patente, ')') AS vehiculo
        FROM ordenes_trabajo ot
        JOIN vehiculos v ON ot.id_vehiculo = v.id
        WHERE ot.id_cliente = $id_usuario
        ORDER BY ot.created_at DESC";
    $rs = mysqli_query($conexion, $SQL);
    return $rs;
}
