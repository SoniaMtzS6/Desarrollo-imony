<?php
include '../functions.php';
include 'config_settlements.php';

session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: ../authentication-two-steps.php");
    exit;
}

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idEmpresa = $_GET['id_empresa'] ?? (isset($_SESSION["usuario"]["id_empresa"]) ? $_SESSION["usuario"]["id_empresa"] : null);
$formato = $_GET['formato'] ?? 'csv'; // csv, xls, pdf

if (!$idEmpresa) {
    die("ID de empresa requerido");
}

// Obtener cuentas de la empresa
$accounts = [];
$stmt = $conn->prepare("SELECT id_account FROM user WHERE id_empresa = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row["id_account"];
}
$stmt->close();

if (count($accounts) === 0) {
    die("No hay cuentas relacionadas con esta empresa.");
}

// Filtros de fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Obtener datos de la empresa
$stmt = $conn->prepare("SELECT NOMBRE_EMPRESA FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
$empresa = $result->fetch_assoc();
$stmt->close();

$empresaNombre = $empresa ? $empresa['NOMBRE_EMPRESA'] : 'Empresa';

// Para exportar, obtenemos todos los registros (un tamaño de página muy grande)
$size = 10000;
$page = 1;

// Obtener datos usando la nueva API de settlements
$apiResponse = getSettlementsReport($fecha_inicio, $fecha_fin, implode(',', $accounts), $page, $size);
$data = convertSettlementsResponse($apiResponse);

// Extraer datos
$movimientos = $data['movimientos'] ?? [];
$totalSettlements = $data['totalSettlements'] ?? 0;
$totalTransactions = $data['totalTransactions'] ?? 0;

// Si hubo un error en la API, detener la ejecución.
if (isset($data['error']) && $data['error']) {
    // Es importante mostrar el error para depuración
    die("Error al obtener los datos de la API: " . htmlspecialchars($data['error']));
}

// Generar nombre del archivo
$fechaReporte = date('Y-m-d_H-i-s');
$nombreArchivo = "Reporte_Settlements_" . preg_replace('/[^a-zA-Z0-9]/', '_', $empresaNombre) . "_{$fechaReporte}";

// Exportar según el formato solicitado
switch ($formato) {
    case 'csv':
        exportarCSV($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions);
        break;
    case 'excel': // Acepta 'excel' como alias de 'xls'
    case 'xls':
        exportarXLS($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions);
        break;
    case 'pdf':
        exportarPDF($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions);
        break;
    default:
        die("Formato no válido");
}

function exportarCSV($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados del reporte
    fputcsv($output, array('REPORTE DE MOVIMIENTOS VS LIQUIDACIONES'));
    fputcsv($output, array('Empresa: ' . $empresaNombre));
    fputcsv($output, array('Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin))));
    fputcsv($output, array('Fecha de generación: ' . date('d/m/Y H:i:s')));
    fputcsv($output, array(''));
    
    // Resumen
    fputcsv($output, array('RESUMEN'));
    fputcsv($output, array('Total Liquidaciones: $' . number_format($totalSettlements, 2) . ' MXN'));
    fputcsv($output, array('Total Transacciones: $' . number_format($totalTransactions, 2) . ' MXN'));
    fputcsv($output, array('Diferencia: $' . number_format($totalSettlements - $totalTransactions, 2) . ' MXN'));
    fputcsv($output, array(''));
    
    // Encabezados de la tabla
    fputcsv($output, array('Fecha', 'Tipo', 'ID', 'Cuenta', 'Descripción', 'Monto', 'Moneda', 'Estado'));
    
    // Datos
    foreach ($movimientos as $movimiento) {
        $tipo = $movimiento['type'] === 'settlement' ? 'Liquidación' : 'Transacción';
        fputcsv($output, array(
            date('d/m/Y H:i', strtotime($movimiento['created_at'])),
            $tipo,
            $movimiento['id'],
            $movimiento['account_id'],
            $movimiento['description'] ?? 'N/A',
            number_format($movimiento['amount'], 2),
            $movimiento['currency'],
            ucfirst($movimiento['status'])
        ));
    }
    
    fclose($output);
}

function exportarXLS($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
    
    // Iniciar el contenido del archivo Excel
    $xls = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    $xls .= '<head>';
    $xls .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    $xls .= '<style>';
    $xls .= 'table { border-collapse: collapse; }';
    $xls .= 'th, td { border: 1px solid #000; padding: 5px; }';
    $xls .= 'th { background-color: #f2f2f2; font-weight: bold; }';
    $xls .= '.title { font-size: 16px; font-weight: bold; text-align: center; }';
    $xls .= '.summary-label { font-weight: bold; }';
    $xls .= '</style>';
    $xls .= '</head>';
    $xls .= '<body>';
    $xls .= '<table>';
    
    // Título del reporte
    $xls .= '<tr><td colspan="8" class="title">REPORTE DE MOVIMIENTOS VS LIQUIDACIONES</td></tr>';
    $xls .= '<tr><td colspan="8"><strong>Empresa:</strong> ' . htmlspecialchars($empresaNombre) . '</td></tr>';
    $xls .= '<tr><td colspan="8"><strong>Período:</strong> ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)) . '</td></tr>';
    $xls .= '<tr><td colspan="8"><strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '</td></tr>';
    $xls .= '<tr><td colspan="8"></td></tr>';

    // Resumen
    $xls .= '<tr><td colspan="8" class="summary-label">RESUMEN</td></tr>';
    $xls .= '<tr><td class="summary-label">Total Liquidaciones:</td><td colspan="7">$' . number_format($totalSettlements, 2) . ' MXN</td></tr>';
    $xls .= '<tr><td class="summary-label">Total Transacciones:</td><td colspan="7">$' . number_format($totalTransactions, 2) . ' MXN</td></tr>';
    $xls .= '<tr><td class="summary-label">Diferencia:</td><td colspan="7">$' . number_format($totalSettlements - $totalTransactions, 2) . ' MXN</td></tr>';
    $xls .= '<tr><td colspan="8"></td></tr>';

    // Encabezados de la tabla
    $xls .= '<tr>';
    $xls .= '<th>Fecha</th>';
    $xls .= '<th>Tipo</th>';
    $xls .= '<th>ID</th>';
    $xls .= '<th>Cuenta</th>';
    $xls .= '<th>Descripción</th>';
    $xls .= '<th>Monto</th>';
    $xls .= '<th>Moneda</th>';
    $xls .= '<th>Estado</th>';
    $xls .= '</tr>';
    
    // Datos
    foreach ($movimientos as $movimiento) {
        $tipo = $movimiento['type'] === 'settlement' ? 'Liquidación' : 'Transacción';
        $xls .= '<tr>';
        $xls .= '<td>' . date('d/m/Y H:i', strtotime($movimiento['created_at'])) . '</td>';
        $xls .= '<td>' . htmlspecialchars($tipo) . '</td>';
        $xls .= '<td>' . htmlspecialchars($movimiento['id']) . '</td>';
        $xls .= '<td>' . htmlspecialchars($movimiento['account_id']) . '</td>';
        $xls .= '<td>' . htmlspecialchars($movimiento['description'] ?? 'N/A') . '</td>';
        $xls .= '<td>' . number_format($movimiento['amount'], 2) . '</td>';
        $xls .= '<td>' . htmlspecialchars($movimiento['currency']) . '</td>';
        $xls .= '<td>' . ucfirst(htmlspecialchars($movimiento['status'])) . '</td>';
        $xls .= '</tr>';
    }
    
    $xls .= '</table>';
    $xls .= '</body>';
    $xls .= '</html>';
    
    echo $xls;
}

function exportarPDF($movimientos, $nombreArchivo, $empresaNombre, $fecha_inicio, $fecha_fin, $totalSettlements, $totalTransactions) {
    require('../tfpdf/tfpdf.php'); // Asegúrate que la ruta sea correcta

    $pdf = new tFPDF('L', 'mm', 'A4'); // 'L' para landscape
    $pdf->AddPage();
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->SetFont('DejaVu','',14);

    // Título
    $pdf->Cell(0, 10, 'REPORTE DE MOVIMIENTOS VS LIQUIDACIONES', 0, 1, 'C');
    $pdf->SetFont('DejaVu','',10);
    $pdf->Cell(0, 7, 'Empresa: ' . $empresaNombre, 0, 1, 'C');
    $pdf->Cell(0, 7, 'Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'C');
    $pdf->Cell(0, 7, 'Fecha de generacion: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);

    // Resumen
    $pdf->SetFont('DejaVu','',11);
    $pdf->Cell(0, 7, 'RESUMEN', 0, 1, 'L');
    $pdf->Cell(60, 7, 'Total Liquidaciones:', 1, 0, 'L');
    $pdf->Cell(0, 7, '$' . number_format($totalSettlements, 2) . ' MXN', 1, 1, 'L');
    $pdf->Cell(60, 7, 'Total Transacciones:', 1, 0, 'L');
    $pdf->Cell(0, 7, '$' . number_format($totalTransactions, 2) . ' MXN', 1, 1, 'L');
    $pdf->Cell(60, 7, 'Diferencia:', 1, 0, 'L');
    $pdf->Cell(0, 7, '$' . number_format($totalSettlements - $totalTransactions, 2) . ' MXN', 1, 1, 'L');
    $pdf->Ln(10);

    // Encabezados de la tabla
    $pdf->SetFont('DejaVu','',9);
    $header = array('Fecha', 'Tipo', 'ID', 'Cuenta', 'Descripcion', 'Monto', 'Moneda', 'Estado');
    $w = array(30, 25, 45, 45, 60, 25, 20, 25); // Anchos de las columnas

    for($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
    }
    $pdf->Ln();

    // Datos
    foreach($movimientos as $movimiento) {
        $tipo = $movimiento['type'] === 'settlement' ? 'Liquidación' : 'Transacción';
        $pdf->Cell($w[0], 6, date('d/m/Y H:i', strtotime($movimiento['created_at'])), 1);
        $pdf->Cell($w[1], 6, $tipo, 1);
        $pdf->Cell($w[2], 6, $movimiento['id'], 1);
        $pdf->Cell($w[3], 6, $movimiento['account_id'], 1);
        $pdf->Cell($w[4], 6, $movimiento['description'] ?? 'N/A', 1);
        $pdf->Cell($w[5], 6, number_format($movimiento['amount'], 2), 1, 0, 'R');
        $pdf->Cell($w[6], 6, $movimiento['currency'], 1, 0, 'C');
        $pdf->Cell($w[7], 6, ucfirst($movimiento['status']), 1, 0, 'C');
        $pdf->Ln();
    }

    $pdf->Output('D', $nombreArchivo . '.pdf');
}

$conn->close();
?> 