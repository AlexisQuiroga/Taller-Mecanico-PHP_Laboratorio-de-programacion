<?php
require_once '../check_session.php';
require_once '../db.php';

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id'];

$id = (int)$_GET['id'];
$cancelar = isset($_GET['cancelar']) && $_GET['cancelar'] == 1;

if ($rol === 'admin') {
    $orden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ordenes_trabajo WHERE id = $id"));
    if (!$orden) {
        header('Location: listar.php');
        exit();
    }
    if (mysqli_query($conn, "DELETE FROM ordenes_trabajo WHERE id = $id")) {
        $_SESSION['mensaje'] = 'Orden eliminada correctamente.';
        $_SESSION['tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar la orden.';
        $_SESSION['tipo'] = 'danger';
    }
} elseif ($rol === 'cliente' && $cancelar) {
    $orden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ordenes_trabajo WHERE id = $id AND id_cliente = $id_usuario AND estado = 'pendiente'"));
    if (!$orden) {
        $_SESSION['mensaje'] = 'No se puede cancelar esta orden.';
        $_SESSION['tipo'] = 'danger';
    } else {
        if (mysqli_query($conn, "UPDATE ordenes_trabajo SET estado = 'cancelado' WHERE id = $id")) {
            $_SESSION['mensaje'] = 'Orden cancelada correctamente.';
            $_SESSION['tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al cancelar la orden.';
            $_SESSION['tipo'] = 'danger';
        }
    }
} else {
    header('Location: ../dashboard.php');
    exit();
}

header('Location: listar.php');
exit();
