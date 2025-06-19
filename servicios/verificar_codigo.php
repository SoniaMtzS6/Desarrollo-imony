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

/* Configuración de PRODUCCIÓN
// En producción, el código se almacena en la base de datos en el campo dato_extra
$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idUsuario = $_SESSION["usuario"]["id"];
$stmt = $conn->prepare("SELECT dato_extra FROM administradores WHERE ID_ADMIN = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

if ($resultado->num_rows === 1) {
    $row = $resultado->fetch_assoc();
    $tokenGuardado = $row["dato_extra"];

    if ($codigo === $tokenGuardado) {
        $update = $conn->prepare("UPDATE administradores SET dato_extra = '' WHERE ID_ADMIN = ?");
        $update->bind_param("i", $idUsuario);
        $update->execute();
        $update->close();

        $_SESSION["usuario"]["doblefactor"] = "1";
        header("Location: ../usuarios.php");
        exit;
    }
}
$conn->close();
*/

// Configuración LOCAL
// Verificar el código usando la sesión
if ($codigo === $_SESSION['token']) {
    // Código correcto: marcar como verificado
    $_SESSION["usuario"]["doblefactor"] = "1";
    unset($_SESSION['token']); // Limpiar el token usado

    // Redirigir al dashboard
    header("Location: ../usuarios.php");
    exit;
} else {
    // Código incorrecto
    $_SESSION['error'] = "Código incorrecto.";
    header("Location: ../authentication-two-steps.php");
    exit;
}
?>
