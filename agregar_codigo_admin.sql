-- Agregar campo codigo_admin a la tabla administradores
ALTER TABLE administradores ADD COLUMN codigo_admin VARCHAR(10) DEFAULT NULL AFTER id;

-- Crear trigger para generar automáticamente el código
DELIMITER //

CREATE TRIGGER before_administradores_insert_codigo
BEFORE INSERT ON administradores
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_admin, 2) AS UNSIGNED)), 0) + 1 INTO next_id FROM administradores;
    SET NEW.codigo_admin = CONCAT('A', LPAD(next_id, 6, '0'));
END//

DELIMITER ;

-- Verificar que se agregó correctamente
DESCRIBE administradores; 