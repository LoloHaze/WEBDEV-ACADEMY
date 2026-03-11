<?php
$host = "localhost";
$usuario = "root";
$password = "";
// $base_datos = "webdevacademy";
$base_datos = "webdevacademyPRO";

// Crear conexión
$conexion = mysqli_connect($host, $usuario, $password, $base_datos);

// Verificar conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>