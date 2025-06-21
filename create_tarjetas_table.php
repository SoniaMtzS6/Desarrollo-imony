<?php
require_once 'functions.php';

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS tarjetas (
    ID_TARJETA INT AUTO_INCREMENT PRIMARY KEY,
    ID_USUARIO INT NOT NULL,
    NUMERO_TARJETA VARCHAR(16) NOT NULL,
    PIN VARCHAR(4) NOT NULL,
    FECHA_ASIGNACION TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ESTATUS ENUM('ACTIVA', 'INACTIVA', 'BLOQUEADA') DEFAULT 'ACTIVA',
    FOREIGN KEY (ID_USUARIO) REFERENCES usuarios(ID_USUARIO)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla tarjetas creada exitosamente\n";
} else {
    echo "Error al crear la tabla: " . $conn->error . "\n";
}

$conn->close(); 