<?php
$data = array(
    "nombre" => "Empresa de Prueba",
    "rfc" => "TEST123456ABC",
    "alias" => "Test",
    "pais" => "México",
    "estado" => "CDMX",
    "codigoPostal" => "12345",
    "montoMaximo" => 1000.00,
    "numeroTarjetas" => 5,
    "estatus" => "ACTIVE"
);

$jsonData = json_encode($data);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/adminfinister/api/empresas.php',
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
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "Error de cURL: " . curl_error($curl);
} else {
    echo "Código HTTP: " . $httpCode . "\n";
    echo "Respuesta: " . $response . "\n";
}

curl_close($curl);
?> 