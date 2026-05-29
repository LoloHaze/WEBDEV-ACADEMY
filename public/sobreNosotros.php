<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="icon" href="../public/assets/logowebdev.png" type="image/png">

    <title>Sobre Nosotros - WebDev Academy</title>

    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/animacion1.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
     <link rel="stylesheet" href="assets/css/reescalado.css">
     <script src="assets/js/responsive.js" defer></script>
    


</head>

<body>

    <?php require_once "../includes/header.php"; ?>

    <div class="container main">

        <!-- HERO -->
        <div class="card" style="text-align:center;">
            <h1>Impulsamos tu futuro en el desarrollo web 🚀</h1>
            <p style="color:#aaa; margin-top:10px;">
                Formación práctica, moderna y enfocada en proyectos reales.
            </p>
        </div>

        <!-- QUIÉNES SOMOS -->
        <div class="card" style="text-align:center;">
            <h2>¿Quiénes somos?</h2>
            <p style="color:#aaa; line-height:1.6;">
                WebDev Academy es una plataforma de formación online enfocada en el desarrollo web moderno.
                Nuestro objetivo es ayudarte a dominar tecnologías como PHP, JavaScript, HTML, CSS y bases de datos.
            </p>

            <p style="color:#aaa; line-height:1.6; margin-top:10px;">
                Creamos cursos estructurados, prácticos y orientados al mundo real.
                No enseñamos solo teoría: enseñamos a construir proyectos reales paso a paso.
            </p>
        </div>

        <!-- VALORES -->
        <div class="card" style="text-align:center;">

            <h2>Nuestros valores</h2>

            <div class="courses-grid" style="margin-top:20px;">

                <div class="course-card">
                    <div class="card-body">
                        <h3>📚 Formación práctica</h3>
                        <p class="card-desc">
                            Aprendizaje basado en proyectos reales y práctica constante.
                        </p>
                    </div>
                </div>

                <div class="course-card">
                    <div class="card-body">
                        <h3>🚀 Innovación constante</h3>
                        <p class="card-desc">
                            Contenidos actualizados con tecnologías actuales.
                        </p>
                    </div>
                </div>

                <div class="course-card">
                    <div class="card-body">
                        <h3>🤝 Comunidad</h3>
                        <p class="card-desc">
                            Fomentamos el crecimiento colaborativo.
                        </p>
                    </div>
                </div>

            </div>

        </div>

        <!-- CTA -->
        <div class="card" style="text-align:center;">
            <h2>¿Listo para empezar?</h2>
            <p style="color:#aaa; margin-bottom:15px;">
                Explora nuestros cursos y comienza tu camino como desarrollador.
            </p>
            <a href="index.php" class="btn btn-primary">
                Explorar cursos
            </a>
        </div>

    </div>

    <?php require_once "../includes/footer.php"; ?>

</body>

</html>