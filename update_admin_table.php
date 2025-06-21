<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Agregar columna FECHA_REGISTRO si no existe
    $result = $conn->query("SHOW COLUMNS FROM administradores LIKE 'FECHA_REGISTRO'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE administradores ADD COLUMN FECHA_REGISTRO DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "Columna FECHA_REGISTRO agregada\n";
    }
    
    // Actualizar registros existentes que no tengan fecha
    $conn->query("UPDATE administradores SET FECHA_REGISTRO = CURRENT_TIMESTAMP WHERE FECHA_REGISTRO IS NULL");
    echo "Fechas actualizadas para registros existentes\n";
    
    // Mostrar la estructura actual de la tabla
    $result = $conn->query("DESCRIBE administradores");
    echo "\nEstructura actual de la tabla administradores:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    // Mostrar los registros actuales
    $result = $conn->query("SELECT * FROM administradores");
    echo "\nRegistros en la tabla administradores:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['ID_ADMIN'] . 
             ", Nombre: " . $row['NOMBRE'] . 
             ", Email: " . $row['EMAIL'] . 
             ", Perfil: " . $row['PERFIL'] . 
             ", Empresa: " . $row['ID_EMPRESA'] . 
             ", Fecha: " . $row['FECHA_REGISTRO'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 