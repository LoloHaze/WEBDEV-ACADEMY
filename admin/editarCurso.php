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
$mensaje = "";

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
        $mensaje = "Título demasiado corto.";
    } elseif (!is_numeric($precio) || $precio < 0) {
        $mensaje = "Precio inválido.";
    } else {

        $imagenNombre = $curso["imagen_portada"]; // mantener actual

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
                        $mensaje = "Imagen demasiado grande (máx 3MB).";
                    }
                } else {
                    $mensaje = "Formato no permitido.";
                }
            }
        }

        if ($mensaje == "") {

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
                $mensaje = "Curso actualizado correctamente.";
                $curso["imagen_portada"] = $imagenNombre;
            } else {
                $mensaje = "Error al actualizar.";
            }
        }
    }
} 
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Curso</title>
</head>

<body>

    <h2>Editar Curso</h2>

    <?php
    $imagenActual = (!empty($curso["imagen_portada"]) &&
        file_exists("../public/uploads/cursos/" . $curso["imagen_portada"]))
        ? "../public/uploads/cursos/" . $curso["imagen_portada"]
        : "https://via.placeholder.com/400x200?text=Sin+imagen";
    ?>

    <img src="<?php echo $imagenActual; ?>" style="width:300px; height:150px; object-fit:cover; border-radius:8px;">
    <br><br>

    <?php if ($mensaje): ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="titulo" value="<?php echo htmlspecialchars($curso["titulo"]); ?>" required><br><br>

        <textarea name="descripcion"><?php
        echo htmlspecialchars($curso["descripcion"]);
        ?></textarea><br><br>

        <input type="number" step="0.01" name="precio"
            value="<?php echo htmlspecialchars($curso["precio"]); ?>"><br><br>

        <label>Nueva imagen
        </label><br>
        <input type="file" name="imagen_portada" accept="image/*"><br><br>

        <button type="submit">Guardar cambios</button>

    </form>

    <br>
    <a href="gestionCursos.php">← Volver</a>

</body>

</html>