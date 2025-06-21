<?php

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

// Código de producción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;
    $acc = $_POST['acc'] ?? null; 
    $card = $_POST['card'] ?? null; 
    
    $token="";
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.pomelo.la/oauth/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "client_id": "cxq8Yq6wFgE53FxOzgHAGrCzRe5n4KgL",
        "client_secret": "hfGy54beNj08H6v7AnvTgo2g5zYGXZ9kyTdtpEHs5b_Vzgv1WDypnyFUz6273YO9",
        "audience": "https://auth-prod.pomelo.la",
        "grant_type": "client_credentials"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $data = json_decode($response, true); 
    $token=$data['access_token'];
    curl_close($curl);

    echo $token;

    if ($action === 'asociar') {
        if ($response) {
            if (isset($token)) {   

                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.pomelo.la/cards/associations/v1/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "card_id": "'.$card.'",
                    "account_id": "'.$acc.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-type: application/json; charset=UTF-8',
                    'Authorization: Bearer '.$token
                ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                echo $response;

                echo "Se asocia la tarjeta y la cuenta";
                header("Location: ../usuarios.php?id=".$id."&acc=".$acc);
            }    
        }
    } elseif ($action === 'eliminar') {
        
        header("Location: ../usuarios.php?id=".$id."&acc=".$acc);
        echo "Se eliminara la tarjeta  el monto.";
    } else {
        echo "Acción no válida.";
    }
}

/* Código local comentado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;
    $card = $_POST['card'] ?? null;

    require_once __DIR__ . '/../functions.php';
    $conn = getDbConnection();

    if ($action === 'asociar') {
        // Actualizar el estado de la tarjeta a ACTIVA
        $stmt = $conn->prepare("UPDATE tarjetas SET ESTATUS = 'ACTIVA' WHERE ID_TARJETA = ? AND ID_USUARIO = ?");
        $stmt->bind_param("ii", $card, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: ../usuarios.php?success=1");
    } elseif ($action === 'eliminar') {
        // Actualizar el estado de la tarjeta a INACTIVA
        $stmt = $conn->prepare("UPDATE tarjetas SET ESTATUS = 'INACTIVA' WHERE ID_TARJETA = ? AND ID_USUARIO = ?");
        $stmt->bind_param("ii", $card, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: ../usuarios.php?success=2");
    } else {
        header("Location: ../usuarios.php?error=3");
    }
    $conn->close();
}
*/
?>