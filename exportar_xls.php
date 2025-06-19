<?php
include 'functions.php';
$conn = getDbConnection();

$idEmpresa = $_GET['id_empresa'] ?? null;
if (!$idEmpresa) die('ID de empresa requerido');

$saldo_inicial = $_GET['saldo_inicial'] ?? '';
$asignaciones = $_GET['asignaciones'] ?? '';
$gasto = $_GET['gasto'] ?? '';
$retiro_saldo = $_GET['retiro_saldo'] ?? '';
$retiro_compra = $_GET['retiro_compra'] ?? '';
$saldo_disponible = $_GET['saldo_disponible'] ?? '';
$fecha = $_GET['fecha'] ?? '';

$accounts = [];
$stmt = $conn->prepare("SELECT id_account FROM users WHERE id_empresa = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accounts[] = "'" . $row["id_account"] . "'";
}
$stmt->close();
if (count($accounts) === 0) die('No hay cuentas relacionadas.');
$accountsIn = implode(",", $accounts);

$sqlMovimientos = "SELECT entry_type, account_id, result, process_type, type, created_at, total_amount as monto FROM activity WHERE account_id IN ($accountsIn) ORDER BY created_at ASC";
$result = $conn->query($sqlMovimientos);

$saldoInicialt=0;
$depositos=0;
$asignaciones_val=0;
$retiros=0;
$retiroscompras=0;
$depositosolo=0;
$retirossolo=0;
$retiroscomprassolo=0;

$rows = [];
while ($row = $result->fetch_assoc()) {
    $entry = $row["entry_type"];
    $process = $row["process_type"];
    $type = $row["type"];
    $status = $row["result"];
    $fecha_mov = date("Y-m-d", strtotime($row["created_at"]));
    $monto = floatval($row["monto"]);
    if ($entry === "CREDIT" && $process === "ORIGINAL" && $status == "APPROVED") {
      $depositos += $monto;
      $depositosolo = $monto;
    } elseif ($entry === "CREDIT" && $process !== "ORIGINAL" && $status == "APPROVED") {
      $asignaciones_val += $monto;
    } elseif ($entry === "DEBIT" && $type === "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiros += $monto;
      $retirossolo = $monto;
    } elseif ($entry === "DEBIT" && $type !== "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiroscompras += $monto;
      $retiroscomprassolo = $monto;
    }
    $saldoinit = $depositos - $asignaciones_val - $retiros - $retiroscompras;
    // Filtros
    $mostrar_fila = true;
    if ($saldo_inicial !== '' && stripos((string)$saldoInicialt, $saldo_inicial) === false) $mostrar_fila = false;
    if ($asignaciones !== '' && stripos((string)$asignaciones_val, $asignaciones) === false) $mostrar_fila = false;
    if ($gasto !== '' && stripos((string)$retirossolo, $gasto) === false) $mostrar_fila = false;
    if ($retiro_saldo !== '' && stripos((string)$retirossolo, $retiro_saldo) === false) $mostrar_fila = false;
    if ($retiro_compra !== '' && stripos((string)$retiroscomprassolo, $retiro_compra) === false) $mostrar_fila = false;
    if ($saldo_disponible !== '' && stripos((string)$saldoinit, $saldo_disponible) === false) $mostrar_fila = false;
    if ($fecha !== '' && $fecha_mov !== $fecha) $mostrar_fila = false;
    if ($mostrar_fila) {
        $rows[] = [
            $saldoInicialt,
            $depositosolo,
            $asignaciones_val,
            $retirossolo,
            $retiroscomprassolo,
            $saldoinit,
            $fecha_mov
        ];
    }
    $saldoInicialt = $saldoinit;
    $depositosolo =0;
    $retirossolo=0;
    $retiroscomprassolo=0;
}
$conn->close();

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporte_empresa.xls"');
echo '<table border="1">';
echo '<tr><th>Saldo Inicial</th><th>Asignaciones de saldo a tarjetas</th><th>Gasto de tarjetas</th><th>Retiro saldo a tarjetas</th><th>Retiro por compra</th><th>Saldo disponible de asignacion</th><th>Fecha</th></tr>';
foreach ($rows as $r) {
    echo '<tr>';
    foreach ($r as $c) echo '<td>' . htmlspecialchars($c) . '</td>';
    echo '</tr>';
}
echo '</table>';
exit; 