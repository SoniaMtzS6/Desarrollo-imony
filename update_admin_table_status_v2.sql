-- Paso 1: Añadir la nueva columna 'status', permitiendo valores nulos temporalmente.
ALTER TABLE `administradores` ADD COLUMN `status` VARCHAR(20);

-- Paso 2: Migrar los datos de 'activo' y 'eliminado' a la nueva columna 'status' en una sola operación.
-- El orden es importante: primero los eliminados, luego los bloqueados, y por último los activos.
UPDATE `administradores`
SET `status` = CASE
    WHEN `eliminado` = 1 THEN 'DELETED'
    WHEN `activo` = 0 THEN 'BLOCKED'
    ELSE 'ACTIVE'
END;

-- Paso 3: Modificar la columna 'status' para que no permita nulos y tenga un valor por defecto.
ALTER TABLE `administradores` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'ACTIVE';

-- Paso 4: Eliminar las columnas antiguas que ya no son necesarias.
ALTER TABLE `administradores` DROP COLUMN `activo`;
ALTER TABLE `administradores` DROP COLUMN `eliminado`; 