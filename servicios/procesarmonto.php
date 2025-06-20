<?php
require_once __DIR__ . '/../functions.php';
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$id_empresa = $_POST['id_empresa'] ?? null;
$monto = $_POST['monto'] ?? 0;
$accion = $_POST['accion'] ?? '';
$comentario = $_POST['comentario'] ?? 'Sin comentario';

if (!$id_empresa || !$accion || $monto <= 0) {
    header("Location: ../editempresa.php?idempresa=$id_empresa&error=datos_invalidos");
    exit;
}

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Determinar el tipo de movimiento y el monto a registrar
if ($accion === 'retirar') {
    // Para retiros, el monto es negativo
    $monto_agregado = -abs($monto);
    $tipo_movimiento = 'Retiro';
} else {
    // Para asignaciones, el monto es positivo
    $monto_agregado = abs($monto);
    $tipo_movimiento = 'Asignacion';
}


// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener el total actual de la empresa
    $stmt_total = $conn->prepare("SELECT total_monto FROM empresas_movimientos WHERE id_empresa = ? ORDER BY id DESC LIMIT 1");
    $stmt_total->bind_param("i", $id_empresa);
    $stmt_total->execute();
    $resultado_total = $stmt_total->get_result();
    
    $total_actual = 0;
    if ($fila = $resultado_total->fetch_assoc()) {
        $total_actual = $fila['total_monto'];
    }
    $stmt_total->close();

    // 2. Calcular el nuevo total
    $nuevo_total = $total_actual + $monto_agregado;

    // 3. Insertar el nuevo movimiento
    $stmt_insert = $conn->prepare("INSERT INTO empresas_movimientos (id_empresa, monto_agregado, total_monto, fecha_movimiento, tipo_movimiento) VALUES (?, ?, ?, CURDATE(), ?)");
    $stmt_insert->bind_param("idds", $id_empresa, $monto_agregado, $nuevo_total, $tipo_movimiento);
    $stmt_insert->execute();
    $stmt_insert->close();

    // 4. Actualizar el MONTO_MAXIMO en la tabla empresas
    $stmt_update = $conn->prepare("UPDATE empresas SET MONTO_MAXIMO = ? WHERE ID_EMPRESA = ?");
    $stmt_update->bind_param("di", $nuevo_total, $id_empresa);
    $stmt_update->execute();
    $stmt_update->close();

    // Confirmar transacción
    $conn->commit();

    header("Location: ../editempresa.php?idempresa=$id_empresa&status=success");

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error en la transacción: " . $e->getMessage());
    header("Location: ../editempresa.php?idempresa=$id_empresa&error=transaccion_fallida");
}

$conn->close();
exit;
?> 