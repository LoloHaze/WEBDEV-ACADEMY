<?php
require_once "../includes/bd.php";
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   LOGIN
========================= */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

/* =========================
   VALIDAR ID
========================= */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$curso_id = intval($_GET["id"]);

/* =========================
   PREGUNTAS
========================= */
$preguntas = [
    1 => [
        "tipo" => "test",
        "pregunta" => "¿Cómo se declara una variable en JavaScript (ES6)?",
        "opciones" => ["var x = 5", "let x = 5", "int x = 5", "variable x = 5"],
        "correcta" => "let x = 5",
        "explicacion" => "En ES6 se usa 'let' o 'const'."
    ],
    2 => [
        "tipo" => "test",
        "pregunta" => "¿Qué método muestra un mensaje en consola?",
        "opciones" => ["print()", "echo()", "console.log()", "log.console()"],
        "correcta" => "console.log()",
        "explicacion" => "console.log() imprime mensajes."
    ],
    3 => [
        "tipo" => "codigo",
        "pregunta" => "Completa: for (let i = 0; i < 5; ____ )",
        "correcta" => "i++",
        "explicacion" => "Incrementa la variable."
    ],
    4 => [
        "tipo" => "texto",
        "pregunta" => "¿Qué palabra clave se usa para crear una función?",
        "correcta" => "function",
        "explicacion" => "Se usa 'function'."
    ],
    5 => [
        "tipo" => "test",
        "pregunta" => "¿Cómo se escribe un comentario de una línea?",
        "opciones" => ["// comentario", "# comentario", "<!-- comentario -->", "/* comentario */"],
        "correcta" => "// comentario",
        "explicacion" => "// es comentario de una línea."
    ],
];

/* =========================
   INICIALIZAR
========================= */
if (!isset($_SESSION["pregunta_actual"])) {
    $_SESSION["pregunta_actual"] = 1;
    $_SESSION["respuestas"] = [];
}

/* =========================
   PROCESAR
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $actual = $_SESSION["pregunta_actual"];

    // Guardar respuesta actual
    $_SESSION["respuestas"][$actual] = trim($_POST["respuesta"] ?? "");

    // Navegación
    if (isset($_POST["siguiente"])) {
        $_SESSION["pregunta_actual"]++;
    }

    if (isset($_POST["anterior"])) {
        $_SESSION["pregunta_actual"]--;
    }

    /* =========================
       FIN DEL EXAMEN
    ========================= */
    if ($_SESSION["pregunta_actual"] > count($preguntas)) {

        $nota = 0;

        echo "<h2>Resultados</h2>";

        foreach ($preguntas as $id => $p) {

            $user = strtolower(trim($_SESSION["respuestas"][$id] ?? ""));
            $correcta = strtolower(trim($p["correcta"]));

            if ($user === $correcta) {
                $nota++;
                echo "<p style='color:green;'><strong>Pregunta $id:</strong> Correcta ✅</p>";
            } else {
                echo "<p style='color:red;'><strong>Pregunta $id:</strong> Incorrecta ❌</p>";
                echo "<p>✔ Correcta: " . htmlspecialchars($p["correcta"]) . "</p>";
                echo "<p>💡 Explicación: " . htmlspecialchars($p["explicacion"]) . "</p><hr>";
            }
        }

        echo "<h2>Nota final: $nota / " . count($preguntas) . "</h2>";

        $aprobado = ($nota >= 3);

        /* =========================
           GUARDAR APROBADO EN SESIÓN
        ========================= */
        if ($aprobado) {
            $_SESSION["examen_aprobado_$curso_id"] = true;

            echo "<p style='color:green; font-weight:bold;'>🎉 ¡Examen aprobado!</p>";

            echo "<form action='certificado.php' method='GET'>
                    <input type='hidden' name='id' value='$curso_id'>
                    <button type='submit'>📜 Descargar certificado</button>
                  </form>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ Examen suspendido</p>";
        }

        // Repetir
        echo "<form action='examen.php' method='GET'>
                <input type='hidden' name='id' value='$curso_id'>
                <button type='submit'>🔄 Repetir examen</button>
              </form>";

        unset($_SESSION["pregunta_actual"]);
        unset($_SESSION["respuestas"]);
        exit;
    }

    header("Location: examen.php?id=" . $curso_id);
    exit;
}

/* =========================
   MOSTRAR
========================= */
$actual = $_SESSION["pregunta_actual"];
$p = $preguntas[$actual];
$total = count($preguntas);
$progreso = ($actual / $total) * 100;

// Recuperar respuesta previa
$respuesta_guardada = $_SESSION["respuestas"][$actual] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
<?php require_once "../includes/header.php"; ?>
<h2>Examen JS</h2>

<!-- PROGRESO -->
<div style="width:100%; background:#ddd; height:20px; border-radius:10px;">
    <div style="width:<?php echo $progreso; ?>%; background:green; height:100%; border-radius:10px;"></div>
</div>

<p>Pregunta <?php echo $actual; ?> / <?php echo $total; ?></p>

<form method="POST">

    <strong><?php echo htmlspecialchars($p["pregunta"]); ?></strong><br><br>

    <?php if ($p["tipo"] === "test"): ?>
        <?php foreach ($p["opciones"] as $op): ?>
            <label>
                <input type="radio"
                       name="respuesta"
                       value="<?php echo htmlspecialchars($op); ?>"
                       <?php echo ($respuesta_guardada == $op) ? "checked" : ""; ?>
                       required>
                <?php echo htmlspecialchars($op); ?>
            </label><br>
        <?php endforeach; ?>
    <?php else: ?>
        <input type="text"
               name="respuesta"
               value="<?php echo htmlspecialchars($respuesta_guardada); ?>"
               required>
    <?php endif; ?>

    <br><br>

    <?php if ($actual > 1): ?>
        <button type="submit" name="anterior">⬅ Anterior</button>
    <?php endif; ?>

    <button type="submit" name="siguiente">Siguiente ➡</button>

</form>
</body>
</html>
