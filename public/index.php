<?php
require_once "../includes/bd.php";
session_start();

// Protección login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener cursos
$sqlCursos = "SELECT * FROM cursos";
$resultadoCursos = mysqli_query($conexion, $sqlCursos);

// Preparar foto desde sesión (seguro)
$foto = isset($_SESSION["foto"]) && $_SESSION["foto"]
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://loremflickr.com/320/240";
?>

<!DOCTYPE html>
<html>
<head>
    <title>WebDev Academy</title>
</head>

<body>

<!-- NAVBAR USUARIO -->
<div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #ccc;">

    <div style="display:flex; align-items:center; gap:10px;">
        <img src="<?php echo $foto; ?>" 
             width="40" height="40"
             style="border-radius:50%; object-fit:cover;">

        <strong>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?> 👋</strong>
    </div>

    <div>
        <?php if ($_SESSION["rol"] === "admin"): ?>
            <a href="../admin/panel.php">Panel Admin</a> |
        <?php endif; ?>

        <a href="perfil.php">Mi perfil</a> |
        <a href="logout.php">Cerrar sesión</a>
    </div>

</div>

<h2>Academia</h2>

<h3>Cursos disponibles</h3>

<?php if (mysqli_num_rows($resultadoCursos) > 0): ?>

    <?php while ($curso = mysqli_fetch_assoc($resultadoCursos)): ?>

        <div style="border:1px solid #ccc; padding:15px; margin:15px 0;">
            <h4><?php echo htmlspecialchars($curso["titulo"]); ?></h4>
            <p><?php echo htmlspecialchars($curso["descripcion"]); ?></p>

            <a href="curso.php?id=<?php echo $curso["id"]; ?>">
                Ver curso
            </a>
        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>No hay cursos disponibles aún.</p>

<?php endif; ?>

</body>
</html>