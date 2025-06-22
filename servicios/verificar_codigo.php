<?php require_once __DIR__ . '/../functions.php'; ?>
<?php
session_start();

// Validar que el usuario está en sesión
if (!isset($_SESSION["usuario"])) {
    die("Acceso no autorizado.");
}

// Verificar que venga por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Método no permitido.");
}

// Obtener el código ingresado
$codigo = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] .
          $_POST['digit4'] . $_POST['digit5'] . $_POST['digit6'];

// Sanitizar (opcional, por seguridad extra)
$codigo = trim($codigo);

// Conexión a la base de datos





$conn = getDbConnection();

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idUsuario = $_SESSION["usuario"]["id"];

// Obtener el dato_extra actual del usuario
$stmt = $conn->prepare("SELECT dato_extra FROM administradores WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

if ($resultado->num_rows === 1) {
    $row = $resultado->fetch_assoc();
    $tokenGuardado = $row["dato_extra"];

    if ($codigo === $tokenGuardado) {
        // Código correcto: limpiar dato_extra y activar doblefactor
        $update = $conn->prepare("UPDATE administradores SET dato_extra = '' WHERE id = ?");
        $update->bind_param("i", $idUsuario);
        $update->execute();
        $update->close();

        $_SESSION["usuario"]["doblefactor"] = "1";

        // Redirigir al dashboard
        header("Location: ../usuarios.php");
        exit;
    } else {
        echo "Código incorrecto.";
        header("Location: ../authentication-two-steps.php");
    }
} else {
    echo "Usuario no encontrado.";
    header("Location: ../authentication-two-steps.php");
}

$conn->close();
?>
