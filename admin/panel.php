<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../public/login.php");
    exit;
}

if ($_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Admin</title>
</head>
<body>

<h2>Panel de Administración</h2>

<p>Bienvenido <?php echo $_SESSION["nombre"]; ?> (ADMIN)</p>

<ul>
    <li><a href="crearCurso.php">Crear curso</a></li>
    <li><a href="crearLeccion.php">Crear lección</a></li>
    <li><a href="gestionUsuarios.php">Gestion usuarios</a></li>
</ul>

<a href="../public/index.php">Volver a la academia</a>

</body>
</html>