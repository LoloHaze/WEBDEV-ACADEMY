<?php
// CURSO
// -------------------------------------
// - MUESTRA INFORMACIÓN DEL CURSO
// - GESTIONA INSCRIPCIÓN
// - CALCULA PROGRESO
// - PERMITE VALORAR
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/funciones.php";
session_start();

// PROTECCIÓN LOGIN
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// VALIDAR ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$curso_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

// CANCELAR INSCRIPCIÓN
if (isset($_POST["cancelar_inscripcion"])) {
    cancelarInscripcion($conexion, $usuario_id, $curso_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// OBTENER CURSO
$curso = obtenerCurso($conexion, $curso_id);

if (!$curso || $curso["activo"] != 1) {
    header("Location: index.php");
    exit;
}

// SOLICITAR INSCRIPCIÓN
if (isset($_POST["solicitar_inscripcion"])) {
    gestionarInscripcion($conexion, $usuario_id, $curso_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// INSCRIPCIÓN
$inscripcion = obtenerInscripcion($conexion, $usuario_id, $curso_id);

// PROGRESO Y LECCIONES
if ($inscripcion && $inscripcion["estado"] === "aprobado") {

    $progreso = obtenerProgresoCurso($conexion, $usuario_id, $curso_id);
    $total = $progreso["total"];
    $completadas = $progreso["completadas"];
    $porcentaje = $progreso["porcentaje"];

    $resultado_lecciones = obtenerLecciones($conexion, $curso_id);
}
// ELIMINAR VALORACIÓN
if (isset($_POST["eliminar_id"])) {
    eliminarValoracion($conexion, intval($_POST["eliminar_id"]), $usuario_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// EDITAR VALORACIÓN
$valoracion_editar = null;

if (isset($_POST["editar_id"])) {
    $valoracion_editar = obtenerValoracion($conexion, intval($_POST["editar_id"]), $usuario_id);
}

// GUARDAR VALORACIÓN
if (isset($_POST["valorar"]) && $inscripcion && $inscripcion["estado"] === "aprobado") {
    guardarValoracion($conexion, $usuario_id, $curso_id, $_POST);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}
// DATOS VALORACIONES
$valoracion_existente = obtenerValoracionUsuario($conexion, $usuario_id, $curso_id);
$datos_media = obtenerMediaCurso($conexion, $curso_id);
$media = round($datos_media["media"], 1);
$total_val = $datos_media["total"];

// EXAMEN
$examen_aprobado = examenAprobado($conexion, $usuario_id, $curso_id);

// COMENTARIOS
$resultado_listar = obtenerComentariosCurso($conexion, $curso_id);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/components.css">

    <title><?php echo htmlspecialchars($curso["titulo"]); ?></title>
</head>

<body>
    <?php require_once "../includes/header.php"; ?>

    <div class="main">

        <div class="container">

            <!--CURSO INFO-->
            <div class="course-hero">
                <div class="hero-left">
                    <h1 class="section-title"><?php echo htmlspecialchars($curso["titulo"]); ?></h1>
                    <div class="hero-rating">
                        <?php if ($total_val > 0): ?>
                            <?php $media_redondeada = floor($media); ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $media_redondeada): ?>
                                    <span class="star filled">★</span>
                                <?php else: ?>
                                    <span class="star">★</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="rating-count">(<?php echo $total_val; ?>)</span>
                        <?php else: ?>
                            <span class="no-rating">Sin valoraciones</span>
                        <?php endif; ?>
                    </div>
                    <p class="card-desc"><?php echo htmlspecialchars($curso["descripcion"]); ?></p>
                    <!-- PROGRESO -->
                    <?php if ($inscripcion && $inscripcion["estado"] === "aprobado"): ?>
                        <div class="hero-progress-row">
                            <div class="hero-progress">
                                <div class="progress-text">
                                    <?php echo $completadas; ?> / <?php echo $total; ?> completadas
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $porcentaje; ?>%">
                                        <span class="progress-percent"><?php echo $porcentaje; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                <div class="hero-right">

                    <?php if (!$inscripcion || $inscripcion["estado"] === "rechazado"): ?>

                        <form method="POST">
                            <button class="btn btn-primary" type="submit" name="solicitar_inscripcion">
                                Inscribirme
                            </button>
                        </form>

                    <?php elseif ($inscripcion["estado"] === "pendiente"): ?>

                        <form method="POST">
                            <button class="btn btn-danger" type="submit" name="cancelar_inscripcion">
                                Cancelar solicitud
                            </button>
                        </form>

                    <?php elseif ($inscripcion["estado"] === "aprobado"): ?>
                        <form method="POST">
                            <button class="btn btn-soft2" type="submit" name="cancelar_inscripcion">
                                Darse de baja
                            </button>
                        </form>
                        <?php if ($porcentaje >= 100 && !$examen_aprobado): ?>
                            <a href="examen.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">
                                Examen
                            </a>
                        <?php endif; ?>

                        <?php if ($porcentaje >= 100 && $examen_aprobado): ?>
                            <a href="certificado.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">
                                Certificado
                            </a>
                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </div>

            <!--CONTENIDO CURSO-->

            <?php if ($inscripcion && $inscripcion["estado"] === "aprobado"): ?>

                <!--LECCIONES-->

                <div class="lessons-section">

                    <h3>Lecciones</h3>

                    <?php while ($leccion = mysqli_fetch_assoc($resultado_lecciones)): ?>

                        <?php
                        $esta_completada = leccionCompletada($conexion, $usuario_id, $leccion["id"]);
                        ?>

                        <div class="lesson-item">
                            <div class="lesson-info">
                                <h4>
                                    <?php echo $leccion["orden"]; ?>.
                                    <?php echo htmlspecialchars($leccion["titulo"]); ?>

                                    <?php if ($esta_completada): ?>
                                        <span style="color:#16a34a;">✔</span>
                                    <?php endif; ?>
                                </h4>

                                <p><?php echo htmlspecialchars($leccion["descripcion"]); ?></p>
                            </div>
                            <a href="leccion.php?id=<?php echo $leccion["id"]; ?>" class="btn btn-primary">
                                Ver lección
                            </a>

                        </div>

                    <?php endwhile; ?>

                </div>
                <!-- VALORACIÓN-->

                <?php if (!$valoracion_existente || $valoracion_editar): ?>
                    <div class="card rating-card">

                        <h3>Valorar curso</h3>

                        <form method="POST" class="rating-form">

                            <!-- ESTRELLAS -->
                            <div class="stars-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="puntuacion" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                    <label for="star<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>
                            <!--  COMENTARIO -->
                            <textarea name="comentario" placeholder="Escribe tu opinión..."></textarea>

                            <!-- BOTÓN -->
                            <button class="btn btn-primary" type="submit" name="valorar">
                                Enviar valoración
                            </button>

                        </form>

                    </div>
                <?php endif; ?>
                <!--  COMENTARIOS  -->
                <div class="comments-section">
                    <h3>💬 Comentarios</h3>
                    <?php if (mysqli_num_rows($resultado_listar) > 0): ?>
                        <?php while ($val = mysqli_fetch_assoc($resultado_listar)): ?>
                            <div class="comment-item">
                                <div class="comment-content">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $val["puntuacion"]): ?>
                                            <span style="color:gold;">★</span>
                                        <?php else: ?>
                                            <span style="color:#ccc;">★</span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <strong><?php echo htmlspecialchars($val["nombre"]); ?></strong>
                                    <small style="color:#777;">
                                        (<?php echo date("d/m/Y", strtotime($val["fecha"])); ?>)
                                    </small>
                                    <p><?php echo nl2br(htmlspecialchars($val["comentario"])); ?></p>
                                </div>
                                <div class="comment-actions">
                                    <?php if ($val["usuario_id"] == $usuario_id): ?>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="editar_id" value="<?php echo $val["id"]; ?>">
                                            <button class="btn btn-primary">Editar</button>
                                        </form>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="eliminar_id" value="<?php echo $val["id"]; ?>">
                                            <button class="btn btn-soft2" onclick="return confirm('¿Eliminar?');">
                                                Eliminar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Este curso aún no tiene valoraciones.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php require_once "../includes/footer.php"; ?>
</body>

</html>