<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Mostrar las tablas disponibles
    $result = $conn->query("SHOW TABLES");
    echo "Tablas disponibles:\n";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
    
    echo "\nEstructura de la tabla empresas:\n";
    $result = $conn->query("DESCRIBE empresas");
    if (!$result) {
        die("Error al describir la tabla: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
?> 