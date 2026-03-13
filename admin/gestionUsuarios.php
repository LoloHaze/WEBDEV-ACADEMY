<?php
require_once "../includes/bd.php";
session_start();

// 🔐 Protección admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

/*
--------------------------------------------------
ACCIONES (APROBAR / DESACTIVAR / ELIMINAR)
--------------------------------------------------
*/

if (isset($_GET["aprobar"])) {
    $id = intval($_GET["aprobar"]);
    $sql = "UPDATE usuarios SET activo = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionUsuarios.php");
    exit;
}

if (isset($_GET["desactivar"])) {
    $id = intval($_GET["desactivar"]);
    $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionUsuarios.php");
    exit;
}

// -----------------------------------------
// ELIMINAR USUARIO
// -----------------------------------------
if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionUsuarios.php");
    exit;
}

/*
--------------------------------------------------
OBTENER USUARIOS SEPARADOS POR ESTADO
--------------------------------------------------
*/

// Pendientes
$sql_pendientes = "SELECT * FROM usuarios WHERE activo = 0 ORDER BY fecha_registro DESC";
$pendientes = mysqli_query($conexion, $sql_pendientes);

// Activos
$sql_activos = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY fecha_registro DESC";
$activos = mysqli_query($conexion, $sql_activos);

// Contadores
$count_pendientes = mysqli_num_rows($pendientes);
$count_activos = mysqli_num_rows($activos);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Usuarios</title>
</head>

<body>

    <h2>Gestión de Usuarios</h2>

    <!-- ========================= -->
    <!-- USUARIOS PENDIENTES -->
    <!-- ========================= -->

    <h3>🔴 Usuarios Pendientes (<?php echo $count_pendientes; ?>)</h3>

    <?php if ($count_pendientes == 0): ?>
        <p>No hay usuarios pendientes.</p>
    <?php else: ?>
        <?php while ($usuario = mysqli_fetch_assoc($pendientes)): ?>

            <?php
            $foto = $usuario["foto"]
                ? "../public/uploads/perfiles/" . $usuario["foto"]
                : "https://loremflickr.com/320/240";
            ?>

            <div style="display:flex; align-items:center; gap:15px; border:1px solid #ccc; padding:10px; margin:10px 0;">

                <img src="<?php echo $foto; ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">

                <div style="flex:1;">
                    <strong><?php echo htmlspecialchars($usuario["nombre"]); ?></strong><br>
                    <small><?php echo htmlspecialchars($usuario["email"]); ?></small>
                </div>

                <div>
                    <a href="?aprobar=<?php echo $usuario["id"]; ?>">✅ Aprobar</a> |
                    <a href="editarUsuario.php?id=<?php echo $usuario["id"]; ?>">✏ Editar</a> |
                    <a href="?eliminar=<?php echo $usuario["id"]; ?>" onclick="return confirm('¿Eliminar usuario?');">
                        🗑 Eliminar
                    </a>
                </div>

            </div>

        <?php endwhile; ?>
    <?php endif; ?>


    <!-- ========================= -->
    <!-- USUARIOS ACTIVOS -->
    <!-- ========================= -->

    <h3>🟢 Usuarios Activos (<?php echo $count_activos; ?>)</h3>

    <?php if ($count_activos == 0): ?>
        <p>No hay usuarios activos.</p>
    <?php else: ?>
        <?php while ($usuario = mysqli_fetch_assoc($activos)): ?>

            <?php
            $foto = $usuario["foto"]
                ? "../public/uploads/perfiles/" . $usuario["foto"]
                : "https://loremflickr.com/320/240";
            ?>

            <div style="display:flex; align-items:center; gap:15px; border:1px solid #ccc; padding:10px; margin:10px 0;">

                <img src="<?php echo $foto; ?>" width="50" height="50" style="border-radius:50%; object-fit:cover;">

                <div style="flex:1;">
                    <strong><?php echo htmlspecialchars($usuario["nombre"]); ?></strong><br>
                    <small><?php echo htmlspecialchars($usuario["email"]); ?></small>
                </div>

                <div>
                    <a href="?desactivar=<?php echo $usuario["id"]; ?>">⛔ Desactivar</a> |
                    <a href="editarUsuario.php?id=<?php echo $usuario["id"]; ?>">✏ Editar</a> |
                    <a href="?eliminar=<?php echo $usuario["id"]; ?>" onclick="return confirm('¿Eliminar usuario?');">
                        🗑 Eliminar
                    </a>
                </div>

            </div>

        <?php endwhile; ?>
    <?php endif; ?>

    <br>
    <a href="panel.php">← Volver al panel</a>

</body>

</html>