<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <title>Contacto - WebDev Academy</title>

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">

    <link rel="stylesheet" href="assets/css/crearCurso.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/reescalado.css">


    <script src="assets/js/responsive.js" defer></script>



</head>

<body>

    <?php require_once "../includes/header.php"; ?>

    <div class="container main">

        <!-- HERO -->
        <div class="card" style="text-align:center;">
            <h1>Hablemos 👋</h1>
            <p style="color:#aaa; margin-top:10px;">
                Estamos aquí para ayudarte. Puedes contactarnos por cualquiera de los siguientes medios.
            </p>
        </div>

        <!-- BLOQUES DE CONTACTO -->
        <div class="card" style="text-align:center;">

            <div class="">
                <div class="card-body">
                    <h2>📧 Email</h2>
                    <p class="card-desc">
                        contacto@webdevacademy.com
                    </p>
                    <div class="card-actions">
                        <a href="https://mail.google.com/mail/?view=cm&to=contacto@webdevacademy.com" target="_blank"
                            class="btn btn-primary">
                            Enviar email
                        </a>
                    </div>
                </div>
            </div>

            <div class="course-car">
                <div class="card-body">
                    <h3>📱 Redes sociales</h3>
                    <p class="card-desc">
                        Síguenos para novedades y actualizaciones.
                    </p>
                    <div class="card-actions">
                        <a href="https://www.instagram.com/" class="btn btn-soft">Instagram</a>
                        <a href="https://www.linkedIn.com/" class="btn btn-soft">LinkedIn</a>
                    </div>
                </div>
            </div>

            <div class="course-car">
                <div class="card-body">
                    <h3>📍 Ubicación</h3>
                    <p class="card-desc">
                        España — Plataforma 100% online
                    </p>
                </div>
            </div>

        </div>

        <!-- CTA -->
        <div class="card" style="text-align:center; margin-top:30px;">
            <h2>¿Prefieres empezar ya?</h2>
            <p style="color:#aaa; margin-bottom:15px;">
                Explora nuestros cursos y comienza tu formación hoy mismo.
            </p>
            <a href="index.php" class="btn btn-primary">
                Ver cursos
            </a>
        </div>

    </div>

    <?php require_once "../includes/footer.php"; ?>

</body>

</html>