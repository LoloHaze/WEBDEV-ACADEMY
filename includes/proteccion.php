<?php


// PROTEGER PÁGINA
function protegerPagina()
{
if (!isset($_SESSION["usuario_id"])) {
header("Location: login.php");
exit;
}
}


function protegerAdmin()
{
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
header("Location: index.php");
exit;
}
}

// VALIDAR ID GET
function validarId($nombre = "id", $redireccion = "index.php")
{
    if (!isset($_GET[$nombre]) || !is_numeric($_GET[$nombre])) {
        header("Location: $redireccion");
        exit;
    }

    return intval($_GET[$nombre]);
}