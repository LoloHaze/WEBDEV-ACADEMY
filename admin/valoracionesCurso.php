<?php
require_once "../includes/bd.php";
session_start();


//PROTECCIÓN ADMIN

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}


//VALIDAR ID CURSO

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$curso_id = intval($_GET["id"]);

// ELIMINAR VALORACIÓN
if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {

    $val_id = intval($_GET["eliminar"]);

    $sqlDel = "DELETE FROM valoraciones WHERE id = ?";
    $stmtDel = mysqli_prepare($conexion, $sqlDel);
    mysqli_stmt_bind_param($stmtDel, "i", $val_id);
    mysqli_stmt_execute($stmtDel);

    header("Location: valoracionesCurso.php?id=" . $curso_id);
    exit;
}

//OBTENER CURSO

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
//OBTENER VALORACIONES
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

//CALCULAR MEDIA

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
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valoraciones del Curso</title>

    <link rel="icon" href="../public/assets/logowebdev.png" type="image/png">

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <link rel="stylesheet" href="../public/assets/css/cursos.css">
    <link rel="stylesheet" href="../public/assets/css/reescalado.css">
    <link rel="stylesheet" href="../public/assets/css/responsiveAdmin.css">

      <script src="../public/assets/js/responsiveAdmin.js" defer></script>
</head>

<body>
    <div class="main">
        <div class="container">
            <?php require_once "../includes/headerAdmin.php"; ?>

            <h2 class="section-title">
                Valoraciones: <?php echo htmlspecialchars($curso["titulo"]); ?>
            </h2>

            <!-- MEDIA -->
            <div class="card" style="text-align:center; margin-bottom:20px;">

                <p><strong>Media:</strong></p>

                <?php if ($total > 0): ?>

                    <div class="hero-rating">

                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= round($media) ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>

                    </div>

                    <p class="rating-info">
                        <?php echo $media; ?> / 5 — <?php echo $total; ?> valoraciones
                    </p>

                <?php else: ?>
                    <p class="empty-text">Sin valoraciones</p>
                <?php endif; ?>

            </div>

            <!-- LISTA -->
            <div class="admin-list">

                <?php if (mysqli_num_rows($resultado) > 0): ?>

                    <?php while ($val = mysqli_fetch_assoc($resultado)): ?>

                        <div class="card admin-item">

                            <!-- INFO -->
                            <div class="admin-user-mini">
                                <div>
                                    <strong><?php echo htmlspecialchars($val["nombre"]); ?></strong>
                                    <p class="rating-course">
                                        📚 <?php echo htmlspecialchars($curso["titulo"]); ?>
                                    </p>

                                    <div class="hero-rating">

                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $val["puntuacion"] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>

                                    </div>

                                    <p class="rating-comment">
                                        <?php echo nl2br(htmlspecialchars($val["comentario"])); ?>
                                    </p>

                                    <span class="rating-date">
                                        <?php echo date("d/m/Y H:i", strtotime($val["fecha"])); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- ACCIONES -->
                            <div class="admin-actions">
                                <a href="?id=<?php echo $curso_id; ?>&eliminar=<?php echo $val["id"]; ?>" class="btn btn-soft2"
                                    onclick="return confirm('¿Eliminar esta valoración?');">
                                    Eliminar
                                </a>
                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p class="empty-text">No hay valoraciones en este curso.</p>

                <?php endif; ?>

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