-- Añadir la nueva columna 'status'
ALTER TABLE `administradores` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'ACTIVE';

-- Migrar los datos de la columna 'activo' a 'status'
-- Se asume que activo = 1 es 'ACTIVE' y activo = 0 es 'BLOCKED'
UPDATE `administradores` SET `status` = 'BLOCKED' WHERE `activo` = 0;
UPDATE `administradores` SET `status` = 'ACTIVE' WHERE `activo` = 1;

-- Migrar los datos de la columna 'eliminado' a 'status'
-- Se asume que si 'eliminado' es 1, el estado debe ser 'DELETED'
UPDATE `administradores` SET `status` = 'DELETED' WHERE `eliminado` = 1;

-- Eliminar las columnas antiguas que ya no son necesarias
ALTER TABLE `administradores` DROP COLUMN `activo`;
ALTER TABLE `administradores` DROP COLUMN `eliminado`; 