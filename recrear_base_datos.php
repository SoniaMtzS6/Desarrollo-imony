<?php
$host = "localhost";
$usuario = "root";
$password = "";

try {
    // Conectar sin seleccionar base de datos
    $conn = new mysqli($host, $usuario, $password);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Eliminar la base de datos si existe
    $conn->query("DROP DATABASE IF EXISTS adminfinister");
    echo "Base de datos eliminada\n";
    
    // Crear la base de datos
    $conn->query("CREATE DATABASE adminfinister");
    echo "Base de datos creada\n";
    
    // Seleccionar la base de datos
    $conn->select_db('adminfinister');
    
    // Crear la tabla empresas
    $sql = "CREATE TABLE empresas (
        ID_EMPRESA INT AUTO_INCREMENT PRIMARY KEY,
        NOMBRE_EMPRESA VARCHAR(255) NOT NULL,
        RFC VARCHAR(13),
        ALIAS VARCHAR(255),
        PAIS VARCHAR(100),
        ESTADO VARCHAR(100),
        CODIGO_POSTAL VARCHAR(10),
        MONTO_MAXIMO DECIMAL(15,2) DEFAULT 0,
        NUMERO_TARJETAS INT DEFAULT 0,
        ESTATUS VARCHAR(20)
    )";
    $conn->query($sql) or die("Error creando tabla empresas: " . $conn->error);
    echo "Tabla empresas creada\n";
    
    // Crear la tabla administradores
    $sql = "CREATE TABLE administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255),
        email VARCHAR(255),
        telefono VARCHAR(20),
        password VARCHAR(255),
        perfil VARCHAR(50),
        idEmpresa INT,
        direccion TEXT,
        activo TINYINT(1),
        dato_extra TEXT,
        image VARCHAR(255),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_password_temporary TINYINT(1),
        FOREIGN KEY (idEmpresa) REFERENCES empresas(ID_EMPRESA)
    )";
    $conn->query($sql) or die("Error creando tabla administradores: " . $conn->error);
    echo "Tabla administradores creada\n";
    
    // Crear la tabla users
    $sql = "CREATE TABLE users (
        id_ INT AUTO_INCREMENT PRIMARY KEY,
        id VARCHAR(50),
        name VARCHAR(255) NOT NULL,
        surname VARCHAR(255),
        identification_type VARCHAR(50),
        identification_value VARCHAR(50),
        birthdate DATE,
        gender VARCHAR(20),
        email VARCHAR(255),
        phone VARCHAR(20),
        tax_identification_type VARCHAR(50),
        tax_identification_value VARCHAR(50),
        nationality VARCHAR(100),
        tax_condition VARCHAR(100),
        status VARCHAR(20),
        operation_country VARCHAR(100),
        create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        id_empresa INT,
        password VARCHAR(255),
        is_password_temporary TINYINT(1),
        id_account VARCHAR(50),
        token VARCHAR(255),
        FOREIGN KEY (id_empresa) REFERENCES empresas(ID_EMPRESA)
    )";
    $conn->query($sql) or die("Error creando tabla users: " . $conn->error);
    echo "Tabla users creada\n";
    
    // Insertar la empresa principal
    $sql = "INSERT INTO empresas (
        NOMBRE_EMPRESA,
        RFC,
        ALIAS,
        PAIS,
        ESTADO,
        CODIGO_POSTAL,
        MONTO_MAXIMO,
        NUMERO_TARJETAS,
        ESTATUS
    ) VALUES (
        'Empresa Principal',
        'XAXX010101000',
        'Principal',
        'México',
        'CDMX',
        '00000',
        100000.00,
        100,
        'ACTIVE'
    )";
    $conn->query($sql) or die("Error insertando empresa principal: " . $conn->error);
    echo "Empresa principal creada\n";
    
    // Insertar el administrador por defecto
    $sql = "INSERT INTO administradores (
        nombre,
        email,
        telefono,
        password,
        perfil,
        idEmpresa,
        direccion,
        activo,
        dato_extra,
        is_password_temporary
    ) VALUES (
        'Admin',
        'admin@admin.com',
        '0000000000',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Superadministrador',
        1,
        'Dirección Principal',
        1,
        NULL,
        0
    )";
    $conn->query($sql) or die("Error insertando administrador: " . $conn->error);
    echo "Administrador por defecto creado\n";
    
    // Verificar los datos insertados
    echo "\nVerificando datos insertados:\n";
    
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
    
    echo "\nBase de datos recreada exitosamente.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 