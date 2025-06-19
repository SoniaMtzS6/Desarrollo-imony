<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Array de consultas ALTER TABLE
    $alterQueries = [
        "ALTER TABLE empresas DROP COLUMN DIRECCION",
        "ALTER TABLE empresas DROP COLUMN TELEFONO",
        "ALTER TABLE empresas DROP COLUMN EMAIL",
        "ALTER TABLE empresas ADD COLUMN RFC VARCHAR(13) NOT NULL DEFAULT ''",
        "ALTER TABLE empresas ADD COLUMN ALIAS VARCHAR(255)",
        "ALTER TABLE empresas ADD COLUMN PAIS VARCHAR(100)",
        "ALTER TABLE empresas ADD COLUMN ESTADO VARCHAR(100)",
        "ALTER TABLE empresas ADD COLUMN CODIGO_POSTAL VARCHAR(10)",
        "ALTER TABLE empresas ADD COLUMN MONTO_MAXIMO DECIMAL(15,2) DEFAULT 0",
        "ALTER TABLE empresas ADD COLUMN NUMERO_TARJETAS INT DEFAULT 0",
        "ALTER TABLE empresas ADD COLUMN ESTATUS ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'"
    ];
    
    // Ejecutar cada consulta
    foreach ($alterQueries as $query) {
        echo "Ejecutando: $query\n";
        if (!$conn->query($query)) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }
        echo "OK\n";
    }
    
    echo "Actualización completada exitosamente";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 