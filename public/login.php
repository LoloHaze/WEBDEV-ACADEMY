<?php
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// 1 - Se conecta a la base de datos usando require_once "../includes/bd.php" para poder consultar los usuarios.                                          
// 2 - Se inicia la sesión con session_start() para poder guardar datos del usuario en $_SESSION.                                                         
// 3 - Se recogen y validan los datos del formulario (email y password) cuando el método es POST.                                                         
// 4 - Se busca el usuario en la base de datos mediante una consulta preparada para evitar SQLInjection y se verifica la contraseña con password_verify().
// 5 - Si el usuario es válido y está activo, se guardan sus datos en $_SESSION y se redirige a index.php. Si no, se redirige al login con un error. 
// -------------------------------------------------------------------------------------------------------------------------------------------------------


// Conexión a base de datos
require_once "../includes/bd.php";

// Iniciar sesión para poder usar $_SESSION
session_start();

$mensaje = "";

// ------------------------------------------------------
// MOSTRAR MENSAJES SEGÚN ERROR 
// ------------------------------------------------------
// Si venimos desde una redirección con ?error=
// mostramos el mensaje correspondiente.
if (isset($_GET["error"])) {

    if ($_GET["error"] === "pendiente") {
        // Usuario existe pero aún no ha sido aprobado
        $mensaje = "Tu cuenta está pendiente de aprobación por el administrador.";
    } else {
        // Email o contraseña incorrectos
        $mensaje = "Credenciales incorrectas.";
    }
}

// ------------------------------------------------------
// PROCESAR FORMULARIO SOLO SI ES POST
// ------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recoger datos del formulario
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // ------------------------------------------------------
    // VALIDACIONES BÁSICAS
    // ------------------------------------------------------

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Email no válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "Contraseña inválida.";
    } else {

        // ------------------------------------------------------
        // BUSCAR USUARIO POR EMAIL
        // ------------------------------------------------------
        // Usamos consulta preparada para evitar SQL Injection.

        $sql = "SELECT id, nombre, password, rol, activo, foto FROM usuarios WHERE email = ?";

        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        // Si existe el usuario
        if ($usuario = mysqli_fetch_assoc($resultado)) {


            // ------------------------------------------------------
            // VERIFICAR CONTRASEÑA 
            // ------------------------------------------------------

            if (password_verify($password, $usuario["password"])) {


                // ------------------------------------------------------
                // VERIFICAR SI EL USUARIO ESTÁ APROBADO
                // ------------------------------------------------------
                // activo = 0  → pendiente
                // activo = 1  → aprobado

                if ($usuario["activo"] == 0) {
                    header("Location: login.php?error=pendiente");
                    exit;
                }

                // ------------------------------------------------------
                // CREAR SESIÓN
                // ------------------------------------------------------
                // Guardamos los datos básicos del usuario

                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["nombre"] = $usuario["nombre"];
                $_SESSION["rol"] = $usuario["rol"];
                $_SESSION["foto"] = $usuario["foto"];

                // Redirigir a la página principal
                header("Location: index.php");
                exit;

            } else {
                // Contraseña incorrecta
                header("Location: login.php?error=1");
                exit;
            }

        } else {
            // Usuario no encontrado
            header("Location: login.php?error=1");
            exit;
        }
    }
}
?>


<!-- ------------------------------------------------------
            HTML
------------------------------------------------------ -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebDev Academy</title>
</head>

<body>

    <h2>Login</h2>

    <?php if ($mensaje != ""): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>
        <button type="submit">Entrar</button>
    </form>

</body>

</html>