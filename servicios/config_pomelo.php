<?php
/**
 * Configuración de credenciales para la API de Pomelo
 * 
 * IMPORTANTE: Reemplaza estos valores con tus credenciales reales de Pomelo
 * Puedes obtenerlas desde el dashboard de Pomelo en https://dashboard.pomelo.la/
 */

// Credenciales de la API de Pomelo
define('POMELO_CLIENT_ID', 'TU_CLIENT_ID_AQUI');
define('POMELO_CLIENT_SECRET', 'TU_CLIENT_SECRET_AQUI');

// URLs de la API de Pomelo
define('POMELO_BASE_URL', 'https://api.pomelo.la');
define('POMELO_TOKEN_URL', POMELO_BASE_URL . '/oauth/token');
define('POMELO_SETTLEMENTS_URL', POMELO_BASE_URL . '/core/settlements/v1');
define('POMELO_TRANSACTIONS_URL', POMELO_BASE_URL . '/core/transactions/v1');
define('POMELO_ACCOUNTS_URL', POMELO_BASE_URL . '/core/accounts/v1');

// Configuración de timeouts y reintentos
define('POMELO_TIMEOUT', 30);
define('POMELO_MAX_RETRIES', 3);

if (!function_exists('getPomeloToken')) {
    /**
     * Función para obtener el token de acceso de Pomelo
     * 
     * @return string|null Token de acceso o null si hay error
     */
    function getPomeloToken() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => POMELO_TOKEN_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => POMELO_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode(POMELO_CLIENT_ID . ':' . POMELO_CLIENT_SECRET)
            ),
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            error_log("Error de cURL al obtener token de Pomelo: " . $error);
            return null;
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                return $data['access_token'];
            }
        }
        
        error_log("Error al obtener token de Pomelo. HTTP Code: $httpCode, Response: $response");
        return null;
    }
}

if (!function_exists('makePomeloRequest')) {
    /**
     * Función para hacer una petición a la API de Pomelo
     * 
     * @param string $url URL de la API
     * @param string $token Token de acceso
     * @param array $params Parámetros de consulta
     * @return array|null Respuesta de la API o null si hay error
     */
    function makePomeloRequest($url, $token, $params = array()) {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => POMELO_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            error_log("Error de cURL en petición a Pomelo: " . $error);
            return null;
        }
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        error_log("Error en petición a Pomelo. URL: $url, HTTP Code: $httpCode, Response: $response");
        return null;
    }
}

if (!function_exists('getPomeloSettlements')) {
    /**
     * Función para obtener settlements de Pomelo
     * 
     * @param string $token Token de acceso
     * @param string|null $accountId ID de la cuenta (opcional)
     * @param string|null $startDate Fecha de inicio (formato ISO 8601)
     * @param string|null $endDate Fecha de fin (formato ISO 8601)
     * @return array|null Datos de settlements o null si hay error
     */
    function getPomeloSettlements($token, $accountId = null, $startDate = null, $endDate = null) {
        $params = array();
        
        if ($accountId) {
            $params['filter[account_id]'] = $accountId;
        }
        if ($startDate) {
            $params['filter[created_at][gte]'] = $startDate;
        }
        if ($endDate) {
            $params['filter[created_at][lte]'] = $endDate;
        }
        
        return makePomeloRequest(POMELO_SETTLEMENTS_URL, $token, $params);
    }
}

if (!function_exists('getPomeloTransactions')) {
    /**
     * Función para obtener transacciones de Pomelo
     * 
     * @param string $token Token de acceso
     * @param string|null $accountId ID de la cuenta (opcional)
     * @param string|null $startDate Fecha de inicio (formato ISO 8601)
     * @param string|null $endDate Fecha de fin (formato ISO 8601)
     * @return array|null Datos de transacciones o null si hay error
     */
    function getPomeloTransactions($token, $accountId = null, $startDate = null, $endDate = null) {
        $params = array();
        
        if ($accountId) {
            $params['filter[account_id]'] = $accountId;
        }
        if ($startDate) {
            $params['filter[created_at][gte]'] = $startDate;
        }
        if ($endDate) {
            $params['filter[created_at][lte]'] = $endDate;
        }
        
        return makePomeloRequest(POMELO_TRANSACTIONS_URL, $token, $params);
    }
}

if (!function_exists('getPomeloAccount')) {
    /**
     * Función para obtener información de una cuenta específica
     * 
     * @param string $token Token de acceso
     * @param string $accountId ID de la cuenta
     * @return array|null Datos de la cuenta o null si hay error
     */
    function getPomeloAccount($token, $accountId) {
        $url = POMELO_ACCOUNTS_URL . '/' . $accountId;
        return makePomeloRequest($url, $token);
    }
}

if (!function_exists('validatePomeloCredentials')) {
    /**
     * Función para validar que las credenciales estén configuradas
     * 
     * @return bool True si las credenciales están configuradas
     */
    function validatePomeloCredentials() {
        if (POMELO_CLIENT_ID === 'TU_CLIENT_ID_AQUI' || POMELO_CLIENT_SECRET === 'TU_CLIENT_SECRET_AQUI') {
            return false;
        }
        return true;
    }
}

if (!function_exists('getPomeloErrorMessage')) {
    /**
     * Función para obtener un mensaje de error amigable
     * 
     * @param string $operation Operación que falló
     * @return string Mensaje de error
     */
    function getPomeloErrorMessage($operation) {
        if (!validatePomeloCredentials()) {
            return "Error: Las credenciales de Pomelo no están configuradas. Por favor, configura POMELO_CLIENT_ID y POMELO_CLIENT_SECRET en config_pomelo.php";
        }
        
        return "Error al $operation. Verifica las credenciales y la conexión a la API de Pomelo.";
    }
}
?> 