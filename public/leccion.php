<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$leccion_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

// Obtener datos de la lección
$sql = "SELECT l.*, c.titulo AS curso_titulo, c.id AS curso_id
        FROM lecciones l
        JOIN cursos c ON l.curso_id = c.id
        WHERE l.id = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $leccion_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$leccion = mysqli_fetch_assoc($resultado);

if (!$leccion) {
    header("Location: index.php");
    exit;
}

// Comprobar si ya está completada
$sql_check = "SELECT id FROM progreso 
              WHERE usuario_id = ? AND leccion_id = ?";

$stmt = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $leccion_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$completada = mysqli_stmt_num_rows($stmt) > 0;

// Marcar como completada
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$completada) {

    $sql_insert = "INSERT INTO progreso (usuario_id, leccion_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conexion, $sql_insert);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $leccion_id);
    mysqli_stmt_execute($stmt);

    header("Location: leccion.php?id=" . $leccion_id);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($leccion["titulo"]); ?></title>
</head>
<body>

<a href="curso.php?id=<?php echo $leccion["curso_id"]; ?>">← Volver al curso</a>

<h2><?php echo htmlspecialchars($leccion["titulo"]); ?></h2>
<p><?php echo htmlspecialchars($leccion["descripcion"]); ?></p>

<!-- Vídeo -->
<iframe width="560" height="315"
    src="<?php echo htmlspecialchars($leccion['video_url']); ?>"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
    allowfullscreen>
</iframe>

<br><br>

<?php if ($completada): ?>
    <p style="color:green;">Lección completada</p>
<?php else: ?>
    <form method="POST">
        <button type="checkbox">Marcar como completada</button>
    </form>
<?php endif; ?>

</body>
</html>