<?php
require_once "../includes/bd.php";
session_start();
$redirect = $_GET["redirect"] ?? null;

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}
// ACTIVAR
if (isset($_GET["activar"])) {
    $id = intval($_GET["activar"]);
    $sql = "UPDATE cursos SET activo = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: " . ($redirect ?? "gestionCursos.php"));
}

// DESACTIVAR
if (isset($_GET["desactivar"])) {
    $id = intval($_GET["desactivar"]);
    $sql = "UPDATE cursos SET activo = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: " . ($redirect ?? "gestionCursos.php"));
}

$sql = "SELECT * FROM cursos ORDER BY id DESC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../public/assets/logowebdev.png" type="image/png">

    <title>Gestión de Cursos</title>

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <link rel="stylesheet" href="../public/assets/css/reescalado.css">
    <link rel="stylesheet" href="../public/assets/css/responsiveAdmin.css">

    <script src="../public/assets/js/responsiveAdmin.js" defer></script>
    <script src="../public/assets/js/responsiveAdmin.js" defer></script>
</head>


<body>



    <div class="main">
        <div class="container">
            <?php require_once "../includes/headerAdmin.php"; ?>
            <h2 class="section-title">Gestión de Cursos</h2>

            <!-- BOTÓN CREAR -->
            <div class="admin-top-actions">
                <a href="crearCurso.php" class="btn btn-primary">
                    ➕ Crear curso
                </a>
            </div>
            <br>

            <!-- LISTA -->
            <div class="admin-list">

                <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

                    <div class="card admin-item">

                        <!-- INFO -->
                        <div class="admin-user-mini">
                            <div>
                                <strong><?php echo htmlspecialchars($curso["titulo"]); ?></strong>

                                <span>
                                    <?php if ($curso["activo"] == 1): ?>
                                        <span class="status success">Activo</span>
                                    <?php else: ?>
                                        <span class="status danger">Desactivado</span>
                                    <?php endif; ?>
                                </span>

                                <p>
                                    <svg width="15" height="15" viewBox="0 0 24 24">
                                        <path fill="currentColor" fill-rule="evenodd"
                                            d="m16.137 4.728l1.83 1.83C20.656 9.248 22 10.592 22 12.262c0 1.671-1.344 3.015-4.033 5.704c-2.69 2.69-4.034 4.034-5.705 4.034c-1.67 0-3.015-1.344-5.704-4.033l-1.83-1.83c-1.545-1.546-2.318-2.318-2.605-3.321c-.288-1.003-.042-2.068.45-4.197l.283-1.228c.413-1.792.62-2.688 1.233-3.302s1.51-.82 3.302-1.233l1.228-.284c2.13-.491 3.194-.737 4.197-.45c1.003.288 1.775 1.061 3.32 2.606m-4.99 9.6c-.673-.672-.668-1.638-.265-2.403a.75.75 0 0 1 1.04-1.046c.34-.18.713-.276 1.085-.272a.75.75 0 0 1-.014 1.5a.88.88 0 0 0-.609.277c-.387.387-.286.775-.177.884c.11.109.497.21.884-.177c.784-.784 2.138-1.044 3.005-.177c.673.673.668 1.639.265 2.404a.75.75 0 0 1-1.04 1.045a2.2 2.2 0 0 1-1.472.232a.75.75 0 1 1 .302-1.47c.177.037.463-.021.708-.266c.387-.388.286-.775.177-.884c-.11-.109-.497-.21-.884.177c-.784.784-2.138 1.044-3.005.176m-1.126-4.035a2 2 0 1 0-2.829-2.828a2 2 0 0 0 2.829 2.828"
                                            clip-rule="evenodd" />
                                    </svg>

                                    <?php echo $curso["precio"] > 0 ? $curso["precio"] . " €" : "Gratis"; ?>
                                </p>
                            </div>
                        </div>

                        <!-- ACCIONES -->
                        <div class="admin-actions">

                            <a href="gestionarLecciones.php?curso_id=<?php echo $curso["id"]; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-soft">
                                Lecciones
                            </a>

                            <a href="adminExamen.php?curso_id=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                                Examen
                            </a>

                            <a href="editarCurso.php?id=<?php echo $curso["id"]; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                class="btn btn-primary">
                                Editar
                            </a>

                            <?php if ($curso["activo"] == 1): ?>
                                <a href="?desactivar=<?php echo $curso["id"]; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    class="btn btn-soft">
                                    Desactivar
                                </a>
                            <?php else: ?>
                                <a href="?activar=<?php echo $curso["id"]; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    class="btn btn-primary">
                                    Activar
                                </a>
                            <?php endif; ?>

                            <a href="valoracionesCurso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-soft">
                                Valoraciones
                            </a>


                            <!-- MEJOR NO ELIMINAR DESCTIVAR
                 <a href="eliminarCurso.php?id=/<?php // echo $curso["id"]; ?>" 
                   class="btn btn-soft"
                   onclick="return confirm('¿Seguro que quieres eliminar este curso?');">
                    Eliminar
                </a> -->

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>


            <!-- VOLVER -->
            <div class="admin-footer">

                <a href="<?php echo htmlspecialchars($redirect ?? 'panel.php'); ?>" class="btn btn-soft">

                    ← Volver

                </a>

            </div>

        </div>
    </div>


</body>

</html>