<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Leer el contenido del archivo SQL
    $sql = file_get_contents('actualizar_estructura.sql');
    
    // Dividir el archivo en consultas individuales
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Ejecutar cada consulta
    foreach ($queries as $query) {
        if (!empty($query)) {
            echo "Ejecutando consulta:\n$query\n";
            if ($conn->query($query)) {
                echo "Ejecutado con éxito\n";
            } else {
                throw new Exception("Error en la consulta: " . $conn->error);
            }
            echo "----------------------------------------\n";
        }
    }
    
    echo "\nEstructura actualizada exitosamente.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 