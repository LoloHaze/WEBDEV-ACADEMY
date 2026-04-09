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

/* ==========================================
   COMPROBAR ACCESO A LA LECCIÓN
========================================== */

// 1️⃣ Comprobar que el curso esté activo
$sql_activo = "SELECT activo FROM cursos WHERE id = ?";
$stmt_activo = mysqli_prepare($conexion, $sql_activo);
mysqli_stmt_bind_param($stmt_activo, "i", $leccion["curso_id"]);
mysqli_stmt_execute($stmt_activo);
$res_activo = mysqli_stmt_get_result($stmt_activo);
$curso_activo = mysqli_fetch_assoc($res_activo);

if (!$curso_activo || $curso_activo["activo"] != 1) {
    header("Location: index.php");
    exit;
}

// 2️⃣ Comprobar inscripción aprobada
$sql_ins = "SELECT estado FROM inscripciones
            WHERE usuario_id = ? AND curso_id = ?";
$stmt_ins = mysqli_prepare($conexion, $sql_ins);
mysqli_stmt_bind_param($stmt_ins, "ii", $usuario_id, $leccion["curso_id"]);
mysqli_stmt_execute($stmt_ins);
$res_ins = mysqli_stmt_get_result($stmt_ins);
$inscripcion = mysqli_fetch_assoc($res_ins);

if (!$inscripcion || $inscripcion["estado"] !== "aprobado") {
    header("Location: curso.php?id=" . $leccion["curso_id"]);
    exit;
}
// Obtener todas las lecciones del curso
$sql_lista = "SELECT * FROM lecciones 
              WHERE curso_id = ?
              ORDER BY orden ASC";

$stmt_lista = mysqli_prepare($conexion, $sql_lista);
mysqli_stmt_bind_param($stmt_lista, "i", $leccion["curso_id"]);
mysqli_stmt_execute($stmt_lista);
$resultado_lista = mysqli_stmt_get_result($stmt_lista);
/* ==========================================
   COMPROBAR SI ESTÁ COMPLETADA
========================================== */

$sql_check = "SELECT id FROM progreso 
              WHERE usuario_id = ? AND leccion_id = ?";

$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $leccion_id);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

$completada = mysqli_stmt_num_rows($stmt_check) > 0;

/* ==========================================
   TOGGLE COMPLETADO / NO COMPLETADO
========================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($completada) {

        // Si ya estaba completada → eliminar
        $sql_delete = "DELETE FROM progreso 
                       WHERE usuario_id = ? AND leccion_id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "ii", $usuario_id, $leccion_id);
        mysqli_stmt_execute($stmt_delete);

    } else {

        // Si no estaba completada → insertar
        $sql_insert = "INSERT INTO progreso (usuario_id, leccion_id) 
                       VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conexion, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ii", $usuario_id, $leccion_id);
        mysqli_stmt_execute($stmt_insert);
    }

    header("Location: leccion.php?id=" . $leccion_id);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($leccion["titulo"]); ?></title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/leccion.css">
    <link rel="stylesheet" href="assets/css/components.css">
</head>

<body>
    <?php require_once "../includes/header.php"; ?>

    <div class="main">
        <div class="leccion-layout">

            <!-- SIDEBAR -->
            <div class="leccion-sidebar">

                <h3 class="sidebar-title"><?php echo htmlspecialchars($leccion["curso_titulo"]); ?></h3>
                <hr>

                <?php while ($item = mysqli_fetch_assoc($resultado_lista)): ?>

                    <?php
                    $sql_prog = "SELECT id FROM progreso 
                         WHERE usuario_id = ? AND leccion_id = ?";
                    $stmt_prog = mysqli_prepare($conexion, $sql_prog);
                    mysqli_stmt_bind_param($stmt_prog, "ii", $usuario_id, $item["id"]);
                    mysqli_stmt_execute($stmt_prog);
                    mysqli_stmt_store_result($stmt_prog);

                    $completada_item = mysqli_stmt_num_rows($stmt_prog) > 0;
                    $activa = ($item["id"] == $leccion_id);
                    ?>

                    <div class="sidebar-item <?php echo $activa ? 'active' : ''; ?>">

                        <a href="leccion.php?id=<?php echo $item["id"]; ?>">

                            <?php if ($completada_item): ?>
                                ✔
                            <?php endif; ?>

                            <?php echo htmlspecialchars($item["orden"] . ". " . $item["titulo"]); ?>

                        </a>

                    </div>

                <?php endwhile; ?>

            </div>


            <!-- CONTENIDO -->
            <div class="leccion-content">

                <div class="leccion-wrapper">

                    <!-- VIDEO -->
                    <div class="video-container">
                        <iframe src="<?php echo htmlspecialchars($leccion['video_url']); ?>" allowfullscreen></iframe>
                    </div>

                    <!-- INFO -->
                    <div class="leccion-info">
                        <h2><?php echo htmlspecialchars($leccion["titulo"]); ?></h2>
                        <p class="leccion-desc">
                            <?php echo htmlspecialchars($leccion["descripcion"]); ?>
                        </p>
                    </div>

                    <!-- ACCIONES -->
                    <div class="leccion-actions">
                        <a class="btn btn-soft" href="curso.php?id=<?php echo $leccion["curso_id"]; ?>">
                            ← Volver al curso
                        </a>
                        <?php if ($completada): ?>
                            <form method="POST">
                                <button type="submit" class="btn btn-danger">
                                    ❌ Marcar como no completada
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <button type="submit" class="btn btn-success">
                                    ✅ Marcar como completada
                                </button>
                            </form>
                        <?php endif; ?>



                    </div>

                </div>

            </div>

        </div>
    </div>
    <?php require_once "../includes/footer.php"; ?>
</body>

</html>