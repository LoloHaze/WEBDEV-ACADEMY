<?php
require_once "../includes/bd.php";
session_start();

// Protección login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener cursos
$buscar = trim($_GET["buscar"] ?? "");
$filtroPrecio = $_GET["precio"] ?? "";
$orden = $_GET["orden"] ?? "";


// PAGINACION 
$porPagina = 1;

$paginaActual = isset($_GET["pagina"]) && is_numeric($_GET["pagina"])
    ? intval($_GET["pagina"])
    : 1;

$offset = ($paginaActual - 1) * $porPagina;

$sqlCursos = "
    SELECT c.*, 
           COUNT(DISTINCT i.id) as total_inscritos,
           AVG(v.puntuacion) as media_rating
    FROM cursos c
    LEFT JOIN inscripciones i ON c.id = i.curso_id
    LEFT JOIN valoraciones v ON c.id = v.curso_id
    WHERE c.activo = 1
";

/* FILTRO BUSCADOR */
if (!empty($buscar)) {
    $buscarSeguro = mysqli_real_escape_string($conexion, $buscar);
    $sqlCursos .= " AND c.titulo LIKE '%$buscarSeguro%'";
}

/* FILTRO PRECIO */
if ($filtroPrecio === "gratis") {
    $sqlCursos .= " AND c.precio = 0";
}

if ($filtroPrecio === "pago") {
    $sqlCursos .= " AND c.precio > 0";
}

$sqlCursos .= " GROUP BY c.id";

/* ORDEN */
if ($orden === "rating") {
    $sqlCursos .= " ORDER BY media_rating DESC";
} elseif ($orden === "inscritos") {
    $sqlCursos .= " ORDER BY total_inscritos DESC";
} elseif ($orden === "recientes") {
    $sqlCursos .= " ORDER BY c.id DESC";
}

/* ========================= */
/* ✅ PAGINACIÓN CORRECTA */
/* ========================= */

// Guardar query SIN LIMIT
$sqlSinLimit = $sqlCursos;

// Contar total cursos correctamente
$sqlTotal = "SELECT COUNT(*) as total FROM ($sqlSinLimit) as subconsulta";
$resTotal = mysqli_query($conexion, $sqlTotal);
$filaTotal = mysqli_fetch_assoc($resTotal);

$totalCursos = $filaTotal["total"];
$totalPaginas = ceil($totalCursos / $porPagina);

// Añadir LIMIT
$sqlCursos .= " LIMIT $porPagina OFFSET $offset";

// Ejecutar consulta final
$resultadoCursos = mysqli_query($conexion, $sqlCursos);

// Preparar foto desde sesión (seguro)
$foto = (!empty($_SESSION["foto"]) &&
    file_exists("uploads/perfiles/" . $_SESSION["foto"]))
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION["nombre"]) . "&background=random&color=fff";
?>

<!DOCTYPE html>
<html>

