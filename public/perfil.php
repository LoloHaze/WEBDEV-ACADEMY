<?php
// PERFIL DE USUARIO
// -------------------------------------
// - MUESTRA DATOS DEL USUARIO
// - PERMITE CAMBIAR CONTRASEÑA
// - PERMITE SUBIR FOTO
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/funciones.php";
require_once "../includes/proteccion.php";

session_start();

// PROTEGER
protegerPagina();

$id = $_SESSION["usuario_id"];
$mensaje = "";

// 👇 NUEVO
$mensaje_error = "";
$mensaje_exito = "";

// OBTENER USUARIO
$usuario = obtenerUsuarioPorId($conexion, $id);

// FOTO PERFIL
$foto = obtenerFotoUsuario($usuario["nombre"], $usuario["foto"]);

// CAMBIAR PASSWORD
if (isset($_POST["actualizar_password"])) {

    $resultado = cambiarPassword($conexion, $usuario, $_POST);

    if ($resultado !== true) {
        $mensaje_error = $resultado;
    } else {
        $mensaje_exito = "Contraseña actualizada correctamente.";
    }
}

// SUBIR FOTO
if (isset($_POST["subir_foto"]) && isset($_FILES["foto"])) {

    $resultado = subirFotoPerfil($conexion, $id, $_FILES["foto"]);

    if ($resultado !== true) {
        $mensaje_error = $resultado;
    } else {
        $mensaje_exito = "Imagen subida correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi Perfil</title>
   <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/crearCurso.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <script src="assets/js/responsive.js" defer></script>
 
</head>

<body>

    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">
            <div class="profile-main-card">

                <!-- PERFIL-->
                <div class="profile-header">

                    <div class="profile-avatar">
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Avatar">
                    </div>

                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($usuario["nombre"]); ?></h3>
                        <span><?php echo htmlspecialchars($usuario["email"]); ?></span>
                    </div>

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

                <!-- PASSWORD -->
                <div class="profile-block">

                    <h4>Cambiar contraseña</h4>

                    <form method="POST" class="auth-form">

                        <input class="file-upload" type="password" name="password_actual"
                            placeholder="Contraseña actual" required>

                        <input class="file-upload" type="password" name="password_nueva" placeholder="Nueva contraseña"
                            required>

                        <input class="file-upload" type="password" name="password_confirmar"
                            placeholder="Confirmar contraseña" required>

                        <button type="submit" name="actualizar_password" class="btn btn-primary">
                            Actualizar contraseña
                        </button>
                    </form>
                </div>

                <!-- FOTO -->
                <div class="profile-block">
                    <h4>Imagen de perfil</h4>
                    <form method="POST" enctype="multipart/form-data" class="auth-imput">

                        <input type="file" name="foto" accept="image/*" required>
                        <br> <br>
                        <button type="submit" name="subir_foto" class="btn btn-primary">
                            Subir imagen
                        </button>
                    </form>
                </div>
            </div>
            <div class="profile-actions">
                <a href="index.php" class="btn btn-soft">← Volver</a>
            </div>
        </div>
    </div>
    <?php require_once "../includes/footer.php"; ?>
</body>

</html>