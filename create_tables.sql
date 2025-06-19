USE finister;

-- accounts definition
CREATE TABLE `accounts` (
  `id_` int NOT NULL AUTO_INCREMENT,
  `id` varchar(50) NOT NULL,
  `country` varchar(3) NOT NULL,
  `license_owner` varchar(8) DEFAULT NULL,
  `currency` varchar(3) NOT NULL,
  `datos_extras` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `owner_type` varchar(8) DEFAULT NULL,
  `client_id` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `created_at` varchar(30) NOT NULL,
  PRIMARY KEY (`id_`),
  UNIQUE KEY `id__UNIQUE` (`id_`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- activity definition
CREATE TABLE `activity` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `account` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `account_id` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `point_type` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `card_brand` varchar(255) DEFAULT NULL,
  `entry_mode` varchar(255) DEFAULT NULL,
  `merchant_name` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(255) DEFAULT NULL,
  `card_type` varchar(255) DEFAULT NULL,
  `mcc` varchar(255) DEFAULT NULL,
  `last_digits` varchar(4) DEFAULT NULL,
  `entry_type` varchar(255) DEFAULT NULL,
  `forced` varchar(255) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `origin_tx_id` varchar(255) DEFAULT NULL,
  `process_type` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `total_amount` varchar(255) DEFAULT NULL,
  `activity_type` varchar(255) DEFAULT NULL,
  `datetime` varchar(255) DEFAULT NULL,
  `idempotency_key` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- adjustment definition
CREATE TABLE `adjustment` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_transaction` varchar(255) DEFAULT NULL,
  `amount_details_amount` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `currency_details_amount` varchar(255) DEFAULT NULL,
  `currency_local_amount` varchar(255) DEFAULT NULL,
  `currency_settlement_amount` varchar(255) DEFAULT NULL,
  `currency_transaction_amount` varchar(255) DEFAULT NULL,
  `id_card` varchar(255) DEFAULT NULL,
  `id_merchant` varchar(255) DEFAULT NULL,
  `id_user` varchar(255) DEFAULT NULL,
  `last_four` varchar(255) DEFAULT NULL,
  `local_date_time` varchar(255) DEFAULT NULL,
  `merchant_mcc` varchar(255) DEFAULT NULL,
  `merchant_name` varchar(255) DEFAULT NULL,
  `name_details_amount` varchar(255) DEFAULT NULL,
  `network` varchar(255) DEFAULT NULL,
  `origin_transaction` varchar(255) DEFAULT NULL,
  `original_transaction_id` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `provider_card` varchar(255) DEFAULT NULL,
  `source_transaction` varchar(255) DEFAULT NULL,
  `total_local_amount` varchar(255) DEFAULT NULL,
  `total_settlement_amount` varchar(255) DEFAULT NULL,
  `total_transaction_amount` double DEFAULT NULL,
  `type_details_amount` varchar(255) DEFAULT NULL,
  `type_transaction` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- administradores definition
CREATE TABLE `administradores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `telefono` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `perfil` varchar(45) NOT NULL,
  `idEmpresa` int NOT NULL,
  `direccion` varchar(45) DEFAULT NULL,
  `activo` tinyint NOT NULL,
  `dato_extra` varchar(45) DEFAULT NULL,
  `image` mediumblob,
  `fecha_creacion` date NOT NULL,
  `is_password_temporary` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- deleted_users definition
CREATE TABLE `deleted_users` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `id_user` varchar(255) NOT NULL COMMENT 'Pomelo API id user',
  `deleted` tinyint(1) DEFAULT '1',
  `create_time` datetime DEFAULT NULL COMMENT 'Create Time',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Used for logical delete';

-- empresas definition
CREATE TABLE `empresas` (
  `ID_EMPRESA` int NOT NULL AUTO_INCREMENT,
  `NOMBRE_EMPRESA` varchar(90) NOT NULL,
  `RFC` varchar(45) NOT NULL,
  `ALIAS` varchar(45) NOT NULL,
  `PAIS` varchar(45) NOT NULL,
  `ESTADO` varchar(45) NOT NULL,
  `CODIGO_POSTAL` varchar(9) NOT NULL,
  `MONTO_MAXIMO` decimal(10,0) NOT NULL,
  `NUMERO_TARJETAS` int NOT NULL,
  `ESTATUS` varchar(7) NOT NULL,
  PRIMARY KEY (`ID_EMPRESA`),
  UNIQUE KEY `NOMBRE_EMPRESA_UNIQUE` (`NOMBRE_EMPRESA`),
  UNIQUE KEY `RFC_UNIQUE` (`RFC`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- empresas_movimientos definition
CREATE TABLE `empresas_movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int NOT NULL,
  `monto_agregado` decimal(15,2) DEFAULT NULL,
  `tarjetas_agregadas` int DEFAULT NULL,
  `total_monto` decimal(15,2) NOT NULL,
  `total_tarjetas` int NOT NULL,
  `fecha_movimiento` varchar(10) NOT NULL,
  `tipo_movimiento` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- notifications definition
CREATE TABLE `notifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `event_id` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount_details_amount` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `currency_details_amount` varchar(255) DEFAULT NULL,
  `currency_local_amount` varchar(255) DEFAULT NULL,
  `currency_settlement_amount` varchar(255) DEFAULT NULL,
  `currency_transaction_amount` varchar(255) DEFAULT NULL,
  `card_id` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `last_four` varchar(255) DEFAULT NULL,
  `local_date_time` varchar(255) DEFAULT NULL,
  `merchant_mcc` varchar(255) DEFAULT NULL,
  `merchant_name` varchar(255) DEFAULT NULL,
  `name_details_amount` varchar(255) DEFAULT NULL,
  `network` varchar(255) DEFAULT NULL,
  `origin_transaction` varchar(255) DEFAULT NULL,
  `original_transaction_id` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `provider_card` varchar(255) DEFAULT NULL,
  `source_transaction` varchar(255) DEFAULT NULL,
  `total_local_amount` varchar(255) DEFAULT NULL,
  `total_settlement_amount` varchar(255) DEFAULT NULL,
  `total_transaction_amount` double DEFAULT NULL,
  `type_details_amount` varchar(255) DEFAULT NULL,
  `type_transaction` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_detail` varchar(255) DEFAULT NULL,
  `extra_detail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- properties definition
CREATE TABLE `properties` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Create Time',
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- transaction definition
CREATE TABLE `transaction` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_transaction` varchar(255) DEFAULT NULL,
  `amount_details_amount` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `currency_details_amount` varchar(255) DEFAULT NULL,
  `currency_local_amount` varchar(255) DEFAULT NULL,
  `currency_settlement_amount` varchar(255) DEFAULT NULL,
  `currency_transaction_amount` varchar(255) DEFAULT NULL,
  `id_card` varchar(255) DEFAULT NULL,
  `id_merchant` varchar(255) DEFAULT NULL,
  `id_user` varchar(255) DEFAULT NULL,
  `last_four` varchar(255) DEFAULT NULL,
  `local_date_time` varchar(255) DEFAULT NULL,
  `merchant_mcc` varchar(255) DEFAULT NULL,
  `merchant_name` varchar(255) DEFAULT NULL,
  `name_details_amount` varchar(255) DEFAULT NULL,
  `network` varchar(255) DEFAULT NULL,
  `origin_transaction` varchar(255) DEFAULT NULL,
  `original_transaction_id` varchar(255) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `provider_card` varchar(255) DEFAULT NULL,
  `source_transaction` varchar(255) DEFAULT NULL,
  `total_local_amount` varchar(255) DEFAULT NULL,
  `total_settlement_amount` varchar(255) DEFAULT NULL,
  `total_transaction_amount` double DEFAULT NULL,
  `type_details_amount` varchar(255) DEFAULT NULL,
  `type_transaction` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- user definition
CREATE TABLE `user` (
  `id_` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico auto incremental',
  `id` varchar(50) DEFAULT NULL COMMENT 'Identificador único original del usuario',
  `name` varchar(100) NOT NULL COMMENT 'Nombre del usuario',
  `surname` varchar(100) NOT NULL COMMENT 'Apellido del usuario',
  `identification_type` varchar(50) DEFAULT NULL COMMENT 'Tipo de identificación',
  `identification_value` bigint DEFAULT NULL COMMENT 'Número de identificación',
  `birthdate` varchar(20) DEFAULT NULL COMMENT 'Fecha de nacimiento',
  `gender` varchar(20) DEFAULT NULL COMMENT 'Género',
  `email` varchar(255) DEFAULT NULL COMMENT 'Correo electrónico',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Número de teléfono',
  `tax_identification_type` varchar(50) DEFAULT NULL COMMENT 'Tipo de identificación fiscal',
  `tax_identification_value` bigint DEFAULT NULL COMMENT 'Número de identificación fiscal',
  `nationality` varchar(3) DEFAULT NULL COMMENT 'Nacionalidad (código de 3 letras, por ejemplo, MEX)',
  `tax_condition` varchar(50) DEFAULT NULL COMMENT 'Condición fiscal',
  `status` enum('ACTIVE','INACTIVE','PENDING','SUSPENDED','BANNED','DELETED','BLOCKED') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING' COMMENT 'Estado del usuario',
  `operation_country` varchar(3) DEFAULT NULL COMMENT 'País de operación (código de 3 letras, por ejemplo, MEX)',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `id_empresa` int NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_password_temporary` tinyint(1) DEFAULT '0',
  `id_account` varchar(50) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla de usuarios, el usuario se genera al recibir una respuesta de creación exitosa de la API de Pomelo';

-- user_address definition
CREATE TABLE `user_address` (
  `user_id` int NOT NULL COMMENT 'Relación con la tabla `user`',
  `street_name` varchar(255) DEFAULT NULL COMMENT 'Nombre de la calle',
  `street_number` int DEFAULT NULL COMMENT 'Número de la calle',
  `floor` int DEFAULT NULL COMMENT 'Piso',
  `apartment` varchar(10) DEFAULT NULL COMMENT 'Departamento',
  `zip_code` int DEFAULT NULL COMMENT 'Código postal',
  `neighborhood` varchar(100) DEFAULT NULL COMMENT 'Barrio',
  `city` varchar(100) DEFAULT NULL COMMENT 'Ciudad',
  `region` varchar(100) DEFAULT NULL COMMENT 'Región o estado',
  `additional_info` varchar(255) DEFAULT NULL COMMENT 'Información adicional',
  `country` varchar(3) DEFAULT NULL COMMENT 'País (código de 3 letras)',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- usuarios_movimientos definition
CREATE TABLE `usuarios_movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` varchar(45) NOT NULL,
  `id_empresa` int NOT NULL,
  `monto` decimal(15,2) DEFAULT NULL,
  `tarjetas_asignadas` int DEFAULT NULL,
  `fecha_movimiento` varchar(10) NOT NULL,
  `tipo_movimiento` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 