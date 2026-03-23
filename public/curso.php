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

/* ================================
   PROCESAR SOLICITUD INSCRIPCIÓN
================================ */
if (isset($_POST["solicitar_inscripcion"])) {

    $sql_insert = "INSERT INTO inscripciones (usuario_id, curso_id)
                   VALUES (?, ?)";
    $stmt = mysqli_prepare($conexion, $sql_insert);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);

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

<!DOCTYPE html>

<html>

<head>
    <title><?php echo htmlspecialchars($curso["titulo"]); ?></title>
</head>

<body>

    <a href="index.php">← Volver a cursos</a>

    <h2><?php echo htmlspecialchars($curso["titulo"]); ?></h2>
    <?php
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
    ?>

    <p style="font-weight:bold;">

        <?php if ($total_val > 0): ?>

            <?php
            $media_redondeada = round($media);
            ?>

            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= $media_redondeada): ?>
                    <span style="color:gold;">★</span>
                <?php else: ?>
                    <span style="color:#ccc;">★</span>
                <?php endif; ?>
            <?php endfor; ?>

            (<?php echo $total_val; ?> valoraciones)

        <?php else: ?>

            ⭐ Sin valoraciones

        <?php endif; ?>

    </p>
    <p><?php echo htmlspecialchars($curso["descripcion"]); ?></p>

    <?php if (!$inscripcion): ?>

        <p>No estás inscrito en este curso.</p>

        <form method="POST">
            <button type="submit" name="solicitar_inscripcion">
                Solicitar inscripción
            </button>
        </form>

    <?php elseif ($inscripcion["estado"] === "pendiente"): ?>

        <p>Tu solicitud está pendiente de aprobación por el administrador.</p>
        <form method="POST">
            <button type="submit" name="cancelar_inscripcion">
                Cancelar solicitud
            </button>
        </form>

  <?php elseif ($inscripcion["estado"] === "aprobado"): ?>
        <form method="POST">
            <button type="submit" name="cancelar_inscripcion">
                Darse de baja del curso
            </button>
        </form>
        <br>

        <h4>Progreso: <?php echo $completadas; ?> / <?php echo $total; ?>
            (<?php echo $porcentaje; ?>%)</h4>

        <div style="width:400px; height:20px; background:#ddd; border-radius:10px;">
            <div style="
            width: <?php echo $porcentaje; ?>%;
            height:100%;
            background:green;
            border-radius:10px;
            transition: width 0.3s;">
            </div>
        </div>

        <br>

        <h3>Lecciones</h3>

        <?php while ($leccion = mysqli_fetch_assoc($resultado_lecciones)):

            $sql_check = "SELECT id FROM progreso
                      WHERE usuario_id = ? AND leccion_id = ?";
            $stmt_check = mysqli_prepare($conexion, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $leccion["id"]);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            $esta_completada = mysqli_stmt_num_rows($stmt_check) > 0;
            ?>

            <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                <h4>
                    <?php echo $leccion["orden"]; ?>.
                    <?php echo htmlspecialchars($leccion["titulo"]); ?>
                    <?php if ($esta_completada): ?>
                        <span style="color:green;">✔</span>
                    <?php endif; ?>
                </h4>
                <p><?php echo htmlspecialchars($leccion["descripcion"]); ?></p>
                <a href="leccion.php?id=<?php echo $leccion["id"]; ?>">
                    Ver lección
                </a>
            </div>

        <?php endwhile; ?>
       <hr>
<h3>Valorar curso</h3>

<?php if (!$valoracion_existente || $valoracion_editar): ?>

    <form method="POST">

        <label>Puntuación:</label><br>
        <select name="puntuacion" required>
            <option value="">Seleccionar</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?php echo $i; ?>"
                    <?php
                    if (
                        ($valoracion_editar && $valoracion_editar["puntuacion"] == $i) ||
                        ($valoracion_existente && $valoracion_existente["puntuacion"] == $i)
                    ) echo "selected";
                    ?>>
                    <?php echo str_repeat("⭐", $i); ?>
                </option>
            <?php endfor; ?>
        </select><br><br>

        <textarea name="comentario" placeholder="Comentario (opcional)"><?php
            echo $valoracion_editar["comentario"]
                ?? $valoracion_existente["comentario"]
                ?? "";
        ?></textarea><br><br>

        <button type="submit" name="valorar">
            <?php echo $valoracion_editar ? "Actualizar valoración" : "Enviar valoración"; ?>
        </button>
    </form>


<?php endif; ?>
       

        <h3>💬 Comentarios</h3>

        <?php
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

        <?php if (mysqli_num_rows($resultado_listar) > 0): ?>

            <?php while ($val = mysqli_fetch_assoc($resultado_listar)): ?>

                <div style="border:1px solid #ddd; padding:10px; margin:10px 0; border-radius:6px;">

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
                    <?php if ($val["usuario_id"] == $usuario_id): ?>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="editar_id" value="<?php echo $val["id"]; ?>">
                            <button type="submit">Editar</button>
                        </form>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="eliminar_id" value="<?php echo $val["id"]; ?>">
                            <button type="submit" onclick="return confirm('¿Eliminar tu comentario?');">
                                Eliminar
                            </button>
                        </form>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>Este curso aún no tiene valoraciones.</p>

        <?php endif; ?>
    <?php endif; ?>

</body>

</html>