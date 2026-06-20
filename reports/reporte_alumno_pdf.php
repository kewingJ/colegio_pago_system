<?php
// reports/reporte_alumno_pdf.php

// Seguridad y configuración
require_once  '../includes/config.php';
require_once  '../includes/security.php'; // si este archivo valida sesión
date_default_timezone_set('America/Managua');
setlocale(LC_TIME, 'es_NI.UTF-8', 'es_ES.UTF-8', 'es_ES', 'es');

// Ajusta la ruta de tu librería TCPDF según tu proyecto:
require_once  '../tcpdf/tcpdf.php'; 
require_once  '../includes/tcpdf_netsoluciones.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Sanitiza/valida parámetros
$id_alumno = isset($_GET['id_alumno']) ? (int) $_GET['id_alumno'] : 0;
$id_anio   = isset($_GET['id_anio'])   ? (int) $_GET['id_anio']   : 0;

if ($id_alumno <= 0 || $id_anio <= 0) {
    die('Parámetros inválidos.');
}

// Obtiene datos
// Usamos exactamente la misma SELECT base que en ajax_table/ajax_table_reporte_alumnos_dos.php
// y agregamos un ORDER BY para que salga ordenado por fecha
$sql = "
    SELECT 
        R.IdReciboMaestro AS NoFACTURA,
        R.FechaPago       AS FECHAPAGO,
        A.NOMBREAPELLIDO  AS NombreApellido,
        CASE 
            WHEN CAP.Id = 3 THEN CONCAT(
                CAP.Concepto, ' ', 
                CASE MONTH(R.MesReferencia)
                    WHEN 1 THEN 'ENERO'
                    WHEN 2 THEN 'FEBRERO'
                    WHEN 3 THEN 'MARZO'
                    WHEN 4 THEN 'ABRIL'
                    WHEN 5 THEN 'MAYO'
                    WHEN 6 THEN 'JUNIO'
                    WHEN 7 THEN 'JULIO'
                    WHEN 8 THEN 'AGOSTO'
                    WHEN 9 THEN 'SEPTIEMBRE'
                    WHEN 10 THEN 'OCTUBRE'
                    WHEN 11 THEN 'NOVIEMBRE'
                    WHEN 12 THEN 'DICIEMBRE'
                    ELSE ''
                END
            )
            ELSE CONCAT(CAP.Concepto, ' ', COP.Concepto)
        END AS CONCEPTO,
        R.ValorTotal,
        (
            SELECT TIPOMONEDA
            FROM tbl_aranceles Ara
            WHERE Ara.IdNivel = R.Nivel 
              AND Ara.IdCategoria = R.IdCategoriaConcepto 
              AND Ara.IdConcepto = R.IdConcepto 
              AND Ara.Anio = R.Anio
            LIMIT 1
        ) AS TIPOMONEDA
    FROM tbl_recibo R
    INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
    INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
    INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IDALUMNO
    WHERE R.Anulado = 0 
      AND R.ANIO = ? 
      AND R.IdAlumno = ?
    ORDER BY R.FechaPago ASC, R.IdReciboMaestro ASC
";

$stmt = $link->prepare($sql);
$stmt->bind_param('ii', $id_anio, $id_alumno);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$alumnoNombre = '';
$moneda = 'C$'; // fallback

