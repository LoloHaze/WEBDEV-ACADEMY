<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

if (!isset($_GET["curso_id"]) || !is_numeric($_GET["curso_id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$curso_id = intval($_GET["curso_id"]);

// Obtener curso
$sqlCurso = "SELECT titulo FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sqlCurso);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$resCurso = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($resCurso);

if (!$curso) {
    header("Location: gestionCursos.php");
    exit;
}

// Obtener lecciones
$sql = "SELECT * FROM lecciones 
        WHERE curso_id = ? 
        ORDER BY orden ASC";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestionar Lecciones</title>

    <!-- 🔥 REUTILIZAMOS TODO -->
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>

<body>



<div class="main">
<div class="container">
<?php require_once "../includes/headerAdmin.php"; ?>
    <!-- TITULO -->
    <h2 class="section-title">
        Lecciones de: <?php echo htmlspecialchars($curso["titulo"]); ?>
    </h2>

    <!-- BOTÓN CREAR -->
    <div class="admin-top-actions">
        <a href="crearLeccion.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-primary">
            ➕ Añadir lección
        </a>
    </div>
    <br>

    <!-- LISTA -->
    <div class="admin-list">

    <?php while ($leccion = mysqli_fetch_assoc($resultado)): ?>

        <div class="card admin-item">

            <!-- INFO -->
            <div class="admin-user-mini">
                <div>
                    <strong>
                        <?php echo $leccion["orden"]; ?>.
                        <?php echo htmlspecialchars($leccion["titulo"]); ?>
                    </strong>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="admin-actions">

                <a href="editarLeccion.php?id=<?php echo $leccion["id"]; ?>" class="btn btn-primary">
                    Editar
                </a>

                <a href="eliminarLeccion.php?id=<?php echo $leccion["id"]; ?>&curso_id=<?php echo $curso_id; ?>"
                   class="btn btn-soft"
                   onclick="return confirm('¿Eliminar lección?');">
                   Eliminar
                </a>

            </div>

        </div>

    <?php endwhile; ?>

    </div>

    <!-- VOLVER -->
    <div class="admin-footer">
        <a href="gestionCursos.php" class="btn btn-soft">
            ← Volver a cursos
        </a>
    </div>

</div>
</div>



</body>
</html>