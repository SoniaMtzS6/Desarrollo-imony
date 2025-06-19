<?php

// CÓDIGO DE PRODUCCIÓN
/*
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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $rfc = $_POST['rfc'] ?? '';
    $alias = $_POST['alias'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $postal = $_POST['codigo'] ?? '';
    $monto = $_POST['monto'] ?? '';
    $ntarjetas = $_POST['ntarjetas'] ?? '';

    $data = array(
        "nombre" => $nombre,
        "rfc" => $rfc,
        "alias" => $alias,
        "pais" => $pais,
        "estado" => $estado,
        "codigoPostal" => $postal,
        "montoMaximo" => $monto,
        "numeroTarjetas" => $ntarjetas,
        "estatus" => 'ACTIVE'
    );

    $jsonData = json_encode($data);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://zw9vytoxof.execute-api.us-east-2.amazonaws.com/Etapa1/fisinter_crearempresa',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response, true);

    if (isset($responseData['data']['idEmpresa'])) {
        header("Location: ../empresas.php?creada=1");
        exit;
    } else {
        header("Location: ../empresas.php?creada=1");
        exit;
    }
} else {
    echo "No se recibió ninguna solicitud POST.";
}
*/

// CÓDIGO LOCAL ACTIVO
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
        die("Todos los campos son obligatorios");
    }

    // Validar formato de RFC (13 caracteres para personas morales, 12 para físicas)
    if (strlen($rfc) < 12 || strlen($rfc) > 13) {
        die("El RFC debe tener 12 o 13 caracteres");
    }

    $data = array(
        "nombre" => $nombre,
        "rfc" => $rfc,
        "alias" => $alias,
        "pais" => $pais,
        "estado" => $estado,
        "codigoPostal" => $postal,
        "montoMaximo" => floatval($monto),
        "numeroTarjetas" => $ntarjetas,
        "estatus" => "ACTIVE"
    );

    // Debug: Mostrar los datos que se van a enviar
    error_log("Datos a enviar: " . print_r($data, true));

    // Convertir los datos a formato JSON
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

    $curl = curl_init();

    // Agregar información de depuración
    error_log("Intentando crear empresa con datos: " . $jsonData);
    
    // Construir la URL completa usando el host actual
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host;
    $endpoint = '/adminfinister/api/empresas.php';
    $url = $baseUrl . $endpoint;
    
    error_log("Intentando conectar a: " . $url);
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,  // URL completa al endpoint local
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        )
    ));

    // Ejecutar la petición y capturar la respuesta
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($curl);
    
    // Log de información detallada
    error_log("Información de la petición cURL:");
    error_log("URL efectiva: " . $curlInfo['url']);
    error_log("Código HTTP: " . $httpCode);
    error_log("Tiempo total: " . $curlInfo['total_time'] . " segundos");
    
    // Obtener información de error si la hay
    if ($response === false) {
        $error = curl_error($curl);
        error_log("Error de cURL: " . $error);
        error_log("Información completa: " . print_r($curlInfo, true));
        curl_close($curl);
        die("Error al crear la empresa: " . $error . "<br>Código HTTP: " . $httpCode . "<br>URL: " . $curlInfo['url']);
    }
    
    curl_close($curl);
    
    error_log("Respuesta del servidor: " . $response);
    $responseData = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && $responseData) {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("INSERT INTO empresas (NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $rfc, $alias, $pais, $estado, $postal);
        
        if ($stmt->execute()) {
            header("Location: ../empresas.php?creada=1");
            exit;
        } else {
            die("Error al guardar en la base de datos local: " . $conn->error);
        }
    } else {
        $errorMessage = isset($responseData['errorMessage']) ? $responseData['errorMessage'] : 'Error desconocido';
        die("Error al crear la empresa: " . $errorMessage . "<br>Código HTTP: " . $httpCode . "<br>Detalles: " . $response);
    }

} else {
    echo "No se recibió ninguna solicitud POST.";
}
?>