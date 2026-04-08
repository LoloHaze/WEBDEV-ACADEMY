<?php
require_once "../includes/bd.php";

$mensaje = "";
$nombre = "";
$email = "";
$password = "";

// MENSAJE OK
if (isset($_GET["success"])) {
    $mensaje = "Registro correcto. Ya puedes iniciar sesión.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (strlen($nombre) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } else {

        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexion, $sql_check);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $mensaje = "El email ya está registrado.";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO usuarios (nombre, email, password)
                           VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($conexion, $sql_insert);
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $password_hash);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: registro.php?success=1");
                exit;
            } else {
                $mensaje = "Error al registrar.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registro - WebDev Academy</title>

    <!-- MISMO CSS QUE LOGIN -->
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/components.css">
</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-logo">🚀 WebDevAcademy</div>
            <h2>Crear cuenta</h2>
        </div>

        <?php if ($mensaje != ""): ?>
            <div class="auth-error">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

            <input type="text" name="nombre" placeholder="Nombre"
                value="<?php echo htmlspecialchars($nombre); ?>" required>

            <input type="email" name="email" placeholder="Email"
                value="<?php echo htmlspecialchars($email); ?>" required>

            <input type="password" name="password" placeholder="Contraseña" required>

            <button type="submit" class="btn btn-primary">
                Registrarse
            </button>

        </form>

        <p class="auth-link">
            ¿Ya tienes cuenta?
            <a href="login.php">Inicia sesión</a>
        </p>

    </div>

</div>

</body>
</html>