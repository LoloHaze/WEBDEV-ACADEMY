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
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
</head>

<body>
    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">

            <h2 class="section-title">📚 Mis cursos</h2>

           

            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <div class="courses-grid">

                    <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

                        <?php
                        // COMPROBAR EXAMEN
                        $sql_examen =
                            "SELECT aprobado 
                        FROM resultados_examen 
                        WHERE usuario_id = ? AND curso_id = ?
                        ORDER BY fecha DESC
                        LIMIT 1
                        ";

                        $stmt_examen = mysqli_prepare($conexion, $sql_examen);
                        mysqli_stmt_bind_param($stmt_examen, "ii", $usuario_id, $curso["id"]);
                        mysqli_stmt_execute($stmt_examen);
                        $res_examen = mysqli_stmt_get_result($stmt_examen);

                        $examen = mysqli_fetch_assoc($res_examen);

                        $aprobado = $examen && $examen["aprobado"] == 1;
                        // TOTAL LECCIONES
                        $sql_total = "SELECT COUNT(*) as total FROM lecciones WHERE curso_id = ?";
                        $stmt_total = mysqli_prepare($conexion, $sql_total);
                        mysqli_stmt_bind_param($stmt_total, "i", $curso["id"]);
                        mysqli_stmt_execute($stmt_total);
                        $res_total = mysqli_stmt_get_result($stmt_total);
                        $total = mysqli_fetch_assoc($res_total)["total"];

                        // COMPLETADAS
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

                        <div class="course-card">

                            <div class="card-body">

                                <h3><?php echo htmlspecialchars($curso["titulo"]); ?></h3>

                                <!-- TEXTO PROGRESO -->
                                <div class="progress-text">
                                    <?php echo $completadas; ?> / <?php echo $total; ?> completadas
                                    (<?php echo $porcentaje; ?>%)
                                </div>

                                <!-- BARRA -->
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $porcentaje; ?>%">
                                        <span class="progress-percent"><?php echo $porcentaje; ?>%</span>
                                    </div>
                                </div>

                                <!-- ACCIONES -->
                                <div class="card-actions" style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">

                                    <!-- SI NO HA TERMINADO -->
                                    <?php if ($porcentaje < 100): ?>

                                        <a href="curso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-primary">
                                            Continuar
                                        </a>

                                        <!-- TERMINADO PERO SIN EXAMEN -->
                                    <?php elseif (!$aprobado): ?>

                                        <a href="examen.php?id=<?php echo $curso["id"]; ?>" class="btn btn-primary">
                                            📝 Hacer examen
                                        </a>

                                        <!-- APROBADO -->
                                    <?php else: ?>

                                        <a href="certificado.php?id=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                                            🎓 Descargar certificado
                                        </a>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="card" style="text-align:center;">
                    <p>No estás inscrito en ningún curso aún.</p>
                    <br>
                    <a href="index.php" class="btn btn-primary">Explorar cursos</a>
                </div>

            <?php endif; ?>
        </div>
    </div>
    <?php require_once "../includes/footer.php"; ?>
</body>

</html>