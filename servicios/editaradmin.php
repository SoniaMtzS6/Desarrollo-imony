<?php require_once __DIR__ . '/../functions.php'; ?>
<?php
// Mostrar errores (solo para debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

try {
    $conn = getDbConnection();
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    // Obtener datos del formulario
    $idadmin   = $_POST['idadmin'] ?? null;
    $nombre    = $_POST['nombre'] ?? '';
    $email     = $_POST['email'] ?? '';
    $telefono  = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $perfil    = $_POST['perfil'] ?? '';
    $id_empresa = $_POST['id_empresa'] ?? null;

    // Validación básica
    if (!$idadmin) {
        die("ID de administrador no proporcionado.");
    }

    // Manejo de acciones de bloqueo/desbloqueo y eliminación lógica
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'bloquear') {
        // Código de producción comentado
        /*
        // Aquí iría la llamada a la API de producción para bloquear/desbloquear
        */
        // Código local
        $stmt = $conn->prepare("SELECT activo FROM administradores WHERE id = ?");
        $stmt->bind_param("i", $idadmin);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        $nuevo_estado = ($admin && $admin['activo'] == 1) ? 0 : 1;
        $stmt = $conn->prepare("UPDATE administradores SET activo = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo_estado, $idadmin);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ../administradores.php?status=bloqueado");
        exit;
    }

    if ($accion === 'eliminar') {
        // Código de producción comentado
        /*
        // Aquí iría la llamada a la API de producción para eliminar lógicamente
        */
        // Código local
        $stmt = $conn->prepare("UPDATE administradores SET eliminado = 1 WHERE id = ?");
        $stmt->bind_param("i", $idadmin);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ../administradores.php?status=eliminado");
        exit;
    }

    // Actualizar datos del administrador SOLO si no es bloquear/eliminar
    $stmt = $conn->prepare("
        UPDATE administradores SET 
            nombre = ?, 
            email = ?, 
            telefono = ?, 
            direccion = ?, 
            perfil = ?, 
            id_empresa = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssssi",
        $nombre,
        $email,
        $telefono,
        $direccion,
        $perfil,
        $id_empresa,
        $idadmin
    );

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        header("Location: ../administradores.php?status=updated");
        exit;
    } else {
        echo "Error al actualizar administrador: " . $stmt->error;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

/* Código de PRODUCCIÓN comentado
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/admin/update',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS => json_encode([
    'idAdmin' => $idadmin,
    'nombre' => $nombre,
    'email' => $email,
    'telefono' => $telefono,
    'direccion' => $direccion,
    'perfil' => $perfil,
    'idEmpresa' => $id_empresa
  ]),
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

if ($response) {
    header("Location: ../administradores.php?status=updated");
    exit;
} else {
    echo "Error al actualizar el administrador";
}
*/
?>
