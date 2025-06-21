<?php require_once __DIR__ . '/../functions.php'; ?>
<?php





$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$token = $_POST['token'] ?? '';
$nueva = $_POST['nueva_password'] ?? '';
$confirmar = $_POST['confirmar_password'] ?? '';

if (!$token || !$nueva || !$confirmar) {
    die("Todos los campos son obligatorios.");
}

if ($nueva !== $confirmar) {
    die("Las contraseñas no coinciden.");
}

// Buscar al usuario con ese token
$stmt = $conn->prepare("SELECT id FROM administradores WHERE dato_extra = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Token inválido o expirado.");
}

$usuario = $result->fetch_assoc();
$id = $usuario['id'];

// Actualizar contraseña y limpiar token
$update = $conn->prepare("UPDATE administradores SET password = ?, dato_extra = '' WHERE id = ?");
$update->bind_param("si", $nueva, $id);
if ($update->execute()) {
    echo "Contraseña actualizada con éxito. Puedes <a href='../index.php'>iniciar sesión</a>.";
    header("Location: ../index.php");

} else {
    echo "Error al actualizar la contraseña.";
    header("Location: ../reset_password.php");
}

$stmt->close();
$update->close();
$conn->close();
?>
