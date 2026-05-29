<?php

require_once "../includes/bd.php";
require_once "../includes/funciones.php";

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

$curso_id = intval($_POST["curso_id"] ?? 0);

/* VALIDACIÓN SIMPLE */
$titular = trim($_POST["titular"] ?? "");
$tarjeta = preg_replace('/\s+/', '', $_POST["tarjeta"] ?? "");
$fecha = trim($_POST["fecha"] ?? "");
$cvv = trim($_POST["cvv"] ?? "");

if (
    strlen($titular) < 3 ||
    strlen($tarjeta) < 16 ||
    strlen($cvv) < 3
) {

    header("Location: checkout.php?id=" . $curso_id);
    exit;
}

/* INSCRIPCIÓN DIRECTA */
$sql = "INSERT INTO inscripciones
        (usuario_id, curso_id, estado)
        VALUES (?, ?, 'aprobado')
        ON DUPLICATE KEY UPDATE
        estado = 'aprobado'";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $usuario_id,
    $curso_id
);

mysqli_stmt_execute($stmt);

header("Location: pagoCompletado.php?id=" . $curso_id);

exit;