<?php
require_once "../includes/bd.php";
session_start();

// 🔐 Protección admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

/* =========================
   ACCIONES (APROBAR / RECHAZAR)
========================= 
*/

if (isset($_GET["aprobar"])) {
    $id = intval($_GET["aprobar"]);

    $sql = "UPDATE inscripciones SET estado = 'aprobado' WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionInscripciones.php");
    exit;
}

if (isset($_GET["rechazar"])) {
    $id = intval($_GET["rechazar"]);

    $sql = "UPDATE inscripciones SET estado = 'rechazado' WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionInscripciones.php");
    exit;
}
if (isset($_GET["pendiente"])) {
    $id = intval($_GET["pendiente"]);

    $sql = "UPDATE inscripciones SET estado = 'pendiente' WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionInscripciones.php");
    exit;
}
// // =========================
// // CANCELAR INSCRIPCIÓN
// // =========================
// if (isset($_GET["cancelar"])) {
//     $id = intval($_GET["cancelar"]);

//     $sql = "DELETE FROM inscripciones WHERE id = ?";
//     $stmt = mysqli_prepare($conexion, $sql);
//     mysqli_stmt_bind_param($stmt, "i", $id);
//     mysqli_stmt_execute($stmt);

//     header("Location: gestionInscripciones.php");
//     exit;
// }

/* =========================
   OBTENER INSCRIPCIONES
========================= */

// Pendientes
$sql_pendientes = "
SELECT i.id, u.nombre, u.email, u.foto, c.titulo
FROM inscripciones i
JOIN usuarios u ON i.usuario_id = u.id
JOIN cursos c ON i.curso_id = c.id
WHERE i.estado = 'pendiente'
ORDER BY i.fecha_solicitud DESC
";
$pendientes = mysqli_query($conexion, $sql_pendientes);

// Aprobadas
$sql_aprobadas = "
SELECT i.id, u.nombre, u.email, u.foto, c.titulo
FROM inscripciones i
JOIN usuarios u ON i.usuario_id = u.id
JOIN cursos c ON i.curso_id = c.id
WHERE i.estado = 'aprobado'
ORDER BY i.fecha_solicitud DESC
";
$aprobadas = mysqli_query($conexion, $sql_aprobadas);


// Rechazadas
$sql_rechazadas = "
SELECT i.id, u.nombre, u.email, u.foto, c.titulo
FROM inscripciones i
JOIN usuarios u ON i.usuario_id = u.id
JOIN cursos c ON i.curso_id = c.id
WHERE i.estado = 'rechazado'
ORDER BY i.fecha_solicitud DESC
";
$rechazadas = mysqli_query($conexion, $sql_rechazadas);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Inscripciones</title>

    <!-- 🔥 REUTILIZAMOS TODO -->
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css"> <!-- tu css admin -->
</head>

<body>



<div class="main">
    
<div class="container">

<?php require_once "../includes/headerAdmin.php"; ?>

    <h2 class="section-title">Gestión de Inscripciones</h2>

    <!-- ================= PENDIENTES ================= -->
    <h3 class="section-subtitle">📌 Pendientes</h3>

    <div class="admin-list">

    <?php while ($row = mysqli_fetch_assoc($pendientes)): ?>

        <?php
        $foto = $row["foto"]
            ? "../public/uploads/perfiles/" . $row["foto"]
            : "https://ui-avatars.com/api/?name=" . urlencode($row["nombre"]);
        ?>

        <div class="card admin-item">

            <div class="admin-user-mini">
                <img src="<?php echo $foto; ?>">
                <div>
                    <strong><?php echo htmlspecialchars($row["nombre"]); ?></strong>
                    <span><?php echo htmlspecialchars($row["email"]); ?></span>
                    <p>Curso: <b><?php echo htmlspecialchars($row["titulo"]); ?></b></p>
                </div>
            </div>

            <div class="admin-actions">
                <a href="?aprobar=<?php echo $row["id"]; ?>" class="btn btn-primary">Aprobar</a>
                <a href="?rechazar=<?php echo $row["id"]; ?>" class="btn btn-soft">Rechazar</a>
            </div>

        </div>

    <?php endwhile; ?>

    </div>


    <!-- ================= APROBADAS ================= -->
    <h3 class="section-subtitle">✅ Aprobadas</h3>

    <div class="admin-list">

    <?php while ($row = mysqli_fetch_assoc($aprobadas)): ?>

        <?php
        $foto = $row["foto"]
            ? "../public/uploads/perfiles/" . $row["foto"]
            : "https://ui-avatars.com/api/?name=" . urlencode($row["nombre"]);
        ?>

        <div class="card admin-item">

            <div class="admin-user-mini">
                <img src="<?php echo $foto; ?>">
                <div>
                    <strong><?php echo htmlspecialchars($row["nombre"]); ?></strong>
                    <span><?php echo htmlspecialchars($row["email"]); ?></span>
                    <p>Curso: <b><?php echo htmlspecialchars($row["titulo"]); ?></b></p>
                </div>
            </div>

            <div class="admin-actions">
                <span class="status success">✔ Aprobado</span>
                <a href="?rechazar=<?php echo $row["id"]; ?>" class="btn btn-soft">
                    Quitar acceso
                </a>
            </div>

        </div>

    <?php endwhile; ?>

    </div>


    <!-- ================= RECHAZADAS ================= -->
    <h3 class="section-subtitle">❌ Rechazadas</h3>

    <div class="admin-list">

    <?php while ($row = mysqli_fetch_assoc($rechazadas)): ?>

        <?php
        $foto = $row["foto"]
            ? "../public/uploads/perfiles/" . $row["foto"]
            : "https://ui-avatars.com/api/?name=" . urlencode($row["nombre"]);
        ?>

        <div class="card admin-item">

            <div class="admin-user-mini">
                <img src="<?php echo $foto; ?>">
                <div>
                    <strong><?php echo htmlspecialchars($row["nombre"]); ?></strong>
                    <span><?php echo htmlspecialchars($row["email"]); ?></span>
                    <p>Curso: <b><?php echo htmlspecialchars($row["titulo"]); ?></b></p>
                </div>
            </div>

            <div class="admin-actions">
                <a href="?aprobar=<?php echo $row["id"]; ?>" class="btn btn-primary">Aprobar</a>
                <a href="?pendiente=<?php echo $row["id"]; ?>" class="btn btn-soft">Pendiente</a>
            </div>

        </div>

    <?php endwhile; ?>

    </div>

    <!-- VOLVER -->
    <div class="admin-footer">
        <a href="panel.php" class="btn btn-soft">← Volver</a>
    </div>

</div>
</div>

<?php require_once "../includes/footer.php"; ?>

</body>
</html>