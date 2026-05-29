<?php

require_once "../includes/bd.php";
require_once "../includes/funciones.php";

session_start();

if (!isset($_GET["id"])) {
    header("Location:index.php");
    exit;
}

$curso_id = intval($_GET["id"]);

$curso = obtenerCurso($conexion, $curso_id);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
           <link rel="icon" href="assets/logowebdev.png" type="image/png">

    <title>Pago completado</title>

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">

</head>

<body>

<div class="main">

    <div class="container">

        <div class="card auth-card">

            <div style="text-align:center;">

                <div style="font-size:70px;">
                    ✅
                </div>

                <h2 class="section-title">
                    Pago completado
                </h2>

                <p>
                    Ya tienes acceso al curso:
                </p>

                <br>

                <strong>
                    <?php echo htmlspecialchars($curso["titulo"]); ?>
                </strong>

                <br><br>

                <a href="curso.php?id=<?php echo $curso_id; ?>"
                   class="btn btn-primary">

                    Ir al curso

                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>