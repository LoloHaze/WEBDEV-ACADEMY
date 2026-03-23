<?php
require_once "../includes/bd.php";
session_start();

/* ==========================
   PROTECCIÓN ADMIN
========================== */
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

/* ==========================
   VALIDAR ID CURSO
========================== */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$curso_id = intval($_GET["id"]);

/* ==========================
   ELIMINAR VALORACIÓN
========================== */
if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {

    $val_id = intval($_GET["eliminar"]);

    $sqlDel = "DELETE FROM valoraciones WHERE id = ?";
    $stmtDel = mysqli_prepare($conexion, $sqlDel);
    mysqli_stmt_bind_param($stmtDel, "i", $val_id);
    mysqli_stmt_execute($stmtDel);

    header("Location: valoracionesCurso.php?id=" . $curso_id);
    exit;
}

/* ==========================
   OBTENER CURSO
========================== */
$sqlCurso = "SELECT titulo FROM cursos WHERE id = ?";
$stmtCurso = mysqli_prepare($conexion, $sqlCurso);
mysqli_stmt_bind_param($stmtCurso, "i", $curso_id);
mysqli_stmt_execute($stmtCurso);
$resCurso = mysqli_stmt_get_result($stmtCurso);
$curso = mysqli_fetch_assoc($resCurso);

if (!$curso) {
    header("Location: gestionCursos.php");
    exit;
}

/* ==========================
   OBTENER VALORACIONES
========================== */
$sql = "SELECT v.id, v.puntuacion, v.comentario, v.fecha,
               u.nombre
        FROM valoraciones v
        JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.curso_id = ?
        ORDER BY v.fecha DESC";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

/* ==========================
   CALCULAR MEDIA
========================== */
$sqlMedia = "SELECT AVG(puntuacion) as media, COUNT(*) as total
             FROM valoraciones
             WHERE curso_id = ?";
$stmtMedia = mysqli_prepare($conexion, $sqlMedia);
mysqli_stmt_bind_param($stmtMedia, "i", $curso_id);
mysqli_stmt_execute($stmtMedia);
$resMedia = mysqli_stmt_get_result($stmtMedia);
$datosMedia = mysqli_fetch_assoc($resMedia);

$media = round($datosMedia["media"], 1);
$total = $datosMedia["total"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Valoraciones del Curso</title>
</head>
<body>

<h2>Valoraciones: <?php echo htmlspecialchars($curso["titulo"]); ?></h2>

<a href="gestionCursos.php">← Volver a gestión de cursos</a>

<hr>

<p>
<strong>Media:</strong>
<?php if ($total > 0): ?>

    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span style="color:<?php echo $i <= round($media) ? 'gold' : '#ccc'; ?>">★</span>
    <?php endfor; ?>

    (<?php echo $media; ?> / 5 — <?php echo $total; ?> valoraciones)

<?php else: ?>
    Sin valoraciones
<?php endif; ?>
</p>

<hr>

<?php if (mysqli_num_rows($resultado) > 0): ?>

    <?php while ($val = mysqli_fetch_assoc($resultado)): ?>

        <div style="border:1px solid #ccc; padding:15px; margin:15px 0; border-radius:8px;">

            <strong><?php echo htmlspecialchars($val["nombre"]); ?></strong>
            <br>

            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span style="color:<?php echo $i <= $val["puntuacion"] ? 'gold' : '#ccc'; ?>">★</span>
            <?php endfor; ?>

            <br><br>

            <p><?php echo nl2br(htmlspecialchars($val["comentario"])); ?></p>

            <small>
                <?php echo date("d/m/Y H:i", strtotime($val["fecha"])); ?>
            </small>

            <br><br>

            <a href="?id=<?php echo $curso_id; ?>&eliminar=<?php echo $val["id"]; ?>"
               onclick="return confirm('¿Eliminar esta valoración?');"
               style="color:red;">
               🗑 Eliminar
            </a>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>No hay valoraciones en este curso.</p>

<?php endif; ?>

</body>
</html>