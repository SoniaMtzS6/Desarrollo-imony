-- Agregar campo codigo_user a la tabla user
ALTER TABLE user ADD COLUMN codigo_user VARCHAR(10) DEFAULT NULL AFTER id_;

-- Crear trigger para generar automáticamente el código de usuario
DELIMITER //

CREATE TRIGGER before_user_insert_codigo
BEFORE INSERT ON user
FOR EACH ROW
BEGIN
    DECLARE next_id INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo_user, 2) AS UNSIGNED)), 0) + 1 INTO next_id FROM user;
    SET NEW.codigo_user = CONCAT('U', LPAD(next_id, 6, '0'));
END//

DELIMITER ;

-- Verificar que se agregó correctamente
DESCRIBE user; 