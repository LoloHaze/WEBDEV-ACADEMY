<?php
require_once "../includes/bd.php";
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: gestionCursos.php");
    exit;
}

$leccion_id = intval($_GET["id"]);
$curso_id = intval($_GET["curso_id"] ?? 0);

// Eliminar lección
$sql = "DELETE FROM lecciones WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $leccion_id);
mysqli_stmt_execute($stmt);

// Volver al curso
header("Location: gestionarLecciones.php?curso_id=" . $curso_id);
exit;