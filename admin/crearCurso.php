<?php
require_once "../includes/bd.php";
session_start();

// 🔐 Protección admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

$mensaje = "";
$titulo = "";
$descripcion = "";
$precio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "0.00");

    // Validaciones básicas
    if (strlen($titulo) < 5) {
        $mensaje = "El título debe tener al menos 5 caracteres.";
    } elseif (!empty($descripcion) && strlen($descripcion) < 10) {
        $mensaje = "La descripción debe tener al menos 10 caracteres.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $mensaje = "El precio debe ser un número válido.";
    } else {

        $imagenNombre = null;

        // ==========================
        // SUBIR IMAGEN PORTADA
        // ==========================
        if (!empty($_FILES["imagen_portada"]["name"])) {

            $archivo = $_FILES["imagen_portada"];

            if ($archivo["error"] === 0) {

                $tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

                if (in_array($archivo["type"], $tiposPermitidos)) {

                    if ($archivo["size"] <= 3 * 1024 * 1024) { // 3MB

                        $extension = pathinfo($archivo["name"], PATHINFO_EXTENSION);
                        $imagenNombre = "curso_" . time() . "." . $extension;
                        $rutaDestino = "../public/uploads/cursos/" . $imagenNombre;

                        move_uploaded_file($archivo["tmp_name"], $rutaDestino);

                    } else {
                        $mensaje = "La imagen supera el tamaño máximo (3MB).";
                    }
                } else {
                    $mensaje = "Formato de imagen no permitido.";
                }
            }
        }

        if ($mensaje == "") {

            $sql = "INSERT INTO cursos (titulo, descripcion, imagen_portada, precio)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sssd", $titulo, $descripcion, $imagenNombre, $precio);

            if (mysqli_stmt_execute($stmt)) {

                /* =========================
                   CREAR EXAMEN AUTOMÁTICO
                ========================= */

                // Obtener ID del curso recién creado
                $curso_id = mysqli_insert_id($conexion);

                // Crear examen asociado
                $sql_examen = "
    INSERT INTO examenes (curso_id, titulo)
    VALUES (?, 'Examen del curso')
    ";

                $stmt_examen = mysqli_prepare($conexion, $sql_examen);
                mysqli_stmt_bind_param($stmt_examen, "i", $curso_id);
                mysqli_stmt_execute($stmt_examen);

                // 🔥 AHORA sí rediriges
                header("Location: crearCurso.php?success=1");
                exit;

            } else {
                $mensaje = "Error al crear el curso.";
            }
        }
    }
}

if (isset($_GET["success"])) {
    $mensaje = "Curso creado correctamente 🎉";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Crear Curso</title>

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <link rel="stylesheet" href="../public/assets/css/crearCurso.css">
</head>

<body>



    <div class="main">
        <div class="container">
            <?php require_once "../includes/headerAdmin.php"; ?>
            <div class="card" style="max-width:500px; margin:auto;">

                <h2 class="section-title" style="text-align:center;">
                    Crear nuevo curso
                </h2>

                <!-- MENSAJE -->
                <?php if ($mensaje != ""): ?>
                    <div class="auth-error">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="POST" enctype="multipart/form-data" class="auth-form">

                    <!-- TITULO -->
                    <input type="text" name="titulo" class="auth-input" placeholder="Título del curso"
                        value="<?php echo htmlspecialchars($titulo); ?>" required>

                    <!-- DESCRIPCION -->
                    <textarea name="descripcion" class="auth-input" placeholder="Descripción del curso"
                        rows="4"><?php echo htmlspecialchars($descripcion); ?></textarea>

                    <!-- PRECIO -->
                    <input type="number" step="0.01" name="precio" class="auth-input" placeholder="Precio (€)"
                        value="<?php echo htmlspecialchars($precio); ?>">

                    <!-- IMAGEN -->
                    <div class="">
                        <input type="file" name="imagen_portada" accept="image/*">
                    </div>

                    <!-- BOTON -->
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        Crear curso
                    </button>

                </form>

               

            </div>
             <!-- VOLVER -->
                <div style="text-align:center; margin-top:20px;">
                    <a href="panel.php" class="btn btn-soft">← Volver</a>
                </div>

        </div>
    </div>

    

</body>

</html>