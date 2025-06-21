<?php
session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: authentication-two-steps.php");
    exit;
}

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../functions.php';

try {
    // Verificar si el formulario fue enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recibir los datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $rfc = strtoupper(trim($_POST['rfc'] ?? ''));
        $alias = trim($_POST['alias'] ?? '');
        $pais = trim($_POST['pais'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $postal = trim($_POST['codigo'] ?? '');
        $monto = str_replace(',', '', $_POST['monto'] ?? '0');
        $ntarjetas = intval($_POST['ntarjetas'] ?? '0');

        // Validar que los campos requeridos no estén vacíos
        if (empty($nombre) || empty($rfc) || empty($monto) || empty($ntarjetas)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Validar formato de RFC
        if (strlen($rfc) < 12 || strlen($rfc) > 13) {
            throw new Exception("El RFC debe tener 12 o 13 caracteres");
        }

        $conn = getDbConnection();
        
        // Preparar la consulta
        $stmt = $conn->prepare("INSERT INTO empresas (NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        // Vincular parámetros
        $estatus = 'ACTIVE';
        $montoMaximo = floatval($monto);
        if (!$stmt->bind_param("ssssssdis", 
            $nombre,
            $rfc,
            $alias,
            $pais,
            $estado,
            $postal,
            $montoMaximo,
            $ntarjetas,
            $estatus
        )) {
            throw new Exception("Error al vincular parámetros: " . $stmt->error);
        }

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        // Redirigir en caso de éxito
        header("Location: ../empresas.php?creada=1");
        exit;
    }
} catch (Exception $e) {
    die("Error al crear la empresa: " . $e->getMessage());
}
?> 