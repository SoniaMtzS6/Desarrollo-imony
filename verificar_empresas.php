<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Mostrar todas las empresas
    $result = $conn->query("SELECT * FROM empresas");
    
    echo "Contenido actual de la tabla empresas:\n";
    echo "=====================================\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['ID_EMPRESA'] . "\n";
        echo "Nombre: " . $row['NOMBRE_EMPRESA'] . "\n";
        echo "RFC: " . $row['RFC'] . "\n";
        echo "Alias: " . $row['ALIAS'] . "\n";
        echo "País: " . $row['PAIS'] . "\n";
        echo "Estado: " . $row['ESTADO'] . "\n";
        echo "Código Postal: " . $row['CODIGO_POSTAL'] . "\n";
        echo "Monto Máximo: " . $row['MONTO_MAXIMO'] . "\n";
        echo "Número de Tarjetas: " . $row['NUMERO_TARJETAS'] . "\n";
        echo "Estatus: " . $row['ESTATUS'] . "\n";
        echo "-------------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 