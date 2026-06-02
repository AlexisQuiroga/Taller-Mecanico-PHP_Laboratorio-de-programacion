<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}
$id_cliente = (int)$_GET['id_cliente'];
$res = mysqli_query($conn, "SELECT id, marca, modelo, patente FROM vehiculos WHERE id_cliente = $id_cliente ORDER BY marca");
$vehiculos = [];
while ($v = mysqli_fetch_assoc($res)) $vehiculos[] = $v;
header('Content-Type: application/json');
echo json_encode($vehiculos);
