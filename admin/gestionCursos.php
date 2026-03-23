<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}
// ACTIVAR
if (isset($_GET["activar"])) {
    $id = intval($_GET["activar"]);
    $sql = "UPDATE cursos SET activo = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionCursos.php");
    exit;
}

// DESACTIVAR
if (isset($_GET["desactivar"])) {
    $id = intval($_GET["desactivar"]);
    $sql = "UPDATE cursos SET activo = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: gestionCursos.php");
    exit;
}

$sql = "SELECT * FROM cursos ORDER BY id DESC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Cursos</title>
</head>

<body>

    <h2>Gestión de Cursos</h2>
    <a href="crearCurso.php"
        style="display:inline-block;margin-bottom:20px;background:#28a745;color:white;padding:8px 12px;border-radius:5px;text-decoration:none;">
        ➕ Crear nuevo curso
    </a>

    <?php while ($curso = mysqli_fetch_assoc($resultado)): ?>

        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <strong><?php echo htmlspecialchars($curso["titulo"]); ?></strong>

            <?php if ($curso["activo"] == 1): ?>
                <span style="color:green;">(Activo)</span>
            <?php else: ?>
                <span style="color:red;">(Desactivado)</span>
            <?php endif; ?>

            <br>
            💰 <?php echo $curso["precio"] > 0 ? $curso["precio"] . " €" : "Gratis"; ?>
            <br><br>
            <a href="gestionarLecciones.php?curso_id=<?php echo $curso["id"]; ?>">
                📖 Gestionar lecciones
            </a> |

            <a href="editarCurso.php?id=<?php echo $curso["id"]; ?>">✏ Editar</a> |

            <?php if ($curso["activo"] == 1): ?>
                <a href="?desactivar=<?php echo $curso["id"]; ?>">⛔ Desactivar</a> |
            <?php else: ?>
                <a href="?activar=<?php echo $curso["id"]; ?>">✅ Activar</a> |
            <?php endif; ?>
            <a href="valoracionesCurso.php?id=<?php echo $curso["id"]; ?>">
                💬 Ver valoraciones
                
            </a>  |
            <a href="eliminarCurso.php?id=<?php echo $curso["id"]; ?>"
                onclick="return confirm('¿Seguro que quieres eliminar este curso?');">
                🗑 Eliminar
            </a>
        </div>

    <?php endwhile; ?>

    <br>
    <a href="panel.php">← Volver</a>

</body>

</html>