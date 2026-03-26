<?php
session_start();

require_once '../includes/bd.php';
require_once '../includes/fpdf/fpdf.php';

if (!isset($_SESSION["usuario_id"])) {
    die("No autorizado");
}

$usuario_id = $_SESSION["usuario_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Curso inválido");
}

$curso_id = intval($_GET["id"]);

// TOTAL LECCIONES
$sql_total = "SELECT COUNT(*) as total FROM lecciones WHERE curso_id = ?";
$stmt = mysqli_prepare($conexion, $sql_total);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$res_total = mysqli_stmt_get_result($stmt);
$total = mysqli_fetch_assoc($res_total)["total"];

// COMPLETADAS
$sql_comp = "SELECT COUNT(*) as completadas
             FROM progreso p
             JOIN lecciones l ON p.leccion_id = l.id
             WHERE p.usuario_id = ? AND l.curso_id = ?";
$stmt = mysqli_prepare($conexion, $sql_comp);
mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
mysqli_stmt_execute($stmt);
$res_comp = mysqli_stmt_get_result($stmt);
$completadas = mysqli_fetch_assoc($res_comp)["completadas"];

$porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;

// ❌ SI NO HA TERMINADO → FUERA
if ($porcentaje < 100) {
    die("No has completado el curso");
}

// USUARIO
$sql_user = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_user);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$res_user = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($res_user);

// CURSO
$sql_curso = "SELECT titulo FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_curso);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$res_curso = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($res_curso);

// PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->Image('../public/assets/diplomaPlantilla.png', 0, 0, 297, 210);
// TÍTULO CENTRADO
$pdf->SetY(40);
$pdf->SetFont('Arial', 'B', 34);
$pdf->SetTextColor(20, 60, 100);
$pdf->Cell(0, 10, 'CERTIFICADO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(40, 100, 180);
$pdf->Cell(0, 10, 'DE FINALIZACION', 0, 1, 'C');

// TEXTO
$pdf->SetY(70);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Este certificado acredita que', 0, 1, 'C');

// NOMBRE (más elegante)
$pdf->SetFont('Arial', 'BI', 26); // B + Italic
$pdf->Cell(0, 15, $usuario["nombre"], 0, 1, 'C');

// LÍNEA CENTRADA
$pdf->SetX(98); // posición centrada
$pdf->Cell(100, 1, '', 'B', 1);

// TEXTO CURSO
$pdf->SetY(110);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, 'ha completado satisfactoriamente el curso', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, '"' . $curso["titulo"] . '"', 0, 1, 'C');

// LÍNEA DECORATIVA
$pdf->SetX(98); // posición centrada
$pdf->Cell(100, 1, '', 'B', 1);

// TEXTO FINAL
$pdf->SetY(160);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, 'Certificado generado automaticamente por la plataforma', 0, 1, 'C');

// FECHA
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, date("d/m/Y"), 0, 1, 'C');
// Descargar
$pdf->Output("I", "certificado.pdf");
exit;

