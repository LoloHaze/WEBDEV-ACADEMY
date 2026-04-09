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

$curso_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

if (isset($_POST["cancelar_inscripcion"])) {
    $sql = "DELETE FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);

    header("Location: curso.php?id=" . $curso_id);
    exit;
}

/* ================================
   OBTENER CURSO
================================ */
$sql_curso = "SELECT * FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_curso);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$resultado_curso = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($resultado_curso);

if (!$curso) {
    header("Location: index.php");
    exit;
}
// 🔒 Bloquear acceso a cursos desactivados
if ($curso["activo"] != 1) {
    header("Location: index.php");
    exit;
}

/* ================================
   PROCESAR SOLICITUD INSCRIPCIÓN
================================ */
if (isset($_POST["solicitar_inscripcion"])) {

    // Comprobar si ya existe inscripción
    $sqlCheck = "SELECT estado FROM inscripciones 
                 WHERE usuario_id = ? AND curso_id = ?";
    $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $inscExistente = mysqli_fetch_assoc($resCheck);

    if (!$inscExistente) {

        // No existe → INSERT
        $sqlInsert = "INSERT INTO inscripciones (usuario_id, curso_id, estado)
                      VALUES (?, ?, 'pendiente')";
        $stmtInsert = mysqli_prepare($conexion, $sqlInsert);
        mysqli_stmt_bind_param($stmtInsert, "ii", $usuario_id, $curso_id);
        mysqli_stmt_execute($stmtInsert);

    } elseif ($inscExistente["estado"] === "rechazado") {

        // Existe pero estaba rechazada → UPDATE a pendiente
        $sqlUpdate = "UPDATE inscripciones 
                      SET estado = 'pendiente'
                      WHERE usuario_id = ? AND curso_id = ?";
        $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ii", $usuario_id, $curso_id);
        mysqli_stmt_execute($stmtUpdate);
    }

    header("Location: curso.php?id=" . $curso_id);
    exit;
}

/* ================================
   COMPROBAR INSCRIPCIÓN
================================ */
$sql_inscripcion = "SELECT estado FROM inscripciones
                    WHERE usuario_id = ? AND curso_id = ?";
$stmt = mysqli_prepare($conexion, $sql_inscripcion);
mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
mysqli_stmt_execute($stmt);
$resultado_inscripcion = mysqli_stmt_get_result($stmt);
$inscripcion = mysqli_fetch_assoc($resultado_inscripcion);

/* ================================
   SI APROBADO → CALCULAR PROGRESO
================================ */
if ($inscripcion && $inscripcion["estado"] === "aprobado") {



    $sql_total = "SELECT COUNT(*) as total
                  FROM lecciones
                  WHERE curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql_total);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    $resultado_total = mysqli_stmt_get_result($stmt);
    $total = mysqli_fetch_assoc($resultado_total)["total"];

    $sql_completadas = "SELECT COUNT(*) as completadas
                        FROM progreso p
                        JOIN lecciones l ON p.leccion_id = l.id
                        WHERE p.usuario_id = ? AND l.curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql_completadas);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
    $resultado_comp = mysqli_stmt_get_result($stmt);
    $completadas = mysqli_fetch_assoc($resultado_comp)["completadas"];

    $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;

    $sql_lecciones = "SELECT * FROM lecciones
                      WHERE curso_id = ?
                      ORDER BY orden ASC";
    $stmt = mysqli_prepare($conexion, $sql_lecciones);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    $resultado_lecciones = mysqli_stmt_get_result($stmt);
}

/* ================================
   PROCESAR VALORACIÓN (INSERT / UPDATE)
================================ */
if (isset($_POST["eliminar_id"])) {

    $id_val = intval($_POST["eliminar_id"]);

    $sql = "DELETE FROM valoraciones 
            WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_val, $usuario_id);
    mysqli_stmt_execute($stmt);

    header("Location: curso.php?id=" . $curso_id);
    exit;
}

// editar
$valoracion_editar = null;

if (isset($_POST["editar_id"])) {

    $id_val = intval($_POST["editar_id"]);

    $sql = "SELECT * FROM valoraciones 
            WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_val, $usuario_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $valoracion_editar = mysqli_fetch_assoc($res);
}

if (isset($_POST["valorar"]) && $inscripcion && $inscripcion["estado"] === "aprobado") {

    $puntuacion = intval($_POST["puntuacion"]);
    $comentario = trim($_POST["comentario"] ?? "");

    if ($puntuacion >= 1 && $puntuacion <= 5) {

        // Comprobar si ya existe valoración
        $sqlCheck = "SELECT id FROM valoraciones 
                     WHERE usuario_id = ? AND curso_id = ?";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "ii", $usuario_id, $curso_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $existe = mysqli_fetch_assoc($resCheck);

        if ($existe) {
            // UPDATE
            $sqlUpdate = "UPDATE valoraciones
                          SET puntuacion = ?, comentario = ?
                          WHERE usuario_id = ? AND curso_id = ?";
            $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);
            mysqli_stmt_bind_param(
                $stmtUpdate,
                "isii",
                $puntuacion,
                $comentario,
                $usuario_id,
                $curso_id
            );
            mysqli_stmt_execute($stmtUpdate);

        } else {
            // INSERT
            $sqlInsert = "INSERT INTO valoraciones 
                          (usuario_id, curso_id, puntuacion, comentario)
                          VALUES (?, ?, ?, ?)";
            $stmtInsert = mysqli_prepare($conexion, $sqlInsert);
            mysqli_stmt_bind_param(
                $stmtInsert,
                "iiis",
                $usuario_id,
                $curso_id,
                $puntuacion,
                $comentario
            );
            mysqli_stmt_execute($stmtInsert);
        }

        header("Location: curso.php?id=" . $curso_id);
        exit;
    }
}

