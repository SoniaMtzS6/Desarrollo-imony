-- Crear empresa principal si no existe
INSERT IGNORE INTO empresas (ID_EMPRESA, NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS)
VALUES (1, 'Empresa Principal', 'XAXX010101000', 'Principal', 'México', 'CDMX', '00000', 100000.00, 100, 'ACTIVE');

-- Insertar administradores de prueba
INSERT INTO administradores (nombre, email, telefono, password, perfil, id_empresa, direccion, activo, eliminado, is_password_temporary) VALUES
('Elizabeth Carranza Gonzalez', 'bancos@consultantsaddonis.com', '5555555555', 'test123', 'Administrador', 1, 'Dirección 1', 1, 0, 0),
('Carolina Audelo', 'caudeloamitern@gmail.com', '5555555556', 'test123', 'Administrador', 1, 'Dirección 2', 1, 0, 0),
('Fernando Lazo de la Vega', 'fernando@naxu.com.mx', '5555555557', 'test123', 'Administrador', 1, 'Dirección 3', 1, 0, 0); 