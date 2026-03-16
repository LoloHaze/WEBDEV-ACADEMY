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
========================= */

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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Inscripciones</title>
</head>

<body>

    <h2>Gestión de Inscripciones</h2>

    <!-- ================= PENDIENTES ================= -->

    <h3>📌 Pendientes</h3>

    <?php while ($row = mysqli_fetch_assoc($pendientes)): ?>

        <?php
        $foto = $row["foto"]
            ? "../public/uploads/perfiles/" . $row["foto"]
            : "https://ui-avatars.com/api/?name=" . urlencode($row["nombre"]);
        ?>

        <div style="display:flex; align-items:center; gap:15px; border:1px solid #ccc; padding:10px; margin:10px 0;">

            <img src="<?php echo $foto; ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">

            <div style="flex:1;">
                <strong><?php echo htmlspecialchars($row["nombre"]); ?></strong><br>
                <small><?php echo htmlspecialchars($row["email"]); ?></small><br>
                Curso: <strong><?php echo htmlspecialchars($row["titulo"]); ?></strong>
            </div>

            <div>
                <a href="?aprobar=<?php echo $row["id"]; ?>">✅ Aprobar</a> |
                <a href="?rechazar=<?php echo $row["id"]; ?>">❌ Rechazar</a>
            </div>

        </div>

    <?php endwhile; ?>


    <!-- ================= APROBADAS ================= -->

    <h3>✅ Aprobadas</h3>

    <?php while ($row = mysqli_fetch_assoc($aprobadas)): ?>

        <?php
        $foto = $row["foto"]
            ? "../public/uploads/perfiles/" . $row["foto"]
            : "https://ui-avatars.com/api/?name=" . urlencode($row["nombre"]);
        ?>

        <div style="display:flex; align-items:center; gap:15px; border:1px solid #ccc; padding:10px; margin:10px 0;">

            <img src="<?php echo $foto; ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">

            <div style="flex:1;">
                <strong><?php echo htmlspecialchars($row["nombre"]); ?></strong><br>
                <small><?php echo htmlspecialchars($row["email"]); ?></small><br>
                Curso: <strong><?php echo htmlspecialchars($row["titulo"]); ?></strong>
            </div>

            <div>
                <span style="color:green;">✔ Aprobado</span>
            </div>

        </div>

    <?php endwhile; ?>

    <br>
    <a href="panel.php">← Volver al panel</a>

</body>

</html>