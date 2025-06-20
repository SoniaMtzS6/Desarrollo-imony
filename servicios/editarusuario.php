<?php require_once __DIR__ . '/../functions.php'; ?>
<?php
// Mostrar errores (solo para debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: ../authentication-two-steps.php");
    exit;
}

try {
    $conn = getDbConnection();
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    // Obtener datos del formulario
    $iduser   = $_POST['iduser'] ?? null;
    $nombre    = $_POST['nombre'] ?? '';
    $email     = $_POST['email'] ?? '';
    $telefono  = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $id_empresa = $_POST['id_empresa'] ?? null;

    // Validación básica
    if (!$iduser) {
        die("ID de usuario no proporcionado.");
    }

    // Manejo de acciones de bloqueo/desbloqueo y eliminación lógica
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'bloquear') {
        // Código local
        $stmt = $conn->prepare("SELECT status FROM user WHERE id_ = ?");
        $stmt->bind_param("i", $iduser);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        $nuevo_estado = ($user && $user['status'] == 'ACTIVE') ? 'BLOCKED' : 'ACTIVE';

        $stmt = $conn->prepare("UPDATE user SET status = ? WHERE id_ = ?");
        $stmt->bind_param("si", $nuevo_estado, $iduser);
        
        if (!$stmt->execute()) {
            die("Error al actualizar el estado del usuario: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        header("Location: ../edituser.php?id=" . $iduser . "&status_update=success");
        exit;
    }

    if ($accion === 'eliminar') {
        // Código local
        $stmt = $conn->prepare("UPDATE user SET status = 'DELETED' WHERE id_ = ?");
        $stmt->bind_param("i", $iduser);

        if (!$stmt->execute()) {
            die("Error al eliminar el usuario: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        header("Location: ../usuarios.php?status=eliminado");
        exit;
    }

    // Actualizar datos del usuario SOLO si no es bloquear/eliminar
    $stmt = $conn->prepare("
        UPDATE user SET 
            nombre = ?, 
            email = ?, 
            telefono = ?, 
            direccion = ?,
            id_empresa = ?
        WHERE id_ = ?
    ");

    $stmt->bind_param(
        "ssssii",
        $nombre,
        $email,
        $telefono,
        $direccion,
        $id_empresa,
        $iduser
    );

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        header("Location: ../edituser.php?id=" . $iduser . "&status_update=success");
        exit;
    } else {
        echo "Error al actualizar usuario: " . $stmt->error;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?> 