<?php

require_once "../includes/bd.php";
require_once "../includes/funciones.php";

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: index.php");
    exit;
}

$curso_id = intval($_GET["id"]);

$curso = obtenerCurso($conexion, $curso_id);

if (!$curso) {

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout</title>

    <link rel="icon" href="assets/logowebdev.png" type="image/png">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/examen.css">

    <link rel="stylesheet" href="assets/css/reescalado.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/checkout.css">


    <script src="assets/js/responsive.js" defer></script>
     <script src="assets/js/forms.js" defer></script>
</head>

<body>

    <?php require_once "../includes/header.php"; ?>

    <div class="main">

        <div class="container">

            <div class="card auth-card">

                <h2 class="section-title">
                   Realizar compra
                </h2>

                <br>

                <div class="checkout-course">

                    <h3>
                        <?php echo htmlspecialchars($curso["titulo"]); ?>
                    </h3>

                    <p>
                        <?php echo number_format($curso["precio"], 2); ?> €
                    </p>

                </div>

                <br>

                <form action="procesarPago.php" method="POST" class="auth-form" id="formCheckout">

                    <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">

                    <input type="text" name="titular" id="titular" class="auth-input"
                        placeholder="Titular de la tarjeta" required>

                    <p class="error-msg" id="errorTitular"></p>

                    <input type="text" name="tarjeta" id="tarjeta" class="auth-input" placeholder="1234 5678 9012 3456"
                        maxlength="19" required>

                    <p class="error-msg" id="errorTarjeta"></p>

                    <div class="checkout-row">

                        <input type="text" name="fecha" id="fecha" class="auth-input" placeholder="MM/YY" maxlength="5"
                            required>

                        <input type="text" name="cvv" id="cvv" class="auth-input" placeholder="CVV" maxlength="3"
                            required>

                    </div>

                    <div class="checkout-row-errors">

                        <p class="error-msg" id="errorFecha"></p>

                        <p class="error-msg" id="errorCvv"></p>

                    </div>

                    <button type="submit" class="btn btn-primary">

                        💳 Pagar ahora

                    </button>

                </form>

            </div>

        </div>

    </div>

    <?php require_once "../includes/footer.php"; ?>

</body>



</html>