# Resumen de Cambios - Refactorización SpotStay (FASE 1-3)

**Fecha**: Abril 24, 2026
**Estado**: COMPLETADO (FASES 1-3), EN PROGRESO (FASE 4+)

---

## RESUMEN EJECUTIVO

Se ha completado la normalización de la base de datos SpotStay según la directiva de eliminar todos los campos JSON y aplicar normalizaciones. Se han realizado cambios en:

- **8 migraciones existentes** (modificadas)
- **5 nuevas migraciones** (tablas nuevas)
- **8 seeders** (actualizados/creados)
- **7 modelos Eloquent** (actualizados/creados)

**Total de cambios completados: 28 archivos**

---

## FASE 1: MIGRACIONES ✅ COMPLETADO

### Migraciones Modificadas (8 archivos - 2024_04_14_*)

#### A. tbl_usuario (2024_04_14_120001)
**Cambios**: Agregados 6 nuevos campos de identidad
```
+ dni_usuario (varchar 20)
+ fecha_nacimiento_usuario (date)
+ iban_usuario (varchar 50)
+ direccion_fiscal_usuario (varchar 255)
+ tipo_arrendador_usuario (enum: 'individual'|'empresa')
+ verificado_identidad_usuario (boolean)
```

#### B. tbl_propiedad (2024_04_14_120004)
**Cambios**: Normalización admin + nuevos campos
```
- gastos_propiedad (JSON ELIMINADO)
+ banos_propiedad (integer)
+ id_admin_aprueba_fk (FK a tbl_usuario)
+ notas_admin_propiedad (text)
+ aprobada_propiedad (boolean)
+ Índice: idx_ciudad
+ FK: id_admin_aprueba_fk
```

#### C. tbl_alquiler (2024_04_14_120005)
**Cambios**: Agregado precio snapshot
```
+ precio_alquiler (decimal 8,2)
```

#### D. tbl_pago (2024_04_14_120007)
**Cambios**: Agregadas relaciones a gastos
```
+ id_gasto_cuota_detalle_fk (FK)
+ id_gasto_cuota_fk (FK)
```

#### E. tbl_conversacion (2024_04_14_120013)
**Cambios**: Agregado título
```
+ titulo_conversacion (varchar 150, nullable)
```

#### F. tbl_notificacion (2024_04_14_120016)
**Cambios**: NORMALIZACIÓN COMPLETA (Mayor cambio)
```
- datos_notificacion (JSON ELIMINADO)
+ titulo_notificacion (varchar 200)
+ mensaje_notificacion (text)
+ url_notificacion (varchar 500)
+ icono_notificacion (varchar 50)
+ color_notificacion (varchar 20)
+ tipo_entidad_notificacion (varchar 50)
+ id_entidad_notificacion (bigint)
```

#### G. tbl_solicitud_arrendador (2024_04_14_120017)
**Cambios**: NORMALIZACIÓN COMPLETA (Mayor cambio - 15 campos)
```
- datos_solicitud_arrendador (JSON ELIMINADO)
+ telefono_solicitud (varchar 20)
+ fecha_nacimiento_solicitud (date)
+ tipo_documento_solicitud (varchar 20)
+ numero_documento_solicitud (varchar 30)
+ iban_solicitud (varchar 50)
+ titular_cuenta_solicitud (varchar 255)
+ nif_solicitud (varchar 30)
+ direccion_fiscal_solicitud (text)
+ tipo_arrendador_solicitud (enum: 'individual'|'empresa')
+ descripcion_solicitud (text)
+ num_propiedades_previstas_solicitud (integer)
+ es_propietario_solicitud (boolean)
+ acepta_terminos_solicitud (boolean)
+ acepta_veracidad_solicitud (boolean)
+ fecha_aceptacion_solicitud (date)
+ Índices: id_usuario, estado, id_admin
```

#### H. tbl_suscripcion (2024_04_14_120018)
**Cambios**: Relacionar con catálogo de planes
```
+ precio_pagado_suscripcion (decimal 8,2)
+ id_plan_fk (FK a tbl_plan)
- plan_suscripcion (REEMPLAZADO por FK)
- max_propiedades_suscripcion (Ahora viene desde tbl_plan)
+ Índices: estado, plan (en tbl_plan), id_usuario
```

### Nuevas Migraciones (5 archivos - 2026_04_24_*)

#### 1. tbl_plan (2026_04_24_100001)
**Contenido**: Catálogo de planes (con datos base insertados)
- Gratuito (0€, 1 propiedad)
- Básico (€9.99, 3 propiedades)
- Pro (€29.99, 10 propiedades)

#### 2. tbl_historial_propiedad (2026_04_24_100002)
**Contenido**: Auditoría de cambios de propiedades
- Registra cambios de estado, precio, etc.

#### 3. tbl_valoracion (2026_04_24_100003)
**Estado**: Estructura únicamente (implementación futura)
- Tabla para calificaciones de alquileres

#### 4. tbl_favorito (2026_04_24_100004)
**Estado**: Estructura únicamente (implementación futura)
- Tabla para propiedades favoritas

