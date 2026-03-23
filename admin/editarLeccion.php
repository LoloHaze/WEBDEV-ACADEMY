<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$id = intval($_GET["id"]);
$mensaje = "";

// Obtener lección
$sql = "SELECT * FROM lecciones WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$leccion = mysqli_fetch_assoc($resultado);

if (!$leccion) {
    header("Location: gestionCursos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $video_url = trim($_POST["video_url"]);

    if (strlen($titulo) < 5) {
        $mensaje = "Título demasiado corto.";
    } else {

        $sqlUpdate = "UPDATE lecciones
                      SET titulo = ?, descripcion = ?, video_url = ?
                      WHERE id = ?";

        $stmt = mysqli_prepare($conexion, $sqlUpdate);
        mysqli_stmt_bind_param($stmt, "sssi",
            $titulo,
            $descripcion,
            $video_url,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Lección actualizada correctamente.";
            $leccion["titulo"] = $titulo;
            $leccion["descripcion"] = $descripcion;
            $leccion["video_url"] = $video_url;
        } else {
            $mensaje = "Error al actualizar.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Lección</title>
</head>
<body>

<h2>Editar Lección</h2>

<?php if ($mensaje): ?>
    <p><?php echo $mensaje; ?></p>
<?php endif; ?>

<form method="POST">

    <label>Título</label><br>
    <input type="text" name="titulo"
        value="<?php echo htmlspecialchars($leccion["titulo"]); ?>"
        required><br><br>

    <label>Descripción</label><br>
    <textarea name="descripcion"><?php
        echo htmlspecialchars($leccion["descripcion"]);
    ?></textarea><br><br>

    <label>Video URL</label><br>
    <input type="text" name="video_url"
        value="<?php echo htmlspecialchars($leccion["video_url"]); ?>"
        required><br><br>

    <button type="submit">Guardar cambios</button>

</form>

<br>
<a href="gestionarLecciones.php?curso_id=<?php echo $leccion["curso_id"]; ?>">
    ← Volver
</a>

</body>
</html>