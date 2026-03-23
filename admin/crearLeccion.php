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
$mensaje = "";

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $video_url = trim($_POST["video_url"]);

    if (strlen($titulo) < 5) {
        $mensaje = "Título demasiado corto.";
    } elseif (strlen($video_url) < 5) {
        $mensaje = "URL de vídeo inválida.";
    } else {

        // Calcular orden automáticamente
        $sqlOrden = "SELECT MAX(orden) as ultimo FROM lecciones WHERE curso_id = ?";
        $stmt = mysqli_prepare($conexion, $sqlOrden);
        mysqli_stmt_bind_param($stmt, "i", $curso_id);
        mysqli_stmt_execute($stmt);
        $resOrden = mysqli_stmt_get_result($stmt);
        $filaOrden = mysqli_fetch_assoc($resOrden);
        $nuevoOrden = ($filaOrden["ultimo"] ?? 0) + 1;

        $sqlInsert = "INSERT INTO lecciones 
                      (curso_id, titulo, descripcion, video_url, orden)
                      VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conexion, $sqlInsert);
        mysqli_stmt_bind_param(
            $stmt,
            "isssi",
            $curso_id,
            $titulo,
            $descripcion,
            $video_url,
            $nuevoOrden
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: gestionarLecciones.php?curso_id=" . $curso_id);
            exit;
        } else {
            $mensaje = "Error al crear lección.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crear Lección</title>
</head>
<body>

<h2>Añadir lección a: <?php echo htmlspecialchars($curso["titulo"]); ?></h2>

<?php if ($mensaje): ?>
    <p style="color:red;"><?php echo $mensaje; ?></p>
<?php endif; ?>

<form method="POST">

    <label>Título</label><br>
    <input type="text" name="titulo" required><br><br>

    <label>Descripción</label><br>
    <textarea name="descripcion"></textarea><br><br>

    <label>URL vídeo (YouTube embed)</label><br>
    <input type="text" name="video_url" required><br><br>

    <button type="submit">Crear lección</button>

</form>

<br>
<a href="gestionarLecciones.php?curso_id=<?php echo $curso_id; ?>">
    ← Volver
</a>

</body>
</html>