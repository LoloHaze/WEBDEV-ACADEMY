<?php
// MIS CURSOS
// -------------------------------------
// - LISTA CURSOS DEL USUARIO
// - CALCULA PROGRESO
// - CONTROLA EXAMEN
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/funciones.php";
require_once "../includes/proteccion.php";

session_start();

// PROTEGER
protegerPagina();

$usuario_id = $_SESSION["usuario_id"];

// OBTENER CURSOS
$resultado = obtenerCursosUsuario($conexion, $usuario_id);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis cursos</title>
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <script src="assets/js/responsive.js" defer></script>

</head>

<body>
    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">

            <h2 class="section-title">Mis cursos</h2>



            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <div class="courses-grid">

                    <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

                        <?php
                        // COMPROBAR EXAMEN
                        $aprobado = examenAprobado($conexion, $usuario_id, $curso["id"]);

                        $progreso = obtenerProgresoCurso($conexion, $usuario_id, $curso["id"]);

                        $total = $progreso["total"];
                        $completadas = $progreso["completadas"];
                        $porcentaje = $progreso["porcentaje"];
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