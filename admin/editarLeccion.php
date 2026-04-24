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

// Obtener lección
$sql = "SELECT * FROM lecciones WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$leccion = mysqli_fetch_assoc($resultado);

if (!$leccion) {
    header("Location: gestionCursos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $video_url = trim($_POST["video_url"]);

    if (strlen($titulo) < 5) {
        $mensaje_error = "Título demasiado corto.";
    } else {

        $sqlUpdate = "UPDATE lecciones
                      SET titulo = ?, descripcion = ?, video_url = ?
                      WHERE id = ?";

        $stmt = mysqli_prepare($conexion, $sqlUpdate);
        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $titulo,
            $descripcion,
            $video_url,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $mensaje_exito = "Lección actualizada correctamente.";

            // actualizar datos en pantalla
            $leccion["titulo"] = $titulo;
            $leccion["descripcion"] = $descripcion;
            $leccion["video_url"] = $video_url;

        } else {
            $mensaje_error = "Error al actualizar.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Lección</title>
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

                <h3>Editar lección</h3>

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
                <form method="POST" class="auth-form" id="formCurso">

                    <!-- TITULO -->
                    <input type="text" name="titulo" id="titulo" class="auth-input" placeholder="Título de la lección"
                        value="<?php echo htmlspecialchars($leccion["titulo"]); ?>">
                    <p class="error-msg" id="errorTitulo"></p>

                    <!-- DESCRIPCION -->
                    <textarea name="descripcion" id="descripcion" class="auth-input"
                        placeholder="Descripción de la lección"><?php echo htmlspecialchars($leccion["descripcion"]); ?></textarea>
                    <p class="error-msg" id="errorDescripcion"></p>

                    <!-- VIDEO -->
                    <input type="text" name="video_url" id="video_url" class="auth-input" placeholder="URL del vídeo"
                        value="<?php echo htmlspecialchars($leccion["video_url"]); ?>">
                    <p class="error-msg" id="errorVideo"></p>

                    <!-- BOTÓN -->
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>

                </form>

            </div>

            <!-- VOLVER -->
            <div style="text-align:center; margin-top:20px;">
                <a href="gestionarLecciones.php?curso_id=<?php echo $leccion["curso_id"]; ?>" class="btn btn-soft">
                    ← Volver
                </a>
            </div>

        </div>
    </div>

</body>

</html>