#### 5. tbl_visita (2026_04_24_100005)
**Estado**: Estructura únicamente (implementación futura)
- Tabla para seguimiento de visitas

---

## FASE 2: SEEDERS ✅ COMPLETADO

### Seeders Actualizados

| Archivo | Cambios |
|---------|---------|
| **UsuarioSeeder.php** | Agregados: dni, fecha_nacimiento, iban, direccion_fiscal, tipo_arrendador, verificado_identidad |
| **NotificacionSeeder.php** | Expandido a 17 notificaciones; campos individuales sin JSON |
| **SuscripcionSeeder.php** | Usa id_plan_fk; agregado precio_pagado_suscripcion |
| **SolicitudArrendadorSeeder.php** | Cambiado: 15 campos individuales en lugar de JSON |
| **PlanSeeder.php** | CREADO: Nuevo seeder para catálogo de planes |
| **PropiedadSeeder.php** | Removido: gastos_propiedad y función generarGastos() |
| **ArrendadorDemoSeeder.php** | Removido: gastos_propiedad |
| **DatabaseSeeder.php** | Agregado: PlanSeeder en orden correcto (antes de SuscripcionSeeder) |

### Notificaciones Creadas (17 totales)

**Para Inquilinos**: pago_vencido, nuevo_mensaje, incidencia_resuelta, contrato_vencimiento
**Para Arrendadores**: nuevo_inquilino, propiedad_publicada, pago_recibido, documento_requerido
**Para Gestores**: incidencia_reportada, propiedad_alquilada
**Para Admin**: solicitud_arrendador_pendiente, propiedad_pendiente_aprobacion, alerta_sistema, estadística_semanal, incidencia_critica, mantenimiento_programado

---

## FASE 3: MODELOS ELOQUENT ✅ COMPLETADO

### Modelos Modificados

#### Usuario.php
```php
$fillable += [
    'dni_usuario',
    'fecha_nacimiento_usuario', 
    'iban_usuario',
    'direccion_fiscal_usuario',
    'tipo_arrendador_usuario',
    'verificado_identidad_usuario',
];

$casts += [
    'verificado_identidad_usuario' => 'boolean',
    'fecha_nacimiento_usuario' => 'date',
];
```

#### Propiedad.php
```php
// Removido
$fillable -= ['gastos_propiedad'];
$casts -= ['gastos_propiedad' => 'array'];

// Agregado
$fillable += [
    'id_admin_aprueba_fk',
    'banos_propiedad',
    'notas_admin_propiedad',
    'aprobada_propiedad',
];

$casts += [
    'banos_propiedad' => 'integer',
    'aprobada_propiedad' => 'boolean',
];
```

#### Notificacion.php
```php
// Removido
$fillable -= ['datos_notificacion'];
$casts -= ['datos_notificacion' => 'array'];

// Agregado
$fillable += [
    'titulo_notificacion',
    'mensaje_notificacion',
    'url_notificacion',
    'icono_notificacion',
    'color_notificacion',
    'tipo_entidad_notificacion',
    'id_entidad_notificacion',
];

$casts += [
    'id_entidad_notificacion' => 'integer',
];
```

#### SolicitudArrendador.php
```php
// Removido
$fillable -= ['datos_solicitud_arrendador'];
$casts -= ['datos_solicitud_arrendador' => 'array'];

// Agregado (15 campos)
$fillable += [ /* todos los campos normalizados */ ];

$casts += [
    'fecha_nacimiento_solicitud' => 'date',
    'fecha_aceptacion_solicitud' => 'date',
    'es_propietario_solicitud' => 'boolean',
    'acepta_terminos_solicitud' => 'boolean',
    'acepta_veracidad_solicitud' => 'boolean',
    'num_propiedades_previstas_solicitud' => 'integer',
];
```

#### Suscripcion.php
```php
// Removido
$fillable -= ['plan_suscripcion', 'max_propiedades_suscripcion'];

// Agregado
$fillable += [
    'id_plan_fk',
    'precio_pagado_suscripcion',
];

$casts += [
    'precio_pagado_suscripcion' => 'decimal:2',
];

// Nueva relación
public function plan(): BelongsTo {
    return $this->belongsTo(Plan::class, 'id_plan_fk', 'id_plan');
}
```

### Modelos Creados

#### Plan.php
- Modelo para catálogo de planes
- Relación: hasMany(Suscripcion)
- Contiene 3 planes base

#### HistorialPropiedad.php
- Modelo para auditoría de propiedades
- Relaciones: belongsTo(Propiedad), belongsTo(Usuario)

---

## FASE 4: CONTROLLERS 🔄 EN PROGRESO

### Controllers Identificados con Referencias JSON (5 archivos)

