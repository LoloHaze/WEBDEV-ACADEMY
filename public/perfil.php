<?php
require_once "../includes/bd.php";
session_start();

// Protección
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["usuario_id"];
$mensaje = "";

/* ==========================================
   OBTENER DATOS ACTUALES
========================================== */
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);


$foto = (!empty($_SESSION["foto"]) &&
    file_exists("uploads/perfiles/" . $_SESSION["foto"]))
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION["nombre"]) . "&background=random&color=fff";


/* ==========================================
   CAMBIAR CONTRASEÑA
========================================== */
if (isset($_POST["actualizar_password"])) {

    $actual = $_POST["password_actual"] ?? "";
    $nueva = $_POST["password_nueva"] ?? "";
    $confirmar = $_POST["password_confirmar"] ?? "";

    if (!password_verify($actual, $usuario["password"])) {
        $mensaje = "La contraseña actual no es correcta.";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {

        $nueva_hash = password_hash($nueva, PASSWORD_DEFAULT);

        $sql_update = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt, "si", $nueva_hash, $id);
        mysqli_stmt_execute($stmt);

        $mensaje = "Contraseña actualizada correctamente.";
    }
}

/* ==========================================
   SUBIR IMAGEN DE PERFIL
========================================== */
if (isset($_POST["subir_foto"]) && isset($_FILES["foto"])) {

    $archivo = $_FILES["foto"];

    if ($archivo["error"] === 0) {

        $tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

        if (in_array($archivo["type"], $tiposPermitidos)) {

            if ($archivo["size"] <= 2 * 1024 * 1024) {

                $extension = pathinfo($archivo["name"], PATHINFO_EXTENSION);
                $nuevoNombre = "usuario_" . $id . "." . $extension;
                $rutaDestino = "uploads/perfiles/" . $nuevoNombre;

                if (move_uploaded_file($archivo["tmp_name"], $rutaDestino)) {

                    $sql_update = "UPDATE usuarios SET foto = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conexion, $sql_update);
                    mysqli_stmt_bind_param($stmt, "si", $nuevoNombre, $id);
                    mysqli_stmt_execute($stmt);

                    // 🔥 Actualizar sesión
                    $_SESSION["foto"] = $nuevoNombre;

                    $mensaje = "Imagen subida correctamente.";
                } else {
                    $mensaje = "Error al mover el archivo.";
                }

            } else {
                $mensaje = "La imagen supera el tamaño máximo (2MB).";
            }

        } else {
            $mensaje = "Formato no permitido (solo JPG, PNG o WEBP).";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
   
</head>

<body>

    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">
            <div class="profile-main-card">

                <!-- HEADER -->
                <div class="profile-header">

                    <div class="profile-avatar">
                        <img src="<?php echo $foto; ?>">
                    </div>

                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($usuario["nombre"]); ?></h3>
                        <span><?php echo htmlspecialchars($usuario["email"]); ?></span>
                    </div>

                </div>

                <?php if ($mensaje != ""): ?>
                    <div class="auth-error">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <!-- PASSWORD -->
                <div class="profile-block">

                    <h4>Cambiar contraseña</h4>

                    <form method="POST" class="auth-form">

                        <input class="file-upload" type="password" name="password_actual" placeholder="Contraseña actual"
                            required>

                        <input class="file-upload" type="password" name="password_nueva" placeholder="Nueva contraseña"
                            required>

                        <input class="file-upload" type="password" name="password_confirmar"
                            placeholder="Confirmar contraseña" required>

                        <button type="submit" class="btn btn-primary">
                            Actualizar contraseña
                        </button>

                    </form>

                </div>

                <!-- FOTO -->
                <div class="profile-block">

                    <h4>Imagen de perfil</h4>

                    <form method="POST" enctype="multipart/form-data" class="auth-form">

                        <div class="file-upload">

                            <label class="file-btn">
                                Seleccionar imagen
                                <input type="file" name="foto" id="fileInput" required>
                            </label>

                            <span id="fileName">Ningún archivo seleccionado</span>

                        </div>

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
<script>
    document.getElementById("fileInput").addEventListener("change", function () {
        const fileName = this.files[0]?.name || "Ningún archivo seleccionado";
        document.getElementById("fileName").textContent = fileName;
    });
</script>