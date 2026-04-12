<?php
// EXAMEN
// -------------------------------------
// - GESTIONA PREGUNTAS
// - CONTROLA PROGRESO EN SESIÓN
// - CALCULA RESULTADO
// - GUARDA NOTA
// -------------------------------------

require_once "../includes/bd.php";
require_once "../includes/proteccion.php";
require_once "../includes/funciones.php";

session_start();

// PROTEGER
protegerPagina();

/* VALIDAR ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$curso_id = intval($_GET["id"]);
$usuario_id = $_SESSION["usuario_id"];

/* PREGUNTAS */
$preguntas = [
    1 => [
        "tipo" => "test",
        "pregunta" => "¿Cómo se declara una variable en JavaScript (ES6)?",
        "opciones" => ["var x = 5", "let x = 5", "int x = 5", "variable x = 5"],
        "correcta" => "let x = 5"
    ],
    2 => [
        "tipo" => "test",
        "pregunta" => "¿Qué método muestra un mensaje en consola?",
        "opciones" => ["print()", "echo()", "console.log()", "log.console()"],
        "correcta" => "console.log()"
    ],
    3 => [
        "tipo" => "codigo",
        "pregunta" => "Completa: for (let i = 0; i < 5; ____ )",
        "correcta" => "i++"
    ],
    4 => [
        "tipo" => "texto",
        "pregunta" => "¿Qué palabra clave se usa para crear una función?",
        "correcta" => "function"
    ],
    5 => [
        "tipo" => "test",
        "pregunta" => "¿Cómo se escribe un comentario de una línea?",
        "opciones" => ["// comentario", "# comentario", "<!-- comentario -->", "/* comentario */"],
        "correcta" => "// comentario"
    ],
];

/* INICIALIZAR */
if (!isset($_SESSION["pregunta_actual"]) || !is_numeric($_SESSION["pregunta_actual"])) {
    $_SESSION["pregunta_actual"] = 1;
    $_SESSION["respuestas"] = [];
}

/* ASEGURAR RANGO */
if ($_SESSION["pregunta_actual"] < 1 || $_SESSION["pregunta_actual"] > count($preguntas)) {
    $_SESSION["pregunta_actual"] = 1;
    $_SESSION["respuestas"] = [];
}

