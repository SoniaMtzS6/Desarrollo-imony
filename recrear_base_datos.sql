-- Eliminar la base de datos si existe
DROP DATABASE IF EXISTS adminfinister;

-- Crear la base de datos
CREATE DATABASE adminfinister;

-- Usar la base de datos
USE adminfinister;

-- Crear la tabla empresas
CREATE TABLE empresas (
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
);

-- Crear la tabla administradores
CREATE TABLE administradores (
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
);

-- Crear la tabla users
CREATE TABLE users (
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
);

-- Insertar la empresa principal
INSERT INTO empresas (
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
);

-- Insertar el administrador por defecto
INSERT INTO administradores (
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
); 