$sql_val = "SELECT * FROM valoraciones 
    WHERE usuario_id = ? AND curso_id = ?";
$stmt_val = mysqli_prepare($conexion, $sql_val);
mysqli_stmt_bind_param($stmt_val, "ii", $usuario_id, $curso_id);
mysqli_stmt_execute($stmt_val);
$res_val = mysqli_stmt_get_result($stmt_val);
$valoracion_existente = mysqli_fetch_assoc($res_val);
?>
<?php
/* ==============================
   MEDIA VALORACIONES
============================== */
$sql_media = "SELECT AVG(puntuacion) as media, COUNT(*) as total
FROM valoraciones
WHERE curso_id = ?";
$stmt_media = mysqli_prepare($conexion, $sql_media);
mysqli_stmt_bind_param($stmt_media, "i", $curso_id);
mysqli_stmt_execute($stmt_media);
$res_media = mysqli_stmt_get_result($stmt_media);
$datos_media = mysqli_fetch_assoc($res_media);

$media = round($datos_media["media"], 1);
$total_val = $datos_media["total"];


/* ==============================
   EXAMEN APROBADO
============================== */
$examen_aprobado = false;

$sql_ex = "SELECT aprobado FROM resultados_examen
WHERE usuario_id = ? AND curso_id = ?";
$stmt_ex = mysqli_prepare($conexion, $sql_ex);
mysqli_stmt_bind_param($stmt_ex, "ii", $usuario_id, $curso_id);
mysqli_stmt_execute($stmt_ex);
$res_ex = mysqli_stmt_get_result($stmt_ex);
$datos_ex = mysqli_fetch_assoc($res_ex);

if ($datos_ex && $datos_ex["aprobado"] == 1) {
    $examen_aprobado = true;
}


/* ==============================
   LISTAR COMENTARIOS
============================== */
$sql_listar = "SELECT v.id, v.usuario_id, v.puntuacion, v.comentario, v.fecha, u.nombre
FROM valoraciones v
JOIN usuarios u ON v.usuario_id = u.id
WHERE v.curso_id = ?
ORDER BY v.fecha DESC";

$stmt_listar = mysqli_prepare($conexion, $sql_listar);
mysqli_stmt_bind_param($stmt_listar, "i", $curso_id);
mysqli_stmt_execute($stmt_listar);
$resultado_listar = mysqli_stmt_get_result($stmt_listar);
?>
<!DOCTYPE html>

<html>

<head>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/components.css">

    <title><?php echo htmlspecialchars($curso["titulo"]); ?></title>
</head>

<body>
    <?php require_once "../includes/header.php"; ?>

    <div class="main">


        <div class="container">

            

            <!-- =========================
     CURSO INFO
========================= -->

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

                    <!-- 🔥 PROGRESO -->
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
                                🚀 Inscribirme
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
                                🎓 Certificado
                            </a>
                        <?php endif; ?>



                    <?php endif; ?>

                </div>

            </div>


            <!-- =========================
     CONTENIDO CURSO
========================= -->

            <?php if ($inscripcion && $inscripcion["estado"] === "aprobado"): ?>

                <!-- =========================
         LECCIONES
    ========================= -->

                <div class="lessons-section">

                    <h3>📚 Lecciones</h3>

                    <?php while ($leccion = mysqli_fetch_assoc($resultado_lecciones)): ?>

                        <?php
                        $sql_check = "SELECT id FROM progreso
        WHERE usuario_id = ? AND leccion_id = ?";
                        $stmt_check = mysqli_prepare($conexion, $sql_check);
                        mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $leccion["id"]);
                        mysqli_stmt_execute($stmt_check);
                        mysqli_stmt_store_result($stmt_check);

                        $esta_completada = mysqli_stmt_num_rows($stmt_check) > 0;
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

                <!-- =========================
     VALORACIÓN
========================= -->



                <?php if (!$valoracion_existente || $valoracion_editar): ?>
                    <div class="card rating-card">

                        <h3>Valorar curso</h3>

                        <form method="POST" class="rating-form">

                            <!-- ⭐ ESTRELLAS -->
                            <div class="stars-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="puntuacion" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                    <label for="star<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>

                            <!-- 📝 COMENTARIO -->
                            <textarea name="comentario" placeholder="Escribe tu opinión..."></textarea>

                            <!-- 🚀 BOTÓN -->
                            <button class="btn btn-primary" type="submit" name="valorar">
                                Enviar valoración
                            </button>

                        </form>

                    </div>
                <?php endif;

                echo "Media: " . $media; ?>


                <!-- ========================= COMENTARIOS ========================= -->

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