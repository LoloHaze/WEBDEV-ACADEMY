<?php
require_once "../includes/bd.php";

$mensaje = "";
$nombre = "";
$email = "";
$password = "";

// SI SE ENVIA MOSTRAMOS REGISTRO CORRECTO
if (isset($_GET["success"])) {
    $mensaje = "Registro correcto";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // VALIDACIONES
    if (strlen($nombre) < 3) {
        $mensaje = "El nombre debe tener al menos 3 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } else {

        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexion, $sql_check);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $mensaje = "El email ya está registrado.";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO usuarios (nombre, email, password)
                           VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($conexion, $sql_insert);
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $password_hash);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: registro.php?success=1");
                exit;
            } else {
                $mensaje = "Error al registrar.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Registro - WebDev Academy</title>
</head>

<body>

    <h2>Registro</h2>

    <?php if ($mensaje != ""): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Contraseña" required><br><br>
        <button type="submit">Registrarse</button>
    </form>

</body>

</html>