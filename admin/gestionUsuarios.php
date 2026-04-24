<?php
require_once "../includes/bd.php";
session_start();

// Protección admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

//ACCIONES (APROBAR / DESACTIVAR / ELIMINAR)

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

// ELIMINAR USUARIO

if (isset($_GET["eliminar"])) {
    $id = intval($_GET["eliminar"]);

    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionUsuarios.php");
    exit;
}

//OBTENER USUARIOS SEPARADOS POR ESTADO
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
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
       <link rel="stylesheet" href="../public/assets/css/reescalado.css">
</head>

<body>

    <div class="main">

        <div class="container">
                <?php require_once "../includes/headerAdmin.php"; ?>

            <h2 class="section-title">Gestión de Usuarios</h2>

            <!-- PENDIENTES -->

            <h3 class="section-subtitle">🔴 Pendientes (<?php echo $count_pendientes; ?>)</h3>
            <div class="admin-list">

                <?php if ($count_pendientes == 0): ?>
                    <p class="empty-text">No hay usuarios pendientes.</p>
                <?php else: ?>
                    <?php while ($usuario = mysqli_fetch_assoc($pendientes)): ?>
                        <?php
                        $foto_usuario = (!empty($usuario["foto"]) &&
                            file_exists("../public/uploads/perfiles/" . $usuario["foto"]))
                            ? "../public/uploads/perfiles/" . $usuario["foto"]
                            : "https://ui-avatars.com/api/?name=" . urlencode($usuario["nombre"]) . "&background=random&color=fff";
                        ?>
                        <div class="card admin-item">
                            <div class="admin-user-mini">
                                <img src="<?php echo $foto_usuario; ?>">
                                <div>
                                    <strong><?php echo htmlspecialchars($usuario["nombre"]); ?></strong>
                                    <span><?php echo htmlspecialchars($usuario["email"]); ?></span>
                                </div>
                            </div>
                            <div class="admin-actions">
                                <a href="?aprobar=<?php echo $usuario["id"]; ?>" class="btn btn-primary">Aprobar</a>
                                <a href="editarUsuario.php?id=<?php echo $usuario["id"]; ?>" class="btn btn-soft">Editar</a>
                                <a href="?eliminar=<?php echo $usuario["id"]; ?>" class="btn btn-soft2"
                                    onclick="return confirm('¿Eliminar usuario?');">
                                    Eliminar
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <!-- ACTIVOS -->
       
            <h3 class="section-subtitle">🟢 Activos (<?php echo $count_activos; ?>)</h3>

            <div class="admin-list">

                <?php if ($count_activos == 0): ?>

                    <p class="empty-text">No hay usuarios activos.</p>

                <?php else: ?>

                    <?php while ($usuario = mysqli_fetch_assoc($activos)): ?>

                        <?php
                        $foto_usuario = (!empty($usuario["foto"]) &&
                            file_exists("../public/uploads/perfiles/" . $usuario["foto"]))
                            ? "../public/uploads/perfiles/" . $usuario["foto"]
                            : "https://ui-avatars.com/api/?name=" . urlencode($usuario["nombre"]) . "&background=random&color=fff";
                        ?>

                        <div class="card admin-item">

                            <div class="admin-user-mini">
                                <img src="<?php echo $foto_usuario; ?>">
                                <div>
                                    <strong><?php echo htmlspecialchars($usuario["nombre"]); ?></strong>
                                    <span><?php echo htmlspecialchars($usuario["email"]); ?></span>
                                </div>
                            </div>

                            <div class="admin-actions">
                                <span class="status success">Activo</span>
                                <a href="?desactivar=<?php echo $usuario["id"]; ?>" class="btn btn-soft">Desactivar</a>
                                <a href="editarUsuario.php?id=<?php echo $usuario["id"]; ?>" class="btn btn-soft">Editar</a>
                                <a href="?eliminar=<?php echo $usuario["id"]; ?>" class="btn btn-soft2"
                                    onclick="return confirm('¿Eliminar usuario?');">
                                    Eliminar
                                </a>
                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php endif; ?>

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