<?php
$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'tallermec';

$conn = mysqli_connect($host, $usuario, $password, $base_datos);

if (!$conn) {
    die('Error de conexion: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

define('BASE_URL', '/Taller_mecanico');
