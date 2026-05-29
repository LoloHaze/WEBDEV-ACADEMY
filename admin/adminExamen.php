<?php

require_once "../includes/bd.php";

session_start();

/* PROTEGER ADMIN */
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

/* VALIDAR CURSO */
if (!isset($_GET["curso_id"]) || !is_numeric($_GET["curso_id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$curso_id = intval($_GET["curso_id"]);

/* BUSCAR EXAMEN */
$sql = "SELECT * FROM examenes WHERE curso_id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$examen = mysqli_fetch_assoc($resultado);

/* CREAR EXAMEN SI NO EXISTE */
if (!$examen) {

    $titulo = "Examen final";

    $sqlCrear = "INSERT INTO examenes (curso_id, titulo)
                 VALUES (?, ?)";

    $stmtCrear = mysqli_prepare($conexion, $sqlCrear);

    mysqli_stmt_bind_param(
        $stmtCrear,
        "is",
        $curso_id,
        $titulo
    );

    mysqli_stmt_execute($stmtCrear);

    $examen_id = mysqli_insert_id($conexion);

} else {

    $examen_id = $examen["id"];
}

/* CREAR PREGUNTA */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pregunta = trim($_POST["pregunta"] ?? "");

    $tipo = trim($_POST["tipo"] ?? "test");

    $respuesta1 = trim($_POST["respuesta1"] ?? "");
    $respuesta2 = trim($_POST["respuesta2"] ?? "");
    $respuesta3 = trim($_POST["respuesta3"] ?? "");
    $respuesta4 = trim($_POST["respuesta4"] ?? "");

    $correcta = intval($_POST["correcta"] ?? 1);

    if ($pregunta !== "") {

        /* INSERTAR PREGUNTA */
        $sqlPregunta = "INSERT INTO preguntas
                        (examen_id, pregunta, tipo)
                        VALUES (?, ?, ?)";

        $stmtPregunta = mysqli_prepare($conexion, $sqlPregunta);

        mysqli_stmt_bind_param(
            $stmtPregunta,
            "iss",
            $examen_id,
            $pregunta,
            $tipo
        );

        mysqli_stmt_execute($stmtPregunta);

        $pregunta_id = mysqli_insert_id($conexion);

        /* RESPUESTAS */
        $respuestas = [
            $respuesta1,
            $respuesta2,
            $respuesta3,
            $respuesta4
        ];

        foreach ($respuestas as $index => $respuesta) {

            if ($respuesta === "") {
                continue;
            }

            $es_correcta = ($correcta == ($index + 1)) ? 1 : 0;

            $sqlRespuesta = "INSERT INTO respuestas
                             (pregunta_id, respuesta, correcta)
                             VALUES (?, ?, ?)";

            $stmtRespuesta = mysqli_prepare($conexion, $sqlRespuesta);

            mysqli_stmt_bind_param(
                $stmtRespuesta,
                "isi",
                $pregunta_id,
                $respuesta,
                $es_correcta
            );

            mysqli_stmt_execute($stmtRespuesta);
        }

        header("Location: adminExamen.php?curso_id=" . $curso_id);
        exit;
    }
}

/* ELIMINAR */
if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {

    $idEliminar = intval($_GET["eliminar"]);

    $sqlDelete = "DELETE FROM preguntas WHERE id = ?";

    $stmtDelete = mysqli_prepare($conexion, $sqlDelete);

    mysqli_stmt_bind_param($stmtDelete, "i", $idEliminar);

    mysqli_stmt_execute($stmtDelete);

    header("Location: adminExamen.php?curso_id=" . $curso_id);
    exit;
}

/* LISTAR PREGUNTAS */
$sqlPreguntas = "SELECT * FROM preguntas
                 WHERE examen_id = ?
                 ORDER BY id ASC";

$stmtPreguntas = mysqli_prepare($conexion, $sqlPreguntas);

mysqli_stmt_bind_param(
    $stmtPreguntas,
    "i",
    $examen_id
);

mysqli_stmt_execute($stmtPreguntas);

$preguntas = mysqli_stmt_get_result($stmtPreguntas);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">

    <title>Gestionar examen</title>

    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/components.css">

    <link rel="stylesheet" href="../public/assets/css/reescalado2.css">
    <link rel="stylesheet" href="../public/assets/css/reescalado.css">

    <link rel="stylesheet" href="../public/assets/css/responsive.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">

    <link rel="stylesheet" href="../public/assets/css/login.css">

    <link rel="stylesheet" href="../public/assets/css/responsiveAdmin.css">
    <link rel="stylesheet" href="../public/assets/css/examen.css">

    <script src="../public/assets/js/forms.js" defer></script>
    <script src="../public/assets/js/responsiveAdmin.js" defer></script>


</head>

<body>

    <div class="main">

        <div class="container">

            <?php require_once "../includes/headerAdmin.php"; ?>

            <h2 class="section-title">
                Gestionar examen
            </h2>

            <!-- FORM -->
            <div class="card">

                <form method="POST" class="auth-form" id="formExamen">

                    <input type="text" name="pregunta" id="pregunta" class="auth-input" placeholder="Pregunta" required>

                    <p class="error-msg" id="errorPregunta"></p>

                    <input type="text" name="respuesta1" id="respuesta1" class="auth-input" placeholder="Respuesta 1"
                        required>

                    <p class="error-msg" id="errorRespuesta1"></p>

                    <input type="text" name="respuesta2" id="respuesta2" class="auth-input" placeholder="Respuesta 2"
                        required>

                    <p class="error-msg" id="errorRespuesta2"></p>

                    <input type="text" name="respuesta3" id="respuesta3" class="auth-input" placeholder="Respuesta 3">

                    <input type="text" name="respuesta4" id="respuesta4" class="auth-input" placeholder="Respuesta 4">

                    <label>
                        Respuesta correcta
                    </label>

                    <select name="correcta" id="correcta" class="auth-input">

                        <option value="1">Respuesta 1</option>
                        <option value="2">Respuesta 2</option>
                        <option value="3">Respuesta 3</option>
                        <option value="4">Respuesta 4</option>

                    </select>

                    <button type="submit" class="btn btn-primary">

                        ➕ Añadir pregunta

                    </button>

                </form>

            </div>

            <br>

            <!-- LISTADO -->
            <div class="admin-list">

                <?php while ($p = mysqli_fetch_assoc($preguntas)): ?>

                    <div class="card admin-item">

                        <div>

                            <strong>
                                <?php echo htmlspecialchars($p["pregunta"]); ?>
                            </strong>

                            <br><br>

                            <?php

                            $sqlResp = "SELECT * FROM respuestas
                                    WHERE pregunta_id = ?";

                            $stmtResp = mysqli_prepare(
                                $conexion,
                                $sqlResp
                            );

                            mysqli_stmt_bind_param(
                                $stmtResp,
                                "i",
                                $p["id"]
                            );

                            mysqli_stmt_execute($stmtResp);

                            $resps = mysqli_stmt_get_result($stmtResp);

                            ?>

                            <?php while ($r = mysqli_fetch_assoc($resps)): ?>

                                <p>

                                    <?php if ($r["correcta"] == 1): ?>
                                        ✅
                                    <?php else: ?>
                                        ❌
                                    <?php endif; ?>

                                    <?php echo htmlspecialchars($r["respuesta"]); ?>

                                </p>

                            <?php endwhile; ?>

                        </div>

                        <div class="admin-actions">


                            <a href="editarPregunta.php?id=<?php echo $p["id"]; ?>" class="btn btn-primary">

                                Editar

                            </a>

                            <a href="?curso_id=<?php echo $curso_id; ?>&eliminar=<?php echo $p["id"]; ?>"
                                class="btn btn-soft" onclick="return confirm('¿Eliminar pregunta?')">

                                Eliminar

                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

            <div class="admin-footer">

                <a href="gestionCursos.php" class="btn btn-soft">

                    ← Volver

                </a>

            </div>

        </div>

    </div>

</body>

</html>