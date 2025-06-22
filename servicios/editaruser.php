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

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["iduser"] ?? null;
    $action = $_POST["action"] ?? 'guardar'; // Por defecto es 'guardar'

    if (!$id) {
        die("Falta el ID de usuario.");
    }

    $tableName = '';
    // Intentar determinar el nombre de la tabla
    $resUser = $conn->query("SHOW TABLES LIKE 'user'");
    if ($resUser->num_rows > 0) {
        $tableName = 'user';
    } else {
        $resUsers = $conn->query("SHOW TABLES LIKE 'users'");
        if ($resUsers->num_rows > 0) {
            $tableName = 'users';
        } else {
            die("No se encontró la tabla 'user' ni 'users' en la base de datos.");
        }
    }

    switch ($action) {
        case 'guardar':
            $nombre = $_POST["nombre"] ?? '';
            $empresa = $_POST["empresa"] ?? '';
            $email = $_POST["email"] ?? '';
            $genero = $_POST["genero"] ?? '';
            $fecha = $_POST["fecha"] ?? '';
            $telefono = $_POST["phone"] ?? '';

            if (empty($nombre) || empty($email)) {
                die("Faltan datos obligatorios para guardar.");
            }

            $stmt = $conn->prepare("UPDATE {$tableName} SET name = ?, id_empresa = ?, email = ?, gender = ?, birthdate = ?, phone = ? WHERE id_ = ?");
            $stmt->bind_param("sissssi", $nombre, $empresa, $email, $genero, $fecha, $telefono, $id);

            if ($stmt->execute()) {
                header("Location: ../usuarios.php?status=success_update");
            } else {
                echo "Error al actualizar: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'bloquear':
            // Asumiendo que la columna se llama 'status' y el valor para bloqueado es 'blocked'
            $stmt = $conn->prepare("UPDATE {$tableName} SET status = 'blocked' WHERE id_ = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header("Location: ../usuarios.php?status=success_block");
            } else {
                echo "Error al bloquear: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'eliminar':
            $stmt = $conn->prepare("DELETE FROM {$tableName} WHERE id_ = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header("Location: ../usuarios.php?status=success_delete");
            } else {
                echo "Error al eliminar: " . $stmt->error;
            }
            $stmt->close();
            break;
        
        default:
            echo "Acción no válida.";
            break;
    }

} else {
    echo "Método no permitido.";
}

$conn->close();
?>
