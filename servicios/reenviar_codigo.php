<?php require_once __DIR__ . '/../functions.php'; ?>
<?php
session_start();






$conn = getDbConnection();
if ($conn->connect_error) {
    http_response_code(500);
    echo "Error de conexión: " . $conn->connect_error;
    exit;
}

$email = $_SESSION["usuario"]["email"] ?? '';
if (!$email) {
    http_response_code(400);
    echo "Sesión no válida.";
    exit;
}

// Generar nuevo código de 6 dígitos
$codigo = random_int(100000, 999999);

// Guardar el nuevo código en la base
$stmt = $conn->prepare("UPDATE administradores SET dato_extra = ? WHERE email = ?");
$stmt->bind_param("ss", $codigo, $email);
$stmt->execute();

// Preparar y enviar el correo
$payload = json_encode([
    "to" => $email,
    "subject" => "Tu nuevo código de verificación",
    "body" => "Tu nuevo código de acceso es: $codigo"
]);

$ch = curl_init("https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendMail");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resultado = curl_exec($ch);
curl_close($ch);

echo "Código reenviado";
$conn->close();
?>
