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
require_once "../includes/proteccion.php";

session_start();

// VALIDAR ID
$curso_id = validarId();

// LOGIN CONTROL
$logueado = isset($_SESSION["usuario_id"]);
$usuario_id = $logueado ? $_SESSION["usuario_id"] : null;

// ACTUALIZAR ÚLTIMA VISITA SOLO SI ESTÁ LOGUEADO
if ($logueado) {
    $sql = "UPDATE inscripciones
            SET ultima_visita = NOW()
            WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
}

// CANCELAR INSCRIPCIÓN
if ($logueado && isset($_POST["cancelar_inscripcion"])) {
    cancelarInscripcion($conexion, $usuario_id, $curso_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// OBTENER CURSO
$curso = obtenerCurso($conexion, $curso_id);
validarCursoActivo($curso);

// SOLICITAR INSCRIPCIÓN
if ($logueado && isset($_POST["solicitar_inscripcion"])) {
    gestionarInscripcion($conexion, $usuario_id, $curso_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// INSCRIPCIÓN
$inscripcion = $logueado
    ? obtenerInscripcion($conexion, $usuario_id, $curso_id)
    : null;

// PROGRESO SOLO SI APROBADO
if ($inscripcion && $inscripcion["estado"] === "aprobado") {
    $progreso = obtenerProgresoCurso($conexion, $usuario_id, $curso_id);
    $total = $progreso["total"];
    $completadas = $progreso["completadas"];
    $porcentaje = $progreso["porcentaje"];
}


// VALORACIONES
if ($logueado && isset($_POST["eliminar_id"])) {
    eliminarValoracion($conexion, intval($_POST["eliminar_id"]), $usuario_id);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

$valoracion_editar = null;
if ($logueado && isset($_POST["editar_id"])) {
    $valoracion_editar = obtenerValoracion($conexion, intval($_POST["editar_id"]), $usuario_id);
}

if ($logueado && isset($_POST["valorar"]) && $inscripcion && $inscripcion["estado"] === "aprobado") {
    guardarValoracion($conexion, $usuario_id, $curso_id, $_POST);
    header("Location: curso.php?id=" . $curso_id);
    exit;
}

$valoracion_existente = $logueado
    ? obtenerValoracionUsuario($conexion, $usuario_id, $curso_id)
    : null;

$datos_media = obtenerMediaCurso($conexion, $curso_id);
$media = round($datos_media["media"], 1);
$total_val = $datos_media["total"];

$examen_aprobado = $logueado
    ? examenAprobado($conexion, $usuario_id, $curso_id)
    : false;

$resultado_listar = obtenerComentariosCurso($conexion, $curso_id);

// SIEMPRE OBTENEMOS LECCIONES
$resultado_lecciones = obtenerLecciones($conexion, $curso_id);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <script src="../public/assets/js/responsive.js" defer></script>
    <script src="../public/assets/js/forms.js" defer></script>

    <title><?php echo htmlspecialchars($curso["titulo"]); ?></title>
</head>

<body>
    <?php require_once "../includes/header.php"; ?>

    <div class="main">
        <div class="container">

            <!-- HERO -->
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

                    <?php if (!$logueado): ?>
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-primary">
                            Inicia sesión para inscribirte
                        </a>

                    <?php elseif (!$inscripcion || $inscripcion["estado"] === "rechazado"): ?>

                        <?php if ($curso["precio"] > 0): ?>

                            <a href="checkout.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">

                                Comprar curso

                            </a>

                        <?php else: ?>

                            <form method="POST">

                                <button class="btn btn-primary" type="submit" name="solicitar_inscripcion">

                                    Inscribirme

                                </button>

                            </form>

                        <?php endif; ?>

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
                            <a href="examen.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">Examen</a>
                        <?php endif; ?>

                        <?php if ($porcentaje >= 100 && $examen_aprobado): ?>
                            <a href="certificado.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">Certificado</a>
                        <?php endif; ?>

                    <?php endif; ?>
                    <?php if ($logueado && $_SESSION["rol"] === "admin"): ?>

                        <div class="admin-course-actions">

                            <a href="../admin/editarCurso.php?id=<?php echo $curso_id; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-soft">

                                Editar curso

                            </a>

                            <a href="../admin/crearLeccion.php?curso_id=<?php echo $curso_id; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-soft">

                                Añadir lección

                            </a>

                            <a href="../admin/gestionCursos.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-soft">

                                Panel cursos

                            </a>

                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <!-- LECCIONES SIEMPRE VISIBLES -->
            <div class="lessons-section">
                <h2>Lecciones</h2>

                <?php while ($leccion = mysqli_fetch_assoc($resultado_lecciones)):

                    $esta_completada = ($logueado && $inscripcion && $inscripcion["estado"] === "aprobado")
                        ? leccionCompletada($conexion, $usuario_id, $leccion["id"])
                        : false;
                    ?>

                    <div class="lesson-item">
                        <div class="lesson-info">
                            <h3>
                                <?php echo $leccion["orden"]; ?>.
                                <?php echo htmlspecialchars($leccion["titulo"]); ?>

                                <?php if ($esta_completada): ?>
                                    <span style="color:#16a34a;">✔</span>
                                <?php endif; ?>
                            </h3>
                            <p><?php echo htmlspecialchars($leccion["descripcion"]); ?></p>
                        </div>

                        <?php if (!$logueado): ?>
                            <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-primary">
                                Inicia sesión para acceder
                            </a>

                        <?php elseif (!$inscripcion || $inscripcion["estado"] !== "aprobado"): ?>
                            <button class="btn btn-soft" disabled>Inscríbete para acceder</button>

                        <?php else: ?>

                            <div style="display:flex; gap:10px; flex-wrap:wrap;">

                                <a href="leccion.php?id=<?php echo $leccion["id"]; ?>" class="btn btn-primary">

                                    Ver lección

                                </a>

                                <?php if ($_SESSION["rol"] === "admin"): ?>

                                    <a href="../admin/editarLeccion.php?id=<?php echo $leccion["id"]; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                        class="btn btn-soft">

                                        Editar

                                    </a>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>
            </div>


            <!-- VALORACIÓN -->
            <!-- VALORACIÓN SOLO SI INSCRITO -->
            <?php if ($inscripcion && $inscripcion["estado"] === "aprobado"): ?>

                <?php if (!$valoracion_existente || isset($_POST["editar_id"])): ?>
                    <div class="card rating-card">

                        <h3>Valorar curso</h3>

                        <form method="POST" class="rating-form" id="formValoracion">

                            <div class="stars-input" id="grupoEstrellas">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="puntuacion" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                    <label for="star<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>

                            <p class="error-msg" id="errorPuntuacion"></p>

                            <textarea name="comentario" id="comentario" placeholder="Escribe tu opinión..."></textarea>
                            <p class="error-msg" id="errorComentario"></p>

                            <button class="btn btn-primary" type="submit" name="valorar">
                                Enviar valoración
                            </button>

                        </form>

                    </div>
                <?php endif; ?>

            <?php endif; ?>


            <!-- COMENTARIOS SIEMPRE VISIBLES -->
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

                                <strong>
                                    <?php echo htmlspecialchars($val["nombre"]); ?>
                                </strong>

                                <small style="color:#777;">
                                    (<?php echo date("d/m/Y", strtotime($val["fecha"])); ?>)
                                </small>

                                <p>
                                    <?php echo nl2br(htmlspecialchars($val["comentario"])); ?>
                                </p>

                            </div>

                            <div class="comment-actions">

                                <?php if ($logueado && $val["usuario_id"] == $usuario_id): ?>

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


        </div>
    </div>

    <?php require_once "../includes/footer.php"; ?>
</body>

</html>