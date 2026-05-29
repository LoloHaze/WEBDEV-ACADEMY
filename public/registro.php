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

$mensaje_error = "";
$mensaje_exito = "";
$nombre = "";
$email = "";
$password = "";

// MENSAJE OK
if (isset($_GET["success"])) {
    $mensaje_exito = "Registro correcto. Ya puedes iniciar sesión.";
}

// PROCESAR REGISTRO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // VALIDACIÓN
    if (strlen($nombre) < 3) {
        $mensaje_error = "El nombre debe tener al menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $mensaje_error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // COMPROBAR EMAIL
        if (existeUsuarioPorEmail($conexion, $email)) {

            $mensaje_error = "El email ya está registrado.";

        } else {

            // CREAR USUARIO
            if (crearUsuario($conexion, $nombre, $email, $password)) {

                header("Location: registro.php?success=1");
                exit;

            } else {
                $mensaje_error = "Error al registrar.";
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
     <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <title>Registro - WebDev Academy</title>

    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/reescalado2.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <script src="assets/js/responsive.js" defer></script>
    <script src="../public/assets/js/forms.js" defer></script>
</head>

<body>

    <div class="auth-container">

        <div class="auth-card">

            <div class="auth-header">
                <div class="auth-logo">
                    <div class="auth-logo">
                        <img src="assets/logowebdev.png" alt="Logo">
                        <span class="logo">WebDevAcademy</span>
                    </div>
                    <h2>Iniciar sesión</h2>
                </div>

                <!-- MENSAJES -->
                <?php if ($mensaje_error != ""): ?>
                    <div class="auth-error">
                        <?php echo $mensaje_error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje_exito != ""): ?>
                    <div class="auth-success">
                        <?php echo $mensaje_exito; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form" id="formRegistro">

                    <input type="text" name="nombre" id="nombreRegistro" placeholder="Nombre"
                        value="<?php echo htmlspecialchars($nombre); ?>">
                    <p class="error-msg" id="errorNombreRegistro"></p>

                    <input type="email" name="email" id="emailRegistro" placeholder="Email"
                        value="<?php echo htmlspecialchars($email); ?>">
                    <p class="error-msg" id="errorEmailRegistro"></p>

                    <input type="password" name="password" id="passwordRegistro" placeholder="Contraseña">
                    <p class="error-msg" id="errorPasswordRegistro"></p>

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
</div>
</body>

</html>