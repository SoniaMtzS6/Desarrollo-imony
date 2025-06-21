USE finister;

-- Asignar cuentas de prueba a los usuarios de la empresa con ID 33
UPDATE user SET id_account = 'ACC001' WHERE id_ = 109;

-- Verificar los cambios
SELECT id_, name, id_account FROM user WHERE id_empresa = 33 ORDER BY id_ ASC LIMIT 5; 