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
$precio = "0.00";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "0.00");

    // Validaciones básicas
    if (strlen($titulo) < 5) {
        $mensaje = "El título debe tener al menos 5 caracteres.";
    }
    elseif (!empty($descripcion) && strlen($descripcion) < 10) {
        $mensaje = "La descripción debe tener al menos 10 caracteres.";
    }
    elseif (!is_numeric($precio) || $precio < 0) {
        $mensaje = "El precio debe ser un número válido.";
    }
    else {

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
</head>
<body>

<h2>Crear nuevo curso</h2>

<?php if ($mensaje != ""): ?>
    <p style="color:green;"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Título</label><br>
    <input type="text" name="titulo"
        value="<?php echo htmlspecialchars($titulo); ?>"
        required><br><br>

    <label>Descripción (opcional)</label><br>
    <textarea name="descripcion" rows="5" cols="50"><?php echo htmlspecialchars($descripcion); ?></textarea><br><br>

    <label>Precio (€)</label><br>
    <input type="number" step="0.01" name="precio"
        value="<?php echo htmlspecialchars($precio); ?>"><br><br>

    <label>Imagen portada (opcional)</label><br>
    <input type="file" name="imagen_portada" accept="image/*"><br><br>

    <button type="submit">Crear curso</button>

</form>

<br>
<a href="panel.php">← Volver al panel</a>

</body>
</html>