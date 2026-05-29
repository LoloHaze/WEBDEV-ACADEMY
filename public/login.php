<?php

// LOGIN DE USUARIO
// -------------------------------------
// - CONECTA CON LA BASE DE DATOS
// - INICIA LA SESIÓN
// - PROCESA EL LOGIN
// - VERIFICA CREDENCIALES
// - CREA SESIÓN
// - REDIRECCIONA
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/funciones.php";
session_start();

$mensaje = "";

// MENSAJES DESDE GET
if (isset($_GET["error"])) {

    if ($_GET["error"] === "pendiente") {
        $mensaje = "Tu cuenta está pendiente de aprobación por el administrador.";
    } else {
        $mensaje = "Credenciales incorrectas.";
    }
}

// PROCESAR LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // VALIDACIÓN BÁSICA
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Email no válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "Contraseña inválida.";
    } else {

        // OBTENER USUARIO
        $usuario = obtenerUsuarioPorEmail($conexion, $email);

        if ($usuario && verificarPassword($password, $usuario["password"])) {

            // COMPROBAR ACTIVACIÓN
            if ($usuario["activo"] == 0) {
                header("Location: login.php?error=pendiente");
                exit;
            }

            // CREAR SESIÓN
            crearSesionUsuario($usuario);

            // REDIRECCIÓN INTELIGENTE
            if (isset($_GET['redirect']) && strpos($_GET['redirect'], '.php') !== false) {
                header("Location: " . $_GET['redirect']);
            } else {
                header("Location: index.php");
            }
            exit;

        } else {
            header("Location: login.php?error=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebDev Academy</title>

    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/reescalado2.css">


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

                <?php if ($mensaje != ""): ?>
                    <div class="auth-error">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form" id="formLogin">

                    <input type="email" name="email" id="emailLogin" placeholder="Email">
                    <p class="error-msg" id="errorEmailLogin"></p>

                    <input type="password" name="password" id="passwordLogin" placeholder="Contraseña">
                    <p class="error-msg" id="errorPasswordLogin"></p>

                    <button type="submit" class="btn btn-primary">
                        Entrar
                    </button>

                </form>

                <p class="auth-link">
                    ¿No tienes cuenta?
                    <a href="registro.php">Regístrate</a>
                </p>

            </div>

        </div>
    </div>
</body>

</html>