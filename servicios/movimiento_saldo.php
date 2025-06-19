<?php
require_once '../functions.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['perfil'] !== 'Superadministrador') {
    die('No autorizado');
}

$id_empresa = intval($_POST['id_empresa']);
$monto = floatval($_POST['monto']);
$accion = $_POST['accion'];
$comentario = $_POST['comentario'] ?? '';
$usuario = $_SESSION['usuario']['email'];

if ($monto <= 0) {
    header("Location: ../editempresa.php?idempresa=$id_empresa&error=monto");
    exit;
}

$conn = getDbConnection();
$conn->begin_transaction();

try {
    // Obtener saldo actual
    $res = $conn->query("SELECT saldo_actual FROM empresas WHERE ID_EMPRESA = $id_empresa FOR UPDATE");
    $row = $res->fetch_assoc();
    $saldo_actual = floatval($row['saldo_actual']);

    if ($accion === 'RETIRO' && $monto > $saldo_actual) {
        throw new Exception('No hay suficiente saldo para retirar');
    }

    // Calcular nuevo saldo
    $nuevo_saldo = $accion === 'ASIGNACION' ? $saldo_actual + $monto : $saldo_actual - $monto;

    // Actualizar saldo en empresas
    $stmt = $conn->prepare("UPDATE empresas SET saldo_actual = ? WHERE ID_EMPRESA = ?");
    $stmt->bind_param("di", $nuevo_saldo, $id_empresa);
    $stmt->execute();

    // Registrar movimiento
    $stmt = $conn->prepare("INSERT INTO movimientos_saldo (id_empresa, tipo, monto, usuario, comentario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $id_empresa, $accion, $monto, $usuario, $comentario);
    $stmt->execute();

    $conn->commit();
    header("Location: ../editempresa.php?idempresa=$id_empresa&ok=1");
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../editempresa.php?idempresa=$id_empresa&error=" . urlencode($e->getMessage()));
} 