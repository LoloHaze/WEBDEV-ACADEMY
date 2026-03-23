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
</head>

<body>

<h2>Mi Perfil</h2>

<?php
$foto = (!empty($_SESSION["foto"]) &&
    file_exists("uploads/perfiles/" . $_SESSION["foto"]))
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION["nombre"]) . "&background=random&color=fff";
?>

<img src="<?php echo $foto; ?>" 
     width="120" height="120"
     style="border-radius:50%; object-fit:cover;"><br><br>

<p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario["nombre"]); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($usuario["email"]); ?></p>

<?php if ($mensaje != ""): ?>
    <p style="color:green;"><?php echo $mensaje; ?></p>
<?php endif; ?>

<hr>

<h3>Cambiar contraseña</h3>

<form method="POST">
    <input type="password" name="password_actual" placeholder="Contraseña actual" required><br><br>
    <input type="password" name="password_nueva" placeholder="Nueva contraseña" required><br><br>
    <input type="password" name="password_confirmar" placeholder="Confirmar nueva contraseña" required><br><br>

    <button type="submit" name="actualizar_password">
        Actualizar contraseña
    </button>
</form>

<hr>

<h3>Imagen de perfil</h3>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foto" accept="image/*" required><br><br>

    <button type="submit" name="subir_foto">
        Subir imagen
    </button>
</form>

<br>
<a href="index.php">← Volver</a>

</body>
</html>