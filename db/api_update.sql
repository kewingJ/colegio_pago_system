-- Actualización para API y Carnet
ALTER TABLE `tbl_alumnos` MODIFY COLUMN `CODIGO` varchar(50) DEFAULT NULL;

-- Tabla para tokens de la API (para gestión y revocación si es necesario)
CREATE TABLE IF NOT EXISTS `tbl_api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token` text NOT NULL,
  `creado` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
