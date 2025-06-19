<?php
// CONEXIÓN CENTRALIZADA A LA BASE DE DATOS
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "finister";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// ENDPOINTS EXTERNOS CENTRALIZADOS
function getApiBaseUrl() {
    // URL LOCAL
    return "http://localhost:3000/api";

    /* URL de PRODUCCIÓN
    return "https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV";
    */
}

function getEmailServiceUrl() {
    // URL LOCAL
    return "http://localhost:3001/email";

    /* URL de PRODUCCIÓN
    return "https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendmailmasivo";
    */
}

// FUNCIONES PARA URLS DE SERVICIOS CURL
function getServiceUrl1() {
    // URL de PRODUCCIÓN
    /*
    return "https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV";
    */

    // URL LOCAL
    return "http://localhost:3000/api";
}

function getServiceUrl2() {
    // URL de PRODUCCIÓN
    /*
    return "https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendmailmasivo";
    */

    // URL LOCAL
    return "http://localhost:3001/email";
}

// GENERAR TOKEN POMELO CENTRALIZADO
function getPomeloToken() {
    $curl = curl_init();

    // Configuración LOCAL
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:3002/pomelo/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "client_id" => "local_test_id",
            "client_secret" => "local_test_secret",
            "audience" => "http://localhost:3002",
            "grant_type" => "client_credentials"
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    /* Configuración de PRODUCCIÓN
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.pomelo.la/oauth/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "client_id" => "cxq8Yq6wFgE53FxOzgHAGrCzRe5n4KgL",
            "client_secret" => "hfGy54beNj08H6v7AnvTgo2g5zYGXZ9kyTdtpEHs5b_Vzgv1WDypnyFUz6273YO9",
            "audience" => "https://auth-prod.pomelo.la",
            "grant_type" => "client_credentials"
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    */

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

// Funciones locales adicionales
function validarTarjeta($numero_tarjeta) {
    // Validar que sea un número de 16 dígitos
    if (!preg_match('/^\d{16}$/', $numero_tarjeta)) {
        return false;
    }
    return true;
}

function validarPIN($pin) {
    // Validar que sea un número de 4 dígitos
    if (!preg_match('/^\d{4}$/', $pin)) {
        return false;
    }
    return true;
}

function obtenerSaldoTarjeta($id_tarjeta) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT SALDO FROM tarjetas WHERE ID_TARJETA = ?");
    $stmt->bind_param("i", $id_tarjeta);
    $stmt->execute();
    $result = $stmt->get_result();
    $saldo = 0;
    
    if ($row = $result->fetch_assoc()) {
        $saldo = $row['SALDO'];
    }
    
    $stmt->close();
    $conn->close();
    return $saldo;
}

function obtenerTransacciones($id_tarjeta) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM transacciones WHERE ID_TARJETA = ? ORDER BY FECHA DESC");
    $stmt->bind_param("i", $id_tarjeta);
    $stmt->execute();
    $result = $stmt->get_result();
    $transacciones = array();
    
    while ($row = $result->fetch_assoc()) {
        $transacciones[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $transacciones;
}

?>