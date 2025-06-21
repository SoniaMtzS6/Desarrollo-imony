<?php
require_once 'functions.php';

$conn = getDbConnection();

// Insertar usuario de prueba
$stmt = $conn->prepare("INSERT INTO usuarios (NOMBRE, EMAIL) VALUES (?, ?)");
$nombre = "Usuario Prueba";
$email = "prueba@test.com";
$stmt->bind_param("ss", $nombre, $email);

if ($stmt->execute()) {
    $id_usuario = $stmt->insert_id;
    echo "Usuario creado exitosamente con ID: " . $id_usuario . "\n";
    
    // Insertar tarjeta de prueba
    $stmt = $conn->prepare("INSERT INTO tarjetas (ID_USUARIO, NUMERO_TARJETA, PIN, ESTATUS, SALDO) VALUES (?, ?, ?, 'ACTIVA', 1000.00)");
    $numero_tarjeta = "1234567890123456";
    $pin = "1234";
    $stmt->bind_param("iss", $id_usuario, $numero_tarjeta, $pin);
    
    if ($stmt->execute()) {
        echo "Tarjeta creada exitosamente\n";
    } else {
        echo "Error al crear la tarjeta: " . $stmt->error . "\n";
    }
} else {
    echo "Error al crear el usuario: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?> 