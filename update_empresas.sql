-- Desactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 0;

-- Hacer backup de los datos existentes
CREATE TABLE empresas_backup AS SELECT * FROM empresas;

-- Eliminar la tabla existente
DROP TABLE IF EXISTS empresas;

-- Crear la tabla con la nueva estructura
CREATE TABLE empresas (
    ID_EMPRESA INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_EMPRESA VARCHAR(255) NOT NULL,
    RFC VARCHAR(13) NOT NULL,
    ALIAS VARCHAR(255),
    PAIS VARCHAR(100),
    ESTADO VARCHAR(100),
    CODIGO_POSTAL VARCHAR(10),
    MONTO_MAXIMO DECIMAL(15,2) DEFAULT 0,
    NUMERO_TARJETAS INT DEFAULT 0,
    ESTATUS ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
    FECHA_REGISTRO DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Restaurar los datos del backup
INSERT INTO empresas (ID_EMPRESA, NOMBRE_EMPRESA, FECHA_REGISTRO)
SELECT ID_EMPRESA, NOMBRE_EMPRESA, FECHA_REGISTRO FROM empresas_backup;

-- Eliminar la tabla de backup
DROP TABLE empresas_backup;

-- Reactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1; 