| Controller | Líneas | Tipo de Cambio |
|-----------|--------|---------------|
| Admin/SolicitudController.php | 173, 194, 230-233 | whereJsonContains → where, json_decode, accesos directos |
| Admin/DashboardController.php | 61 | Remover select de JSON |
| Gestor/PropiedadController.php | 450, 455 | Remover gastos_propiedad y json_decode |
| Arrendador/PrecioGastoController.php | 33, 63, 84, 132 | REFACTORIZACIÓN MAYOR (controller dedicado a gastos) |
| Arrendador/PropiedadController.php | 54, 58, 71, 227, 302 | Remover validaciones y lógica de gastos |

**Acción Requerida**: 
- Reemplazar todas las referencias a campos JSON eliminados
- Cambiar whereJsonContains por where normal
- Reemplazar json_decode por acceso directo a campos
- Actualizar transacciones para incluir auditoría (HistorialPropiedad)

---

## FASE 5: VISTAS BLADE 📋 POR HACER

**Archivos afectados**: ~15 templates Blade
- Buscar: json_decode() en Blade
- Buscar: referencias a datos_* o gastos_*
- Actualizar iteraciones sobre datos

---

## FASE 6: JAVASCRIPT 📋 POR HACER

**Archivos afectados**: ~20 archivos .js
- Buscar: JSON.parse() para campos eliminados
- Buscar: comunicaciones API con datos antiguos
- Actualizar estructuras de datos esperadas

---

## RESUMEN ESTADÍSTICO

| Aspecto | Cantidad | Estado |
|---------|----------|--------|
| **Migraciones Modificadas** | 8 | ✅ Completado |
| **Nuevas Migraciones** | 5 | ✅ Completado |
| **Seeders Actualizados** | 8 | ✅ Completado |
| **Modelos Actualizados** | 5 | ✅ Completado |
| **Modelos Creados** | 2 | ✅ Completado |
| **Controllers por Revisar** | 35+ | 🔄 En progreso |
| **Vistas por Revisar** | 15+ | 📋 Por hacer |
| **Archivos JS por Revisar** | 20+ | 📋 Por hacer |
| **Otros Roles por Auditar** | 12+ | 📋 Por hacer |

---

## CAMBIOS CLAVE - PRIORIDADES

### ✅ NORMALIZACION COMPLETADA
1. JSON eliminado de: `datos_notificacion`, `datos_solicitud_arrendador`, `gastos_propiedad`
2. Todos los campos normalizados a columnas individuales
3. Relaciones FK establecidas correctamente

### 🔄 PRÓXIMAS PRIORIDADES
1. **CRÍTICA**: Actualizar Admin/SolicitudController
2. **CRÍTICA**: Refactorizar Arrendador/PrecioGastoController
3. **ALTA**: Revisar otros controllers admin
4. **ALTA**: Auditar vistas Blade
5. **MEDIA**: Auditar JavaScript

### 📊 IMPACTO POR ROL
- **Admin**: 7 controllers, cambios extensos
- **Arrendador**: 8 controllers, cambios extensos (especialmente PrecioGasto)
- **Inquilino**: 1 controller, cambios mínimos
- **Gestor**: 3 controllers, cambios medios
- **Otros**: 3+ controllers raíz

---

## NOTAS TÉCNICAS

### Cambio Mayor: Normalización Notificación
La tabla `tbl_notificacion` pasó de:
```
datos_notificacion: {"mensaje": "..."}  // JSON
```
A:
```
titulo_notificacion: "..."
mensaje_notificacion: "..."
url_notificacion: "..."
icono_notificacion: "..."
color_notificacion: "..."
tipo_entidad_notificacion: "..."
id_entidad_notificacion: ...
```

### Cambio Mayor: Normalización Solicitud Arrendador
La tabla `tbl_solicitud_arrendador` pasó de:
```
datos_solicitud_arrendador: { "nombre_empresa": "...", "ciudad": "...", ... }  // JSON
```
A 15 campos individuales normalizados.

### Transacciones de BD
Se recomienda que todos los controllers que actualicen múltiples tablas usen:
```php
DB::beginTransaction();
try {
    // cambios
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

### Auditoría
Para cambios de propiedad, registrar en `tbl_historial_propiedad`:
```php
HistorialPropiedad::create([
    'id_propiedad_fk' => $id_propiedad,
    'id_usuario_fk' => $id_usuario,
    'tipo_cambio' => 'aprobacion|rechazo|modificacion',
    'campo_modificado' => 'estado|precio|...',
    'valor_anterior' => '...',
    'valor_nuevo' => '...',
    'comentario' => 'Notas del cambio',
]);
```

---

## PRÓXIMOS PASOS

1. ✅ Limpiar migraciones obsoletas
2. ✅ Actualizar seeders
3. ✅ Actualizar modelos
4. 🔄 **SIGUIENTE**: Actualizar controllers (priorizar Admin/SolicitudController)
5. 📋 Auditar vistas Blade
6. 📋 Auditar JavaScript
7. 📋 Revisar otros roles
8. 📋 Generar README_CAMBIOS final

---

*Documento generado: 2026-04-24*
*Autor: GitHub Copilot*
*Instrucciones: SpotStay - copilot-instructions.md v1.0*
