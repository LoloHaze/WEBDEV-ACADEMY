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
</head>
<body>

<h2>Lecciones de: <?php echo htmlspecialchars($curso["titulo"]); ?></h2>

<a href="crearLeccion.php?curso_id=<?php echo $curso_id; ?>">
    ➕ Añadir lección
</a>

<br><br>

<?php while ($leccion = mysqli_fetch_assoc($resultado)): ?>

    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <strong><?php echo $leccion["orden"]; ?>. 
            <?php echo htmlspecialchars($leccion["titulo"]); ?>
        </strong>
        <br>
        <a href="editarLeccion.php?id=<?php echo $leccion["id"]; ?>">
            ✏ Editar
        </a> |
        <a href="eliminarLeccion.php?id=<?php echo $leccion["id"]; ?>&curso_id=<?php echo $curso_id; ?>"
           onclick="return confirm('¿Eliminar lección?');">
           🗑 Eliminar
        </a>
    </div>

<?php endwhile; ?>

<br>
<a href="gestionCursos.php">← Volver a cursos</a>

</body>
</html>