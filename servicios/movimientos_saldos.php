<?php
require_once __DIR__ . '/../functions.php';
session_start();

// Verificar usuario logueado
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();

// Capturar datos del formulario
$idempresa = $_POST['idempresa'] ?? null;
$monto     = floatval($_POST['monto'] ?? 0);
$accion    = $_POST['accion'] ?? '';
$idusuario = $_SESSION["usuario"]["idusuario"] ?? 0; // Asegúrate que este valor exista en sesión

if (!$idempresa || $monto <= 0 || !in_array($accion, ['asignar', 'retirar'])) {
    die("Datos inválidos.");
}

// Obtener saldo actual
$stmt = $conn->prepare("SELECT SALDO FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idempresa);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    die("Empresa no encontrada.");
}

$saldo_actual = floatval($row['SALDO']);

// Calcular nuevo saldo
if ($accion === "asignar") {
    $nuevo_saldo = $saldo_actual + $monto;
    $tipo = 'asignacion';
} else {
    $nuevo_saldo = max(0, $saldo_actual - $monto);
    $tipo = 'retiro';
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar en movimientos_saldo
    $stmt_mov = $conn->prepare("
        INSERT INTO movimientos_saldo (ID_EMPRESA, MONTO, TIPO, ID_USUARIO)
        VALUES (?, ?, ?, ?)
    ");
    $stmt_mov->bind_param("ds si", $idempresa, $monto, $tipo, $idusuario);
    $stmt_mov->execute();

    // Actualizar saldo en empresas
    $stmt_upd = $conn->prepare("
        UPDATE empresas SET SALDO = ? WHERE ID_EMPRESA = ?
    ");
    $stmt_upd->bind_param("di", $nuevo_saldo, $idempresa);
    $stmt_upd->execute();

    $conn->commit();
    header("Location: editempresa.php?idempresa=$idempresa&saldo_updated=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error en movimiento de saldo: " . $e->getMessage());
    die("Ocurrió un error al actualizar el saldo.");
}
