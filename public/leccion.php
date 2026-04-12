<?php
// LECCIÓN
// -------------------------------------
// - MUESTRA CONTENIDO DE LA LECCIÓN
// - CONTROLA ACCESO
// - GESTIONA PROGRESO
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/funciones.php";
require_once "../includes/proteccion.php";

session_start();

// PROTEGER
protegerPagina();

// VALIDAR ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$leccion_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

// OBTENER LECCIÓN
$leccion = obtenerLeccion($conexion, $leccion_id);

if (!$leccion) {
    header("Location: index.php");
    exit;
}

// COMPROBAR ACCESO
if (!puedeAccederLeccion($conexion, $usuario_id, $leccion["curso_id"])) {
    header("Location: curso.php?id=" . $leccion["curso_id"]);
    exit;
}

// LISTA LECCIONES
$resultado_lista = obtenerLecciones($conexion, $leccion["curso_id"]);

// ESTADO COMPLETADO
$completada = leccionCompletada($conexion, $usuario_id, $leccion_id);

// TOGGLE PROGRESO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    toggleProgreso($conexion, $usuario_id, $leccion_id, $completada);

    header("Location: leccion.php?id=" . $leccion_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    $completada_item = leccionCompletada($conexion, $usuario_id, $item["id"]);
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