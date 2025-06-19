-- Desactivar restricciones de clave foránea temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar todas las tablas
TRUNCATE TABLE users;
TRUNCATE TABLE administradores;
TRUNCATE TABLE empresas;

-- Reactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1;

-- Reiniciar los auto_increment
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE administradores AUTO_INCREMENT = 1;
ALTER TABLE empresas AUTO_INCREMENT = 1;

-- Crear la empresa principal
INSERT INTO empresas (NOMBRE_EMPRESA, RFC, ALIAS, PAIS, ESTADO, CODIGO_POSTAL, MONTO_MAXIMO, NUMERO_TARJETAS, ESTATUS) VALUES ('Empresa Principal', 'XAXX010101000', 'Principal', 'México', 'CDMX', '00000', 100000.00, 100, 'ACTIVE');

-- Crear el administrador por defecto
INSERT INTO administradores (nombre, email, telefono, password, perfil, idEmpresa, direccion, activo, dato_extra, is_password_temporary) VALUES ('Admin', 'admin@admin.com', '0000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Superadministrador', 1, 'Dirección Principal', 1, NULL, 0); 