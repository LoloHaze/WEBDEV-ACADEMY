<?php
// CERTIFICADO
// -------------------------------------
// - VALIDA ACCESO
// - COMPRUEBA EXAMEN APROBADO
// - GENERA PDF
// -------------------------------------

require_once '../includes/bd.php';
require_once '../includes/funciones.php';
require_once '../includes/proteccion.php';
require_once '../includes/fpdf/fpdf.php';

session_start();

// PROTEGER
protegerPagina();

$usuario_id = $_SESSION["usuario_id"];

/* VALIDAR CURSO */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
   die("Curso inválido");
}

$curso_id = intval($_GET["id"]);

/* COMPROBAR EXAMEN */
if (!examenAprobado($conexion, $usuario_id, $curso_id)) {
   die("No has aprobado el curso");
}

/* DATOS */
$usuario = obtenerNombreUsuario($conexion, $usuario_id);
$curso = obtenerCurso($conexion, $curso_id);

/* PDF */
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// IMAGEN PARA EL FONDO 
$pdf->Image('../public/assets/diplomaPlantilla.png', 0, 0, 297, 210);

/* TITULO */
$pdf->SetY(40);
$pdf->SetFont('Arial', 'B', 34);
$pdf->SetTextColor(20, 60, 100);
$pdf->Cell(0, 10, 'CERTIFICADO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(40, 100, 180);
$pdf->Cell(0, 10, 'DE FINALIZACION', 0, 1, 'C');

/* TEXTO */
$pdf->SetY(70);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Este certificado acredita que', 0, 1, 'C');

/* NOMBRE */
$pdf->SetFont('Arial', 'BI', 26);
$pdf->Cell(0, 15, $usuario["nombre"], 0, 1, 'C');

$pdf->SetX(98);
$pdf->Cell(100, 1, '', 'B', 1);

/* CURSO */
$pdf->SetY(110);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, 'ha completado satisfactoriamente el curso', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, '"' . $curso["titulo"] . '"', 0, 1, 'C');

$pdf->SetX(98);
$pdf->Cell(100, 1, '', 'B', 1);

/* PIE */
$pdf->SetY(160);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, 'Certificado generado automaticamente por la plataforma', 0, 1, 'C');

/* FECHA */
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, date("d/m/Y"), 0, 1, 'C');

/* OUTPUT */
$pdf->Output("I", "certificado.pdf");
exit;