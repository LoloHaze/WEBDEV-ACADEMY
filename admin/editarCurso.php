<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$id = intval($_GET["id"]);

// 👇 NUEVO
$mensaje_error = "";
$mensaje_exito = "";

// Obtener curso
$sql = "SELECT * FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($resultado);

if (!$curso) {
    header("Location: gestionCursos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = trim($_POST["precio"]);

    if (strlen($titulo) < 5) {
        $mensaje_error = "Título demasiado corto.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $mensaje_error = "Precio inválido.";
    } else {

        $imagenNombre = $curso["imagen_portada"];

        if (!empty($_FILES["imagen_portada"]["name"])) {

            $archivo = $_FILES["imagen_portada"];

            if ($archivo["error"] === 0) {

                $tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

                if (in_array($archivo["type"], $tiposPermitidos)) {

                    if ($archivo["size"] <= 3 * 1024 * 1024) {

                        $extension = pathinfo($archivo["name"], PATHINFO_EXTENSION);
                        $imagenNombre = "curso_" . $id . "." . $extension;
                        $rutaDestino = "../public/uploads/cursos/" . $imagenNombre;

                        move_uploaded_file($archivo["tmp_name"], $rutaDestino);

                    } else {
                        $mensaje_error = "Imagen demasiado grande (máx 3MB).";
                    }
                } else {
                    $mensaje_error = "Formato no permitido.";
                }
            }
        }

        if ($mensaje_error == "") {

            $sql_update = "UPDATE cursos 
                           SET titulo = ?, descripcion = ?, precio = ?, imagen_portada = ?
                           WHERE id = ?";

            $stmt = mysqli_prepare($conexion, $sql_update);
            mysqli_stmt_bind_param(
                $stmt,
                "ssdsi",
                $titulo,
                $descripcion,
                $precio,
                $imagenNombre,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                $mensaje_exito = "Curso actualizado correctamente.";
                $curso["imagen_portada"] = $imagenNombre;
            } else {
                $mensaje_error = "Error al actualizar.";
            }
        }
    }
}

$imagenActual = (!empty($curso["imagen_portada"]))
    ? "../public/uploads/cursos/" . $curso["imagen_portada"]
    : "https://via.placeholder.com/400x200?text=Sin+imagen";

?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Curso</title>
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="../public/assets/css/perfil.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <link rel="stylesheet" href="../public/assets/css/crearCurso.css">
       <link rel="stylesheet" href="../public/assets/css/reescalado.css">

    <script src="../public/assets/js/forms.js" defer></script>
</head>

<body>

    <div class="main">
        <div class="container">

            <?php require_once "../includes/headerAdmin.php"; ?>

            <div class="card" style="max-width:500px; margin:auto;">

                <h3>Editar curso</h3>

                <!-- IMAGEN -->
                <img src="<?php echo $imagenActual; ?>"
                    style="width:100%; height:200px; object-fit:cover; border-radius:10px; margin-bottom:15px;">

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
                <form method="POST" enctype="multipart/form-data" class="auth-form" id="formCurso">

                    <!-- TITULO -->
                    <input type="text" name="titulo" id="titulo" class="auth-input"
                        value="<?php echo htmlspecialchars($curso["titulo"]); ?>">
                    <p class="error-msg" id="errorTitulo"></p>


                    <!-- DESCRIPCION -->
                    <textarea name="descripcion" id="descripcion"
                        class="auth-input"><?php echo htmlspecialchars($curso["descripcion"]); ?></textarea>
                    <p class="error-msg" id="errorDescripcion"></p>


                    <!-- PRECIO -->
                    <input type="number" step="0.01" name="precio" id="precio" class="auth-input"
                        value="<?php echo htmlspecialchars($curso["precio"]); ?>">
                    <p class="error-msg" id="errorPrecio"></p>


                    <!-- IMAGEN -->
                    <input type="file" name="imagen_portada" id="imagen" class="auth-input" accept="image/*">
                    <p class="error-msg" id="errorImagen"></p>

                    <!-- BOTÓN -->
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>

                </form>

            </div>

            <!-- VOLVER -->
            <div style="text-align:center; margin-top:20px;">
                <a href="gestionCursos.php" class="btn btn-soft">
                    ← Volver
                </a>
            </div>

        </div>
    </div>

</body>

</html>