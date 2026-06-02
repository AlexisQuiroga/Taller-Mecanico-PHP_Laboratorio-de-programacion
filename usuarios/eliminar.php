<?php
require_once '../check_session.php';
require_once '../db.php';

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

$id = (int)$_GET['id'];

$ordenes_activas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ordenes_trabajo WHERE (id_cliente = $id OR id_trabajador = $id) AND estado NOT IN ('completado','cancelado')"))[0];

if ($ordenes_activas > 0) {
    $_SESSION['mensaje'] = 'No se puede eliminar el usuario porque tiene órdenes activas.';
    $_SESSION['tipo'] = 'danger';
} else {
    if (mysqli_query($conn, "DELETE FROM usuarios WHERE id = $id")) {
        $_SESSION['mensaje'] = 'Usuario eliminado correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el usuario.';
        $_SESSION['tipo'] = 'danger';
    }
}

header('Location: listar.php');
exit();
