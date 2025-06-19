<?php

$servername = "localhost";
$username = "root";
$password = "";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS adminfinister";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada exitosamente\n";
} else {
    echo "Error al crear la base de datos: " . $conn->error . "\n";
}

// Seleccionar la base de datos
$conn->select_db("adminfinister");

// Crear tabla usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    ID_USUARIO INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE VARCHAR(100) NOT NULL,
    EMAIL VARCHAR(100) UNIQUE NOT NULL,
    FECHA_CREACION TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla usuarios creada exitosamente\n";
} else {
    echo "Error al crear la tabla usuarios: " . $conn->error . "\n";
}

// Crear tabla empresas
$sql = "CREATE TABLE IF NOT EXISTS empresas (
    ID_EMPRESA INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_EMPRESA VARCHAR(255) NOT NULL,
    MONTO_MAXIMO DECIMAL(10,2) DEFAULT 0.00,
    NUMERO_TARJETAS INT DEFAULT 0,
    ESTATUS ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    FECHA_REGISTRO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla empresas creada exitosamente\n";
    
    // Insertar empresa de prueba
    $sql = "INSERT INTO empresas (NOMBRE_EMPRESA, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS) 
            VALUES ('Empresa de Prueba', 10000.00, 5, 'ACTIVE')";
    if ($conn->query($sql) === TRUE) {
        echo "Empresa de prueba creada exitosamente\n";
    } else {
        echo "Error al crear empresa de prueba: " . $conn->error . "\n";
    }
} else {
    echo "Error al crear la tabla empresas: " . $conn->error . "\n";
}

// Crear tabla tarjetas
$sql = "CREATE TABLE IF NOT EXISTS tarjetas (
    ID_TARJETA INT AUTO_INCREMENT PRIMARY KEY,
    ID_USUARIO INT,
    NUMERO_TARJETA VARCHAR(16) NOT NULL,
    PIN VARCHAR(4) NOT NULL,
    FECHA_ASIGNACION TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ESTATUS ENUM('ACTIVA', 'INACTIVA', 'BLOQUEADA') DEFAULT 'INACTIVA',
    SALDO DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (ID_USUARIO) REFERENCES usuarios(ID_USUARIO)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla tarjetas creada exitosamente\n";
} else {
    echo "Error al crear la tabla tarjetas: " . $conn->error . "\n";
}

// Crear tabla transacciones
$sql = "CREATE TABLE IF NOT EXISTS transacciones (
    ID_TRANSACCION INT AUTO_INCREMENT PRIMARY KEY,
    ID_TARJETA INT,
    TIPO ENUM('DEPOSITO', 'RETIRO') NOT NULL,
    MONTO DECIMAL(10,2) NOT NULL,
    FECHA TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_TARJETA) REFERENCES tarjetas(ID_TARJETA)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla transacciones creada exitosamente\n";
} else {
    echo "Error al crear la tabla transacciones: " . $conn->error . "\n";
}

$conn->close();
?> 