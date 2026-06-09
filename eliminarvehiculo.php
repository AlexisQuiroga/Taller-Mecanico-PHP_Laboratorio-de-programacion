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

$id = (int)$_GET['id'];
$vehiculo = obtenerVehiculo($conexion, $id, $rol !== 'admin' ? $id_usuario : 0);

if (!$vehiculo) {
    header('Location: tablavehiculos.php');
    exit();
}

if (tieneOrdenesActivasVehiculo($conexion, $id)) {
    $_SESSION['mensaje'] = 'No se puede eliminar el vehículo porque tiene órdenes activas.';
    $_SESSION['tipo'] = 'danger';
} else {
    if (eliminarVehiculo($conexion, $id)) {
        $_SESSION['mensaje'] = 'Vehículo eliminado correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el vehículo.';
        $_SESSION['tipo'] = 'danger';
    }
}

header('Location: tablavehiculos.php');
exit();
