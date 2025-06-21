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

$id_user = $_POST['id_user'] ?? null;
$monto = $_POST['monto'] ?? 0;
$accion = $_POST['accion'] ?? '';
$comentario = $_POST['comentario'] ?? 'Sin comentario';

if (!$id_user || !$accion || $monto <= 0) {
    header("Location: ../edituser.php?id=$id_user&error=datos_invalidos");
    exit;
}

// === CÓDIGO AJUSTADO A LA LÓGICA DE PRODUCCIÓN ===
$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Obtener id de la empresa del usuario
    $stmt_user = $conn->prepare("SELECT id_empresa FROM user WHERE id_ = ?");
    $stmt_user->bind_param("i", $id_user);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($row_user = $result_user->fetch_assoc()) {
        $id_empresa = $row_user['id_empresa'];
    } else {
        throw new Exception("Usuario no encontrado.");
    }
    $stmt_user->close();

    // 2. Obtener el saldo/monto total de la empresa y el último total de movimientos
    $stmt_empresa_total = $conn->prepare("
        SELECT e.MONTO_MAXIMO, em.total_monto, em.total_tarjetas
        FROM empresas e
        LEFT JOIN (
            SELECT id_empresa, total_monto, total_tarjetas
            FROM empresas_movimientos
            WHERE id_empresa = ?
            ORDER BY id DESC
            LIMIT 1
        ) em ON e.ID_EMPRESA = em.id_empresa
        WHERE e.ID_EMPRESA = ?
    ");
    $stmt_empresa_total->bind_param("ii", $id_empresa, $id_empresa);
    $stmt_empresa_total->execute();
    $result_empresa_total = $stmt_empresa_total->get_result();
    $empresa_data = $result_empresa_total->fetch_assoc();

    if (!$empresa_data) {
        throw new Exception("Empresa no encontrada.");
    }

    $monto_empresa_actual = $empresa_data['MONTO_MAXIMO'] ?? 0;
    $total_monto_mov_actual = $empresa_data['total_monto'] ?? 0;
    $total_tarjetas_mov_actual = $empresa_data['total_tarjetas'] ?? 0;
    $stmt_empresa_total->close();


    $monto_usuario = 0;
    $monto_empresa_agregado = 0;
    $tipo_movimiento_usuario = '';
    $tipo_movimiento_empresa = '';

    if ($accion === 'asignar') {
        if ($monto > $monto_empresa_actual) {
            throw new Exception("La empresa no tiene saldo suficiente para realizar esta operación.");
        }
        $monto_usuario = abs($monto);
        $monto_empresa_agregado = -abs($monto); // Se resta del saldo de la empresa
        $tipo_movimiento_usuario = 'ASIGNA';
        $tipo_movimiento_empresa = 'ASIGNA_U'; // Asignación a Usuario
    } elseif ($accion === 'retirar') {
        $monto_usuario = -abs($monto);
        $monto_empresa_agregado = abs($monto); // Se suma al saldo de la empresa
        $tipo_movimiento_usuario = 'RETIRO';
        $tipo_movimiento_empresa = 'RETIRO_U'; // Retiro de Usuario
    } else {
        throw new Exception("Acción no reconocida.");
    }

    // Calcular nuevos totales para la empresa
    $nuevo_monto_empresa = $monto_empresa_actual + $monto_empresa_agregado;

    // 3. Actualizar el MONTO_MAXIMO en la tabla de la empresa
    $stmt_update_empresa = $conn->prepare("UPDATE empresas SET MONTO_MAXIMO = ? WHERE ID_EMPRESA = ?");
    $stmt_update_empresa->bind_param("di", $nuevo_monto_empresa, $id_empresa);
    $stmt_update_empresa->execute();
    $stmt_update_empresa->close();

    // 4. Insertar el movimiento de la empresa
    $nuevo_total_monto_mov = $total_monto_mov_actual + $monto_empresa_agregado;
    $stmt_insert_empresa_mov = $conn->prepare(
        "INSERT INTO empresas_movimientos (id_empresa, monto_agregado, total_monto, total_tarjetas, fecha_movimiento, tipo_movimiento) VALUES (?, ?, ?, ?, CURDATE(), ?)"
    );
    $stmt_insert_empresa_mov->bind_param("iddis", $id_empresa, $monto_empresa_agregado, $nuevo_total_monto_mov, $total_tarjetas_mov_actual, $tipo_movimiento_empresa);
    $stmt_insert_empresa_mov->execute();
    $stmt_insert_empresa_mov->close();

    // 5. Insertar el movimiento del usuario
    $stmt_insert_usuario_mov = $conn->prepare(
        "INSERT INTO usuarios_movimientos (id_user, id_empresa, monto, fecha_movimiento, tipo_movimiento) VALUES (?, ?, ?, CURDATE(), ?)"
    );
    // id_user en la tabla es varchar(45), por lo que se usa "s"
    $stmt_insert_usuario_mov->bind_param("sids", $id_user, $id_empresa, $monto_usuario, $tipo_movimiento_usuario);
    $stmt_insert_usuario_mov->execute();
    $stmt_insert_usuario_mov->close();

    // Confirmar la transacción
    $conn->commit();

    header("Location: ../edituser.php?id=$id_user&status=success");

} catch (Exception $e) {
    $conn->rollback();
    // Guardar el error en un log para depuración
    error_log("Error en la transacción de monto de tarjeta: " . $e->getMessage());
    // Redirigir con un mensaje de error genérico
    header("Location: ../edituser.php?id=$id_user&error=transaccion_fallida");
}

$conn->close();
exit;
?> 