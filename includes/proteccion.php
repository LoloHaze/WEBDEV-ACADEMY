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