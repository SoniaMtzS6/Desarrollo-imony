-- Desactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar las tablas existentes
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS empresas;

-- Crear la tabla empresas con la estructura correcta
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
    ESTATUS ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
    saldo_actual DECIMAL(15,2) DEFAULT 0
);

-- Crear la tabla administradores con la estructura correcta
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    perfil ENUM('Superadministrador','Administrador') NOT NULL,
    id_empresa INT,
    direccion TEXT,
    activo TINYINT(1) DEFAULT 1,
    dato_extra TEXT,
    image VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_password_temporary TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_empresa) REFERENCES empresas(ID_EMPRESA)
);

-- Crear tabla movimientos_saldo si no existe
CREATE TABLE IF NOT EXISTS movimientos_saldo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    tipo ENUM('ASIGNACION', 'RETIRO') NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario VARCHAR(255),
    comentario VARCHAR(255),
    FOREIGN KEY (id_empresa) REFERENCES empresas(ID_EMPRESA)
);

-- Agregar campo 'eliminado' a la tabla administradores si no existe
ALTER TABLE administradores ADD COLUMN IF NOT EXISTS eliminado TINYINT(1) DEFAULT 0;

-- Reactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1; 

DESCRIBE administradores; 