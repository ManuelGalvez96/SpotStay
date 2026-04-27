-- ============================================================================
-- SpotStay Database Schema
-- Generated from Laravel 11 migrations (exacto a las migraciones)
-- Database: spotStay (MySQL compatible)
-- ============================================================================

-- ============================================================================
-- USUARIOS Y AUTENTICACIÓN
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_usuario` (
  `id_usuario` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre_usuario` VARCHAR(100) NOT NULL,
  `email_usuario` VARCHAR(150) NOT NULL UNIQUE,
  `contrasena_usuario` VARCHAR(255) NOT NULL,
  `telefono_usuario` VARCHAR(20),
  `avatar_usuario` VARCHAR(255),
  `activo_usuario` BOOLEAN DEFAULT TRUE,
  `verificado_usuario` TIMESTAMP,
  `token_usuario` VARCHAR(100),
  `creado_usuario` TIMESTAMP,
  `actualizado_usuario` TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ROLES Y PERMISOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_rol` (
  `id_rol` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre_rol` VARCHAR(50) NOT NULL,
  `slug_rol` VARCHAR(50) NOT NULL UNIQUE,
  `creado_rol` TIMESTAMP,
  `actualizado_rol` TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_rol_usuario` (
  `id_rol_usuario` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_rol_fk` BIGINT(20) UNSIGNED NOT NULL,
  `asignado_rol_usuario` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_usuario_rol` (`id_usuario_fk`, `id_rol_fk`),
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
  FOREIGN KEY (`id_rol_fk`) REFERENCES `tbl_rol` (`id_rol`) ON DELETE CASCADE,
  INDEX `idx_usuario` (`id_usuario_fk`),
  INDEX `idx_rol` (`id_rol_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROPIEDADES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_propiedad` (
  `id_propiedad` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_arrendador_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_gestor_fk` BIGINT(20) UNSIGNED,
  `titulo_propiedad` VARCHAR(150) NOT NULL,
  `calle_propiedad` VARCHAR(150) NOT NULL,
  `numero_propiedad` VARCHAR(20) NOT NULL,
  `piso_propiedad` VARCHAR(20),
  `puerta_propiedad` VARCHAR(20),
  `ciudad_propiedad` VARCHAR(100) NOT NULL,
  `codigo_postal_propiedad` VARCHAR(10) NOT NULL,
  `latitud_propiedad` DECIMAL(10, 7),
  `longitud_propiedad` DECIMAL(10, 7),
  `descripcion_propiedad` TEXT,
  `precio_propiedad` DECIMAL(8, 2) NOT NULL,
  `tipo_propiedad` VARCHAR(30),
  `habitaciones_propiedad` VARCHAR(20),
  `metros_cuadrados_propiedad` SMALLINT(5) UNSIGNED,
  `gastos_propiedad` JSON,
  `estado_propiedad` VARCHAR(30) DEFAULT 'borrador',
  `creado_propiedad` TIMESTAMP,
  `actualizado_propiedad` TIMESTAMP,
  FOREIGN KEY (`id_arrendador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_gestor_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_arrendador` (`id_arrendador_fk`),
  INDEX `idx_estado` (`estado_propiedad`),
  INDEX `idx_tipo` (`tipo_propiedad`),
  INDEX `idx_habitaciones` (`habitaciones_propiedad`),
  INDEX `idx_metros` (`metros_cuadrados_propiedad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_fotos` (
  `id_foto` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propiedad_fk` BIGINT(20) UNSIGNED NOT NULL,
  `ruta_foto` VARCHAR(255) NOT NULL,
  `orden` INT(10) UNSIGNED DEFAULT 0,
  `creado_foto` TIMESTAMP,
  FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE CASCADE,
  INDEX `idx_propiedad` (`id_propiedad_fk`),
  UNIQUE KEY `uq_propiedad_ruta` (`id_propiedad_fk`, `ruta_foto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ALQUILERES Y CONTRATOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_alquiler` (
  `id_alquiler` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propiedad_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_inquilino_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_admin_aprueba_fk` BIGINT(20) UNSIGNED,
  `fecha_inicio_alquiler` DATE NOT NULL,
  `fecha_fin_alquiler` DATE,
  `estado_alquiler` VARCHAR(30) DEFAULT 'pendiente',
  `aprobado_alquiler` TIMESTAMP,
  `creado_alquiler` TIMESTAMP,
  `actualizado_alquiler` TIMESTAMP,
  FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_inquilino_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_admin_aprueba_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL,
  INDEX `idx_propiedad` (`id_propiedad_fk`),
  INDEX `idx_inquilino` (`id_inquilino_fk`),
  INDEX `idx_estado` (`estado_alquiler`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_contrato` (
  `id_contrato` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_alquiler_fk` BIGINT(20) UNSIGNED NOT NULL UNIQUE,
  `url_pdf_contrato` VARCHAR(500) NOT NULL,
  `hash_contrato` VARCHAR(64) NOT NULL,
  `firmado_arrendador` BOOLEAN DEFAULT FALSE,
  `fecha_firma_arrendador` TIMESTAMP,
  `ip_firma_arrendador` VARCHAR(45),
  `firmado_inquilino` BOOLEAN DEFAULT FALSE,
  `fecha_firma_inquilino` TIMESTAMP,
  `ip_firma_inquilino` VARCHAR(45),
  `estado_contrato` VARCHAR(30) DEFAULT 'pendiente',
  `creado_contrato` TIMESTAMP,
  `actualizado_contrato` TIMESTAMP,
  FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE RESTRICT,
  INDEX `idx_estado` (`estado_contrato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PAGOS Y GASTOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_pago` (
  `id_pago` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_alquiler_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_pagador_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_gasto_cuota_detalle_fk` BIGINT(20) UNSIGNED,
  `id_gasto_cuota_fk` BIGINT(20) UNSIGNED,
  `tipo_pago` ENUM('alquiler', 'gasto', 'fianza') NOT NULL,
  `concepto_pago` VARCHAR(200) NOT NULL,
  `importe_pago` DECIMAL(8, 2) NOT NULL,
  `mes_pago` DATE,
  `estado_pago` VARCHAR(30) DEFAULT 'pendiente',
  `referencia_pago` VARCHAR(100),
  `fecha_confirmacion_pago` TIMESTAMP,
  `creado_pago` TIMESTAMP,
  `actualizado_pago` TIMESTAMP,
  FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_pagador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_alquiler` (`id_alquiler_fk`),
  INDEX `idx_pagador` (`id_pagador_fk`),
  INDEX `idx_gasto_cuota_detalle` (`id_gasto_cuota_detalle_fk`),
  INDEX `idx_gasto_cuota` (`id_gasto_cuota_fk`),
  INDEX `idx_estado` (`estado_pago`),
  INDEX `idx_mes` (`mes_pago`),
  INDEX `idx_referencia` (`referencia_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_gasto` (
  `id_gasto` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propiedad_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_alquiler_fk` BIGINT(20) UNSIGNED,
  `id_gestor_fk` BIGINT(20) UNSIGNED NOT NULL,
  `concepto_gasto` VARCHAR(200) NOT NULL,
  `categoria_gasto` VARCHAR(50),
  `importe_estimado` DECIMAL(10, 2),
  `ambito_gasto` ENUM('propiedad', 'contrato') NOT NULL,
  `pagador_gasto` ENUM('arrendador', 'inquilino') DEFAULT 'inquilino',
  `periodicidad_gasto` VARCHAR(30) DEFAULT 'mensual',
  `dia_vencimiento` TINYINT(3) UNSIGNED DEFAULT 5,
  `fecha_inicio_gasto` DATE NOT NULL,
  `fecha_fin_gasto` DATE,
  `estado_gasto` VARCHAR(30) DEFAULT 'activo',
  `creado_gasto` TIMESTAMP,
  `actualizado_gasto` TIMESTAMP,
  FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE CASCADE,
  FOREIGN KEY (`id_gestor_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_propiedad` (`id_propiedad_fk`),
  INDEX `idx_alquiler` (`id_alquiler_fk`),
  INDEX `idx_gestor` (`id_gestor_fk`),
  INDEX `idx_estado` (`estado_gasto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_gasto_cuota` (
  `id_gasto_cuota` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_gasto_fk` BIGINT(20) UNSIGNED NOT NULL,
  `mes_cuota` DATE NOT NULL,
  `vencimiento_cuota` DATE NOT NULL,
  `importe_total_cuota` DECIMAL(10, 2) NOT NULL,
  `estado_cuota` VARCHAR(30) DEFAULT 'pendiente',
  `pagado_cuota` TIMESTAMP,
  `creado_cuota` TIMESTAMP,
  `actualizado_cuota` TIMESTAMP,
  FOREIGN KEY (`id_gasto_fk`) REFERENCES `tbl_gasto` (`id_gasto`) ON DELETE CASCADE,
  INDEX `idx_gasto` (`id_gasto_fk`),
  INDEX `idx_mes` (`mes_cuota`),
  INDEX `idx_estado` (`estado_cuota`),
  UNIQUE KEY `uq_gasto_mes` (`id_gasto_fk`, `mes_cuota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_gasto_cuota_detalle` (
  `id_gasto_cuota_detalle` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_gasto_cuota_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_alquiler_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_pagador_fk` BIGINT(20) UNSIGNED NOT NULL,
  `importe_detalle` DECIMAL(10, 2) NOT NULL,
  `estado_detalle` VARCHAR(30) DEFAULT 'pendiente',
  `pagado_detalle` TIMESTAMP,
  `creado_detalle` TIMESTAMP,
  `actualizado_detalle` TIMESTAMP,
  FOREIGN KEY (`id_gasto_cuota_fk`) REFERENCES `tbl_gasto_cuota` (`id_gasto_cuota`) ON DELETE CASCADE,
  FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE CASCADE,
  FOREIGN KEY (`id_pagador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_cuota` (`id_gasto_cuota_fk`),
  INDEX `idx_alquiler` (`id_alquiler_fk`),
  INDEX `idx_pagador` (`id_pagador_fk`),
  INDEX `idx_estado` (`estado_detalle`),
  UNIQUE KEY `uq_cuota_alquiler` (`id_gasto_cuota_fk`, `id_alquiler_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DOCUMENTOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_documento` (
  `id_documento` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `tipo_documento` VARCHAR(50) NOT NULL,
  `tipo_entidad_documento` VARCHAR(50) NOT NULL,
  `id_entidad_documento` BIGINT(20) UNSIGNED NOT NULL,
  `nombre_documento` VARCHAR(200) NOT NULL,
  `url_documento` VARCHAR(500) NOT NULL,
  `hash_documento` VARCHAR(64) NOT NULL,
  `pdfmonkey_id_documento` VARCHAR(100),
  `creado_documento` TIMESTAMP,
  `actualizado_documento` TIMESTAMP,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_usuario` (`id_usuario_fk`),
  INDEX `idx_entidad` (`tipo_entidad_documento`, `id_entidad_documento`),
  INDEX `idx_id_entidad` (`id_entidad_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INCIDENCIAS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_incidencia` (
  `id_incidencia` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propiedad_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_reporta_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_asignado_fk` BIGINT(20) UNSIGNED,
  `titulo_incidencia` VARCHAR(200) NOT NULL,
  `descripcion_incidencia` TEXT NOT NULL,
  `categoria_incidencia` VARCHAR(50) NOT NULL,
  `prioridad_incidencia` VARCHAR(20) DEFAULT 'media',
  `estado_incidencia` VARCHAR(30) DEFAULT 'abierta',
  `creado_incidencia` TIMESTAMP,
  `actualizado_incidencia` TIMESTAMP,
  FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_reporta_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_asignado_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL,
  INDEX `idx_propiedad` (`id_propiedad_fk`),
  INDEX `idx_estado` (`estado_incidencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_historial_incidencia` (
  `id_historial_incidencia` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_incidencia_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `comentario_historial` TEXT,
  `cambio_estado_historial` VARCHAR(30),
  `creado_historial` TIMESTAMP,
  `actualizado_historial` TIMESTAMP,
  FOREIGN KEY (`id_incidencia_fk`) REFERENCES `tbl_incidencia` (`id_incidencia`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_incidencia` (`id_incidencia_fk`),
  INDEX `idx_usuario` (`id_usuario_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- COMUNICACIONES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_conversacion` (
  `id_conversacion` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propiedad_fk` BIGINT(20) UNSIGNED,
  `tipo_conversacion` VARCHAR(30) DEFAULT 'directa',
  `creado_conversacion` TIMESTAMP,
  `actualizado_conversacion` TIMESTAMP,
  FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_conversacion_usuario` (
  `id_conversacion_usuario` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_conversacion_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `ultima_lectura_conv_usuario` TIMESTAMP,
  UNIQUE KEY `uq_conversacion_usuario` (`id_conversacion_fk`, `id_usuario_fk`),
  FOREIGN KEY (`id_conversacion_fk`) REFERENCES `tbl_conversacion` (`id_conversacion`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_mensaje` (
  `id_mensaje` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_conversacion_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_remitente_fk` BIGINT(20) UNSIGNED NOT NULL,
  `cuerpo_mensaje` TEXT NOT NULL,
  `leido_mensaje` BOOLEAN DEFAULT FALSE,
  `creado_mensaje` TIMESTAMP,
  `actualizado_mensaje` TIMESTAMP,
  FOREIGN KEY (`id_conversacion_fk`) REFERENCES `tbl_conversacion` (`id_conversacion`) ON DELETE CASCADE,
  FOREIGN KEY (`id_remitente_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
  INDEX `idx_conversacion` (`id_conversacion_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CHATBOT IA
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_chatbot_sesion` (
  `id_sesion_chatbot` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `creado_sesion_chatbot` TIMESTAMP,
  `actualizado_sesion_chatbot` TIMESTAMP,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
  INDEX `idx_usuario_creado` (`id_usuario_fk`, `creado_sesion_chatbot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_chatbot_mensaje` (
  `id_mensaje_chatbot` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_sesion_chatbot_fk` BIGINT(20) UNSIGNED NOT NULL,
  `rol_mensaje_chatbot` VARCHAR(10) NOT NULL,
  `cuerpo_mensaje_chatbot` TEXT NOT NULL,
  `creado_mensaje_chatbot` TIMESTAMP,
  FOREIGN KEY (`id_sesion_chatbot_fk`) REFERENCES `tbl_chatbot_sesion` (`id_sesion_chatbot`) ON DELETE CASCADE,
  INDEX `idx_sesion` (`id_sesion_chatbot_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICACIONES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_notificacion` (
  `id_notificacion` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `tipo_notificacion` VARCHAR(100) NOT NULL,
  `datos_notificacion` JSON NOT NULL,
  `leida_notificacion` BOOLEAN DEFAULT FALSE,
  `leida_en_notificacion` TIMESTAMP,
  `creado_notificacion` TIMESTAMP,
  `actualizado_notificacion` TIMESTAMP,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
  INDEX `idx_usuario_leida` (`id_usuario_fk`, `leida_notificacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SOLICITUDES Y SUSCRIPCIONES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_solicitud_arrendador` (
  `id_solicitud_arrendador` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `id_admin_revisa_fk` BIGINT(20) UNSIGNED,
  `datos_solicitud_arrendador` JSON NOT NULL,
  `estado_solicitud_arrendador` VARCHAR(30) DEFAULT 'pendiente',
  `notas_solicitud_arrendador` TEXT,
  `creado_solicitud_arrendador` TIMESTAMP,
  `actualizado_solicitud_arrendador` TIMESTAMP,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
  FOREIGN KEY (`id_admin_revisa_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_suscripcion` (
  `id_suscripcion` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_usuario_fk` BIGINT(20) UNSIGNED NOT NULL,
  `plan_suscripcion` VARCHAR(30) NOT NULL,
  `max_propiedades_suscripcion` TINYINT(3) UNSIGNED DEFAULT 1,
  `inicio_suscripcion` DATE NOT NULL,
  `fin_suscripcion` DATE,
  `estado_suscripcion` VARCHAR(20) DEFAULT 'activa',
  `creado_suscripcion` TIMESTAMP,
  `actualizado_suscripcion` TIMESTAMP,
  FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- RESUMEN DE TABLAS
-- ============================================================================
/*
Total: 23 tablas

ESTRUCTURA DEL SISTEMA:
- Usuarios: tbl_usuario, tbl_rol, tbl_rol_usuario
- Propiedades: tbl_propiedad, tbl_fotos
- Alquileres: tbl_alquiler, tbl_contrato
- Pagos: tbl_pago, tbl_gasto, tbl_gasto_cuota, tbl_gasto_cuota_detalle
- Documentos: tbl_documento
- Incidencias: tbl_incidencia, tbl_historial_incidencia
- Comunicaciones: tbl_conversacion, tbl_conversacion_usuario, tbl_mensaje
- Chatbot: tbl_chatbot_sesion, tbl_chatbot_mensaje
- Notificaciones: tbl_notificacion
- Solicitudes: tbl_solicitud_arrendador
- Suscripciones: tbl_suscripcion
*/
