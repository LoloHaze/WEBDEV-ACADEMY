<?php

// INDEX USUARIO
// -------------------------------------
// - MUESTRA CURSOS DISPONIBLES
// - PERMITE FILTRAR Y ORDENAR
// - GESTIONA PAGINACIÓN
// - MUESTRA PROGRESO DEL USUARIO
// -------------------------------------

require_once "../includes/bd.php";
session_start();

// cambio!
$logueado = isset($_SESSION["usuario_id"]);

require_once "../includes/proteccion.php";
require_once "../includes/funciones.php";

// protegerPagina();


// GET
$buscar = trim($_GET["buscar"] ?? "");
$filtroPrecio = $_GET["precio"] ?? "";
$orden = $_GET["orden"] ?? "";

// cambio!
$foto = $logueado
    ? obtenerFotoUsuario($_SESSION["nombre"], $_SESSION["foto"])
    : "https://ui-avatars.com/api/?name=Invitado";

// PAGINACIÓN
$porPagina = 3;
$paginaActual = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) ? intval($_GET["pagina"]) : 1;
$offset = ($paginaActual - 1) * $porPagina;

//  USAR FUNCIONES
$totalCursos = contarCursosFiltrados($conexion, $buscar, $filtroPrecio);
$totalPaginas = ceil($totalCursos / $porPagina);

$resultadoCursos = obtenerCursosFiltrados(
    $conexion,
    $buscar,
    $filtroPrecio,
    $orden,
    $porPagina,
    $offset
);

// CURSO CONTINUAR

//cambio!
$cursoContinuar = $logueado
    ? obtenerCursoContinuar($conexion, $_SESSION["usuario_id"])
    : null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebDev Academy</title>
    <link rel="icon" href="assets/logowebdev.png" type="image/png">

    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <script src="assets/js/responsive.js" defer></script>
    <script src="assets/js/frasesRandom.js" defer></script>

</head>

