<?php
// CERTIFICADO
// -------------------------------------
// - VALIDA ACCESO
// - COMPRUEBA EXAMEN APROBADO
// - GENERA CERTIFICADO PDF 
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
$usuario = obtenerUsuarioPorId($conexion, $usuario_id);
$curso = obtenerCurso($conexion, $curso_id);

/* PDF */
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// IMAGEN PARA EL FONDO 
$pdf->Image('../public/assets/diplomaPlantilla.png', 0, 0, 297, 210);

/* TITULO */
$pdf->SetY(45);
$pdf->SetFont('Arial', 'B', 34);
$pdf->SetTextColor(90, 60, 140); // MORADO ELEGANTE
$pdf->Cell(0, 10, 'CERTIFICADO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(200, 80, 120); // ROSA SUAVE
$pdf->Cell(0, 8, 'DE FINALIZACION', 0, 1, 'C');


/* TEXTO */
$pdf->SetY(75);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 10, 'Este certificado acredita que', 0, 1, 'C');


/* NOMBRE */
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell(0, 15, strtoupper($usuario["nombre"]), 0, 1, 'C');

// Línea fina elegante
$pdf->SetDrawColor(200, 80, 120);
$pdf->SetLineWidth(0.4);
$pdf->Line(95, $pdf->GetY(), 200, $pdf->GetY());


/* CURSO */
$pdf->SetY(110);
$pdf->SetFont('Arial', '', 13);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 10, 'ha completado satisfactoriamente el curso', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(90, 60, 140);
$pdf->Cell(0, 10, '"' . $curso["titulo"] . '"', 0, 1, 'C');

// Línea inferior más discreta
$pdf->SetDrawColor(150, 150, 150);
$pdf->Line(90, $pdf->GetY(), 205, $pdf->GetY());

/* PIE */
$pdf->SetY(165);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(130, 130, 130);
$pdf->Cell(0, 10, 'Certificado generado automaticamente por la plataforma', 0, 1, 'C');

/* LOGO */
$pdf->Image('../public/assets/logowebdev.png', 133, 140, 30);

/* FECHA */
$pdf->SetY(175);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 8, date("d/m/Y"), 0, 1, 'C');

/* OUTPUT */
$pdf->Output("I", "certificado.pdf");
exit;