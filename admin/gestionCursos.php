<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}
// ACTIVAR
if (isset($_GET["activar"])) {
    $id = intval($_GET["activar"]);
    $sql = "UPDATE cursos SET activo = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionCursos.php");
    exit;
}

// DESACTIVAR
if (isset($_GET["desactivar"])) {
    $id = intval($_GET["desactivar"]);
    $sql = "UPDATE cursos SET activo = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionCursos.php");
    exit;
}

$sql = "SELECT * FROM cursos ORDER BY id DESC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Cursos</title>

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>

<body>



<div class="main">
<div class="container">
<?php require_once "../includes/headerAdmin.php"; ?>
    <h2 class="section-title">Gestión de Cursos</h2>

    <!-- BOTÓN CREAR -->
    <div class="admin-top-actions">
        <a href="crearCurso.php" class="btn btn-primary">
            ➕ Crear curso
        </a>
    </div>
<br>

    <!-- LISTA -->
    <div class="admin-list">

    <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

        <div class="card admin-item">

            <!-- INFO -->
            <div class="admin-user-mini">
                <div>
                    <strong><?php echo htmlspecialchars($curso["titulo"]); ?></strong>

                    <span>
                        <?php if ($curso["activo"] == 1): ?>
                            <span class="status success">Activo</span>
                        <?php else: ?>
                            <span class="status danger">Desactivado</span>
                        <?php endif; ?>
                    </span>

                    <p>
                        💰 <?php echo $curso["precio"] > 0 ? $curso["precio"] . " €" : "Gratis"; ?>
                    </p>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="admin-actions">

                <a href="gestionarLecciones.php?curso_id=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                    Lecciones
                </a>

                <a href="editarCurso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-primary">
                    Editar
                </a>

                <?php if ($curso["activo"] == 1): ?>
                    <a href="?desactivar=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                        Desactivar
                    </a>
                <?php else: ?>
                    <a href="?activar=<?php echo $curso["id"]; ?>" class="btn btn-primary">
                        Activar
                    </a>
                <?php endif; ?>

                <a href="valoracionesCurso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                    Valoraciones
                </a>

                <!-- <a href="eliminarCurso.php?id=<?php echo $curso["id"]; ?>" 
                   class="btn btn-soft"
                   onclick="return confirm('¿Seguro que quieres eliminar este curso?');">
                    Eliminar
                </a> -->

            </div>

        </div>

    <?php endwhile; ?>

    </div>

    <!-- VOLVER -->
    <div class="admin-footer">
        <a href="panel.php" class="btn btn-soft">← Volver</a>
    </div>

</div>
</div>

<?php require_once "../includes/footer.php"; ?>

</body>
</html>