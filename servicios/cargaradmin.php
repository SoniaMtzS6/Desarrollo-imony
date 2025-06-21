<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../functions.php';
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

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $email = $_POST['email'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $idEmpresa = $_POST['idEmpresa'] ?? '';
    $perfil = $_POST['perfil'] ?? '';
    $activo = 1; // Por defecto, el administrador se crea como activo
    $fecha_creacion = date('Y-m-d H:i:s');
    $is_password_temporary = 1; // La contraseña es temporal
    
    try {
        $conn = getDbConnection();
        
        // Generar un password por defecto (puede ser cambiado después)
        $password = password_hash('123456', PASSWORD_DEFAULT);
        
        // Preparar la consulta SQL
        $stmt = $conn->prepare("INSERT INTO administradores (nombre, email, password, telefono, idEmpresa, perfil, activo, fecha_creacion, is_password_temporary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssisi", $nombre, $email, $password, $telefono, $idEmpresa, $perfil, $activo, $fecha_creacion, $is_password_temporary);
        
        if ($stmt->execute()) {
            header("Location: ../administradores.php?creada=1");
            exit;
        } else {
            echo "Error al crear el administrador: " . $stmt->error;
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Código de PRODUCCIÓN
$data = array(
    "nombre" => $nombre,
    "email" => $email,
    "telefono" => $telefono,
    "idEmpresa" => (int)$idEmpresa,
    "perfil" => $perfil
);

echo "cambio15"; 
// Convertir los datos a formato JSON
$jsonData = json_encode($data, JSON_PRETTY_PRINT);

 echo $jsonData;

$curl = curl_init();

curl_setopt_array($curl, array(
CURLOPT_URL => 'https://9kjot10cte.execute-api.us-east-2.amazonaws.com/dev/admin/create',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => $jsonData,
CURLOPT_HTTPHEADER => array(
'Content-Type: application/json'
),
));

$response = curl_exec($curl);

$datare ="";
$datare = json_decode($response, true); 
if (isset($data['OK'])) {  
    echo "Tu usuarios es el: ".$data['OK'];
     header("Location: ../administradores.php?creada=1");
     exit;
} else {
    echo "no se cargo con exito";
    header("Location: ../administradores.php?creada=1");
    exit;
}

/* Código local comentado
try {
    $conn = getDbConnection();
    
    // Generar un password por defecto (puede ser cambiado después)
    $password = password_hash('123456', PASSWORD_DEFAULT);
    
    // Preparar la consulta SQL
    $stmt = $conn->prepare("INSERT INTO administradores (nombre, email, password, telefono, idEmpresa, perfil, activo, fecha_creacion, is_password_temporary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisi", $nombre, $email, $password, $telefono, $idEmpresa, $perfil, $activo, $fecha_creacion, $is_password_temporary);
    
    if ($stmt->execute()) {
        header("Location: ../administradores.php?creada=1");
        exit;
    } else {
        echo "Error al crear el administrador: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
*/
?>