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

// Código de producción comentado
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $monto = $_POST['monto'] ?? null;
    $usr = $_POST['usr'] ?? null;
    $acc = $_POST['acc'] ?? null;

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

    if ($action === 'asignar') {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.pomelo.la/core/transactions/v1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "amount": '.$monto.',
            "currency": "MXN",
            "description": "Carga de saldo",
            "source_account_id": "acc_2Ry4lBZnQxSX8Vkj",
            "target_account_id": "'.$acc.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-type: application/json; charset=UTF-8',
            'Authorization: Bearer '.$token
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        header("Location: ../edituser.php?id=".$usr."&acc=".$acc);
    } elseif ($action === 'retirar') {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.pomelo.la/core/transactions/v1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "amount": '.$monto.',
            "currency": "MXN",
            "description": "Retiro de saldo",
            "source_account_id": "'.$acc.'",
            "target_account_id": "acc_2Ry4lBZnQxSX8Vkj"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-type: application/json; charset=UTF-8',
            'Authorization: Bearer '.$token
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        header("Location: ../edituser.php?id=".$usr."&acc=".$acc);
    } else {
        echo "Acción no válida.";
    }
}
*/

// Código local
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../functions.php';
    
    $action = $_POST['action'] ?? null;
    $monto = $_POST['monto'] ?? null;
    $usr = $_POST['usr'] ?? null;
    
    if (empty($action) || empty($monto) || empty($usr) || !is_numeric($monto)) {
        header("Location: ../edituser.php?id=".$usr."&error=1");
        exit;
    }
    
    $conn = getDbConnection();
    
    // Verificar si el usuario tiene una tarjeta activa
    $stmt = $conn->prepare("SELECT ID_TARJETA, SALDO FROM tarjetas WHERE ID_USUARIO = ? AND ESTATUS = 'ACTIVA' LIMIT 1");
    $stmt->bind_param("i", $usr);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        header("Location: ../edituser.php?id=".$usr."&error=2");
        exit;
    }
    
    $tarjeta = $result->fetch_assoc();
    $stmt->close();
    
    if ($action === 'asignar') {
        // Agregar saldo
        $nuevo_saldo = $tarjeta['SALDO'] + $monto;
        $stmt = $conn->prepare("UPDATE tarjetas SET SALDO = ? WHERE ID_TARJETA = ?");
        $stmt->bind_param("di", $nuevo_saldo, $tarjeta['ID_TARJETA']);
        
    } elseif ($action === 'retirar') {
        // Verificar si hay saldo suficiente
        if ($tarjeta['SALDO'] < $monto) {
            $conn->close();
            header("Location: ../edituser.php?id=".$usr."&error=3");
            exit;
        }
        
        // Retirar saldo
        $nuevo_saldo = $tarjeta['SALDO'] - $monto;
        $stmt = $conn->prepare("UPDATE tarjetas SET SALDO = ? WHERE ID_TARJETA = ?");
        $stmt->bind_param("di", $nuevo_saldo, $tarjeta['ID_TARJETA']);
    } else {
        $conn->close();
        header("Location: ../edituser.php?id=".$usr."&error=4");
        exit;
    }
    
    if ($stmt->execute()) {
        // Registrar la transacción
        $tipo = ($action === 'asignar') ? 'DEPOSITO' : 'RETIRO';
        $stmt = $conn->prepare("INSERT INTO transacciones (ID_TARJETA, TIPO, MONTO, FECHA) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isd", $tarjeta['ID_TARJETA'], $tipo, $monto);
        $stmt->execute();
        $stmt->close();
        
        header("Location: ../edituser.php?id=".$usr."&success=1");
    } else {
        header("Location: ../edituser.php?id=".$usr."&error=5");
    }
    
    $conn->close();
}
?>