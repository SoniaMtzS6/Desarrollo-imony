<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Desactivar restricciones de clave foránea
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Limpiar tablas
    $conn->query("TRUNCATE TABLE users");
    $conn->query("TRUNCATE TABLE administradores");
    $conn->query("TRUNCATE TABLE empresas");
    
    // Reactivar restricciones de clave foránea
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Reiniciar auto_increment
    $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
    $conn->query("ALTER TABLE administradores AUTO_INCREMENT = 1");
    $conn->query("ALTER TABLE empresas AUTO_INCREMENT = 1");
    
    // Insertar empresa principal
    $sql = "INSERT INTO empresas (NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS) 
            VALUES ('Empresa Principal', 'XAXX010101000', 'Principal', 'México', 'CDMX', '00000', 100000.00, 100, 'ACTIVE')";
    if (!$conn->query($sql)) {
        throw new Exception("Error al insertar empresa: " . $conn->error);
    }
    
    // Insertar administrador por defecto
    $sql = "INSERT INTO administradores (nombre, email, telefono, password, perfil, id_empresa, direccion, activo, dato_extra, is_password_temporary) 
            VALUES ('Admin', 'admin@admin.com', '0000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Superadministrador', 1, 'Dirección Principal', 1, NULL, 0)";
    if (!$conn->query($sql)) {
        throw new Exception("Error al insertar administrador: " . $conn->error);
    }
    
    echo "Limpieza completada exitosamente.\n\n";
    
    // Verificar datos insertados
    echo "Verificando datos insertados:\n";
    
    $result = $conn->query("SELECT * FROM empresas");
    echo "\nEmpresas:\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    
    $result = $conn->query("SELECT * FROM administradores");
    echo "\nAdministradores:\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 