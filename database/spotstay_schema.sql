-- ============================================================
-- SpotStay - Esquema completo de base de datos
-- Generado a partir de las migraciones de Laravel
-- Motor: MySQL / MariaDB
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `spotstay` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `spotstay`;

-- ============================================================
-- TABLAS DE LARAVEL (framework)
-- ============================================================

-- Tabla: users (autenticación Laravel Breeze/Jetstream)
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: password_reset_tokens
CREATE TABLE `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL PRIMARY KEY,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: sessions
CREATE TABLE `sessions` (
    `id` VARCHAR(255) NOT NULL PRIMARY KEY,
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    INDEX `sessions_user_id_index` (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: cache
CREATE TABLE `cache` (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` BIGINT NOT NULL,
    INDEX `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: cache_locks
CREATE TABLE `cache_locks` (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` BIGINT NOT NULL,
    INDEX `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: jobs
CREATE TABLE `jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: job_batches
CREATE TABLE `job_batches` (
    `id` VARCHAR(255) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` MEDIUMTEXT NOT NULL,
    `options` MEDIUMTEXT DEFAULT NULL,
    `cancelled_at` INT DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: failed_jobs
CREATE TABLE `failed_jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLAS PROPIAS DEL SISTEMA
-- ============================================================

-- Tabla: tbl_usuario
CREATE TABLE `tbl_usuario` (
    `id_usuario` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_usuario` VARCHAR(100) NOT NULL,
    `email_usuario` VARCHAR(150) NOT NULL,
    `contrasena_usuario` VARCHAR(255) NOT NULL,
    `telefono_usuario` VARCHAR(20) DEFAULT NULL,
    `avatar_usuario` VARCHAR(255) DEFAULT NULL,
    `dni_usuario` VARCHAR(20) DEFAULT NULL,
    `fecha_nacimiento_usuario` DATE DEFAULT NULL,
    `iban_usuario` VARCHAR(34) DEFAULT NULL,
    `direccion_fiscal_usuario` VARCHAR(255) DEFAULT NULL,
    `tipo_arrendador_usuario` VARCHAR(20) DEFAULT NULL,
    `verificado_identidad_usuario` TINYINT(1) NOT NULL DEFAULT 0,
    `activo_usuario` TINYINT(1) NOT NULL DEFAULT 1,
    `verificado_usuario` TIMESTAMP NULL DEFAULT NULL,
    `token_usuario` VARCHAR(100) DEFAULT NULL,
    `creado_usuario` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_usuario` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `tbl_usuario_email_usuario_unique` (`email_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_rol
CREATE TABLE `tbl_rol` (
    `id_rol` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_rol` VARCHAR(50) NOT NULL,
    `slug_rol` VARCHAR(50) NOT NULL,
    `creado_rol` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_rol` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `tbl_rol_slug_rol_unique` (`slug_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_rol_usuario
CREATE TABLE `tbl_rol_usuario` (
    `id_rol_usuario` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `id_rol_fk` BIGINT UNSIGNED NOT NULL,
    `asignado_rol_usuario` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `tbl_rol_usuario_usuario_rol_unique` (`id_usuario_fk`, `id_rol_fk`),
    CONSTRAINT `tbl_rol_usuario_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `tbl_rol_usuario_id_rol_fk_foreign` FOREIGN KEY (`id_rol_fk`) REFERENCES `tbl_rol` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_propiedad
CREATE TABLE `tbl_propiedad` (
    `id_propiedad` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_arrendador_fk` BIGINT UNSIGNED NOT NULL,
    `id_gestor_fk` BIGINT UNSIGNED DEFAULT NULL,
    `titulo_propiedad` VARCHAR(150) NOT NULL,
    `calle_propiedad` VARCHAR(150) NOT NULL,
    `numero_propiedad` VARCHAR(20) NOT NULL,
    `piso_propiedad` VARCHAR(20) DEFAULT NULL,
    `puerta_propiedad` VARCHAR(20) DEFAULT NULL,
    `ciudad_propiedad` VARCHAR(100) NOT NULL,
    `codigo_postal_propiedad` VARCHAR(10) NOT NULL,
    `latitud_propiedad` DECIMAL(10,7) DEFAULT NULL,
    `longitud_propiedad` DECIMAL(10,7) DEFAULT NULL,
    `descripcion_propiedad` TEXT DEFAULT NULL,
    `precio_propiedad` DECIMAL(8,2) NOT NULL,
    `tipo_propiedad` VARCHAR(30) DEFAULT NULL,
    `habitaciones_propiedad` VARCHAR(20) DEFAULT NULL,
    `metros_cuadrados_propiedad` SMALLINT UNSIGNED DEFAULT NULL,
    `amueblado_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `piscina_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `terraza_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `garaje_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `ascensor_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `aire_acondicionado_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `calefaccion_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `trastero_propiedad` TINYINT(1) NOT NULL DEFAULT 0,
    `adicional_propiedad` VARCHAR(255) DEFAULT NULL,
    `banos_propiedad` TINYINT UNSIGNED DEFAULT NULL,
    `id_admin_aprueba_fk` BIGINT UNSIGNED DEFAULT NULL,
    `estado_propiedad` VARCHAR(30) NOT NULL DEFAULT 'borrador',
    `notas_admin_propiedad` TEXT DEFAULT NULL,
    `aprobada_propiedad` TIMESTAMP NULL DEFAULT NULL,
    `creado_propiedad` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_propiedad` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_propiedad_id_arrendador_fk_index` (`id_arrendador_fk`),
    INDEX `tbl_propiedad_estado_propiedad_index` (`estado_propiedad`),
    INDEX `tbl_propiedad_tipo_propiedad_index` (`tipo_propiedad`),
    INDEX `tbl_propiedad_habitaciones_propiedad_index` (`habitaciones_propiedad`),
    INDEX `tbl_propiedad_metros_cuadrados_propiedad_index` (`metros_cuadrados_propiedad`),
    INDEX `tbl_propiedad_ciudad_propiedad_index` (`ciudad_propiedad`),
    CONSTRAINT `tbl_propiedad_id_arrendador_fk_foreign` FOREIGN KEY (`id_arrendador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_propiedad_id_gestor_fk_foreign` FOREIGN KEY (`id_gestor_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_propiedad_id_admin_aprueba_fk_foreign` FOREIGN KEY (`id_admin_aprueba_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_alquiler
CREATE TABLE `tbl_alquiler` (
    `id_alquiler` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `id_inquilino_fk` BIGINT UNSIGNED NOT NULL,
    `id_admin_aprueba_fk` BIGINT UNSIGNED DEFAULT NULL,
    `fecha_inicio_alquiler` DATE NOT NULL,
    `fecha_fin_alquiler` DATE DEFAULT NULL,
    `precio_alquiler` DECIMAL(8,2) DEFAULT NULL,
    `estado_alquiler` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `aprobado_alquiler` TIMESTAMP NULL DEFAULT NULL,
    `creado_alquiler` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_alquiler` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_alquiler_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_alquiler_id_inquilino_fk_index` (`id_inquilino_fk`),
    INDEX `tbl_alquiler_estado_alquiler_index` (`estado_alquiler`),
    CONSTRAINT `tbl_alquiler_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_alquiler_id_inquilino_fk_foreign` FOREIGN KEY (`id_inquilino_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_alquiler_id_admin_aprueba_fk_foreign` FOREIGN KEY (`id_admin_aprueba_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_contrato
CREATE TABLE `tbl_contrato` (
    `id_contrato` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_alquiler_fk` BIGINT UNSIGNED NOT NULL,
    `url_pdf_contrato` VARCHAR(500) NOT NULL,
    `hash_contrato` VARCHAR(64) NOT NULL,
    `firmado_arrendador` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_firma_arrendador` TIMESTAMP NULL DEFAULT NULL,
    `ip_firma_arrendador` VARCHAR(45) DEFAULT NULL,
    `firmado_inquilino` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_firma_inquilino` TIMESTAMP NULL DEFAULT NULL,
    `ip_firma_inquilino` VARCHAR(45) DEFAULT NULL,
    `estado_contrato` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `creado_contrato` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_contrato` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_contrato_estado_contrato_index` (`estado_contrato`),
    UNIQUE KEY `tbl_contrato_id_alquiler_fk_unique` (`id_alquiler_fk`),
    CONSTRAINT `tbl_contrato_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_pago
CREATE TABLE `tbl_pago` (
    `id_pago` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_alquiler_fk` BIGINT UNSIGNED NOT NULL,
    `id_alquiler_cuota_fk` BIGINT UNSIGNED DEFAULT NULL,
    `id_pagador_fk` BIGINT UNSIGNED NOT NULL,
    `id_gasto_cuota_detalle_fk` BIGINT UNSIGNED DEFAULT NULL,
    `id_gasto_cuota_fk` BIGINT UNSIGNED DEFAULT NULL,
    `tipo_pago` ENUM('alquiler','gasto','fianza') NOT NULL,
    `concepto_pago` VARCHAR(200) NOT NULL,
    `importe_pago` DECIMAL(8,2) NOT NULL,
    `mes_pago` DATE DEFAULT NULL,
    `estado_pago` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `referencia_pago` VARCHAR(100) DEFAULT NULL,
    `fecha_confirmacion_pago` TIMESTAMP NULL DEFAULT NULL,
    `creado_pago` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_pago` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_pago_id_alquiler_fk_index` (`id_alquiler_fk`),
    INDEX `tbl_pago_id_alquiler_cuota_fk_index` (`id_alquiler_cuota_fk`),
    INDEX `tbl_pago_id_pagador_fk_index` (`id_pagador_fk`),
    INDEX `tbl_pago_id_gasto_cuota_detalle_fk_index` (`id_gasto_cuota_detalle_fk`),
    INDEX `tbl_pago_id_gasto_cuota_fk_index` (`id_gasto_cuota_fk`),
    INDEX `tbl_pago_estado_pago_index` (`estado_pago`),
    INDEX `tbl_pago_mes_pago_index` (`mes_pago`),
    INDEX `tbl_pago_referencia_pago_index` (`referencia_pago`),
    CONSTRAINT `tbl_pago_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_pago_id_pagador_fk_foreign` FOREIGN KEY (`id_pagador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_pago_id_alquiler_cuota_fk_foreign` FOREIGN KEY (`id_alquiler_cuota_fk`) REFERENCES `tbl_alquiler_cuota` (`id_alquiler_cuota`) ON DELETE SET NULL,
    CONSTRAINT `tbl_pago_id_gasto_cuota_detalle_fk_foreign` FOREIGN KEY (`id_gasto_cuota_detalle_fk`) REFERENCES `tbl_gasto_cuota_detalle` (`id_gasto_cuota_detalle`) ON DELETE SET NULL,
    CONSTRAINT `tbl_pago_id_gasto_cuota_fk_foreign` FOREIGN KEY (`id_gasto_cuota_fk`) REFERENCES `tbl_gasto_cuota` (`id_gasto_cuota`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_documento
CREATE TABLE `tbl_documento` (
    `id_documento` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `tipo_documento` VARCHAR(50) NOT NULL,
    `tipo_entidad_documento` VARCHAR(50) NOT NULL,
    `id_entidad_documento` BIGINT UNSIGNED NOT NULL,
    `nombre_documento` VARCHAR(200) NOT NULL,
    `url_documento` VARCHAR(500) NOT NULL,
    `hash_documento` VARCHAR(64) NOT NULL,
    `pdfmonkey_id_documento` VARCHAR(100) DEFAULT NULL,
    `creado_documento` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_documento` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_documento_id_usuario_fk_index` (`id_usuario_fk`),
    INDEX `tbl_documento_tipo_entidad_documento_id_entidad_documento_index` (`tipo_entidad_documento`, `id_entidad_documento`),
    INDEX `tbl_documento_id_entidad_documento_index` (`id_entidad_documento`),
    CONSTRAINT `tbl_documento_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_chatbot_sesion
CREATE TABLE `tbl_chatbot_sesion` (
    `id_sesion_chatbot` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `creado_sesion_chatbot` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_sesion_chatbot` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_chatbot_sesion_usuario_creado_index` (`id_usuario_fk`, `creado_sesion_chatbot`),
    CONSTRAINT `tbl_chatbot_sesion_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_chatbot_mensaje
CREATE TABLE `tbl_chatbot_mensaje` (
    `id_mensaje_chatbot` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_sesion_chatbot_fk` BIGINT UNSIGNED NOT NULL,
    `rol_mensaje_chatbot` VARCHAR(10) NOT NULL,
    `cuerpo_mensaje_chatbot` TEXT NOT NULL,
    `creado_mensaje_chatbot` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_chatbot_mensaje_id_sesion_chatbot_fk_index` (`id_sesion_chatbot_fk`),
    CONSTRAINT `tbl_chatbot_mensaje_id_sesion_chatbot_fk_foreign` FOREIGN KEY (`id_sesion_chatbot_fk`) REFERENCES `tbl_chatbot_sesion` (`id_sesion_chatbot`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_incidencia
CREATE TABLE `tbl_incidencia` (
    `id_incidencia` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `id_reporta_fk` BIGINT UNSIGNED NOT NULL,
    `id_asignado_fk` BIGINT UNSIGNED DEFAULT NULL,
    `titulo_incidencia` VARCHAR(200) NOT NULL,
    `descripcion_incidencia` TEXT NOT NULL,
    `categoria_incidencia` VARCHAR(50) NOT NULL,
    `prioridad_incidencia` VARCHAR(20) NOT NULL DEFAULT 'media',
    `estado_incidencia` VARCHAR(30) NOT NULL DEFAULT 'abierta',
    `esperando_de_incidencia` VARCHAR(30) DEFAULT NULL,
    `presupuesto_importe_incidencia` DECIMAL(10,2) DEFAULT NULL,
    `detalle_presupuesto_incidencia` TEXT DEFAULT NULL,
    `responsable_pago_incidencia` VARCHAR(30) DEFAULT NULL,
    `pagado_presupuesto_incidencia` TINYINT(1) NOT NULL DEFAULT 0,
    `pagado_incidencia` TIMESTAMP NULL DEFAULT NULL,
    `resuelto_incidencia` TIMESTAMP NULL DEFAULT NULL,
    `cerrado_incidencia` TIMESTAMP NULL DEFAULT NULL,
    `creado_incidencia` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_incidencia` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_incidencia_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_incidencia_estado_incidencia_index` (`estado_incidencia`),
    INDEX `tbl_incidencia_esperando_de_incidencia_index` (`esperando_de_incidencia`),
    INDEX `tbl_incidencia_resuelto_incidencia_index` (`resuelto_incidencia`),
    INDEX `tbl_incidencia_responsable_pago_incidencia_index` (`responsable_pago_incidencia`),
    INDEX `tbl_incidencia_cerrado_incidencia_index` (`cerrado_incidencia`),
    CONSTRAINT `tbl_incidencia_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_incidencia_id_reporta_fk_foreign` FOREIGN KEY (`id_reporta_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_incidencia_id_asignado_fk_foreign` FOREIGN KEY (`id_asignado_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_historial_incidencia
CREATE TABLE `tbl_historial_incidencia` (
    `id_historial_incidencia` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_incidencia_fk` BIGINT UNSIGNED NOT NULL,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `comentario_historial` TEXT DEFAULT NULL,
    `cambio_estado_historial` VARCHAR(30) DEFAULT NULL,
    `creado_historial` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_historial` TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT `tbl_historial_incidencia_id_incidencia_fk_foreign` FOREIGN KEY (`id_incidencia_fk`) REFERENCES `tbl_incidencia` (`id_incidencia`) ON DELETE CASCADE,
    CONSTRAINT `tbl_historial_incidencia_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_conversacion
CREATE TABLE `tbl_conversacion` (
    `id_conversacion` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED DEFAULT NULL,
    `tipo_conversacion` VARCHAR(30) NOT NULL DEFAULT 'directa',
    `titulo_conversacion` VARCHAR(150) DEFAULT NULL,
    `creado_conversacion` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_conversacion` TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT `tbl_conversacion_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_conversacion_usuario
CREATE TABLE `tbl_conversacion_usuario` (
    `id_conversacion_usuario` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_conversacion_fk` BIGINT UNSIGNED NOT NULL,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `ultima_lectura_conv_usuario` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `tbl_conversacion_usuario_conv_usuario_unique` (`id_conversacion_fk`, `id_usuario_fk`),
    CONSTRAINT `tbl_conversacion_usuario_id_conversacion_fk_foreign` FOREIGN KEY (`id_conversacion_fk`) REFERENCES `tbl_conversacion` (`id_conversacion`) ON DELETE CASCADE,
    CONSTRAINT `tbl_conversacion_usuario_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_mensaje
CREATE TABLE `tbl_mensaje` (
    `id_mensaje` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_conversacion_fk` BIGINT UNSIGNED NOT NULL,
    `id_remitente_fk` BIGINT UNSIGNED NOT NULL,
    `cuerpo_mensaje` TEXT NOT NULL,
    `leido_mensaje` TINYINT(1) NOT NULL DEFAULT 0,
    `creado_mensaje` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_mensaje` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_mensaje_id_conversacion_fk_index` (`id_conversacion_fk`),
    CONSTRAINT `tbl_mensaje_id_conversacion_fk_foreign` FOREIGN KEY (`id_conversacion_fk`) REFERENCES `tbl_conversacion` (`id_conversacion`) ON DELETE CASCADE,
    CONSTRAINT `tbl_mensaje_id_remitente_fk_foreign` FOREIGN KEY (`id_remitente_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_notificacion
CREATE TABLE `tbl_notificacion` (
    `id_notificacion` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `tipo_notificacion` VARCHAR(100) NOT NULL,
    `titulo_notificacion` VARCHAR(200) NOT NULL,
    `mensaje_notificacion` TEXT NOT NULL,
    `url_notificacion` VARCHAR(500) DEFAULT NULL,
    `icono_notificacion` VARCHAR(50) DEFAULT NULL,
    `color_notificacion` VARCHAR(20) DEFAULT NULL,
    `tipo_entidad_notificacion` VARCHAR(50) DEFAULT NULL,
    `id_entidad_notificacion` BIGINT UNSIGNED DEFAULT NULL,
    `leida_notificacion` TINYINT(1) NOT NULL DEFAULT 0,
    `leida_en_notificacion` TIMESTAMP NULL DEFAULT NULL,
    `creado_notificacion` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_notificacion` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_notificacion_usuario_leida_index` (`id_usuario_fk`, `leida_notificacion`),
    CONSTRAINT `tbl_notificacion_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_solicitud_arrendador
CREATE TABLE `tbl_solicitud_arrendador` (
    `id_solicitud_arrendador` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `id_admin_revisa_fk` BIGINT UNSIGNED DEFAULT NULL,
    `telefono_solicitud` VARCHAR(20) DEFAULT NULL,
    `fecha_nacimiento_solicitud` DATE DEFAULT NULL,
    `tipo_documento_solicitud` VARCHAR(10) DEFAULT NULL,
    `numero_documento_solicitud` VARCHAR(20) DEFAULT NULL,
    `iban_solicitud` VARCHAR(34) DEFAULT NULL,
    `titular_cuenta_solicitud` VARCHAR(100) DEFAULT NULL,
    `nif_solicitud` VARCHAR(20) DEFAULT NULL,
    `direccion_fiscal_solicitud` VARCHAR(255) DEFAULT NULL,
    `tipo_arrendador_solicitud` VARCHAR(20) DEFAULT NULL,
    `descripcion_solicitud` TEXT DEFAULT NULL,
    `num_propiedades_previstas_solicitud` TINYINT UNSIGNED DEFAULT NULL,
    `es_propietario_solicitud` TINYINT(1) NOT NULL DEFAULT 0,
    `acepta_terminos_solicitud` TINYINT(1) NOT NULL DEFAULT 0,
    `acepta_veracidad_solicitud` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_aceptacion_solicitud` TIMESTAMP NULL DEFAULT NULL,
    `estado_solicitud_arrendador` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `revisado_solicitud_arrendador` TIMESTAMP NULL DEFAULT NULL,
    `notas_solicitud_arrendador` TEXT DEFAULT NULL,
    `creado_solicitud_arrendador` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_solicitud_arrendador` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_solicitud_arrendador_id_usuario_fk_index` (`id_usuario_fk`),
    INDEX `tbl_solicitud_arrendador_estado_index` (`estado_solicitud_arrendador`),
    INDEX `tbl_solicitud_arrendador_id_admin_revisa_fk_index` (`id_admin_revisa_fk`),
    CONSTRAINT `tbl_solicitud_arrendador_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `tbl_solicitud_arrendador_id_admin_revisa_fk_foreign` FOREIGN KEY (`id_admin_revisa_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_suscripcion
CREATE TABLE `tbl_suscripcion` (
    `id_suscripcion` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `plan_suscripcion` VARCHAR(30) NOT NULL,
    `precio_pagado_suscripcion` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `id_plan_fk` BIGINT UNSIGNED DEFAULT NULL,
    `max_propiedades_suscripcion` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `inicio_suscripcion` DATE NOT NULL,
    `fin_suscripcion` DATE DEFAULT NULL,
    `estado_suscripcion` VARCHAR(20) NOT NULL DEFAULT 'activa',
    `creado_suscripcion` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_suscripcion` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_suscripcion_estado_suscripcion_index` (`estado_suscripcion`),
    INDEX `tbl_suscripcion_plan_suscripcion_index` (`plan_suscripcion`),
    INDEX `tbl_suscripcion_id_usuario_fk_index` (`id_usuario_fk`),
    CONSTRAINT `tbl_suscripcion_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `tbl_suscripcion_id_plan_fk_foreign` FOREIGN KEY (`id_plan_fk`) REFERENCES `tbl_plan` (`id_plan`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_fotos
CREATE TABLE `tbl_fotos` (
    `id_foto` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `ruta_foto` VARCHAR(255) NOT NULL,
    `es_principal_foto` TINYINT(1) NOT NULL DEFAULT 0,
    `orden` INT UNSIGNED NOT NULL DEFAULT 0,
    `creado_foto` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_fotos_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_fotos_es_principal_foto_index` (`es_principal_foto`),
    UNIQUE KEY `tbl_fotos_propiedad_ruta_unique` (`id_propiedad_fk`, `ruta_foto`),
    CONSTRAINT `tbl_fotos_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_gasto
CREATE TABLE `tbl_gasto` (
    `id_gasto` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `id_alquiler_fk` BIGINT UNSIGNED DEFAULT NULL,
    `id_gestor_fk` BIGINT UNSIGNED NOT NULL,
    `concepto_gasto` VARCHAR(200) DEFAULT NULL,
    `categoria_gasto` VARCHAR(50) DEFAULT NULL,
    `importe_estimado` DECIMAL(10,2) DEFAULT NULL,
    `ambito_gasto` ENUM('propiedad','contrato') NOT NULL DEFAULT 'propiedad',
    `pagador_gasto` ENUM('arrendador','inquilino') NOT NULL DEFAULT 'inquilino',
    `periodicidad_gasto` VARCHAR(30) NOT NULL DEFAULT 'mensual',
    `dia_vencimiento` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `fecha_inicio_gasto` DATE NOT NULL,
    `fecha_fin_gasto` DATE DEFAULT NULL,
    `estado_gasto` VARCHAR(30) NOT NULL DEFAULT 'activo',
    `creado_gasto` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_gasto` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_gasto_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_gasto_id_alquiler_fk_index` (`id_alquiler_fk`),
    INDEX `tbl_gasto_id_gestor_fk_index` (`id_gestor_fk`),
    INDEX `tbl_gasto_estado_gasto_index` (`estado_gasto`),
    CONSTRAINT `tbl_gasto_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_gasto_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE CASCADE,
    CONSTRAINT `tbl_gasto_id_gestor_fk_foreign` FOREIGN KEY (`id_gestor_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_gasto_cuota
CREATE TABLE `tbl_gasto_cuota` (
    `id_gasto_cuota` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_gasto_fk` BIGINT UNSIGNED NOT NULL,
    `mes_cuota` DATE NOT NULL,
    `vencimiento_cuota` DATE NOT NULL,
    `importe_total_cuota` DECIMAL(10,2) NOT NULL,
    `estado_cuota` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `pagado_cuota` TIMESTAMP NULL DEFAULT NULL,
    `creado_cuota` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_cuota` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_gasto_cuota_id_gasto_fk_index` (`id_gasto_fk`),
    INDEX `tbl_gasto_cuota_mes_cuota_index` (`mes_cuota`),
    INDEX `tbl_gasto_cuota_estado_cuota_index` (`estado_cuota`),
    UNIQUE KEY `uq_gasto_mes` (`id_gasto_fk`, `mes_cuota`),
    CONSTRAINT `tbl_gasto_cuota_id_gasto_fk_foreign` FOREIGN KEY (`id_gasto_fk`) REFERENCES `tbl_gasto` (`id_gasto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_gasto_cuota_detalle
CREATE TABLE `tbl_gasto_cuota_detalle` (
    `id_gasto_cuota_detalle` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_gasto_cuota_fk` BIGINT UNSIGNED NOT NULL,
    `id_alquiler_fk` BIGINT UNSIGNED NOT NULL,
    `id_pagador_fk` BIGINT UNSIGNED NOT NULL,
    `importe_detalle` DECIMAL(10,2) NOT NULL,
    `estado_detalle` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    `pagado_detalle` TIMESTAMP NULL DEFAULT NULL,
    `creado_detalle` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_detalle` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_gasto_cuota_detalle_id_gasto_cuota_fk_index` (`id_gasto_cuota_fk`),
    INDEX `tbl_gasto_cuota_detalle_id_alquiler_fk_index` (`id_alquiler_fk`),
    INDEX `tbl_gasto_cuota_detalle_id_pagador_fk_index` (`id_pagador_fk`),
    INDEX `tbl_gasto_cuota_detalle_estado_detalle_index` (`estado_detalle`),
    UNIQUE KEY `uq_cuota_alquiler` (`id_gasto_cuota_fk`, `id_alquiler_fk`),
    CONSTRAINT `tbl_gasto_cuota_detalle_id_gasto_cuota_fk_foreign` FOREIGN KEY (`id_gasto_cuota_fk`) REFERENCES `tbl_gasto_cuota` (`id_gasto_cuota`) ON DELETE CASCADE,
    CONSTRAINT `tbl_gasto_cuota_detalle_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE CASCADE,
    CONSTRAINT `tbl_gasto_cuota_detalle_id_pagador_fk_foreign` FOREIGN KEY (`id_pagador_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_alquiler_cuota
CREATE TABLE `tbl_alquiler_cuota` (
    `id_alquiler_cuota` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_alquiler_fk` BIGINT UNSIGNED NOT NULL,
    `mes_cuota` DATE NOT NULL,
    `importe_base` DECIMAL(10,2) NOT NULL,
    `estado` ENUM('pendiente','pagado','atrasado') NOT NULL DEFAULT 'pendiente',
    `fecha_vencimiento` DATE NOT NULL,
    `pagado_en` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `tbl_alquiler_cuota_alquiler_mes_unique` (`id_alquiler_fk`, `mes_cuota`),
    CONSTRAINT `tbl_alquiler_cuota_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_plan
CREATE TABLE `tbl_plan` (
    `id_plan` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_plan` VARCHAR(50) NOT NULL,
    `slug_plan` VARCHAR(30) NOT NULL,
    `precio_plan` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `max_propiedades_plan` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `descripcion_plan` TEXT DEFAULT NULL,
    `activo_plan` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_plan` TIMESTAMP NULL DEFAULT NULL,
    `actualizado_plan` TIMESTAMP NULL DEFAULT NULL,
    INDEX `tbl_plan_slug_plan_index` (`slug_plan`),
    INDEX `tbl_plan_activo_plan_index` (`activo_plan`),
    UNIQUE KEY `tbl_plan_slug_plan_unique` (`slug_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_historial_propiedad
CREATE TABLE `tbl_historial_propiedad` (
    `id_historial_propiedad` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `tipo_cambio_historial` VARCHAR(50) NOT NULL,
    `campo_modificado_historial` VARCHAR(100) DEFAULT NULL,
    `valor_anterior_historial` TEXT DEFAULT NULL,
    `valor_nuevo_historial` TEXT DEFAULT NULL,
    `estado_anterior_historial` VARCHAR(30) DEFAULT NULL,
    `estado_nuevo_historial` VARCHAR(30) DEFAULT NULL,
    `comentario_historial` TEXT DEFAULT NULL,
    `creado_historial` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `tbl_historial_propiedad_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_historial_propiedad_creado_historial_index` (`creado_historial`),
    CONSTRAINT `tbl_historial_propiedad_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE CASCADE,
    CONSTRAINT `tbl_historial_propiedad_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_favorito
CREATE TABLE `tbl_favorito` (
    `id_favorito` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `creado_favorito` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `tbl_favorito_usuario_propiedad_unique` (`id_usuario_fk`, `id_propiedad_fk`),
    INDEX `tbl_favorito_id_propiedad_fk_index` (`id_propiedad_fk`),
    CONSTRAINT `tbl_favorito_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE,
    CONSTRAINT `tbl_favorito_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_visita
CREATE TABLE `tbl_visita` (
    `id_visita` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_propiedad_fk` BIGINT UNSIGNED NOT NULL,
    `id_usuario_fk` BIGINT UNSIGNED DEFAULT NULL,
    `ip_visita` VARCHAR(45) DEFAULT NULL,
    `sesion_visita` VARCHAR(100) DEFAULT NULL,
    `creado_visita` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `tbl_visita_propiedad_creado_index` (`id_propiedad_fk`, `creado_visita`),
    CONSTRAINT `tbl_visita_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE CASCADE,
    CONSTRAINT `tbl_visita_id_usuario_fk_foreign` FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tbl_valoracion
CREATE TABLE `tbl_valoracion` (
    `id_valoracion` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_alquiler_fk` BIGINT UNSIGNED NOT NULL,
    `id_autor_fk` BIGINT UNSIGNED NOT NULL,
    `id_destinatario_fk` BIGINT UNSIGNED DEFAULT NULL,
    `id_propiedad_fk` BIGINT UNSIGNED DEFAULT NULL,
    `tipo_valoracion` VARCHAR(40) NOT NULL,
    `puntuacion_valoracion` TINYINT UNSIGNED NOT NULL,
    `comentario_valoracion` TEXT DEFAULT NULL,
    `creado_valoracion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `tbl_valoracion_alquiler_autor_tipo_unique` (`id_alquiler_fk`, `id_autor_fk`, `tipo_valoracion`),
    INDEX `tbl_valoracion_id_propiedad_fk_index` (`id_propiedad_fk`),
    INDEX `tbl_valoracion_id_destinatario_fk_index` (`id_destinatario_fk`),
    CONSTRAINT `tbl_valoracion_id_alquiler_fk_foreign` FOREIGN KEY (`id_alquiler_fk`) REFERENCES `tbl_alquiler` (`id_alquiler`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_valoracion_id_autor_fk_foreign` FOREIGN KEY (`id_autor_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE RESTRICT,
    CONSTRAINT `tbl_valoracion_id_destinatario_fk_foreign` FOREIGN KEY (`id_destinatario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE SET NULL,
    CONSTRAINT `tbl_valoracion_id_propiedad_fk_foreign` FOREIGN KEY (`id_propiedad_fk`) REFERENCES `tbl_propiedad` (`id_propiedad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS SEMILLA
-- ============================================================

INSERT INTO `tbl_plan` (`nombre_plan`, `slug_plan`, `precio_plan`, `max_propiedades_plan`, `descripcion_plan`, `activo_plan`, `creado_plan`, `actualizado_plan`) VALUES
('Gratuito', 'gratuito', 0.00, 1, 'Plan básico sin coste para empezar', 1, NOW(), NOW()),
('Básico', 'basico', 9.99, 3, 'Plan para arrendadores con pocas propiedades', 1, NOW(), NOW()),
('Pro', 'pro', 29.99, 10, 'Plan para arrendadores con muchas propiedades', 1, NOW(), NOW());
