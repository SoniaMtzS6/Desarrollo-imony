<?php
require_once __DIR__ . '/../functions.php';

session_start();

if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: ../index.php");
    exit;
}

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idAdmin = $_POST["idadmin"] ?? null;
    $action = $_POST["action"] ?? 'guardar';

    if (!$idAdmin) {
        die("Falta el ID del administrador.");
    }

    $tableName = 'administradores';

    switch ($action) {
        case 'guardar':
            $nombre = $_POST["nombre"] ?? '';
            $idEmpresa = $_POST["idEmpresa"] ?? null;
            $email = $_POST["email"] ?? '';
            $alias = $_POST["alias"] ?? '';
            $perfil = $_POST["perfil"] ?? '';
            $telefono = $_POST["telefono"] ?? '';
            $direccion = $_POST["direccion"] ?? '';

            $stmt = $conn->prepare("UPDATE {$tableName} SET nombre = ?, idEmpresa = ?, email = ?, alias = ?, perfil = ?, telefono = ?, direccion = ? WHERE id = ?");
            $stmt->bind_param("sisssssi", $nombre, $idEmpresa, $email, $alias, $perfil, $telefono, $direccion, $idAdmin);
            
            if ($stmt->execute()) {
                header("Location: ../administradores.php?status=success_update");
            } else {
                echo "Error al actualizar: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'bloquear':
            $stmt = $conn->prepare("UPDATE {$tableName} SET status = 'BLOCKED' WHERE id = ?");
            $stmt->bind_param("i", $idAdmin);
            if ($stmt->execute()) {
                header("Location: ../administradores.php?status=success_block");
            } else {
                echo "Error al bloquear: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'desbloquear':
            $stmt = $conn->prepare("UPDATE {$tableName} SET status = 'ACTIVE' WHERE id = ?");
            $stmt->bind_param("i", $idAdmin);
            if ($stmt->execute()) {
                header("Location: ../administradores.php?status=success_unblock");
            } else {
                echo "Error al desbloquear: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'eliminar':
            $stmt = $conn->prepare("UPDATE {$tableName} SET status = 'DELETED' WHERE id = ?");
            $stmt->bind_param("i", $idAdmin);
            if ($stmt->execute()) {
                header("Location: ../administradores.php?status=success_delete");
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
