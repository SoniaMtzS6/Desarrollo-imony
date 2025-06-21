<?php
require_once 'functions.php';

try {
    $conn = getDbConnection();
    
    // Verificar si existe el administrador por defecto
    $stmt = $conn->prepare("SELECT * FROM administradores WHERE EMAIL = ?");
    $email = 'admin@admin.com';
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insertar el administrador por defecto
        $stmt = $conn->prepare("INSERT INTO administradores (NOMBRE, EMAIL, PASSWORD, PERFIL, ID_EMPRESA) VALUES (?, ?, ?, ?, ?)");
        $nombre = 'Admin';
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $perfil = 'Superadministrador';
        $idEmpresa = 1;
        
        $stmt->bind_param("ssssi", $nombre, $email, $password, $perfil, $idEmpresa);
        if ($stmt->execute()) {
            echo "Administrador por defecto creado\n";
        } else {
            echo "Error al crear administrador: " . $stmt->error . "\n";
        }
    } else {
        echo "El administrador por defecto ya existe\n";
    }
    
    // Mostrar todos los administradores
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 