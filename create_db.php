<?php
$host = "localhost";
$usuarioDB = "root";
$passwordDB = "";

try {
    // Conectar sin seleccionar base de datos
    $conn = new mysqli($host, $usuarioDB, $passwordDB);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Crear la base de datos si no existe
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS fisinter")) {
        die("Error al crear la base de datos: " . $conn->error);
    }
    
    echo "Base de datos creada o ya existente\n";
    
    // Seleccionar la base de datos
    if (!$conn->select_db("fisinter")) {
        die("Error al seleccionar la base de datos: " . $conn->error);
    }
    
    // Crear la tabla empresas
    $sql = "CREATE TABLE IF NOT EXISTS empresas (
        ID_EMPRESA INT AUTO_INCREMENT PRIMARY KEY,
        NOMBRE_EMPRESA VARCHAR(255) NOT NULL,
        RFC VARCHAR(13) NOT NULL,
        ALIAS VARCHAR(255),
        PAIS VARCHAR(100),
        ESTADO VARCHAR(100),
        CODIGO_POSTAL VARCHAR(10),
        MONTO_MAXIMO DECIMAL(15,2) DEFAULT 0,
        NUMERO_TARJETAS INT DEFAULT 0,
        ESTATUS ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
        FECHA_REGISTRO DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        die("Error al crear la tabla empresas: " . $conn->error);
    }
    
    echo "Tabla empresas creada exitosamente\n";
    
    // Crear otras tablas necesarias del archivo db_structure.sql
    $structureSQL = file_get_contents('db_structure.sql');
    $queries = explode(';', $structureSQL);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$conn->query($query)) {
                echo "Error en la consulta: " . $conn->error . "\n";
                echo "Consulta: " . $query . "\n";
            }
        }
    }
    
    echo "Estructura de base de datos creada exitosamente";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 