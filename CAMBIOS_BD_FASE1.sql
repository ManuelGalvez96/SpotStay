-- ============================================================================
-- CAMBIOS DE BASE DE DATOS SPOTSTAY - FASE 1
-- Normalización de JSON, añadir campos nuevos, crear nuevas tablas
-- ORDEN ESTRICTO DE EJECUCIÓN PARA RESPETAR FOREIGN KEYS
-- ============================================================================

-- ============================================================================
-- CAMBIO 4: AÑADIR CAMPOS A tbl_usuario
-- ============================================================================
ALTER TABLE tbl_usuario
  ADD COLUMN dni_usuario VARCHAR(20) NULL
    AFTER avatar_usuario,
  ADD COLUMN fecha_nacimiento_usuario DATE NULL
    AFTER dni_usuario,
  ADD COLUMN iban_usuario VARCHAR(34) NULL
    AFTER fecha_nacimiento_usuario,
  ADD COLUMN direccion_fiscal_usuario VARCHAR(255) NULL
    AFTER iban_usuario,
  ADD COLUMN tipo_arrendador_usuario VARCHAR(20) NULL
    AFTER direccion_fiscal_usuario,
  ADD COLUMN verificado_identidad_usuario BOOLEAN DEFAULT FALSE
    AFTER tipo_arrendador_usuario;

