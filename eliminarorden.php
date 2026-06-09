<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

$id = (int)$_GET['id'];
$cancelar = isset($_GET['cancelar']) && $_GET['cancelar'] == 1;

if ($rol === 'admin') {
    $orden = obtenerOrden($conexion, $id);
    if (!$orden) {
        header('Location: tablaordenes.php');
        exit();
    }
    if (eliminarOrden($conexion, $id)) {
        $_SESSION['mensaje'] = 'Orden eliminada correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar la orden.';
        $_SESSION['tipo'] = 'danger';
    }
} elseif ($rol === 'cliente' && $cancelar) {
    $orden = obtenerOrdenPendienteCliente($conexion, $id, $id_usuario);
    if (!$orden) {
        $_SESSION['mensaje'] = 'No se puede cancelar esta orden.';
        $_SESSION['tipo'] = 'danger';
    } else {
        if (cancelarOrden($conexion, $id)) {
            $_SESSION['mensaje'] = 'Orden cancelada correctamente.';
            $_SESSION['tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al cancelar la orden.';
            $_SESSION['tipo'] = 'danger';
        }
    }
} else {
    header('Location: dashboard.php');
    exit();
}

header('Location: tablaordenes.php');
exit();