/* PROCESAR */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $actual = $_SESSION["pregunta_actual"];
    $respuesta = trim($_POST["respuesta"] ?? "");

    /* VALIDAR RESPUESTA SOLO SI ES TEST */
    if (isset($preguntas[$actual]) && $preguntas[$actual]["tipo"] === "test") {
        if (!in_array($respuesta, $preguntas[$actual]["opciones"])) {
            $respuesta = "";
        }
    }

    $_SESSION["respuestas"][$actual] = $respuesta;

    /* SIGUIENTE */
    if (isset($_POST["siguiente"])) {

        if ($respuesta === "") {
            header("Location: examen.php?id=" . $curso_id);
            exit;
        }

        $_SESSION["pregunta_actual"]++;
    }

    /* ANTERIOR */
    if (isset($_POST["anterior"])) {
        if ($_SESSION["pregunta_actual"] > 1) {
            $_SESSION["pregunta_actual"]--;
        }
    }

    /* FINAL EXAMEN */
    if ($_SESSION["pregunta_actual"] > count($preguntas)) {

        $nota = 0;
        $total = count($preguntas);

        foreach ($preguntas as $id => $p) {
            $user = strtolower(trim($_SESSION["respuestas"][$id] ?? ""));
            $correcta = strtolower(trim($p["correcta"]));

            if ($user == $correcta) {
                $nota++;
            }
        }

        $aprobado = ($nota >= 3) ? 1 : 0;

        /* BORRAR INTENTO */
        borrarResultadoExamen($conexion, $usuario_id, $curso_id);

        /* GUARDAR */
        guardarResultadoExamen($conexion, $usuario_id, $curso_id, $nota, $aprobado);
        mysqli_stmt_bind_param(
            $stmt_guardar,
            "iiii",
            $_SESSION["usuario_id"],
            $curso_id,
            $nota,
            $aprobado
        );
        mysqli_stmt_execute($stmt_guardar);

        /* LIMPIAR */
        unset($_SESSION["pregunta_actual"], $_SESSION["respuestas"]);
        ?>

        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Resultado examen</title>
            <link rel="stylesheet" href="assets/css/components.css">
            <link rel="stylesheet" href="assets/css/index.css">
            <link rel="stylesheet" href="assets/css/examen.css">
        </head>

        <body>
            <?php require_once "../includes/header.php"; ?>
            <div class="main">
                <div class="container">

                    <div class="card result-card">

                        <h2>Resultado</h2>

                        <div class="result-score">
                            <?php echo $nota; ?> / <?php echo $total; ?>
                        </div>

                        <?php if ($aprobado): ?>

                            <p class="result-success">
                                🎉 ¡Examen aprobado!
                            </p>

                            <a href="certificado.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">
                                🎓 Descargar certificado
                            </a>

                        <?php else: ?>

                            <p class="result-fail">
                                ❌ Examen suspendido
                            </p>

                            <a href="examen.php?id=<?php echo $curso_id; ?>" class="btn btn-primary">
                                🔄 Repetir examen
                            </a>

                        <?php endif; ?>

                        <div class="result-actions">
                            <a href="misCursos.php" class="btn btn-soft">
                                ← Volver a mis cursos
                            </a>
                        </div>

                    </div>

                </div>
            </div>
            <?php require_once "../includes/footer.php"; ?>
        </body>

        </html>
        <?php
        exit;
    }

    header("Location: examen.php?id=" . $curso_id);
    exit;
}

/* MOSTRAR */
$actual = $_SESSION["pregunta_actual"];

if (!isset($preguntas[$actual])) {
    $_SESSION["pregunta_actual"] = 1;
    $_SESSION["respuestas"] = [];
    header("Location: examen.php?id=" . $curso_id);
    exit;
}

$p = $preguntas[$actual];
$total = count($preguntas);
$progreso = ($actual / $total) * 100;
$respuesta_guardada = $_SESSION["respuestas"][$actual] ?? "";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Examen</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/examen.css">

</head>

<body>

    <?php require_once "../includes/header.php"; ?>
    <div class="main">
        <div class="container">

            <h2>Examen</h2>

            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $progreso; ?>%"></div>
            </div>

            <p class="progress-text">
                Pregunta <?php echo $actual; ?> / <?php echo $total; ?>
                (<?php echo round($progreso); ?>%)
            </p>

            <div class="exam-card">

                <form method="POST" class="auth-form">

                    <h3><?php echo htmlspecialchars((string) $p["pregunta"], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <br>

                    <?php if ($p["tipo"] === "test" && !empty($p["opciones"])): ?>

                        <?php foreach ($p["opciones"] as $op): ?>
                            <label class="exam-option">
                                <input type="radio" name="respuesta"
                                    value="<?= htmlspecialchars((string) $op, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= $respuesta_guardada == $op ? 'checked' : '' ?>>
                                <?php echo htmlspecialchars((string) $op, ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="exam-input-wrapper">
                            <input type="text" name="respuesta" class="exam-input" placeholder="Escribe tu respuesta..."
                                value="<?php echo htmlspecialchars((string) $respuesta_guardada, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                    <?php endif; ?>

                    <div class="exam-actions">

                        <?php if ($actual > 1): ?>
                            <button type="submit" name="anterior" class="btn btn-soft">← Anterior</button>
                        <?php endif; ?>

                        <button type="submit" name="siguiente" class="btn btn-primary">Siguiente →</button>

                    </div>

                </form>

            </div>

        </div>
    </div>
    <?php require_once "../includes/footer.php"; ?>

    <script>
        document.querySelectorAll('.exam-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.exam-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
            });
        });
    </script>


</body>

</html>