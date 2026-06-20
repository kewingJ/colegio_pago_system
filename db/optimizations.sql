-- Optimizaciones de Base de Datos para Sistema Escolar

-- 1. Creación de la tabla de bitácora de correos
CREATE TABLE IF NOT EXISTS `tbl_log_emails` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `destinatario` varchar(255) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `estado` varchar(50) DEFAULT 'Enviado',
  `id_usuario` int(11) DEFAULT NULL,
  `id_alumno` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `idx_fecha` (`fecha_envio`),
  KEY `idx_alumno` (`id_alumno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Adición de Índices para mejorar el rendimiento de las consultas (Cuellos de botella)

-- Índices en tbl_inscripcion
ALTER TABLE `tbl_inscripcion` ADD INDEX `idx_insc_alumno_anio` (`IDALUMNO`, `ANIOLECTIVO`);
ALTER TABLE `tbl_inscripcion` ADD INDEX `idx_insc_grado_seccion` (`IDGRADO`, `SECCION`);

-- Índices en tbl_pagosmensualidades
ALTER TABLE `tbl_pagosmensualidades` ADD INDEX `idx_pago_inscripcion` (`IdInscripcion`);

-- Índices en tbl_recibo (Muy usado en reportes)
ALTER TABLE `tbl_recibo` ADD INDEX `idx_recibo_alumno_anio` (`IdAlumno`, `Anio`);
ALTER TABLE `tbl_recibo` ADD INDEX `idx_recibo_categoria_mes` (`IdCategoriaConcepto`, `MesReferencia`);
ALTER TABLE `tbl_recibo` ADD INDEX `idx_recibo_anulado` (`Anulado`);

-- Índices en tbl_cargos
ALTER TABLE `tbl_cargos` ADD INDEX `idx_cargos_alumno_anio` (`IdAlumno`, `AnioLectivo`);
ALTER TABLE `tbl_cargos` ADD INDEX `idx_cargos_concepto_mes` (`IdConcepto`, `MesReferencia`);

-- Índices en tbl_alumnos
ALTER TABLE `tbl_alumnos` ADD INDEX `idx_nombre_alumno` (`NOMBREAPELLIDO`);

-- Índices en tbl_conceptospago
ALTER TABLE `tbl_conceptospago` ADD INDEX `idx_concepto_cat` (`IdCategoria`);

-- 3. Asegurar integridad (Opcional pero recomendado: Llaves Foráneas)
-- Nota: Solo si los datos actuales son consistentes.
-- ALTER TABLE `tbl_inscripcion` ADD CONSTRAINT `fk_insc_alumno` FOREIGN KEY (`IDALUMNO`) REFERENCES `tbl_alumnos`(`IDALUMNO`);
-- ALTER TABLE `tbl_recibo` ADD CONSTRAINT `fk_recibo_alumno` FOREIGN KEY (`IdAlumno`) REFERENCES `tbl_alumnos`(`IDALUMNO`);