<body>


    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">

            <div class="welcome">

                <div class="welcome-left">

                <!-- //cambio! -->
                    <?php if ($logueado): ?>
                        <img src="<?php echo $foto; ?>" class="welcome-avatar" alt="Foto usuario">
                    <?php endif; ?>

                    <div>

                        <!-- //cambio! -->

                        <?php if ($logueado): ?>
                            <h2>Hola, <br><?php echo htmlspecialchars($_SESSION["nombre"]); ?></h2>
                        <?php else: ?>
                            <h2>Bienvenido a WebDev Academy</h2>
                        <?php endif; ?>

                        <p id="frase-bienvenida"></p>
                    </div>
                </div>


                <div class="welcome-right">
                    <!-- //cambio! -->
                    <?php if ($logueado && $cursoContinuar): ?>
                        <a href="curso.php?id=<?php echo $cursoContinuar["id"]; ?>" class="btn btn-primary">
                            ▶ Continuar: <?php echo htmlspecialchars($cursoContinuar["titulo"]); ?>
                        </a>
                    <?php else: ?>
                        <a href="#" class="btn btn-primary" style="opacity:0.5; pointer-events:none;">
                            ▶ Sin cursos
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <h2 class="section-title">Academia</h2>
            <div class="section-divider"></div>
            <h3 class="section-subtitle">Cursos disponibles</h3>

            <?php if (mysqli_num_rows($resultadoCursos) > 0):
                // cambio!
                $usuario_id = $logueado ? $_SESSION["usuario_id"] : null; ?>

                <div class="courses-grid">

                    <?php while ($curso = mysqli_fetch_assoc($resultadoCursos)): ?>

                        <?php
                        $imagen = (!empty($curso["imagen_portada"]) &&
                            file_exists(__DIR__ . "/uploads/cursos/" . $curso["imagen_portada"]))
                            ? "uploads/cursos/" . $curso["imagen_portada"]
                            : "https://via.placeholder.com/400x200?text=Sin+imagen";

                        $precio = ($curso["precio"] > 0)
                            ? number_format($curso["precio"], 2) . " €"
                            : "Gratis";

                        $datos_media = obtenerMediaCurso($conexion, $curso["id"]);
                        $media = round($datos_media["media"], 1);
                        $total_val = $datos_media["total"];

                        $inscripcion = obtenerInscripcion($conexion, $usuario_id, $curso["id"]);
                        ?>

                        <div class="course-card">

                            <div class="card-image">
                                <img src="<?php echo $imagen; ?>" alt="Foto curso">

                                <?php if ($curso["precio"] == 0): ?>
                                    <span class="badge free">Gratis</span>
                                <?php else: ?>
                                    <span class="badge paid">Premium</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">

                                <h3><?php echo htmlspecialchars($curso["titulo"]); ?></h3>

                                <p class="card-desc">
                                    <?php echo htmlspecialchars($curso["descripcion"]); ?>
                                </p>

                                <div class="card-meta"><span><svg width="15" height="15" viewBox="0 0 24 24">
                                            <path fill="currentColor" fill-rule="evenodd"
                                                d="m16.137 4.728l1.83 1.83C20.656 9.248 22 10.592 22 12.262c0 1.671-1.344 3.015-4.033 5.704c-2.69 2.69-4.034 4.034-5.705 4.034c-1.67 0-3.015-1.344-5.704-4.033l-1.83-1.83c-1.545-1.546-2.318-2.318-2.605-3.321c-.288-1.003-.042-2.068.45-4.197l.283-1.228c.413-1.792.62-2.688 1.233-3.302s1.51-.82 3.302-1.233l1.228-.284c2.13-.491 3.194-.737 4.197-.45c1.003.288 1.775 1.061 3.32 2.606m-4.99 9.6c-.673-.672-.668-1.638-.265-2.403a.75.75 0 0 1 1.04-1.046c.34-.18.713-.276 1.085-.272a.75.75 0 0 1-.014 1.5a.88.88 0 0 0-.609.277c-.387.387-.286.775-.177.884c.11.109.497.21.884-.177c.784-.784 2.138-1.044 3.005-.177c.673.673.668 1.639.265 2.404a.75.75 0 0 1-1.04 1.045a2.2 2.2 0 0 1-1.472.232a.75.75 0 1 1 .302-1.47c.177.037.463-.021.708-.266c.387-.388.286-.775.177-.884c-.11-.109-.497-.21-.884.177c-.784.784-2.138 1.044-3.005.176m-1.126-4.035a2 2 0 1 0-2.829-2.828a2 2 0 0 0 2.829 2.828"
                                                clip-rule="evenodd" />
                                        </svg> <?php echo $precio; ?></span>

                                    <span class="rating">
                                        <?php if ($total_val > 0): ?>

                                            <?php $media_redondeada = round($media); ?>

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
                                    </span>
                                </div>

                                <div class="card-actions">


                                    <!-- // cambio! --> <?php if (!$logueado): ?>
                                        <a href="curso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-primary">
                                            Ver curso
                                        </a>

                                        <!-- // -->
                                    <?php elseif (!$inscripcion || $inscripcion["estado"] === "rechazado"): ?>
                                        <a href="curso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-soft">Ver curso</a>

                                    <?php elseif ($inscripcion["estado"] === "pendiente"): ?>
                                        <span class="pending">Pendiente</span>

                                    <?php elseif ($inscripcion["estado"] === "aprobado"): ?>
                                        <a href="curso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-primary">Continuar</a>
                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>

                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <p>No hay cursos disponibles aún.</p>
            <?php endif; ?>

        </div>
        <div class="pagination">

            <?php if ($paginaActual > 1): ?>
                <a
                    href="?pagina=<?php echo $paginaActual - 1; ?>&buscar=<?php echo urlencode($buscar); ?>&precio=<?php echo $filtroPrecio; ?>&orden=<?php echo $orden; ?>">
                    ←
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>&precio=<?php echo $filtroPrecio; ?>&orden=<?php echo $orden; ?>"
                    class="<?php echo $i == $paginaActual ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <a
                    href="?pagina=<?php echo $paginaActual + 1; ?>&buscar=<?php echo urlencode($buscar); ?>&precio=<?php echo $filtroPrecio; ?>&orden=<?php echo $orden; ?>">
                    →
                </a>
            <?php endif; ?>

        </div>
    </div>

    <?php require_once "../includes/footer.php"; ?>

</body>


</html>