<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

if (!isset($_GET["curso_id"]) || !is_numeric($_GET["curso_id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$curso_id = intval($_GET["curso_id"]);

// 👇 NUEVO
$mensaje_error = "";
$mensaje_exito = "";

// Obtener curso
$sqlCurso = "SELECT titulo FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sqlCurso);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$resCurso = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($resCurso);

if (!$curso) {
    header("Location: gestionCursos.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $video_url = trim($_POST["video_url"]);

    if (strlen($titulo) < 5) {
        $mensaje_error = "Título demasiado corto.";
    } elseif (strlen($video_url) < 5) {
        $mensaje_error = "URL de vídeo inválida.";
    } else {

        // Calcular orden automáticamente
        $sqlOrden = "SELECT MAX(orden) as ultimo FROM lecciones WHERE curso_id = ?";
        $stmt = mysqli_prepare($conexion, $sqlOrden);
        mysqli_stmt_bind_param($stmt, "i", $curso_id);
        mysqli_stmt_execute($stmt);
        $resOrden = mysqli_stmt_get_result($stmt);
        $filaOrden = mysqli_fetch_assoc($resOrden);
        $nuevoOrden = ($filaOrden["ultimo"] ?? 0) + 1;

        $sqlInsert = "INSERT INTO lecciones 
                      (curso_id, titulo, descripcion, video_url, orden)
                      VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conexion, $sqlInsert);
        mysqli_stmt_bind_param(
            $stmt,
            "isssi",
            $curso_id,
            $titulo,
            $descripcion,
            $video_url,
            $nuevoOrden
        );

        if (mysqli_stmt_execute($stmt)) {
            $mensaje_exito = "Lección creada correctamente.";
        } else {
            $mensaje_error = "Error al crear lección.";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Crear Lección</title>

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="../public/assets/css/perfil.css">
    <link rel="stylesheet" href="../public/assets/css/crearCurso.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
       <link rel="stylesheet" href="../public/assets/css/reescalado.css">

     <script src="../public/assets/js/forms.js" defer></script>
</head>

<body>

    <div class="main">
        <div class="container">

            <?php require_once "../includes/headerAdmin.php"; ?>

            <div class="card" style="max-width:500px; margin:auto;">

                <h3>
                    Añadir lección a:<br>
                    <?php echo htmlspecialchars($curso["titulo"]); ?>
                </h3>

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

                <form method="POST" class="auth-form" id="formCurso">

                    <!-- TITULO -->
                    <input type="text" name="titulo" id="titulo" class="auth-input" placeholder="Título de la lección">
                    <p class="error-msg" id="errorTitulo"></p>


                    <!-- DESCRIPCION -->
                    <textarea name="descripcion" id="descripcion" class="auth-input"
                        placeholder="Descripción de la lección"></textarea>
                    <p class="error-msg" id="errorDescripcion"></p>


                    <!-- VIDEO URL -->
                    <input type="text" name="video_url" id="video_url" class="auth-input"
                        placeholder="URL del vídeo (YouTube embed)">
                    <p class="error-msg" id="errorVideo"></p>

                    <button type="submit" class="btn btn-primary">
                        Crear lección
                    </button>

                </form>

            </div>

            <div style="text-align:center; margin-top:20px;">
                <a href="gestionarLecciones.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-soft">
                    ← Volver
                </a>
            </div>

        </div>
    </div>

</body>

</html>