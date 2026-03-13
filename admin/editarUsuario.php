<?php
require_once "../includes/bd.php";
session_start();

// Protección admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Verificar ID válido
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionUsuarios.php");
    exit;
}

$id = intval($_GET["id"]);
$mensaje = "";

// Obtener datos actuales
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

if (!$usuario) {
    header("Location: gestionUsuarios.php");
    exit;
}

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $rol = $_POST["rol"];

    if (strlen($nombre) < 3) {
        $mensaje = "Nombre demasiado corto.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Email no válido.";
    } else {

        $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $rol, $id);
        mysqli_stmt_execute($stmt);

        header("Location: gestionUsuarios.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Usuario</title>
</head>
<body>

<h2>Editar Usuario</h2>

<?php if ($mensaje != ""): ?>
    <p><?php echo $mensaje; ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario["nombre"]); ?>" required><br><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario["email"]); ?>" required><br><br>

    <select name="rol">
        <option value="alumno" <?php if ($usuario["rol"] == "alumno") echo "selected"; ?>>Alumno</option>
        <option value="admin" <?php if ($usuario["rol"] == "admin") echo "selected"; ?>>Admin</option>
    </select><br><br>

    <button type="submit">Guardar cambios</button>
</form>

<br>
<a href="gestionUsuarios.php">← Volver</a>

</body>
</html>