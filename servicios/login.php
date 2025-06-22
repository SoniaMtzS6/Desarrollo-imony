<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../functions.php';

// Crear conexión
$conn = getDbConnection();

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Validar que venga vía POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        die("Email y contraseña son obligatorios.");
    }

    // Consulta preparada para evitar SQL Injection
    $stmt = $conn->prepare("SELECT * FROM administradores WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Validar la contraseña usando password_verify
        //if (password_verify($password, $usuario["password"])) {
        if ($password == $usuario["password"]) {
            // Generar token de 6 dígitos
            $token = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            
            // Guardar token en campo dato_extra
            $updateStmt = $conn->prepare("UPDATE administradores SET dato_extra = ? WHERE id = ?");
            $updateStmt->bind_param("si", $token, $usuario["id"]);
            $updateStmt->execute();
            $updateStmt->close();


            // Guardar token en sesión para local
            /*
            $_SESSION['token'] = $token;

            // Guardar datos en sesión
            $_SESSION["usuario"] = [
                "id" => $usuario["id"],
                "email" => $usuario["email"],
                "nombre" => $usuario["nombre"],
                "perfil" => $usuario["perfil"],
                "id_empresa" => $usuario["idEmpresa"],
                "doblefactor" => "0"
            ];

            // Código local para enviar email
            /*
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://localhost:3001/email',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "to": "'.$usuario["email"].'",
                    "subject": "Código de verificación",
                    "body": "Tu código de acceso es '.$token.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            */

            // Código de PRODUCCIÓN
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://v6g3vgism2.execute-api.us-east-2.amazonaws.com/dev/sendMail',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "to": "'.$usuario["email"].'",
                    "subject": "Código de verificación",
                    "body": "Tu código de acceso es '.$token.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // Guardar datos en sesión
            $_SESSION["usuario"] = [
                "id" => $usuario["id"],
                "email" => $usuario["email"],
                "nombre" => $usuario["nombre"],
                "perfil" => $usuario["perfil"],
                "id_empresa" => $usuario["idEmpresa"],
                "doblefactor" => "0"
            ];


            // Redirigir al segundo paso de autenticación
            header("Location: ../authentication-two-steps.php");
            exit;
        } else {
            echo "Contraseña incorrecta.";
            // header("Location: ../index.php");
        }
    } else {
        echo "Usuario no encontrado.";
        header("Location: ../index.php");
    }

    $stmt->close();
} else {
    echo "Método no permitido.";
    header("Location: ../index.php");
}

$conn->close();
?>
