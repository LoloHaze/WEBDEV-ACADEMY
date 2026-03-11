<?php
require_once "../includes/bd.php";
session_start();

// 🔐 Protección admin
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../public/login.php");
    exit;
}

if ($_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

$mensaje = "";
$titulo = "";
$descripcion = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    if (strlen($titulo) < 5) {
        $mensaje = "El título debe tener al menos 5 caracteres.";
    }
    elseif (strlen($descripcion) < 10) {
        $mensaje = "La descripción debe tener al menos 10 caracteres.";
    }
    else {

        $sql = "INSERT INTO cursos (titulo, descripcion) VALUES (?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $titulo, $descripcion);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: crear_curso.php?success=1");
            exit;
        } else {
            $mensaje = "Error al crear el curso.";
        }
    }
}

// Mostrar mensaje tras redirección
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
    <p><?php echo $mensaje; ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="titulo" placeholder="Título del curso"
        value="<?php echo htmlspecialchars($titulo); ?>" required><br><br>

    <textarea name="descripcion" placeholder="Descripción"
        required><?php echo htmlspecialchars($descripcion); ?></textarea><br><br>

    <button type="submit">Crear curso</button>
</form>

<br>
<a href="dashboard.php">← Volver al panel</a>

</body>
</html>