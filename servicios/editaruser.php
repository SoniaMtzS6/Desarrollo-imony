<?php require_once __DIR__ . '/../functions.php'; ?>
<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: authentication-two-steps.php");
    exit;
}

// Conexión a la base de datos





$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["iduser"] ?? null;
    $nombre = $_POST["nombre"] ?? '';
    $empresa = $_POST["empresa"] ?? '';
    $email = $_POST["email"] ?? '';
    $genero = $_POST["genero"] ?? '';
    $fecha = $_POST["fecha"] ?? '';
    $telefono = $_POST["phone"] ?? '';

    if (!$id || empty($nombre) || empty($email)) {
        die("Faltan datos obligatorios.");
    }

    // Intentar primero con 'user', si falla probar con 'users'
    try {
        $stmt = $conn->prepare("UPDATE user SET name = ?, id_empresa = ?, email = ?, gender = ?, birthdate = ?, phone = ? WHERE id_ = ?");
        if (!$stmt) throw new Exception($conn->error);
    } catch (Exception $e) {
        // Si falla, intentamos con 'users'
        try {
            $stmt = $conn->prepare("UPDATE users SET name = ?, id_empresa = ?, email = ?, gender = ?, birthdate = ?, phone = ? WHERE id_ = ?");
            if (!$stmt) throw new Exception($conn->error);
        } catch (Exception $e2) {
            die("No se encontró la tabla 'user' ni 'users' en la base de datos.");
        }
    }

    $stmt->bind_param("sissssi", $nombre, $empresa, $email, $genero, $fecha, $telefono, $id);

    if ($stmt->execute()) {
        header("Location: ../usuarios.php?status=success");
        exit;
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Método no permitido.";
}

$conn->close();
?>