while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
    if ($alumnoNombre === '' && !empty($r['NombreApellido'])) {
        // Título con mayúscula inicial por palabra
        $alumnoNombre = mb_convert_case(mb_strtolower($r['NombreApellido'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    if (!empty($r['TIPOMONEDA'])) {
        // Si deseas mapear TIPOMONEDA a prefijo, hazlo aquí
        // Ejemplos típicos:
        // 'CORDOBA' => 'C$', 'USD' => '$', etc.
        $tip = strtoupper(trim($r['TIPOMONEDA']));
        if ($tip === 'USD' || $tip === 'DOLAR' || $tip === 'DÓLAR') {
            $moneda = '$';
        } else {
            $moneda = 'C$';
        }
    }
}

$stmt->close();

// Si no hay datos, generamos un PDF sencillo informando
if (count($rows) === 0) {
    $pdf = new TCPDFNetsoluciones('P', 'mm', 'LETTER', true, 'UTF-8', false);
    $pdf->SetCreator('Sistema');
    $pdf->SetAuthor('Netsoluciones');
    $pdf->SetTitle('Reporte de pagos por alumno');
    $pdf->SetMargins(15, 15, 15);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 11);
    $pdf->Write(6, "No se encontraron pagos para el alumno seleccionado en el año $id_anio.");
    $pdf->Output('reporte_pagos_alumno.pdf', 'I');
    exit;
}

// Suma total
$total = 0.0;
foreach ($rows as $r) {
    $total += (float) $r['ValorTotal'];
}

// ---------- TCPDF ----------
class PDFConEncabezado extends TCPDFNetsoluciones {
    public $logoPath = '';
    public $titulo   = '';
    public $subtitulo= '';
    public $info     = '';

    public function Header() {
        if ($this->logoPath && file_exists($this->logoPath)) {
            // x, y, width
            $this->Image($this->logoPath, 15, 12, 20);
        }
        $this->SetY(12);
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 6, $this->titulo, 0, 1, 'C', 0, '', 0);
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 5, $this->subtitulo, 0, 1, 'C', 0, '', 0);
        $this->Ln(2);
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 5, $this->info, 0, 0, 'C', 0, '', 0);
        $this->Ln(8);
        // Línea
        $this->Line(15, 30, 200, 30);
        $this->Ln(2);
    }
    public function Footer() {
        parent::Footer();
    }
}

$pdf = new PDFConEncabezado('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->logoPath = __DIR__ . '/../img/Logo-SYSCPE.png'; // ajusta si tu logo se llama distinto
$pdf->titulo   = 'Reporte de pagos por alumno';
$pdf->subtitulo= 'Año lectivo: ' . $id_anio;
$pdf->info     = 'Alumno: ' . ($alumnoNombre ?: 'N/D') . '   |   Generado: ' . date('d/m/Y H:i');

$pdf->SetCreator('Sistema');
$pdf->SetAuthor('Netsoluciones');
$pdf->SetTitle('Reporte de pagos por alumno');
$pdf->SetMargins(15, 36, 15); // margen superior más grande por Header personalizado
$pdf->SetAutoPageBreak(true, 18);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
// $pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// Tabla HTML
$css = '
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #777; padding: 6px; font-size: 10pt; }
th { background-color: #f0f0f0; font-weight: bold; }
.text-right { text-align: right; }
.small { font-size: 9pt; color: #444; }
</style>';

$html = $css;
$html .= '<table>
    <thead>
        <tr>
            <th><strong>No. Recibo</strong></th>
            <th><strong>Fecha de pago</strong></th>
            <th><strong>Concepto</strong></th>
            <th class="text-right"><strong>Monto pagado</strong></th>
        </tr>
    </thead>
    <tbody>';

foreach ($rows as $r) {
    $no = htmlspecialchars((string)$r['NoFACTURA'], ENT_QUOTES, 'UTF-8');
    // Formatea fecha a d/m/Y
    $fecha = '';
    if (!empty($r['FECHAPAGO'])) {
        try {
            $dt = new DateTime($r['FECHAPAGO']);
            $fecha = $dt->format('d/m/Y');
        } catch (Exception $e) {
            $fecha = htmlspecialchars((string)$r['FECHAPAGO'], ENT_QUOTES, 'UTF-8');
        }
    }
    $concepto = htmlspecialchars((string)$r['CONCEPTO'], ENT_QUOTES, 'UTF-8');
    $monto = number_format((float)$r['ValorTotal'], 2, ',', '.');

    $html .= '<tr>
        <td>'.$no.'</td>
        <td>'.$fecha.'</td>
        <td>'.$concepto.'</td>
        <td class="text-right">'.$moneda.' '.$monto.'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Total
$html .= '<br><table>
    <tr>
        <td></td>
        <td></td>
        <td class="text-right"><strong>TOTAL:</strong></td>
        <td class="text-right"><strong>'.$moneda.' '.number_format($total, 2, ',', '.').'</strong></td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Salida: inline en el navegador
$filename = 'reporte_pagos_alumno_'.$id_alumno.'_'.$id_anio.'.pdf';
$pdf->Output($filename, 'I');
