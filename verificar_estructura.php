<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Mostrar la estructura de la tabla empresas
    $result = $conn->query("DESCRIBE empresas");
    
    echo "Estructura de la tabla empresas:\n";
    echo "==============================\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "Campo: " . $row['Field'] . "\n";
        echo "Tipo: " . $row['Type'] . "\n";
        echo "Nulo: " . $row['Null'] . "\n";
        echo "Por defecto: " . $row['Default'] . "\n";
        echo "------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 