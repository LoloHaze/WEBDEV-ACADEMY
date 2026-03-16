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
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($curso["titulo"]); ?></title>
</head>
<body>

<a href="index.php">← Volver a cursos</a>

<h2><?php echo htmlspecialchars($curso["titulo"]); ?></h2>
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

<?php elseif ($inscripcion["estado"] === "aprobado"): ?>

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

<?php endif; ?>

</body>
</html>