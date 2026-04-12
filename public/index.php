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

// PROTECCIÓN LOGIN
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
require_once "../includes/proteccion.php";
require_once "../includes/funciones.php";


// VARIABLES GET
$buscar = trim($_GET["buscar"] ?? "");
$filtroPrecio = $_GET["precio"] ?? "";
$orden = $_GET["orden"] ?? "";

// PAGINACIÓN
$porPagina = 3;
$paginaActual = isset($_GET["pagina"]) && is_numeric($_GET["pagina"]) ? intval($_GET["pagina"]) : 1;
$offset = ($paginaActual - 1) * $porPagina;

// CONSULTA BASE
$sqlCursos = "
    SELECT c.*, 
           COUNT(DISTINCT i.id) as total_inscritos,
           AVG(v.puntuacion) as media_rating
    FROM cursos c
    LEFT JOIN inscripciones i ON c.id = i.curso_id
    LEFT JOIN valoraciones v ON c.id = v.curso_id
    WHERE c.activo = 1
";

// FILTRO BÚSQUEDA
if (!empty($buscar)) {
    $buscarSeguro = mysqli_real_escape_string($conexion, $buscar);
    $sqlCursos .= " AND c.titulo LIKE '%$buscarSeguro%'";
}

// FILTRO PRECIO
if ($filtroPrecio === "gratis") {
    $sqlCursos .= " AND c.precio = 0";
}
if ($filtroPrecio === "pago") {
    $sqlCursos .= " AND c.precio > 0";
}

$sqlCursos .= " GROUP BY c.id";

// ORDEN
if ($orden === "rating") {
    $sqlCursos .= " ORDER BY media_rating DESC";
} elseif ($orden === "inscritos") {
    $sqlCursos .= " ORDER BY total_inscritos DESC";
} elseif ($orden === "recientes") {
    $sqlCursos .= " ORDER BY c.id DESC";
}

// PAGINACIÓN TOTAL
$sqlSinLimit = $sqlCursos;
$sqlTotal = "SELECT COUNT(*) as total FROM ($sqlSinLimit) as subconsulta";
$resTotal = mysqli_query($conexion, $sqlTotal);
$filaTotal = mysqli_fetch_assoc($resTotal);

$totalCursos = $filaTotal["total"];
$totalPaginas = ceil($totalCursos / $porPagina);

// CONSULTA FINAL
$sqlCursos .= " LIMIT $porPagina OFFSET $offset";
$resultadoCursos = mysqli_query($conexion, $sqlCursos);

// FOTO USUARIO
$foto = (!empty($_SESSION["foto"]) &&
    file_exists("uploads/perfiles/" . $_SESSION["foto"]))
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION["nombre"]) . "&background=random&color=fff";

// ÚLTIMO CURSO
$sql_continue = "
SELECT c.id, c.titulo
FROM inscripciones i
JOIN cursos c ON i.curso_id = c.id
WHERE i.usuario_id = ? AND i.estado = 'aprobado'
ORDER BY i.id DESC
LIMIT 1
";

$stmt_continue = mysqli_prepare($conexion, $sql_continue);
mysqli_stmt_bind_param($stmt_continue, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt_continue);
$res_continue = mysqli_stmt_get_result($stmt_continue);

$cursoContinuar = mysqli_fetch_assoc($res_continue);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebDev Academy</title>

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/cursos.css">
</head>

<body>
<div class="main">

<?php require_once "../includes/header.php"; ?>

<div class="container">

<div class="welcome">

<div class="welcome-left">
<img src="<?php echo $foto; ?>" class="welcome-avatar">

<div>
<h2>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?> 👋</h2>
<p>¿Listo para seguir aprendiendo hoy?</p>
</div>
</div>

<div class="welcome-right">
<?php if ($cursoContinuar): ?>
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
$usuario_id = $_SESSION["usuario_id"]; ?>

<div class="courses-grid">

<?php while ($curso = mysqli_fetch_assoc($resultadoCursos)): ?>

<?php
$imagen = (!empty($curso["imagen_portada"]) &&
file_exists("uploads/cursos/" . $curso["imagen_portada"]))
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
<img src="<?php echo $imagen; ?>">

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

<div class="card-meta">
<span class="price">💰 <?php echo $precio; ?></span>

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

<?php if (!$inscripcion || $inscripcion["estado"] === "rechazado"): ?>
<a href="curso.php?id=<?php echo $curso["id"]; ?>" class="btn btn-soft">Ver curso</a>

<?php elseif ($inscripcion["estado"] === "pendiente"): ?>
<span class="pending">⏳ Pendiente</span>

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
</div>

<?php require_once "../includes/footer.php"; ?>

</body>
</html>