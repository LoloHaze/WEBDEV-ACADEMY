<?php

require_once "../includes/bd.php";

session_start();

/* ADMIN */
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

/* VALIDAR */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$pregunta_id = intval($_GET["id"]);

/* PREGUNTA */
$sqlPregunta = "SELECT * FROM preguntas
                WHERE id = ?";

$stmtPregunta = mysqli_prepare(
    $conexion,
    $sqlPregunta
);

mysqli_stmt_bind_param(
    $stmtPregunta,
    "i",
    $pregunta_id
);

mysqli_stmt_execute($stmtPregunta);

$resultPregunta = mysqli_stmt_get_result(
    $stmtPregunta
);

$pregunta = mysqli_fetch_assoc(
    $resultPregunta
);

if (!$pregunta) {
    exit("Pregunta no encontrada");
}

/* RESPUESTAS */
$sqlRespuestas = "SELECT * FROM respuestas
                  WHERE pregunta_id = ?
                  ORDER BY id ASC";

$stmtRespuestas = mysqli_prepare(
    $conexion,
    $sqlRespuestas
);

mysqli_stmt_bind_param(
    $stmtRespuestas,
    "i",
    $pregunta_id
);

mysqli_stmt_execute($stmtRespuestas);

$respuestas = mysqli_stmt_get_result(
    $stmtRespuestas
);

/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $textoPregunta = trim($_POST["pregunta"] ?? "");

    $r1 = trim($_POST["respuesta1"] ?? "");
    $r2 = trim($_POST["respuesta2"] ?? "");
    $r3 = trim($_POST["respuesta3"] ?? "");
    $r4 = trim($_POST["respuesta4"] ?? "");

    $correcta = intval($_POST["correcta"] ?? 1);

    /* ACTUALIZAR PREGUNTA */
    $sqlUpdatePregunta = "UPDATE preguntas
                          SET pregunta = ?
                          WHERE id = ?";

    $stmtUpdatePregunta = mysqli_prepare(
        $conexion,
        $sqlUpdatePregunta
    );

    mysqli_stmt_bind_param(
        $stmtUpdatePregunta,
        "si",
        $textoPregunta,
        $pregunta_id
    );

    mysqli_stmt_execute(
        $stmtUpdatePregunta
    );

    /* RESPUESTAS */
    $nuevas = [$r1, $r2, $r3, $r4];

    mysqli_data_seek($respuestas, 0);

    $index = 0;

    while ($respuesta = mysqli_fetch_assoc($respuestas)) {

        $texto = $nuevas[$index] ?? "";

        $es_correcta = (
            $correcta == ($index + 1)
        ) ? 1 : 0;

        $sqlUpdateRespuesta = "UPDATE respuestas
                               SET respuesta = ?,
                                   correcta = ?
                               WHERE id = ?";

        $stmtUpdateRespuesta = mysqli_prepare(
            $conexion,
            $sqlUpdateRespuesta
        );

        mysqli_stmt_bind_param(
            $stmtUpdateRespuesta,
            "sii",
            $texto,
            $es_correcta,
            $respuesta["id"]
        );

        mysqli_stmt_execute(
            $stmtUpdateRespuesta
        );

        $index++;
    }

    header("Location: adminExamen.php?curso_id=" . $pregunta["examen_id"]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">

    <title>Editar pregunta</title>



    <!-- <link rel="stylesheet" href="../public/assets/css/nav.css"> -->
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
                Editar pregunta
            </h2>

            <?php

            mysqli_data_seek($respuestas, 0);

            $datos = [];

            while ($r = mysqli_fetch_assoc($respuestas)) {
                $datos[] = $r;
            }

            ?>

            <div class="card">

                <form method="POST" class="auth-form" id="formExamen">

                    <!-- PREGUNTA -->
                    <input type="text" name="pregunta" id="pregunta" class="auth-input"
                        value="<?php echo htmlspecialchars($pregunta["pregunta"]); ?>" required>

                    <p class="error-msg" id="errorPregunta"></p>

                    <!-- RESPUESTAS -->
                    <?php foreach ($datos as $index => $r): ?>

                        <input type="text" name="respuesta<?php echo $index + 1; ?>" id="respuesta<?php echo $index + 1; ?>"
                            class="auth-input" value="<?php echo htmlspecialchars($r["respuesta"]); ?>" required>

                        <p class="error-msg" id="errorRespuesta<?php echo $index + 1; ?>"></p>

                    <?php endforeach; ?>

                    <!-- CORRECTA -->
                    <label>
                        Respuesta correcta
                    </label>

                    <select name="correcta" id="correcta" class="auth-input">

                        <?php foreach ($datos as $index => $r): ?>

                            <option value="<?php echo $index + 1; ?>" <?php echo $r["correcta"] == 1 ? "selected" : ""; ?>>

                                Respuesta <?php echo $index + 1; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <button type="submit" class="btn btn-primary">

                        Guardar cambios

                    </button>

                </form>

            </div>

        </div>
        <div class="admin-footer">

            <a href="adminExamen.php?curso_id=<?php echo $pregunta["examen_id"]; ?>" class="btn btn-soft">

                ← Volver

            </a>

        </div>

    </div>


</body>

</html>