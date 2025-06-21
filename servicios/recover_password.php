<?php require_once __DIR__ . '/../functions.php'; ?>
<?php





$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
if (!$email) {
    die("Email no proporcionado.");
}

$stmt = $conn->prepare("SELECT id FROM administradores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    $token = bin2hex(random_bytes(16));

    // Guardar token temporal en la base (puedes usar otro campo temporal si lo tienes)
    $conn->query("UPDATE administradores SET dato_extra = '$token' WHERE email = '$email'");

    $link = "https://d14ybbs36vspto.cloudfront.net/reset_password.php?token=$token";

    // Enviar correo
    $data = [
        "to" => $email,
        "subject" => "Recuperación de contraseña",
        "body" => "Haz clic en el siguiente enlace para cambiar tu contraseña:\n\n$link"
    ];

    $ch = curl_init("https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendMail");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    echo "Correo enviado correctamente.";
    header("Location: ../index.php");
} else {
    echo "El correo no está registrado.";
    header("Location: ../index.php");
}

$stmt->close();
$conn->close();
?>