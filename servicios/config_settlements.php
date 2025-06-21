<?php
/**
 * Configuración para el servicio de Settlements
 * Maneja automáticamente entornos local y producción
 */

// Cargar variables de entorno si existe el archivo .env
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '#') !== 0) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                // Remover comillas si existen
                $value = trim($value, '"\'');
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Configuración de entornos
define('SETTLEMENTS_ENV', getenv('SETTLEMENTS_ENV') ?: 'production'); // 'local' o 'production'

// URLs de los servicios
define('SETTLEMENTS_LAMBDA_URL', getenv('SETTLEMENTS_LAMBDA_URL') ?: 'https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV');
define('SETTLEMENTS_LOCAL_URL', getenv('SETTLEMENTS_LOCAL_URL') ?: 'http://localhost:3003');

// Configuración de timeout
define('SETTLEMENTS_TIMEOUT', 30); // segundos

/**
 * Obtiene la URL base del servicio según el entorno
 */
function getSettlementsBaseUrl() {
    if (SETTLEMENTS_ENV === 'local') {
        return SETTLEMENTS_LOCAL_URL;
    }
    return SETTLEMENTS_LAMBDA_URL;
}

/**
 * Obtiene la URL completa para un endpoint específico
 */
function getSettlementsEndpoint($endpoint) {
    $baseUrl = getSettlementsBaseUrl();
    return rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
}

/**
 * Realiza una petición HTTP al servicio de Settlements
 */
function callSettlementsAPI($endpoint, $params = []) {
    $url = getSettlementsEndpoint($endpoint);
    
    // Agregar parámetros a la URL si existen
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    // Configurar cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => SETTLEMENTS_TIMEOUT,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => SETTLEMENTS_ENV === 'production', // Verificar SSL solo en producción
        CURLOPT_SSL_VERIFYHOST => SETTLEMENTS_ENV === 'production' ? 2 : 0
    ]);
    
    // Ejecutar petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Manejar errores
    if ($error) {
        error_log("Error cURL en Settlements API: " . $error);
        return [
            'success' => false,
            'message' => 'Error de conexión: ' . $error,
            'data' => [],
            'summary' => null,
            'pagination' => null
        ];
    }
    
    // Decodificar respuesta
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decodificando JSON de Settlements API: " . json_last_error_msg());
        return [
            'success' => false,
            'message' => 'Error decodificando respuesta del servidor',
            'data' => [],
            'summary' => null,
            'pagination' => null
        ];
    }
    
    // Verificar código HTTP
    if ($httpCode >= 400) {
        error_log("Error HTTP en Settlements API: " . $httpCode . " - " . $response);
        return [
            'success' => false,
            'message' => 'Error del servidor (HTTP ' . $httpCode . ')',
            'data' => [],
            'summary' => null,
            'pagination' => null
        ];
    }
    
    return $data ?: [
        'success' => false,
        'message' => 'Respuesta vacía del servidor',
        'data' => [],
        'summary' => null,
        'pagination' => null
    ];
}

/**
 * Obtiene el reporte combinado de settlements y transacciones
 */
function getSettlementsReport($startDate = null, $endDate = null, $accountId = null, $page = 1, $size = 10) {
    $params = [
        'page' => $page,
        'size' => $size
    ];
    
    if ($startDate) $params['start_date'] = $startDate;
    if ($endDate) $params['end_date'] = $endDate;
    if ($accountId) $params['account_id'] = $accountId;
    
    return callSettlementsAPI('api/settlements/report', $params);
}

/**
 * Obtiene solo settlements
 */
function getSettlementsOnly($startDate = null, $endDate = null, $accountId = null, $page = 1, $size = 10) {
    $params = [
        'page' => $page,
        'size' => $size
    ];
    
    if ($startDate) $params['start_date'] = $startDate;
    if ($endDate) $params['end_date'] = $endDate;
    if ($accountId) $params['account_id'] = $accountId;
    
    return callSettlementsAPI('api/settlements/settlements', $params);
}

/**
 * Obtiene solo transacciones
 */
function getTransactionsOnly($startDate = null, $endDate = null, $accountId = null, $page = 1, $size = 10) {
    $params = [
        'page' => $page,
        'size' => $size
    ];
    
    if ($startDate) $params['start_date'] = $startDate;
    if ($endDate) $params['end_date'] = $endDate;
    if ($accountId) $params['account_id'] = $accountId;
    
    return callSettlementsAPI('api/settlements/transactions', $params);
}

/**
 * Verifica el estado del servicio
 */
function checkSettlementsHealth() {
    return callSettlementsAPI('api/settlements/health');
}

/**
 * Obtiene información del entorno actual
 */
function getSettlementsEnvironmentInfo() {
    return [
        'environment' => SETTLEMENTS_ENV,
        'base_url' => getSettlementsBaseUrl(),
        'lambda_url' => SETTLEMENTS_LAMBDA_URL,
        'local_url' => SETTLEMENTS_LOCAL_URL,
        'timeout' => SETTLEMENTS_TIMEOUT
    ];
}

/**
 * Función de compatibilidad para el código existente
 * Convierte la respuesta de la nueva API al formato esperado por el código PHP existente
 */
function convertSettlementsResponse($apiResponse) {
    if (!$apiResponse || !isset($apiResponse['success']) || !$apiResponse['success']) {
        return [
            'settlements' => [],
            'transactions' => [],
            'movimientos' => [],
            'totalSettlements' => 0,
            'totalTransactions' => 0,
            'totalMovements' => 0,
            'error' => $apiResponse['message'] ?? 'Error desconocido'
        ];
    }
    
    $settlements = [];
    $transactions = [];
    $movimientos = [];
    
    // Procesar datos
    foreach ($apiResponse['data'] as $item) {
        $movimiento = [
            'id' => $item['id'] ?? '',
            'account_id' => $item['account_id'] ?? '',
            'amount' => $item['amount'] ?? 0,
            'currency' => $item['currency'] ?? 'MXN',
            'status' => $item['status'] ?? 'unknown',
            'created_at' => $item['created_at'] ?? '',
            'type' => $item['type'] ?? 'unknown',
            'description' => $item['description'] ?? ''
        ];
        
        $movimientos[] = $movimiento;
        
        if ($item['type'] === 'settlement') {
            $settlements[] = $movimiento;
        } elseif ($item['type'] === 'transaction') {
            $transactions[] = $movimiento;
        }
    }
    
    // Calcular totales
    $summary = $apiResponse['summary'] ?? [];
    $totalSettlements = $summary['total_settlements'] ?? 0;
    $totalTransactions = $summary['total_transactions'] ?? 0;
    $totalMovements = $summary['total_movements'] ?? 0;
    
    return [
        'settlements' => $settlements,
        'transactions' => $transactions,
        'movimientos' => $movimientos,
        'totalSettlements' => $totalSettlements,
        'totalTransactions' => $totalTransactions,
        'totalMovements' => $totalMovements,
        'summary' => $summary,
        'pagination' => $apiResponse['pagination'] ?? null,
        'error' => null
    ];
}
?> 