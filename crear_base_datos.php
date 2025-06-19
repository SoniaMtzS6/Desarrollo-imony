<?php
$host = "localhost";
$usuario = "root";
$password = "";
$basedatos = "adminfinister";

try {
    // Conectar sin seleccionar base de datos
    $conn = new mysqli($host, $usuario, $password);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Crear la base de datos si no existe
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS $basedatos")) {
        die("Error al crear la base de datos: " . $conn->error);
    }
    
    echo "Base de datos verificada/creada exitosamente\n";
    
    // Seleccionar la base de datos
    if (!$conn->select_db($basedatos)) {
        die("Error al seleccionar la base de datos: " . $conn->error);
    }
    
    // Crear la tabla empresas
    $sql = "CREATE TABLE IF NOT EXISTS empresas (
        ID_EMPRESA INT AUTO_INCREMENT PRIMARY KEY,
        NOMBRE_EMPRESA VARCHAR(255) NOT NULL,
        RFC VARCHAR(13),
        ALIAS VARCHAR(255),
        PAIS VARCHAR(100),
        ESTADO VARCHAR(100),
        CODIGO_POSTAL VARCHAR(10),
        MONTO_MAXIMO DECIMAL(15,2) DEFAULT 0,
        NUMERO_TARJETAS INT DEFAULT 0,
        ESTATUS ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
    )";
    
    if (!$conn->query($sql)) {
        die("Error al crear la tabla empresas: " . $conn->error);
    }
    
    echo "Tabla empresas creada exitosamente\n";
    
    // Crear la tabla administradores
    $sql = "CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        telefono VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        perfil ENUM('Superadministrador','Administrador') NOT NULL,
        idEmpresa INT,
        direccion TEXT,
        activo TINYINT(1) DEFAULT 1,
        dato_extra TEXT,
        image VARCHAR(255),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_password_temporary TINYINT(1) DEFAULT 1,
        FOREIGN KEY (idEmpresa) REFERENCES empresas(ID_EMPRESA)
    )";
    
    if (!$conn->query($sql)) {
        die("Error al crear la tabla administradores: " . $conn->error);
    }
    
    echo "Tabla administradores creada exitosamente\n";
    
    // Crear la tabla users
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id_ INT AUTO_INCREMENT PRIMARY KEY,
        id VARCHAR(50),
        name VARCHAR(255) NOT NULL,
        surname VARCHAR(255),
        identification_type VARCHAR(50),
        identification_value VARCHAR(50),
        birthdate DATE,
        gender VARCHAR(20),
        email VARCHAR(255),
        phone VARCHAR(20),
        tax_identification_type VARCHAR(50),
        tax_identification_value VARCHAR(50),
        nationality VARCHAR(100),
        tax_condition VARCHAR(100),
        status VARCHAR(20),
        operation_country VARCHAR(100),
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        id_empresa INT,
        password VARCHAR(255),
        is_password_temporary TINYINT(1) DEFAULT 1,
        id_account VARCHAR(50),
        token VARCHAR(255),
        FOREIGN KEY (id_empresa) REFERENCES empresas(ID_EMPRESA)
    )";
    
    if (!$conn->query($sql)) {
        die("Error al crear la tabla users: " . $conn->error);
    }
    
    echo "Tabla users creada exitosamente\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 