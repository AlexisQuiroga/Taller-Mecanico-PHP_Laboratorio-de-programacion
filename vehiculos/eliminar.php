<?php
require_once '../check_session.php';
require_once '../db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

if ($rol !== 'admin' && $rol !== 'cliente') {
    header('Location: ../dashboard.php');
    exit();
}

$id = (int)$_GET['id'];

if ($rol === 'admin') {
    $vehiculo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM vehiculos WHERE id = $id"));
} else {
    $vehiculo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM vehiculos WHERE id = $id AND id_cliente = $id_usuario"));
}

if (!$vehiculo) {
    header('Location: listar.php');
    exit();
}

$ordenes_activas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE id_vehiculo = $id AND estado NOT IN ('completado','cancelado')"))[0];

if ($ordenes_activas > 0) {
    $_SESSION['mensaje'] = 'No se puede eliminar el vehículo porque tiene órdenes activas.';
    $_SESSION['tipo'] = 'danger';
} else {
    if (mysqli_query($conn, "DELETE FROM vehiculos WHERE id = $id")) {
        $_SESSION['mensaje'] = 'Vehículo eliminado correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el vehículo.';
        $_SESSION['tipo'] = 'danger';
    }
}

header('Location: listar.php');
exit();
