<?php require_once __DIR__ . '/../functions.php'; ?>

<?php
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

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $empresa = $_POST['empresa'] ?? '';
    $email = $_POST['email'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $rfc = $_POST['rfc'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $direccion = mb_substr($direccion, 0, 25);

    $legal = array(
        "street_name"=> $direccion,
        "street_number"=> 300,
        "floor"=> 1,
        "apartment"=> " ",
        "zip_code"=> $cp,
        "neighborhood"=> "Sin definir",
        "city"=> "CDMX",
        "region"=> "CDMX",
        "additional_info"=> " ",
        "country"=> "MEX"
    );

    $data = array(
        "id_empresa" => $empresa,
        "name" => $nombre,
        "surname" => $nombre,
        "identification_type" => "INE",
        "identification_value" => str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        "birthdate" => $fecha,
        "gender" => $genero,
        "email" => $email,
        "phone" => $phone,
        "tax_identification_type"=>"RFC",
        "tax_identification_value"=>(int)$rfc,
        "nationality" => "MEX",
        "tax_condition" => "VAT_REGISTERED",
        "operation_country" => "MEX",
        "legal_address" => $legal
    );

    echo "cambio13"; 
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    echo $jsonData;

    // Código de producción
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://37kylcuth7.execute-api.us-east-2.amazonaws.com/DEV/user/api/v1',
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
    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
    }
    curl_close($curl);

    $data = json_decode($response, true); 
    
    /* Código local comentado
    try {
        $conn = getDbConnection();
        
        // Verificar si la empresa existe
        $stmt = $conn->prepare("SELECT ID_EMPRESA FROM empresas WHERE ID_EMPRESA = ?");
        $stmt->bind_param("i", $empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Si la empresa no existe, usar la empresa por defecto (ID 1)
            $empresa = 1;
        }
        $stmt->close();

        // Generar un ID único para el usuario
        $id = uniqid('USR_');
        $status = 'ACTIVE'; // Usuario activo por defecto (debe ser uno de los valores del ENUM)
        
        // Preparar todos los valores antes del bind_param
        $identification_type = "INE";
        $identification_value = random_int(10000000, 99999999); // Número entero para BIGINT
        $tax_identification_type = "RFC";
        $tax_identification_value = (int)$rfc; // Convertir a entero para BIGINT
        $nationality = "MEX";
        $tax_condition = "VAT_REGISTERED";
        $operation_country = "MEX";
        $surname = $nombre; // usando el mismo valor para surname
        $password = password_hash('temporal123', PASSWORD_DEFAULT); // Contraseña temporal por defecto
        $is_password_temporary = 1; // Indicar que es contraseña temporal
        
        // Insertar usuario en la tabla user
        $stmt = $conn->prepare("INSERT INTO user (id, name, surname, identification_type, identification_value, 
            birthdate, gender, email, phone, tax_identification_type, tax_identification_value, 
            nationality, tax_condition, operation_country, status, id_empresa, password, is_password_temporary) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->bind_param("ssssisssssssssiisi", 
            $id, 
            $nombre, 
            $surname,
            $identification_type,
            $identification_value,
            $fecha,
            $genero,
            $email,
            $phone,
            $tax_identification_type,
            $tax_identification_value,
            $nationality,
            $tax_condition,
            $operation_country,
            $status,
            $empresa,
            $password,
            $is_password_temporary
        );
        
        if ($stmt->execute()) {
            $user_id = $id; // El ID del usuario que acabamos de crear

            // Ahora, creamos la cuenta asociada al usuario
            $account_data = [
                "id_empresa" => $empresa,
                "provider" => "POMELO",
                "id_user" => $user_id,
                "currency" => "MXN"
            ];
            $jsonDataAccount = json_encode($account_data);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => getApiBaseUrl() . '/account/api/v1',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $jsonDataAccount,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            
            $response = curl_exec($curl);
            $curl_error = curl_error($curl);
            curl_close($curl);
            
            if ($curl_error) {
                throw new Exception("Error en cURL al crear la cuenta: " . $curl_error);
            }
            
            $account_response = json_decode($response, true);

            if (isset($account_response['data']['id'])) {
                $id_account = $account_response['data']['id'];
                
                // Actualizamos el usuario con el id_account
                $stmt_update = $conn->prepare("UPDATE user SET id_account = ? WHERE id = ?");
                $stmt_update->bind_param("ss", $id_account, $user_id);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Si la API no devuelve un ID de cuenta, lanzamos un error para verlo
                $error_message = isset($account_response['message']) ? $account_response['message'] : 'Respuesta inesperada de la API de cuentas.';
                throw new Exception("Error al crear la cuenta en la API: " . $error_message . " | Respuesta completa: " . $response);
            }

            header("Location: ../usuarios.php?creada=1");
        } else {
            throw new Exception("Error al crear el usuario: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        error_log("Error en cargaruser.php: " . $e->getMessage());
        header("Location: ../usuarios.php?error=1");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
    */
    exit;
} else {
    echo "No se recibió ninguna solicitud POST.";
}
?>
