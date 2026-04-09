<?php
session_start();

require_once '../includes/bd.php';
require_once '../includes/fpdf/fpdf.php';

/* =========================
   LOGIN
========================= */
if (!isset($_SESSION["usuario_id"])) {
   die("No autorizado");
}

$usuario_id = $_SESSION["usuario_id"];

/* =========================
   VALIDAR CURSO
========================= */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
   die("Curso inválido");
}

$curso_id = intval($_GET["id"]);

/* =========================
   COMPROBAR EXAMEN APROBADO
========================= */
$sql_check = "
SELECT aprobado 
FROM resultados_examen 
WHERE usuario_id = ? AND curso_id = ?
LIMIT 1
";

$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $usuario_id, $curso_id);
mysqli_stmt_execute($stmt_check);

$res_check = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($res_check);

if (!$row || $row["aprobado"] != 1) {
   die("No has aprobado el curso");
}

/* =========================
   USUARIO
========================= */
$sql_user = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_user);
mysqli_stmt_bind_param($stmt, "i", $usuario_id);
mysqli_stmt_execute($stmt);
$res_user = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($res_user);

/* =========================
   CURSO
========================= */
$sql_curso = "SELECT titulo FROM cursos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_curso);
mysqli_stmt_bind_param($stmt, "i", $curso_id);
mysqli_stmt_execute($stmt);
$res_curso = mysqli_stmt_get_result($stmt);
$curso = mysqli_fetch_assoc($res_curso);

/* =========================
   PDF
========================= */
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Fondo (tu plantilla)
$pdf->Image('../public/assets/diplomaPlantilla.png', 0, 0, 297, 210);

/* =========================
   TÍTULO
========================= */
$pdf->SetY(40);
$pdf->SetFont('Arial', 'B', 34);
$pdf->SetTextColor(20, 60, 100);
$pdf->Cell(0, 10, 'CERTIFICADO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(40, 100, 180);
$pdf->Cell(0, 10, 'DE FINALIZACION', 0, 1, 'C');

/* =========================
   TEXTO
========================= */
$pdf->SetY(70);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Este certificado acredita que', 0, 1, 'C');

/* =========================
   NOMBRE
========================= */
$pdf->SetFont('Arial', 'BI', 26);
$pdf->Cell(0, 15, $usuario["nombre"], 0, 1, 'C');

// Línea
$pdf->SetX(98);
$pdf->Cell(100, 1, '', 'B', 1);

/* =========================
   CURSO
========================= */
$pdf->SetY(110);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, 'ha completado satisfactoriamente el curso', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, '"' . $curso["titulo"] . '"', 0, 1, 'C');

// Línea
$pdf->SetX(98);
$pdf->Cell(100, 1, '', 'B', 1);

/* =========================
   PIE
========================= */
$pdf->SetY(160);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, 'Certificado generado automaticamente por la plataforma', 0, 1, 'C');

/* =========================
   FECHA
========================= */
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, date("d/m/Y"), 0, 1, 'C');

/* =========================
   OUTPUT
========================= */
$pdf->Output("I", "certificado.pdf");
exit;