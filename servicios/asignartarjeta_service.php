<?php
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'] ?? '';
    $numero_tarjeta = $_POST['numero_tarjeta'] ?? '';
    $pin = $_POST['pin'] ?? '';

    // Validaciones básicas
    if (empty($id_usuario) || empty($numero_tarjeta) || empty($pin)) {
        header("Location: ../asignartarjeta.php?id=$id_usuario&error=1");
        exit;
    }

    if (strlen($numero_tarjeta) !== 16 || !is_numeric($numero_tarjeta)) {
        header("Location: ../asignartarjeta.php?id=$id_usuario&error=2");
        exit;
    }

    if (strlen($pin) !== 4 || !is_numeric($pin)) {
        header("Location: ../asignartarjeta.php?id=$id_usuario&error=3");
        exit;
    }

    try {
        $conn = getDbConnection();

        // Verificar si el usuario existe
        $stmt = $conn->prepare("SELECT id_ FROM user WHERE id_ = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Usuario no encontrado");
        }
        $stmt->close();

        // Crear la tabla tarjetas_local con estructura similar a producción/Pomelo
        $conn->query("CREATE TABLE IF NOT EXISTS tarjetas_local (
            id INT AUTO_INCREMENT PRIMARY KEY,
            card_id VARCHAR(50),
            user_id VARCHAR(50),
            status VARCHAR(20),
            last_four VARCHAR(4),
            provider VARCHAR(50),
            affinity_group_name VARCHAR(100),
            start_date DATETIME,
            fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // DEPURACIÓN: Mostrar datos POST recibidos
        if (!empty($_POST)) {
            echo '<pre>POST: ' . print_r($_POST, true) . '</pre>';
        } else {
            echo 'No llegaron datos por POST.';
            exit;
        }

        // Simular datos similares a los que regresaría Pomelo
        $card_id = $numero_tarjeta;
        $status = 'ACTIVA';
        $last_four = substr($numero_tarjeta, -4);
        $provider = 'LOCAL';
        $affinity_group_name = 'PRUEBA';
        $start_date = date('Y-m-d H:i:s');

        // DEPURACIÓN: Mostrar datos a insertar
        echo '<pre>Insertando: ' . print_r([
            'card_id' => $card_id,
            'user_id' => $id_usuario,
            'status' => $status,
            'last_four' => $last_four,
            'provider' => $provider,
            'affinity_group_name' => $affinity_group_name,
            'start_date' => $start_date
        ], true) . '</pre>';

        $stmt = $conn->prepare("INSERT INTO tarjetas_local (card_id, user_id, status, last_four, provider, affinity_group_name, start_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $card_id, $id_usuario, $status, $last_four, $provider, $affinity_group_name, $start_date);
        if ($stmt->execute()) {
            header("Location: ../asignartarjeta.php?id=$id_usuario&success=1");
            exit;
        } else {
            echo "Error al asignar la tarjeta: " . $stmt->error;
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Error en asignartarjeta_service.php: " . $e->getMessage());
        // Mostrar el error en pantalla para depuración
        echo "<pre>Error al asignar la tarjeta: " . $e->getMessage() . "</pre>";
        exit;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    header("Location: ../usuarios.php");
    exit;
} 