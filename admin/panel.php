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

/* 
ESTADÍSTICAS DASHBOARD */

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

/* 
   CURSO MÁS INSCRITO */

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

/* CURSO MEJOR VALORADO */

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


// CONTADOR USUARIOS PENDIENTES

$sql_users = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 0";
$result_users = mysqli_query($conexion, $sql_users);
$total_users_pendientes = mysqli_fetch_assoc($result_users)["total"];


// CONTADOR INSCRIPCIONES PENDIENTES

$sql_ins = "SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'pendiente'";
$result_ins = mysqli_query($conexion, $sql_ins);
$total_ins_pendientes = mysqli_fetch_assoc($result_ins)["total"];
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../public/assets/logowebdev.png" type="image/png">
    <title>Panel Admin - WebDev Academy</title>


    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <link rel="stylesheet" href="../public/assets/css/reescalado.css">
    <link rel="stylesheet" href="../public/assets/css/responsiveAdmin.css">

    <script src="../public/assets/js/responsiveAdmin.js" defer></script>


</head>

<body>

    <div class="main">
        <div class="container">

            <?php require_once "../includes/headerAdmin.php"; ?>

            <!-- ADMIN -->
            <h2 class="section-title">Administración</h2>

            <div class="admin-grid">

                <a href="gestionCursos.php" class="card admin-link">
                    <span class="admin-link-text"> Cursos</span>
                </a>

                <a href="gestionUsuarios.php" class="card admin-link">
                    <span class="admin-link-text"> Usuarios</span>

                    <?php if ($total_users_pendientes > 0): ?>
                        <span class="badge"><?php echo $total_users_pendientes; ?></span>
                    <?php endif; ?>
                </a>

                <a href="gestionInscripciones.php" class="card admin-link">
                    <span class="admin-link-text"> Inscripciones</span>

                    <?php if ($total_ins_pendientes > 0): ?>
                        <span class="badge"><?php echo $total_ins_pendientes; ?></span>
                    <?php endif; ?>
                </a>

            </div>

            <!-- STADISTICAS -->
            <h2 class="section-title">Dashboard</h2>

            <div class="admin-stats">

                <div class="card admin-stat">
                    <span></span>
                    <strong><?php echo $totalUsuarios; ?></strong>
                    <p>Usuarios</p>
                </div>

                <div class="card admin-stat">
                    <span></span>
                    <strong><?php echo $totalCursos; ?></strong>
                    <p>Cursos</p>
                </div>

                <div class="card admin-stat">
                    <span></span>
                    <strong><?php echo $totalInscripciones; ?></strong>
                    <p>Inscripciones</p>
                </div>

                <div class="card admin-stat">
                    <span></span>
                    <strong><?php echo $totalValoraciones; ?></strong>
                    <p>Valoraciones</p>
                </div>

            </div>

            <!-- INFO -->
            <div class="admin-info">

                <div class="card">
                    <h3>Curso más inscrito</h3>
                    <p>
                        <?php echo $cursoMasInscrito ? htmlspecialchars($cursoMasInscrito["titulo"]) : "Sin datos"; ?>
                    </p>
                </div>

                <div class="card">
                    <h3>Mejor valorado</h3>
                    <p>
                        <?php echo $cursoMejorValorado ? htmlspecialchars($cursoMejorValorado["titulo"]) : "Sin datos"; ?>
                    </p>
                </div>

            </div>



        </div>
    </div>

</body>

</html>