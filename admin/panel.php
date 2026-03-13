<?php
require_once "../includes/bd.php";
session_start();

// Protección login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../public/login.php");
    exit;
}

// Protección rol admin
if ($_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Foto desde sesión
$foto = isset($_SESSION["foto"]) && $_SESSION["foto"]
    ? "../public/uploads/perfiles/" . $_SESSION["foto"]
    : "https://placekitten.com/640/360";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Admin - WebDev Academy</title>
</head>
<body>

<!-- Barra superior admin -->
<div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #ccc;">

    <div style="display:flex; align-items:center; gap:10px;">
        <img src="<?php echo $foto; ?>" 
             width="50" height="50"
             style="border-radius:50%; object-fit:cover;">
        <div>
            <strong><?php echo $_SESSION["nombre"]; ?></strong><br>
            <small>Administrador</small>
        </div>
    </div>

    <div>
        <a href="../public/index.php">Volver a la academia</a> |
        <a href="../public/logout.php">Cerrar sesión</a>
    </div>

</div>

<h2>Panel de Administración</h2>

<ul>
    <li><a href="crearCurso.php">Crear curso</a></li>
    <li><a href="crearLeccion.php">Crear lección</a></li>
    <li><a href="gestionUsuarios.php">Gestionar usuarios</a></li>
</ul>

</body>
</html>