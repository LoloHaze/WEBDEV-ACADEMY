<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Aprobar usuario
if (isset($_GET["aprobar"])) {
    $id = intval($_GET["aprobar"]);
    $sql = "UPDATE usuarios SET activo = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: gestionar_usuarios.php");
    exit;
}

$sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
$resultado = mysqli_query($conexion, $sql);
?>

<h2>Gestionar Usuarios</h2>

<?php while ($usuario = mysqli_fetch_assoc($resultado)): ?>

    <div style="border:1px solid #ccc; padding:10px; margin:10px;">
        <strong><?php echo htmlspecialchars($usuario["nombre"]); ?></strong>
        (<?php echo $usuario["email"]; ?>)

        <?php if ($usuario["activo"] == 0): ?>
            - ⏳ Pendiente
            <a href="?aprobar=<?php echo $usuario["id"]; ?>">
                Aprobar
            </a>
        <?php else: ?>
            - ✅ Activo
        <?php endif; ?>
    </div>

<?php endwhile; ?>

<a href="dashboard.php">Volver</a>