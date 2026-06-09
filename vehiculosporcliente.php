<?php
session_start();
require_once 'consultas.php';
if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}
$conexion = conexion();
$id_cliente = (int)$_GET['id_cliente'];
$vehiculos = obtenerVehiculosPorCliente($conexion, $id_cliente);
header('Content-Type: application/json');
echo json_encode($vehiculos);