<head>
    <title>WebDev Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php require_once "../includes/header.php"; ?>
    <!-- NAVBAR USUARIO -->

    <div class="container">
    <div>
        <img src="<?php echo $foto; ?>" width="40" height="40" style="border-radius:50%; object-fit:cover;">
        <strong>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?> 👋</strong>
    </div>
    
        <h2>Academia</h2>



        <h3>Cursos disponibles</h3>

        <?php if (mysqli_num_rows($resultadoCursos) > 0):
            $usuario_id = $_SESSION["usuario_id"]; ?>
            <?php while ($curso = mysqli_fetch_assoc($resultadoCursos)): ?>

                <?php
                // Imagen
                $imagen = (!empty($curso["imagen_portada"]) &&
                    file_exists("uploads/cursos/" . $curso["imagen_portada"]))
                    ? "uploads/cursos/" . $curso["imagen_portada"]
                    : "https://via.placeholder.com/400x200?text=Sin+imagen";

                // Precio
                $precio = ($curso["precio"] > 0)
                    ? number_format($curso["precio"], 2) . " €"
                    : "Gratis";

                // Media valoraciones
                $sql_media = "SELECT AVG(puntuacion) as media, COUNT(*) as total
              FROM valoraciones
              WHERE curso_id = ?";
                $stmt_media = mysqli_prepare($conexion, $sql_media);
                mysqli_stmt_bind_param($stmt_media, "i", $curso["id"]);
                mysqli_stmt_execute($stmt_media);
                $res_media = mysqli_stmt_get_result($stmt_media);
                $datos_media = mysqli_fetch_assoc($res_media);

                $media = round($datos_media["media"], 1);
                $total_val = $datos_media["total"];
                ?>

                <div style="
        border:1px solid #ddd;
        border-radius:10px;
        padding:15px;
        margin:20px 0;
        max-width:600px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    ">

                    <img src="<?php echo $imagen; ?>" style="width:100%; height:200px; object-fit:cover; border-radius:8px;">

                    <h3 style="margin-top:10px;">
                        <?php echo htmlspecialchars($curso["titulo"]); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($curso["descripcion"]); ?>
                    </p>

                    <p style="font-weight:bold;">
                        💰 <?php echo $precio; ?>
                    </p>

                    <p style="font-weight:bold;">
                        <?php if ($total_val > 0): ?>
                            <?php $media_redondeada = round($media); ?>

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

                    <?php
                    $sql_ins = "SELECT estado FROM inscripciones
                WHERE usuario_id = ? AND curso_id = ?";
                    $stmt_ins = mysqli_prepare($conexion, $sql_ins);
                    mysqli_stmt_bind_param($stmt_ins, "ii", $usuario_id, $curso["id"]);
                    mysqli_stmt_execute($stmt_ins);
                    $res_ins = mysqli_stmt_get_result($stmt_ins);
                    $inscripcion = mysqli_fetch_assoc($res_ins);
                    ?>

                    <?php if (!$inscripcion || $inscripcion["estado"] === "rechazado"): ?>

                        <a href="curso.php?id=<?php echo $curso["id"]; ?>"
                            style="display:inline-block;padding:8px 12px;background:#007bff;color:white;text-decoration:none;border-radius:5px;">
                            Ver curso
                        </a>

                    <?php elseif ($inscripcion["estado"] === "pendiente"): ?>

                        <span style="color:orange; font-weight:bold;">
                            ⏳ Solicitud pendiente
                        </span>

                    <?php elseif ($inscripcion["estado"] === "aprobado"): ?>

                        <span style="color:green; font-weight:bold;">
                            ✔ Inscrito
                        </span>
                        <br><br>
                        <a href="curso.php?id=<?php echo $curso["id"]; ?>"
                            style="display:inline-block;padding:8px 12px;background:#28a745;color:white;text-decoration:none;border-radius:5px;">
                            Entrar al curso
                        </a>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

            <?php if ($totalPaginas > 1): ?>
                <div style="margin-top:20px;">

                    <?php
                    $params = $_GET;
                    ?>

                    <!-- 🔙 BOTÓN ANTERIOR -->
                    <?php if ($paginaActual > 1): ?>
                        <?php
                        $params["pagina"] = $paginaActual - 1;
                        $urlAnterior = "?" . http_build_query($params);
                        ?>
                        <a href="<?php echo $urlAnterior; ?>" style="margin:5px;">⬅ Anterior</a>
                    <?php endif; ?>


                    <!-- 🔢 NÚMEROS DE PÁGINA -->
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

                        <?php
                        $params["pagina"] = $i;
                        $url = "?" . http_build_query($params);
                        ?>

                        <?php if ($i == $paginaActual): ?>
                            <strong style="margin:5px;"><?php echo $i; ?></strong>
                        <?php else: ?>
                            <a href="<?php echo $url; ?>" style="margin:5px;"><?php echo $i; ?></a>
                        <?php endif; ?>

                    <?php endfor; ?>


                    <!-- 🔜 BOTÓN SIGUIENTE -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <?php
                        $params["pagina"] = $paginaActual + 1;
                        $urlSiguiente = "?" . http_build_query($params);
                        ?>
                        <a href="<?php echo $urlSiguiente; ?>" style="margin:5px;">Siguiente ➡</a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        <?php else: ?>
            <p>No hay cursos disponibles aún.</p>
        <?php endif; ?>
    </div>
</body>

</html>