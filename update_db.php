<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Leer el contenido del archivo SQL
    $sql = file_get_contents('update_empresas.sql');
    
    // Ejecutar las consultas
    if ($conn->multi_query($sql)) {
        do {
            // Consumir los resultados
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
    
    echo "Base de datos actualizada exitosamente";
} catch (Exception $e) {
    echo "Error al actualizar la base de datos: " . $e->getMessage();
}
?> 