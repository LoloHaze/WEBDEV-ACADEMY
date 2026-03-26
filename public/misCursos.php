<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

/* ================================
   OBTENER CURSOS APROBADOS
================================ */

$sql = "
SELECT c.*
FROM inscripciones i
JOIN cursos c ON i.curso_id = c.id
WHERE i.usuario_id = ?
AND i.estado = 'aprobado'
AND c.activo = 1
";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis cursos</title>
</head>
<body>

<h2>📚 Mis cursos</h2>
<a href="index.php">← Volver a la academia</a>
<hr>

<?php if (mysqli_num_rows($resultado) > 0): ?>

<?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

<?php
// TOTAL LECCIONES
$sql_total = "SELECT COUNT(*) as total FROM lecciones WHERE curso_id = ?";
$stmt_total = mysqli_prepare($conexion, $sql_total);
mysqli_stmt_bind_param($stmt_total, "i", $curso["id"]);
mysqli_stmt_execute($stmt_total);
$res_total = mysqli_stmt_get_result($stmt_total);
$total = mysqli_fetch_assoc($res_total)["total"];

// LECCIONES COMPLETADAS
$sql_comp = "
SELECT COUNT(*) as completadas
FROM progreso p
JOIN lecciones l ON p.leccion_id = l.id
WHERE p.usuario_id = ? AND l.curso_id = ?
";
$stmt_comp = mysqli_prepare($conexion, $sql_comp);
mysqli_stmt_bind_param($stmt_comp, "ii", $usuario_id, $curso["id"]);
mysqli_stmt_execute($stmt_comp);
$res_comp = mysqli_stmt_get_result($stmt_comp);
$completadas = mysqli_fetch_assoc($res_comp)["completadas"];

$porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
?>

<div style="border:1px solid #ccc; padding:15px; margin:15px 0; border-radius:8px;">

    <h3><?php echo htmlspecialchars($curso["titulo"]); ?></h3>

    <p>Progreso: <?php echo $completadas; ?> / <?php echo $total; ?>
    (<?php echo $porcentaje; ?>%)</p>

    <div style="width:300px; height:15px; background:#ddd; border-radius:10px;">
        <div style="
            width: <?php echo $porcentaje; ?>%;
            height:100%;
            background:green;
            border-radius:10px;">
        </div>
    </div>

    <br>

    <a href="curso.php?id=<?php echo $curso["id"]; ?>"
       style="padding:8px 12px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">
       Continuar curso
    </a>

    <?php if ($porcentaje >= 100): ?>
        <br><br>
        <a href="certificado.php?id=<?php echo $curso["id"]; ?>">
            🎓 Descargar certificado
        </a>
    <?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>No estás inscrito en ningún curso aún.</p>

<?php endif; ?>

</body>
</html>