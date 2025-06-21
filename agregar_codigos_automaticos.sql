-- =====================================================
-- SCRIPT PARA AGREGAR CÓDIGOS AUTOMÁTICOS
-- Compatible con Windows (Desarrollo) y Linux (QA)
-- =====================================================

-- 1. AGREGAR CÓDIGO ADMINISTRADOR
-- =====================================================
ALTER TABLE administradores ADD COLUMN codigo_admin VARCHAR(10) DEFAULT NULL AFTER id;

-- Trigger para administradores (A000001, A000002, etc.)
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

-- 2. AGREGAR CÓDIGO USUARIO
-- =====================================================
ALTER TABLE user ADD COLUMN codigo_user VARCHAR(10) DEFAULT NULL AFTER id_;

-- Trigger para usuarios (U000001, U000002, etc.)
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

-- 3. VERIFICAR CAMBIOS
-- =====================================================
SELECT 'Verificando tabla administradores:' AS mensaje;
DESCRIBE administradores;

SELECT 'Verificando tabla user:' AS mensaje;
DESCRIBE user;

-- 4. MOSTRAR TRIGGERS CREADOS
-- =====================================================
SELECT 'Triggers creados:' AS mensaje;
SHOW TRIGGERS WHERE `Table` IN ('administradores', 'user'); 