-- ============================================================================
-- CAMBIO 5: AÑADIR CAMPOS A tbl_propiedad
-- ============================================================================
ALTER TABLE tbl_propiedad
  ADD COLUMN banos_propiedad TINYINT UNSIGNED NULL
    AFTER habitaciones_propiedad,
  ADD COLUMN id_admin_aprueba_fk BIGINT(20) UNSIGNED NULL
    AFTER id_gestor_fk,
  ADD COLUMN notas_admin_propiedad TEXT NULL
    AFTER estado_propiedad,
  ADD COLUMN aprobada_propiedad TIMESTAMP NULL
    AFTER notas_admin_propiedad,
  ADD FOREIGN KEY (id_admin_aprueba_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE SET NULL,
  ADD INDEX idx_ciudad (ciudad_propiedad);

-- ============================================================================
-- CAMBIO 6: AÑADIR CAMPO A tbl_alquiler
-- ============================================================================
ALTER TABLE tbl_alquiler
  ADD COLUMN precio_alquiler DECIMAL(8,2) NULL
    AFTER fecha_fin_alquiler;

-- ============================================================================
-- CAMBIO 7: AÑADIR CAMPOS E ÍNDICES A tbl_suscripcion (sin FK aún)
-- ============================================================================
ALTER TABLE tbl_suscripcion
  ADD COLUMN precio_pagado_suscripcion DECIMAL(8,2) DEFAULT 0.00
    AFTER plan_suscripcion,
  ADD COLUMN id_plan_fk BIGINT(20) UNSIGNED NULL
    AFTER precio_pagado_suscripcion,
  ADD INDEX idx_estado (estado_suscripcion),
  ADD INDEX idx_plan (plan_suscripcion),
  ADD INDEX idx_usuario (id_usuario_fk);

-- ============================================================================
-- CAMBIO 8: AÑADIR CAMPO A tbl_conversacion
-- ============================================================================
ALTER TABLE tbl_conversacion
  ADD COLUMN titulo_conversacion VARCHAR(150) NULL
    AFTER tipo_conversacion;

-- ============================================================================
-- CAMBIO 9: AÑADIR FOREIGN KEYS FORMALES A tbl_pago
-- ============================================================================
ALTER TABLE tbl_pago
  ADD FOREIGN KEY (id_gasto_cuota_detalle_fk)
    REFERENCES tbl_gasto_cuota_detalle(id_gasto_cuota_detalle)
    ON DELETE SET NULL,
  ADD FOREIGN KEY (id_gasto_cuota_fk)
    REFERENCES tbl_gasto_cuota(id_gasto_cuota)
    ON DELETE SET NULL;

-- ============================================================================
-- CAMBIO 1: NORMALIZAR tbl_notificacion - ELIMINAR JSON
-- ============================================================================
ALTER TABLE tbl_notificacion
  DROP COLUMN datos_notificacion,
  ADD COLUMN titulo_notificacion VARCHAR(200) NOT NULL
    AFTER tipo_notificacion,
  ADD COLUMN mensaje_notificacion TEXT NOT NULL
    AFTER titulo_notificacion,
  ADD COLUMN url_notificacion VARCHAR(500) NULL
    AFTER mensaje_notificacion,
  ADD COLUMN icono_notificacion VARCHAR(50) NULL
    AFTER url_notificacion,
  ADD COLUMN color_notificacion VARCHAR(20) NULL
    AFTER icono_notificacion,
  ADD COLUMN tipo_entidad_notificacion VARCHAR(50) NULL
    AFTER color_notificacion,
  ADD COLUMN id_entidad_notificacion BIGINT(20) UNSIGNED NULL
    AFTER tipo_entidad_notificacion;

-- ============================================================================
-- CAMBIO 3: NORMALIZAR tbl_solicitud_arrendador - ELIMINAR JSON Y AÑADIR CAMPOS
-- ============================================================================
ALTER TABLE tbl_solicitud_arrendador
  DROP COLUMN datos_solicitud_arrendador,
  ADD COLUMN telefono_solicitud VARCHAR(20) NULL
    AFTER id_admin_revisa_fk,
  ADD COLUMN fecha_nacimiento_solicitud DATE NULL
    AFTER telefono_solicitud,
  ADD COLUMN tipo_documento_solicitud VARCHAR(10) NULL
    AFTER fecha_nacimiento_solicitud,
  ADD COLUMN numero_documento_solicitud VARCHAR(20) NULL
    AFTER tipo_documento_solicitud,
  ADD COLUMN iban_solicitud VARCHAR(34) NULL
    AFTER numero_documento_solicitud,
  ADD COLUMN titular_cuenta_solicitud VARCHAR(100) NULL
    AFTER iban_solicitud,
  ADD COLUMN nif_solicitud VARCHAR(20) NULL
    AFTER titular_cuenta_solicitud,
  ADD COLUMN direccion_fiscal_solicitud VARCHAR(255) NULL
    AFTER nif_solicitud,
  ADD COLUMN tipo_arrendador_solicitud VARCHAR(20) NULL
    AFTER direccion_fiscal_solicitud,
  ADD COLUMN descripcion_solicitud TEXT NULL
    AFTER tipo_arrendador_solicitud,
  ADD COLUMN num_propiedades_previstas_solicitud TINYINT UNSIGNED NULL
    AFTER descripcion_solicitud,
  ADD COLUMN es_propietario_solicitud BOOLEAN DEFAULT FALSE
    AFTER num_propiedades_previstas_solicitud,
  ADD COLUMN acepta_terminos_solicitud BOOLEAN DEFAULT FALSE
    AFTER es_propietario_solicitud,
  ADD COLUMN acepta_veracidad_solicitud BOOLEAN DEFAULT FALSE
    AFTER acepta_terminos_solicitud,
  ADD COLUMN fecha_aceptacion_solicitud TIMESTAMP NULL
    AFTER acepta_veracidad_solicitud,
  ADD COLUMN revisado_solicitud_arrendador TIMESTAMP NULL
    AFTER estado_solicitud_arrendador,
  ADD INDEX idx_usuario (id_usuario_fk),
  ADD INDEX idx_estado (estado_solicitud_arrendador),
  ADD INDEX idx_admin (id_admin_revisa_fk);

-- ============================================================================
-- CAMBIO 2: ELIMINAR JSON gastos_propiedad DE tbl_propiedad
-- ============================================================================
ALTER TABLE tbl_propiedad
  DROP COLUMN gastos_propiedad;

-- ============================================================================
-- CAMBIO 10: CREAR TABLA tbl_plan (CATÁLOGO DE PLANES GLOBALES)
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_plan (
  id_plan BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_plan VARCHAR(50) NOT NULL,
  slug_plan VARCHAR(30) NOT NULL UNIQUE,
  precio_plan DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  max_propiedades_plan TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
  descripcion_plan TEXT NULL,
  activo_plan BOOLEAN DEFAULT TRUE,
  creado_plan TIMESTAMP,
  actualizado_plan TIMESTAMP,
  INDEX idx_slug (slug_plan),
  INDEX idx_activo (activo_plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar los 3 planes base
INSERT INTO tbl_plan
  (nombre_plan, slug_plan, precio_plan, max_propiedades_plan, descripcion_plan, activo_plan, creado_plan, actualizado_plan)
VALUES
  ('Gratuito', 'gratuito', 0.00, 1, 'Plan básico sin coste para empezar', TRUE, NOW(), NOW()),
  ('Básico', 'basico', 9.99, 3, 'Plan para arrendadores con pocas propiedades', TRUE, NOW(), NOW()),
  ('Pro', 'pro', 29.99, 10, 'Plan para arrendadores con muchas propiedades', TRUE, NOW(), NOW());

-- ============================================================================
-- CAMBIO 10 (RETROACTIVO): AÑADIR FOREIGN KEY EN tbl_suscripcion
-- ============================================================================
ALTER TABLE tbl_suscripcion
  ADD FOREIGN KEY (id_plan_fk)
    REFERENCES tbl_plan(id_plan)
    ON DELETE RESTRICT;

-- ============================================================================
-- CAMBIO 11: CREAR TABLA tbl_historial_propiedad (AUDITORÍA)
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_historial_propiedad (
  id_historial_propiedad BIGINT(20) UNSIGNED NOT NULL
    AUTO_INCREMENT PRIMARY KEY,
  id_propiedad_fk BIGINT(20) UNSIGNED NOT NULL,
  id_usuario_fk BIGINT(20) UNSIGNED NOT NULL,
  tipo_cambio_historial VARCHAR(50) NOT NULL,
  campo_modificado_historial VARCHAR(100) NULL,
  valor_anterior_historial TEXT NULL,
  valor_nuevo_historial TEXT NULL,
  estado_anterior_historial VARCHAR(30) NULL,
  estado_nuevo_historial VARCHAR(30) NULL,
  comentario_historial TEXT NULL,
  creado_historial TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_propiedad_fk)
    REFERENCES tbl_propiedad(id_propiedad) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE RESTRICT,
  INDEX idx_propiedad (id_propiedad_fk),
  INDEX idx_fecha (creado_historial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CAMBIO 12: CREAR TABLA tbl_valoracion
-- NOTA: Solo estructura, NO implementar lógica en esta versión
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_valoracion (
  id_valoracion BIGINT(20) UNSIGNED NOT NULL
    AUTO_INCREMENT PRIMARY KEY,
  id_alquiler_fk BIGINT(20) UNSIGNED NOT NULL,
  id_autor_fk BIGINT(20) UNSIGNED NOT NULL,
  id_destinatario_fk BIGINT(20) UNSIGNED NULL,
  id_propiedad_fk BIGINT(20) UNSIGNED NULL,
  tipo_valoracion VARCHAR(40) NOT NULL,
  puntuacion_valoracion TINYINT(1) UNSIGNED NOT NULL,
  comentario_valoracion TEXT NULL,
  creado_valoracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_alquiler_fk)
    REFERENCES tbl_alquiler(id_alquiler) ON DELETE RESTRICT,
  FOREIGN KEY (id_autor_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE RESTRICT,
  FOREIGN KEY (id_destinatario_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE SET NULL,
  FOREIGN KEY (id_propiedad_fk)
    REFERENCES tbl_propiedad(id_propiedad) ON DELETE SET NULL,
  UNIQUE KEY uq_valoracion_autor_tipo
    (id_alquiler_fk, id_autor_fk, tipo_valoracion),
  INDEX idx_propiedad (id_propiedad_fk),
  INDEX idx_destinatario (id_destinatario_fk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CAMBIO 13: CREAR TABLA tbl_favorito
-- NOTA: Solo estructura, NO implementar lógica en esta versión
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_favorito (
  id_favorito BIGINT(20) UNSIGNED NOT NULL
    AUTO_INCREMENT PRIMARY KEY,
  id_usuario_fk BIGINT(20) UNSIGNED NOT NULL,
  id_propiedad_fk BIGINT(20) UNSIGNED NOT NULL,
  creado_favorito TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_propiedad_fk)
    REFERENCES tbl_propiedad(id_propiedad) ON DELETE CASCADE,
  UNIQUE KEY uq_favorito (id_usuario_fk, id_propiedad_fk),
  INDEX idx_propiedad (id_propiedad_fk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CAMBIO 14: CREAR TABLA tbl_visita
-- NOTA: Solo estructura, NO implementar lógica en esta versión
-- ============================================================================
CREATE TABLE IF NOT EXISTS tbl_visita (
  id_visita BIGINT(20) UNSIGNED NOT NULL
    AUTO_INCREMENT PRIMARY KEY,
  id_propiedad_fk BIGINT(20) UNSIGNED NOT NULL,
  id_usuario_fk BIGINT(20) UNSIGNED NULL,
  ip_visita VARCHAR(45) NULL,
  sesion_visita VARCHAR(100) NULL,
  creado_visita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_propiedad_fk)
    REFERENCES tbl_propiedad(id_propiedad) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario_fk)
    REFERENCES tbl_usuario(id_usuario) ON DELETE SET NULL,
  INDEX idx_propiedad_fecha (id_propiedad_fk, creado_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FIN DE CAMBIOS FASE 1
-- ============================================================================
