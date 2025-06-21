<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Eliminar la tabla si existe
    $conn->query("DROP TABLE IF EXISTS administradores");
    echo "Tabla eliminada\n";
    
    // Crear la tabla con la estructura correcta
    $sql = "CREATE TABLE administradores (
        ID_ADMIN INT AUTO_INCREMENT PRIMARY KEY,
        NOMBRE VARCHAR(255) NOT NULL,
        EMAIL VARCHAR(255) UNIQUE NOT NULL,
        PASSWORD VARCHAR(255) NOT NULL,
        PERFIL ENUM('Superadministrador','Administrador') NOT NULL,
        ID_EMPRESA INT,
        FECHA_REGISTRO DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ID_EMPRESA) REFERENCES empresas(ID_EMPRESA)
    )";
    
    if ($conn->query($sql)) {
        echo "Tabla creada\n";
        
        // Insertar el administrador por defecto
        $stmt = $conn->prepare("INSERT INTO administradores (NOMBRE, EMAIL, PASSWORD, PERFIL, ID_EMPRESA) VALUES (?, ?, ?, ?, ?)");
        $nombre = 'Admin';
        $email = 'admin@admin.com';
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $perfil = 'Superadministrador';
        $idEmpresa = 1;
        
        if ($stmt->bind_param("ssssi", $nombre, $email, $password, $perfil, $idEmpresa) && $stmt->execute()) {
            echo "Administrador por defecto creado\n";
        } else {
            echo "Error al crear administrador: " . $stmt->error . "\n";
        }
        
        // Verificar los registros
        $result = $conn->query("SELECT a.*, e.NOMBRE_EMPRESA FROM administradores a LEFT JOIN empresas e ON a.ID_EMPRESA = e.ID_EMPRESA");
        echo "\nAdministradores en el sistema:\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['ID_ADMIN'] . 
                 "\nNombre: " . $row['NOMBRE'] . 
                 "\nEmail: " . $row['EMAIL'] . 
                 "\nPerfil: " . $row['PERFIL'] . 
                 "\nEmpresa: " . ($row['NOMBRE_EMPRESA'] ?? 'N/A') . 
                 "\nFecha: " . $row['FECHA_REGISTRO'] . "\n\n";
        }
    } else {
        echo "Error al crear la tabla: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 