<?php
require_once "../includes/bd.php";
session_start();

// Protección login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../public/login.php");
    exit;
}

// Protección rol admin
if ($_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Foto desde sesión
$foto = isset($_SESSION["foto"]) && $_SESSION["foto"]
    ? "../public/uploads/perfiles/" . $_SESSION["foto"]
    : "https://placekitten.com/640/360";

/* ==========================
ESTADÍSTICAS DASHBOARD
========================== */

// Total usuarios
$resUsuarios = mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuarios");
$totalUsuarios = mysqli_fetch_assoc($resUsuarios)["total"];

// Total cursos
$resCursos = mysqli_query($conexion, "SELECT COUNT(*) as total FROM cursos");
$totalCursos = mysqli_fetch_assoc($resCursos)["total"];

// Total inscripciones
$resInscripciones = mysqli_query($conexion, "SELECT COUNT(*) as total FROM inscripciones");
$totalInscripciones = mysqli_fetch_assoc($resInscripciones)["total"];

// Total valoraciones
$resValoraciones = mysqli_query($conexion, "SELECT COUNT(*) as total FROM valoraciones");
$totalValoraciones = mysqli_fetch_assoc($resValoraciones)["total"];

/* ==========================
   CURSO MÁS INSCRITO
========================== */

$sqlMasInscrito = "
    SELECT c.titulo, COUNT(i.id) as total
    FROM cursos c
    LEFT JOIN inscripciones i ON c.id = i.curso_id
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 1
";

$resMasInscrito = mysqli_query($conexion, $sqlMasInscrito);
$cursoMasInscrito = mysqli_fetch_assoc($resMasInscrito);

/* ==========================
   CURSO MEJOR VALORADO
========================== */

$sqlMejorValorado = "
    SELECT c.titulo, AVG(v.puntuacion) as media
    FROM cursos c
    JOIN valoraciones v ON c.id = v.curso_id
    GROUP BY c.id
    HAVING COUNT(v.id) >= 1
    ORDER BY media DESC
    LIMIT 1
";

$resMejorValorado = mysqli_query($conexion, $sqlMejorValorado);
$cursoMejorValorado = mysqli_fetch_assoc($resMejorValorado);

// =============================
// CONTADOR USUARIOS PENDIENTES
// =============================
$sql_users = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 0";
$result_users = mysqli_query($conexion, $sql_users);
$total_users_pendientes = mysqli_fetch_assoc($result_users)["total"];

// =============================
// CONTADOR INSCRIPCIONES PENDIENTES
// =============================
$sql_ins = "SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'pendiente'";
$result_ins = mysqli_query($conexion, $sql_ins);
$total_ins_pendientes = mysqli_fetch_assoc($result_ins)["total"];
?>


<!DOCTYPE html>
<html>

<head>
    <title>Panel Admin - WebDev Academy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <!-- Barra superior admin -->
    <div
        style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #ccc;">

        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?php echo $foto; ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">
            <div>
                <strong><?php echo $_SESSION["nombre"]; ?></strong><br>
                <small>Administrador</small>
            </div>
        </div>

        <div>
            <a href="../public/index.php">Volver a la academia</a> |
            <a href="../public/logout.php">Cerrar sesión</a>
        </div>

    </div>
    <h2>📊 Panel de Estadísticas</h2>

    <div style="display:grid; grid-template-columns:repeat(2, 250px); gap:20px; margin-bottom:30px;">

        <div style="border:1px solid #ccc; padding:20px; border-radius:10px;">
            👥 <strong>
                <?php echo $totalUsuarios; ?>
            </strong><br>
            Usuarios
        </div>

        <div style="border:1px solid #ccc; padding:20px; border-radius:10px;">
            📚 <strong>
                <?php echo $totalCursos; ?>
            </strong><br>
            Cursos
        </div>

        <div style="border:1px solid #ccc; padding:20px; border-radius:10px;">
            📝 <strong>
                <?php echo $totalInscripciones; ?>
            </strong><br>
            Inscripciones
        </div>

        <div style="border:1px solid #ccc; padding:20px; border-radius:10px;">
            ⭐ <strong>
                <?php echo $totalValoraciones; ?>
            </strong><br>
            Valoraciones
        </div>

    </div>

    <hr>

    <h3>🔥 Curso más inscrito</h3>

    <?php if ($cursoMasInscrito && $cursoMasInscrito["total"] > 0): ?>
        <?php echo htmlspecialchars($cursoMasInscrito["titulo"]); ?>
        (
        <?php echo $cursoMasInscrito["total"]; ?> alumnos)
    <?php else: ?>
        No hay inscripciones todavía.
    <?php endif; ?>

    <hr>

    <h3>🏆 Curso mejor valorado</h3>

    <?php if ($cursoMejorValorado): ?>
        <?php echo htmlspecialchars($cursoMejorValorado["titulo"]); ?>
        (
        <?php echo round($cursoMejorValorado["media"], 1); ?> ⭐)
    <?php else: ?>
        No hay valoraciones todavía.
    <?php endif; ?>

    <hr>
    <h2>Panel de Administración</h2>

    <ul>
        <li><a href="gestionCursos.php">📚 Gestión de cursos</a></li>
        <li>
            <a href="gestionUsuarios.php">
                👥 Gestión de usuarioss
                <?php if ($total_users_pendientes > 0): ?>
                    <span style="color:red;">
                        (<?php echo $total_users_pendientes; ?>)
                    </span>
                <?php endif; ?>
            </a>
        </li>

        <li>
            <a href="gestionInscripciones.php">
                📩 Gestión de inscripciones
                <?php if ($total_ins_pendientes > 0): ?>
                    <span style="color:red;">
                        (<?php echo $total_ins_pendientes; ?>)
                    </span>
                <?php endif; ?>
            </a>
        </li>
    </ul>

</body>

</html>