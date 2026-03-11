<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM cursos";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>WebDev Academy</title>
</head>

<body>

    <h2>Bienvenido, <?php echo $_SESSION["nombre"]; ?> 👋</h2>

    <?php if ($_SESSION["rol"] === "admin"): ?>
        <a href="../admin/panel.php">⚙ Panel Admin</a> |
    <?php endif; ?>

    <a href="logout.php">Cerrar sesión</a>

    <h3>Cursos disponibles</h3>

    <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <h4><?php echo htmlspecialchars($curso["titulo"]); ?></h4>
            <p><?php echo htmlspecialchars($curso["descripcion"]); ?></p>
            <a href="curso.php?id=<?php echo $curso["id"]; ?>">Ver curso</a>
        </div>

    <?php endwhile; ?>

</body>

</html>