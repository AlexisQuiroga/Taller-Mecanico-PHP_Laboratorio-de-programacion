<?php
require_once 'validaciones.php';
require_once 'consultas.php';
$conexion = conexion();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$id = (int)$_GET['id'];

if (tieneOrdenesActivasUsuario($conexion, $id)) {
    $_SESSION['mensaje'] = 'No se puede eliminar el usuario porque tiene órdenes activas.';
    $_SESSION['tipo'] = 'danger';
} else {
    if (eliminarUsuario($conexion, $id)) {
        $_SESSION['mensaje'] = 'Usuario eliminado correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el usuario.';
        $_SESSION['tipo'] = 'danger';
    }
}

header('Location: tablausuarios.php');
exit();
