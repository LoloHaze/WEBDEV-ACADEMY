<?php
// REGISTRO DE USUARIO
// -------------------------------------
// - PROCESA EL FORMULARIO
// - VALIDA LOS DATOS
// - COMPRUEBA EL EMAIL
// - CREA USUARIO
// -------------------------------------
require_once "../includes/bd.php";
require_once "../includes/funciones.php";

$mensaje = "";
$nombre = "";
$email = "";
$password = "";

// MENSAJE OK
if (isset($_GET["success"])) {
    $mensaje = "Registro correcto. Ya puedes iniciar sesión.";
}
// PROCESAR REGISTRO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // VALIDACIÓN
    if (strlen($nombre) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } else {
    // COMPROBAR EMAIL
        if (existeUsuarioPorEmail($conexion, $email)) {

            $mensaje = "El email ya está registrado.";

        } else {

    // CREAR USUARIO
            if (crearUsuario($conexion, $nombre, $email, $password)) {

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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - WebDev Academy</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/components.css">
</head>

<body>

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">
                <div class="auth-logo">
                    <img src="assets/logowebdev.png" alt="Logo">
                    <span>WebDevAcademy</span>
                </div>
                <h2>Crear cuenta</h2>
            </div>

            <?php if ($mensaje != ""): ?>
                <div class="auth-error">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($nombre); ?>"
                    required>

                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>"
                    required>

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