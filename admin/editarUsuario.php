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

// 👇 NUEVO
$mensaje_error = "";
$mensaje_exito = "";

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
        $mensaje_error = "Nombre demasiado corto.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "Email no válido.";
    } else {

        $sql_update = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $rol, $id);

        if (mysqli_stmt_execute($stmt)) {

            $mensaje_exito = "Usuario actualizado correctamente.";

            // actualizar datos en pantalla
            $usuario["nombre"] = $nombre;
            $usuario["email"] = $email;
            $usuario["rol"] = $rol;

        } else {
            $mensaje_error = "Error al actualizar.";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
       <link rel="stylesheet" href="../public/assets/css/reescalado.css">

    <script src="../public/assets/js/forms.js" defer></script>
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

                <!-- MENSAJES -->
                <?php if ($mensaje_error): ?>
                    <div class="auth-error">
                        <?php echo htmlspecialchars($mensaje_error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje_exito): ?>
                    <div class="auth-success">
                        <?php echo htmlspecialchars($mensaje_exito); ?>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="POST" class="auth-form" id="formUsuario">

                    <!-- NOMBRE -->
                    <input type="text" name="nombre" id="nombre" class="auth-input"
                        value="<?php echo htmlspecialchars($usuario["nombre"]); ?>">
                    <p class="error-msg" id="errorNombre"></p>

                    <!-- EMAIL -->
                    <input type="email" name="email" id="email" class="auth-input"
                        value="<?php echo htmlspecialchars($usuario["email"]); ?>">
                    <p class="error-msg" id="errorEmail"></p>

                    <!-- ROL -->
                    <select name="rol" id="rol" class="auth-input">
                        <option value="alumno" <?php if ($usuario["rol"] == "alumno")
                            echo "selected"; ?>>
                            Alumno
                        </option>
                        <option value="admin" <?php if ($usuario["rol"] == "admin")
                            echo "selected"; ?>>
                            Administrador
                        </option>
                    </select>

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