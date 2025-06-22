<?php
require_once __DIR__ . '/../functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION["usuario"])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$card_id = $_POST['card_id'] ?? null;
$action = $_POST['action'] ?? null;
$iduser = $_POST['iduser'] ?? null; // Asegúrate de pasar el id del usuario para la redirección

if (!$card_id || !$action) {
    http_response_code(400);
    // Redirigir con error si faltan datos
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&error=missing_data');
    exit;
}

$token = getPomeloToken();

if (!$token) {
    http_response_code(500);
    // Redirigir con error si no se obtiene el token
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&error=token_failed');
    exit;
}

// Determinar el endpoint de la API según la acción
$endpoint = '';
if ($action === 'activar') {
    $endpoint = "https://api.pomelo.la/cards/v1/{$card_id}/unblock";
} elseif ($action === 'desactivar' || $action === 'bloquear') {
    $endpoint = "https://api.pomelo.la/cards/v1/{$card_id}/block";
} else {
    // Acción no válida, redirigir con error
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&error=invalid_action');
    exit;
}

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Redirigir a la página anterior con un mensaje de éxito o error
if ($http_code >= 200 && $http_code < 300) {
    // Éxito
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&success=true');
} else {
    // Error
    $decoded_response = json_decode($response, true);
    $error_message = urlencode($decoded_response['details'] ?? 'Error desconocido');
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&error=' . $error_message);
}
exit;
?> 