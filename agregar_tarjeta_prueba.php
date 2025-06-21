<?php
require_once 'functions.php';
$conn = getDbConnection();

$id_usuario = 1;
$numero_tarjeta = '1234567890123456';
$pin = '1234';
$estatus = 'ACTIVA';

$stmt = $conn->prepare("INSERT INTO tarjetas (ID_USUARIO, NUMERO_TARJETA, PIN, ESTATUS) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id_usuario, $numero_tarjeta, $pin, $estatus);
if ($stmt->execute()) {
    echo "Tarjeta de prueba agregada correctamente.";
} else {
    echo "Error al agregar tarjeta: " . $stmt->error;
}
$stmt->close();
$conn->close();
?> 