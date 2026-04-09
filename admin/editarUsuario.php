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

    <!-- 🔥 REUTILIZAMOS TODO -->
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    
</head>

<body>

<div class="main">
    <div class="container">

        <?php require_once "../includes/headerAdmin.php"; ?>

        <!-- CARD FORM -->
        <div class="card admin-form-card">

            <h2 class="section-title" style="text-align:center;">
                Editar usuario
            </h2>

            <!-- MENSAJE -->
            <?php if ($mensaje != ""): ?>
                <div class="auth-error">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" class="auth-form">

                <!-- NOMBRE -->
                <input type="text"
                       name="nombre"
                       class="auth-input"
                       placeholder="Nombre"
                       value="<?php echo htmlspecialchars($usuario["nombre"]); ?>"
                       required>

                <!-- EMAIL -->
                <input type="email"
                       name="email"
                       class="auth-input"
                       placeholder="Email"
                       value="<?php echo htmlspecialchars($usuario["email"]); ?>"
                       required>

                <!-- ROL -->
                <select name="rol" class="auth-input">
                    <option value="alumno" <?php if ($usuario["rol"] == "alumno") echo "selected"; ?>>
                        Alumno
                    </option>
                    <option value="admin" <?php if ($usuario["rol"] == "admin") echo "selected"; ?>>
                        Administrador
                    </option>
                </select>

                <!-- BOTÓN -->
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    Guardar cambios
                </button>

            </form>

        </div>

        <!-- VOLVER -->
        <div style="text-align:center; margin-top:20px;">
            <a href="gestionUsuarios.php" class="btn btn-soft">
                ← Volver
            </a>
        </div>

    </div>
</div>



</body>
</html>