<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$leccion_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

// Obtener datos de la lección
$sql = "SELECT l.*, c.titulo AS curso_titulo, c.id AS curso_id
        FROM lecciones l
        JOIN cursos c ON l.curso_id = c.id
        WHERE l.id = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $leccion_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$leccion = mysqli_fetch_assoc($resultado);

if (!$leccion) {
    header("Location: index.php");
    exit;
}
// Obtener todas las lecciones del curso
$sql_lista = "SELECT * FROM lecciones 
              WHERE curso_id = ?
              ORDER BY orden ASC";

$stmt_lista = mysqli_prepare($conexion, $sql_lista);
mysqli_stmt_bind_param($stmt_lista, "i", $leccion["curso_id"]);
mysqli_stmt_execute($stmt_lista);
$resultado_lista = mysqli_stmt_get_result($stmt_lista);
/* ==========================================
   COMPROBAR SI ESTÁ COMPLETADA
========================================== */

$sql_check = "SELECT id FROM progreso 
              WHERE usuario_id = ? AND leccion_id = ?";

$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $leccion_id);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

$completada = mysqli_stmt_num_rows($stmt_check) > 0;

/* ==========================================
   TOGGLE COMPLETADO / NO COMPLETADO
========================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($completada) {

        // Si ya estaba completada → eliminar
        $sql_delete = "DELETE FROM progreso 
                       WHERE usuario_id = ? AND leccion_id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "ii", $usuario_id, $leccion_id);
        mysqli_stmt_execute($stmt_delete);

    } else {

        // Si no estaba completada → insertar
        $sql_insert = "INSERT INTO progreso (usuario_id, leccion_id) 
                       VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conexion, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ii", $usuario_id, $leccion_id);
        mysqli_stmt_execute($stmt_insert);
    }

    header("Location: leccion.php?id=" . $leccion_id);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($leccion["titulo"]); ?></title>
</head>

<body style="margin:0; font-family:Arial;">

    <div style="display:flex; height:100vh;">

        <!-- SIDEBAR -->
        <div style="width:300px; background:#f5f5f5; overflow-y:auto; padding:15px; border-right:1px solid #ddd;">

            <h3><?php echo htmlspecialchars($leccion["curso_titulo"]); ?></h3>
            <hr>

            <?php while ($item = mysqli_fetch_assoc($resultado_lista)): ?>

                <?php
                // Comprobar progreso de cada lección
                $sql_prog = "SELECT id FROM progreso 
                         WHERE usuario_id = ? AND leccion_id = ?";
                $stmt_prog = mysqli_prepare($conexion, $sql_prog);
                mysqli_stmt_bind_param($stmt_prog, "ii", $usuario_id, $item["id"]);
                mysqli_stmt_execute($stmt_prog);
                mysqli_stmt_store_result($stmt_prog);

                $completada_item = mysqli_stmt_num_rows($stmt_prog) > 0;

                $activa = ($item["id"] == $leccion_id);
                ?>

                <div style="
                padding:10px;
                margin-bottom:5px;
                background:<?php echo $activa ? '#e3f2fd' : 'white'; ?>;
                border-radius:5px;
            ">

                    <a href="leccion.php?id=<?php echo $item["id"]; ?>" style="text-decoration:none; color:black;">

                        <?php if ($completada_item): ?>
                            ✔
                        <?php endif; ?>

                        <?php echo htmlspecialchars($item["orden"] . ". " . $item["titulo"]); ?>

                    </a>

                </div>

            <?php endwhile; ?>

        </div>


        <!-- CONTENIDO PRINCIPAL -->
        <div style="flex:1; padding:30px; overflow-y:auto;">

            <h2><?php echo htmlspecialchars($leccion["titulo"]); ?></h2>

            <p><?php echo htmlspecialchars($leccion["descripcion"]); ?></p>

            <iframe width="50%" height="50%" src="<?php echo htmlspecialchars($leccion['video_url']); ?>"
                frameborder="0" allowfullscreen>
            </iframe>

            <br><br>

            <?php if ($completada): ?>

                <form method="POST">
                    <button type="submit"
                        style="background:#dc3545; color:white; padding:10px 15px; border:none; border-radius:5px;">
                        ❌ Marcar como no completada
                    </button>
                </form>

            <?php else: ?>

                <form method="POST">
                    <button type="submit"
                        style="background:#28a745; color:white; padding:10px 15px; border:none; border-radius:5px;">
                        ✅ Marcar como completada
                    </button>
                </form>

            <?php endif; ?>

            <br><br>
            <a href="curso.php?id=<?php echo $leccion["curso_id"]; ?>">
                ← Volver al curso
            </a>

        </div>

    </div>

</body>

</html>