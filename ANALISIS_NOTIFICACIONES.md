# Análisis Completo del Sistema de Notificaciones - SpotStay

## 1. MODELO: Notificacion (Base de Datos y ORM)

### Ubicación
- **Modelo**: [app/Models/Notificacion.php](app/Models/Notificacion.php)
- **Tabla**: `tbl_notificacion`
- **Schema**: [database/spotstay_schema.sql](database/spotstay_schema.sql#L391)

### Estructura de la Tabla
```sql
CREATE TABLE `tbl_notificacion` (
    `id_notificacion` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_usuario_fk` BIGINT UNSIGNED NOT NULL,           -- Usuario destino (FK → tbl_usuario)
    `tipo_notificacion` VARCHAR(100) NOT NULL,          -- Tipo: 'nueva_incidencia', 'pago_realizado', etc.
    `titulo_notificacion` VARCHAR(200) NOT NULL,        -- Título mostrado
    `mensaje_notificacion` TEXT NOT NULL,               -- Contenido/descripción
    `url_notificacion` VARCHAR(500) DEFAULT NULL,       -- URL a la que navega al hacer click
    `icono_notificacion` VARCHAR(50) DEFAULT NULL,      -- Ícono Bootstrap (ej: 'bell', 'chat-dots')
    `color_notificacion` VARCHAR(20) DEFAULT NULL,      -- Color hex (ej: '#DC2626')
    `tipo_entidad_notificacion` VARCHAR(50) DEFAULT NULL,  -- Tipo de entidad relacionada (ej: 'propiedad', 'incidencia')
    `id_entidad_notificacion` BIGINT UNSIGNED DEFAULT NULL, -- ID de la entidad relacionada
    `leida_notificacion` TINYINT(1) NOT NULL DEFAULT 0,    -- ¿Fue leída?
    `leida_en_notificacion` TIMESTAMP NULL DEFAULT NULL,   -- Cuándo se marcó como leída
    `creado_notificacion` TIMESTAMP NULL DEFAULT NULL,     -- Timestamp de creación
    `actualizado_notificacion` TIMESTAMP NULL DEFAULT NULL, -- Timestamp de actualización
    
    INDEX `tbl_notificacion_usuario_leida_index` (`id_usuario_fk`, `leida_notificacion`),
    CONSTRAINT `tbl_notificacion_id_usuario_fk_foreign` 
        FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Modelo PHP
```php
class Notificacion extends Model
{
    protected $table = 'tbl_notificacion';
    protected $primaryKey = 'id_notificacion';
    
    protected $fillable = [
        'id_usuario_fk', 'tipo_notificacion', 'titulo_notificacion', 
        'mensaje_notificacion', 'url_notificacion', 'icono_notificacion',
        'color_notificacion', 'tipo_entidad_notificacion', 'id_entidad_notificacion',
        'leida_notificacion', 'leida_en_notificacion', 'creado_notificacion',
        'actualizado_notificacion'
    ];
    
    protected $casts = [
        'leida_notificacion' => 'boolean',
        'id_entidad_notificacion' => 'integer',
        'leida_en_notificacion' => 'datetime',
        'creado_notificacion' => 'datetime',
        'actualizado_notificacion' => 'datetime',
    ];
    
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
```

---

## 2. CREACIÓN DE NOTIFICACIONES

### Punto Central: ActividadService
**Ubicación**: [app/Services/ActividadService.php](app/Services/ActividadService.php)

El servicio `ActividadService` es el **único responsable** de crear notificaciones. Contiene un método privado `crear()` que centraliza toda la lógica de inserción:

```php
private function crear(
    int $usuarioId, 
    string $tipo, 
    string $titulo, 
    string $mensaje, 
    ?string $url, 
    string $icono, 
    string $color, 
    ?string $tipoEntidad, 
    ?int $idEntidad
): void
```

**Lógica**:
1. Verifica que el usuario existe (valida FK)
2. Inserta un registro en `tbl_notificacion` con:
   - `leida_notificacion = false` (siempre nueva)
   - `creado_notificacion = ahora` (timestamp)
   - `actualizado_notificacion = ahora`

### Métodos Públicos de ActividadService

| Método | Trigger | Tipo | Icono | Color | URL Destino |
|--------|---------|------|-------|-------|-------------|
| `mensajeNuevo()` | Nuevo mensaje en chat | `mensaje_nuevo` | chat-dots | #7C3AED | `/miembro/chat/{id}` o `/gestor/mensajes?activa={id}` |
| `avisoImportante()` | Admin envía aviso manual | `aviso_importante` | megaphone | #035498 | Personalizada |
| `incidenciaCreada()` | Nueva incidencia reportada | `nueva_incidencia` | exclamation-triangle | #DC2626 | `/gestor/incidencias/{id}` |
| `incidenciaCambioEstado()` | Estado incidencia cambia | `incidencia_actualizada` | arrow-left-right | #2563EB | `/gestor/incidencias/{id}` |
| `pagoRealizado()` | Inquilino paga cuota | `pago_realizado` | check-circle | #16A34A | `/gestor/propiedades/{id}` |
| `pagoAtrasado()` | Cuota vencida sin pagar | `pago_atrasado` | clock-history | #EA580C | `/gestor/propiedades/{id}` |
| `gastoAtrasado()` | Gasto vencido sin pagar | `pago_atrasado` | clock-history | #EA580C | `/gestor/propiedades/{id}` |
| `presupuestoCreado()` | Gestor crea presupuesto | `presupuesto_creado` | cash-coin | #D97706 | `/gestor/incidencias/{id}` |
| `gastoCreado()` | Gestor crea gasto/recibo | `gasto_creado` | receipt | #0891B2 | `/gestor/propiedades/{id}` |
| `propiedadEstadoCambiado()` | Propiedad cambia estado | `propiedad_estado` | building-gear | #035498 | `/gestor/propiedades/{id}` |
| `alquilerCreado()` | Nueva solicitud de alquiler | `alquiler_creado` | house-check | #059669 | `/gestor/propiedades/{id}` |
| `alquilerAprobado()` | Admin aprueba alquiler | `alquiler_aprobado` | check-lg | #16A34A | `/gestor/propiedades/{id}` |
| `contratoFirmado()` | Contrato es firmado | `contrato_firmado` | file-earmark-check | #059669 | `/gestor/propiedades/{id}` |

### Dónde se Crean (Controllers que usan ActividadService)

| Controlador | Métodos | Línea |
|------------|---------|------|
| [Admin/IncidenciaController.php](app/Http/Controllers/Admin/IncidenciaController.php) | `incidenciaCreada()` | 529 |
| [Admin/PropiedadController.php](app/Http/Controllers/Admin/PropiedadController.php) | `propiedadEstadoCambiado()` | 268, 475 |
| [Admin/AlquilerController.php](app/Http/Controllers/Admin/AlquilerController.php) | `alquilerCreado()`, `alquilerAprobado()`, `propiedadEstadoCambiado()` | 467, 474, 687, 836 |
| [Admin/ConfiguracionController.php](app/Http/Controllers/Admin/ConfiguracionController.php) | `avisoImportante()` | 94-96 |
| [Gestor/IncidenciaController.php](app/Http/Controllers/Gestor/IncidenciaController.php) | `presupuestoCreado()` | 444 |
| [Gestor/PropiedadController.php](app/Http/Controllers/Gestor/PropiedadController.php) | `gastoCreado()` | 507 |
| [Arrendador/ContratoController.php](app/Http/Controllers/Arrendador/ContratoController.php) | `contratoFirmado()` | (inyectado en constructor) |
| [Miembro/MensajesController.php](app/Http/Controllers/Miembro/MensajesController.php) | `mensajeNuevo()` | 200-210 |
| [Miembro/SolicitudAlquilerController.php](app/Http/Controllers/Miembro/SolicitudAlquilerController.php) | `alquilerCreado()`, `propiedadEstadoCambiado()` | 70-80 |
| [inquilino/InquilinoPagoController.php](app/Http/Controllers/inquilino/InquilinoPagoController.php) | `pagoRealizado()` | 639 |

---

## 3. VISUALIZACIÓN DE NOTIFICACIONES

### Vistas Principales

#### A. Dropdown en Layout Gestor
**Archivo**: [resources/views/layouts/gestor.blade.php](resources/views/layouts/gestor.blade.php)

```blade
<!-- Botón campana -->
<button class="campana-container" id="campanaContainer" aria-label="Ver notificaciones">
    @if(($notificacionesGestorSinLeer ?? 0) > 0)
        <span class="badge-campana" id="badgeCampana">{{ $notificacionesGestorSinLeer }}</span>
    @endif
</button>

<!-- Dropdown con notificaciones -->
<div class="campana-dropdown" id="campanaDropdown">
    <h6 class="campana-dropdown-titulo">Notificaciones</h6>
    
    @forelse($notificacionesGestor as $notificacion)
        <div class="campana-item-wrap">
            <a class="campana-item" data-notif-id="{{ $notificacion->id_notificacion }}">
                <!-- Icono coloreado -->
                <i class="bi bi-{{ $notificacion->icono_notificacion }}" 
                   style="color: {{ $notificacion->color_notificacion }}"></i>
                
                <!-- Contenido -->
                <div class="campana-item-contenido">
                    <span class="campana-item-titulo">{{ $notificacion->titulo_notificacion }}</span>
                    <span class="campana-item-mensaje">{{ $notificacion->mensaje_notificacion }}</span>
                </div>
                
                <!-- Botón eliminar -->
                <button class="campana-item-borrar" data-notif-id="{{ $notificacion->id_notificacion }}">✕</button>
            </a>
        </div>
    @empty
        <p class="text-muted">Sin notificaciones</p>
    @endforelse
</div>
```

**Características**:
- Muestra **máximo 6 notificaciones sin leer** (última carga de página)
- Ordenadas por `creado_notificacion DESC`
- Ícono coloreado según `color_notificacion`
- Badge dinámico con contador de sin leer

#### B. Dashboard Admin - Timeline de Actividad
**Archivo**: [resources/views/admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php)

```blade
@foreach($actividadReciente as $notif)
    <div class="timeline-item">
        <i class="timeline-icono bi bi-{{ $notif->icono_notificacion }}"
           style="background-color: {{ $notif->color_notificacion ?? '#035498' }}"></i>
        <p class="timeline-texto">{{ $notif->titulo_notificacion }}</p>
        <span class="timeline-hora">{{ Carbon::parse($notif->creado_notificacion)->diffForHumans() }}</span>
    </div>
@endforeach
```

#### C. Configuración Admin - Crear Notificaciones Manuales
**Archivo**: [resources/views/admin/configuracion.blade.php](resources/views/admin/configuracion.blade.php)

Formulario para que el admin envíe notificaciones personalizadas a:
- Todos los usuarios de un rol
- Todos los usuarios con acceso a una propiedad específica
- Un usuario específico

---

### Inyección en Todos los Layouts

**Archivo**: [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) (línea 40-90)

Cada vez que se carga una vista, el proveedor calcula:
- `$notificacionesGestorSinLeer`: Contador
- `$notificacionesGestor`: Últimas 6 notificaciones sin leer
- `$notificacionesUsuario`: Similar para otros roles

```php
$notificacionesQueryBase = DB::table('tbl_notificacion')
    ->where('id_usuario_fk', $usuario->id_usuario);

$notificacionesUsuarioSinLeer = (clone $notificacionesQueryBase)
    ->where('leida_notificacion', false)
    ->count();

$notificacionesUsuario = (clone $notificacionesQueryBase)
    ->where('leida_notificacion', false)
    ->select([...])
    ->orderBy('creado_notificacion', 'desc')
    ->limit(6)
    ->get();
```

---

## 4. SINCRONIZACIÓN / ACTUALIZACIÓN EN TIEMPO REAL

### ⚠️ IMPORTANTE: NO hay Polling ni WebSocket

El sistema **NO utiliza**:
- ❌ Polling con intervals regulares
- ❌ WebSocket o Server-Sent Events
- ❌ Long-polling

### Cómo se Actualiza Actualmente

**1. Al cargar página**: AppServiceProvider inyecta notificaciones del usuario
**2. Al marcar/eliminar**: AJAX actualiza el DOM dinámicamente sin recargar

#### AJAX - Marcar como Leída
**Endpoint**: `POST /notificaciones/{id}/marcar-leida`

```javascript
// public/js/admin/layout.js (línea 197)
fetch('/notificaciones/' + id + '/marcar-leida', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
    }
})
.then(resp => resp.json())
.then(data => {
    if (data.ok) {
        // Remover item del DOM
        // Actualizar badge counter
    }
});
```

#### AJAX - Eliminar Notificación
**Endpoint**: `POST /notificaciones/{id}/eliminar`

```javascript
// public/js/admin/layout.js (línea 167)
fetch('/notificaciones/' + id + '/eliminar', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
    }
})
.then(resp => resp.json())
.then(data => {
    if (data.ok) {
        // Remover item del DOM
        // Decrementar badge
    }
});
```

### Controlador que maneja los endpoints
**Archivo**: [app/Http/Controllers/Gestor/NotificacionController.php](app/Http/Controllers/Gestor/NotificacionController.php)

```php
class NotificacionController extends Controller
{
    public function marcarLeida(Request $request, int $id)
    {
        $gestorId = Auth::user()?->id_usuario;
        
        $updated = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)  // Seguridad: solo su propia notificación
            ->update([
                'leida_notificacion' => true,
                'leida_en_notificacion' => now()
            ]);

        return response()->json(['ok' => (bool) $updated]);
    }

    public function eliminar(Request $request, int $id)
    {
        $gestorId = Auth::user()?->id_usuario;
        
        $deleted = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', $gestorId)  // Seguridad: solo su propia notificación
            ->delete();

        return response()->json(['ok' => (bool) $deleted]);
    }
}
```

### Rutas
**Archivo**: [routes/web.php](routes/web.php)

```php
Route::post('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida']);
Route::post('/notificaciones/{id}/eliminar', [NotificacionController::class, 'eliminar']);
```

---

## 5. ELIMINACIÓN DE NOTIFICACIONES

### Métodos de Eliminación

#### 1. Manual por Usuario
- Usuario hace click en botón ✕ en dropdown
- AJAX → `POST /notificaciones/{id}/eliminar`
- Registro se borra de BD

#### 2. Cascada de Base de Datos
- Cuando se elimina un usuario:
  ```sql
  CONSTRAINT `tbl_notificacion_id_usuario_fk_foreign` 
      FOREIGN KEY (`id_usuario_fk`) REFERENCES `tbl_usuario` (`id_usuario`) 
      ON DELETE CASCADE
  ```
- Todas sus notificaciones se eliminan automáticamente

#### 3. Sin Limpieza Automática
⚠️ **Nota**: No hay proceso cron que limpie notificaciones antiguas.
- Las notificaciones se quedan en la BD indefinidamente
- Solo se muestran las 6 últimas sin leer en el dropdown
- Histórico completo disponible en dashboard admin (timeline)

---

## 6. TIPOS DE NOTIFICACIONES (13 en Total)

### Definidos en ActividadService::tiposActividad()

```php
public static function tiposActividad(): array
{
    return [
        'nueva_incidencia' => [
            'label' => 'Incidencias nuevas',
            'color' => '#DC2626',  // Rojo
            'icono' => 'exclamation-triangle'
        ],
        'incidencia_actualizada' => [
            'label' => 'Cambios de estado',
            'color' => '#2563EB',  // Azul
            'icono' => 'arrow-left-right'
        ],
        'pago_realizado' => [
            'label' => 'Pagos recibidos',
            'color' => '#16A34A',  // Verde
            'icono' => 'check-circle'
        ],
        'pago_atrasado' => [
            'label' => 'Pagos atrasados',
            'color' => '#EA580C',  // Naranja
            'icono' => 'clock-history'
        ],
        'presupuesto_creado' => [
            'label' => 'Presupuestos',
            'color' => '#D97706',  // Ámbar
            'icono' => 'cash-coin'
        ],
        'gasto_creado' => [
            'label' => 'Recibos creados',
            'color' => '#0891B2',  // Cyan
            'icono' => 'receipt'
        ],
        'mensaje_nuevo' => [
            'label' => 'Mensajes',
            'color' => '#7C3AED',  // Púrpura
            'icono' => 'chat-dots'
        ],
        'aviso_importante' => [
            'label' => 'Avisos importantes',
            'color' => '#035498',  // Azul oscuro
            'icono' => 'megaphone'
        ],
        'alquiler_pendiente' => [
            'label' => 'Alquiler pendiente',
            'color' => '#035498',
            'icono' => 'calendar-event'
        ],
        'propiedad_estado' => [
            'label' => 'Propiedad cambió de estado',
            'color' => '#035498',
            'icono' => 'building-gear'
        ],
        'alquiler_creado' => [
            'label' => 'Nuevo alquiler creado',
            'color' => '#059669',  // Verde oscuro
            'icono' => 'house-check'
        ],
        'alquiler_aprobado' => [
            'label' => 'Alquiler aprobado',
            'color' => '#16A34A',  // Verde más claro
            'icono' => 'check-lg'
        ],
        'contrato_firmado' => [
            'label' => 'Contrato firmado',
            'color' => '#059669',
            'icono' => 'file-earmark-check'
        ],
    ];
}
```

### Tipologías por Contexto

**Incidencias** (Mantenimiento):
- `nueva_incidencia`: Una incidencia es reportada
- `incidencia_actualizada`: Estado cambia (pendiente → resuelto, etc.)
- `presupuesto_creado`: Costo estimado para la incidencia

**Pagos y Dinero**:
- `pago_realizado`: Inquilino paga la cuota
- `pago_atrasado`: Cuota vencida sin pago
- `gasto_creado`: Gestor crea nuevo gasto/recibo

**Propiedades**:
- `propiedad_estado`: Cambio de estado (alquilada → disponible, etc.)

**Alquileres**:
- `alquiler_creado`: Nueva solicitud
- `alquiler_aprobado`: Admin aprueba alquiler
- `alquiler_pendiente`: Alquiler sin completar (no usado en código aún)

**Comunicación**:
- `mensaje_nuevo`: Nuevo mensaje en chat

**Avisos**:
- `aviso_importante`: Notificación manual del admin
- `contrato_firmado`: Contrato es firmado digitalmente

---

## 7. FLUJO DE VIDA DE UNA NOTIFICACIÓN

```
┌─────────────────────────────────────────────────────────────────────┐
│                   CICLO DE VIDA DE UNA NOTIFICACIÓN                │
└─────────────────────────────────────────────────────────────────────┘

NACIMIENTO
──────────────────────────────────────────────────────────────────────

1. EVENTO DISPARA EN APLICACIÓN
   ├─ Admin crea incidencia
   ├─ Inquilino paga cuota
   ├─ Gestor crea presupuesto
   ├─ Nuevo mensaje enviado
   └─ ... (cualquiera de 13 tipos)

2. CONTROLADOR LLAMA ActividadService
   │
   ├─ Ejemplo: IncidenciaController.crear()
   │   └─> new ActividadService()->incidenciaCreada($usuarioId, ...)
   │
   └─ Ejemplo: InquilinoPagoController.pagarCuota()
       └─> new ActividadService()->pagoRealizado($usuarioId, ...)

3. ActividadService::crear() INSERTA EN BD
   │
   └─ INSERT INTO tbl_notificacion
      ├─ id_usuario_fk = $usuarioId (destinatario)
      ├─ tipo_notificacion = (ej: 'nueva_incidencia')
      ├─ titulo_notificacion = "Nueva incidencia en Piso 3B"
      ├─ mensaje_notificacion = "Agua goteando — reportada por Juan"
      ├─ url_notificacion = "/gestor/incidencias/42"
      ├─ icono_notificacion = "exclamation-triangle"
      ├─ color_notificacion = "#DC2626"
      ├─ tipo_entidad_notificacion = "incidencia"
      ├─ id_entidad_notificacion = 42
      ├─ leida_notificacion = FALSE
      ├─ leida_en_notificacion = NULL
      ├─ creado_notificacion = NOW()
      └─ actualizado_notificacion = NOW()

       ✅ NOTIFICACIÓN CREADA


VISUALIZACIÓN
──────────────────────────────────────────────────────────────────────

4a. CARGA DE PÁGINA → AppServiceProvider
    │
    └─ Para cada usuario autenticado:
       ├─ SELECT * FROM tbl_notificacion
       │   WHERE id_usuario_fk = $usuarioId
       │   AND leida_notificacion = FALSE
       │   ORDER BY creado_notificacion DESC
       │   LIMIT 6
       │
       └─ Inyecta en todas las vistas:
          ├─ $notificacionesGestorSinLeer (contador)
          └─ $notificacionesGestor (colección de 6)

4b. BLADE RENDERIZA DROPDOWN
    │
    └─ <div class="campana-dropdown">
         ├─ Badge en campana (contador de sin leer)
         │
         └─ Para cada notificación:
            ├─ <div class="campana-item" data-notif-id="{{ id }}">
            │   ├─ Icono coloreado
            │   ├─ Título + Mensaje
            │   └─ Botón ✕ (eliminar)
            │
            ├─ onclick → navega a url_notificacion
            └─ onclick del ✕ → AJAX eliminar


ACTUALIZACIÓN EN TIEMPO REAL
──────────────────────────────────────────────────────────────────────

5a. USUARIO HACE CLICK EN NOTIFICACIÓN
    │
    ├─ Opciones:
    │  ├─ Navega a url_notificacion (ej: /gestor/incidencias/42)
    │  │  └─ Pero NO marca como leída automáticamente
    │  │     (debe hacer click explícito en el item)
    │  │
    │  └─ Hace click en el item sin link:
    │     └─ AJAX POST /notificaciones/{id}/marcar-leida
    │        ├─ Backend: UPDATE tbl_notificacion
    │        │   SET leida_notificacion = TRUE,
    │        │       leida_en_notificacion = NOW()
    │        │   WHERE id_notificacion = $id
    │        │   AND id_usuario_fk = $usuarioId (seguridad)
    │        │
    │        └─ Frontend: Remover item del DOM
    │           ├─ Decrementar badge
    │           └─ Si badge = 0, quitar badge completamente

5b. USUARIO HACE CLICK EN BOTÓN ELIMINAR (✕)
    │
    └─ AJAX POST /notificaciones/{id}/eliminar
       ├─ Backend: DELETE FROM tbl_notificacion
       │   WHERE id_notificacion = $id
       │   AND id_usuario_fk = $usuarioId (seguridad)
       │
       └─ Frontend: Remover elemento del DOM
          └─ Decrementar badge


MUERTE
──────────────────────────────────────────────────────────────────────

6. ELIMINACIÓN (uno de estos caminos)

   ├─ MANUAL
   │  ├─ Usuario hace click ✕ en dropdown
   │  └─ DELETE via /notificaciones/{id}/eliminar

   ├─ CASCADA (cuando se elimina usuario)
   │  ├─ Admin elimina usuario
   │  └─ Todas sus notificaciones se borran por FK ON DELETE CASCADE

   └─ (NO HAY LIMPIEZA AUTOMÁTICA POR ANTIGÜEDAD)
      └─ Las notificaciones viejas se quedan en BD para siempre


ESTADÍSTICAS
──────────────────────────────────────────────────────────────────────

7. VISTAS DE HISTÓRICO

   A. Dashboard Admin (Timeline)
      ├─ SELECT FROM tbl_notificacion
      │  WHERE (sin filtro de leida)
      │  LIMIT 10-20 últimas
      │
      └─ Muestra histórico visual de toda la actividad

   B. Próximamente (NO IMPLEMENTADO)
      └─ Panel de notificaciones para usuario (historial completo)


ÍNDICES Y OPTIMIZACIÓN
──────────────────────────────────────────────────────────────────────

tbl_notificacion_usuario_leida_index (id_usuario_fk, leida_notificacion)
├─ Optimiza queries que filtran por usuario Y estado de lectura
├─ Usado por: AppServiceProvider al cargar notificaciones del usuario
└─ ✅ INDEXADO

```

---

## 8. FLUJO TÉCNICO: REQUEST → NOTIFICACIÓN

### Caso Real: Nueva Incidencia

```
1️⃣ USUARIO REPORTA INCIDENCIA
   POST /admin/incidencias/crear
   ├─ Datos: propiedad, título, descripción, etc.
   └─ Usuario autenticado: gestor_id = 5

2️⃣ CONTROLADOR PROCESA
   [Admin/IncidenciaController::crear()]
   ├─ Valida datos
   ├─ INSERT INTO tbl_incidencia (...)
   ├─ $incidenciaId = 42
   │
   └─ 🔔 CREA NOTIFICACIÓN
      new ActividadService()->incidenciaCreada(
          $gestorId = 5,           // Quien recibe la notif
          $incidenciaId = 42,      // Qué se crea
          $propiedadTitulo = "Piso 3B",
          $incidenciaTitulo = "Agua goteando",
          $reportaNombre = "Juan"
      )

3️⃣ SERVICE INSERTA EN BD
   [ActividadService::incidenciaCreada()]
   └─ $this->crear(
        usuarioId: 5,
        tipo: 'nueva_incidencia',
        titulo: "Nueva incidencia en Piso 3B",
        mensaje: "Agua goteando — reportada por Juan",
        url: "/gestor/incidencias/42",
        icono: "exclamation-triangle",
        color: "#DC2626",
        tipoEntidad: "incidencia",
        idEntidad: 42
      )

   [ActividadService::crear()]
   ├─ ✅ Usuario existe? Sí (id 5 = gestor)
   │
   └─ DB::table('tbl_notificacion')->insert([
        'id_usuario_fk' => 5,
        'tipo_notificacion' => 'nueva_incidencia',
        'titulo_notificacion' => 'Nueva incidencia en Piso 3B',
        'mensaje_notificacion' => 'Agua goteando — reportada por Juan',
        'url_notificacion' => '/gestor/incidencias/42',
        'icono_notificacion' => 'exclamation-triangle',
        'color_notificacion' => '#DC2626',
        'tipo_entidad_notificacion' => 'incidencia',
        'id_entidad_notificacion' => 42,
        'leida_notificacion' => false,
        'creado_notificacion' => Carbon::now(),
        'actualizado_notificacion' => Carbon::now(),
      ])

4️⃣ RESPUESTA AL USUARIO
   ✅ Incidencia creada
   🔔 Notificación guardada en BD

5️⃣ GESTOR VE LA NOTIFICACIÓN

   Opción A: Abre nueva pestaña en el mismo navegador
   ├─ Navega a /gestor/dashboard
   ├─ AppServiceProvider carga notificaciones sin leer
   │  SELECT FROM tbl_notificacion WHERE id_usuario = 5 AND leida = FALSE LIMIT 6
   ├─ Inyecta en vistas: $notificacionesGestor = [...]
   └─ 🔔 Ve badge (1) y dropdown con notificación nueva

   Opción B: Ya tiene la página abierta
   ├─ NO verá la notificación automáticamente ❌
   ├─ Debe recargar la página (F5)
   └─ Entonces sí la ve (AppServiceProvider actualiza)

6️⃣ GESTOR INTERACTÚA

   A. Click en notificación
   ├─ JS: fetch POST /notificaciones/42/marcar-leida
   ├─ Backend: UPDATE tbl_notificacion SET leida = TRUE WHERE id = 42
   ├─ Frontend: Remover del DOM, decrementar badge
   └─ ✅ Marca como leída en BD

   B. Click en botón ✕
   ├─ JS: fetch POST /notificaciones/42/eliminar
   ├─ Backend: DELETE FROM tbl_notificacion WHERE id = 42
   ├─ Frontend: Remover del DOM, decrementar badge
   └─ ✅ Elimina de BD

7️⃣ DESPUÉS

   ├─ Si vuelve a cargar página:
   │  └─ La notificación ya NO aparece (ni leída ni eliminada)
   │
   └─ En dashboard admin:
      └─ Sigue visible en timeline (histórico, sin filtro de leida)
```

---

## 9. PUNTOS CLAVE A RECORDAR

### ✅ Fortalezas del Sistema
1. **Centralizado**: Todas las notificaciones pasan por `ActividadService`
2. **Normalizado**: Tabla sin JSON, campos específicos
3. **Seguro**: Validaciones en controlador + check de usuario en BD
4. **Flexible**: 13 tipos predefinidos, extensible
5. **Visual**: Códigos de color e iconos consistentes
6. **Relaciones**: Vinculadas a entidades (incidencia, propiedad, etc.)

### ⚠️ Limitaciones Actuales
1. **Sin sincronización en tiempo real**
   - No hay polling ni WebSocket
   - Usuario debe recargar página para ver nuevas notificaciones
   - Solo AJAX para marcar/eliminar

2. **Sin histórico en UI del usuario**
   - Solo dropdown de 6 últimas (sin leer)
   - Resto solo visible en admin dashboard

3. **Sin límite de edad**
   - Notificaciones nunca se borran automáticamente
   - BD puede crecer indefinidamente

4. **Sin preferencias**
   - No hay opción para usuario de desactivar ciertos tipos
   - No hay silenciadores por tipo de notificación

5. **Sin opciones de entrega**
   - Solo en app (no hay email)
   - No hay push notifications

### 🔮 Posibles Mejoras Futuras
1. **Polling Light**: Cada 30-60s check notificaciones nuevas sin recargar
2. **Panel de Notificaciones**: Usuario pueda ver historial completo
3. **Preferencias**: Desactivar tipos específicos
4. **Limpieza**: Cron que archive/borre notificaciones > 90 días
5. **Email**: Notificaciones importantes por email
6. **Push**: Notificaciones push en navegador
7. **Filtros**: Por tipo de entidad en UI
8. **Búsqueda**: Buscar en histórico de notificaciones

---

## 10. ARCHIVOS RELACIONADOS - REFERENCIA RÁPIDA

| Función | Archivo | Línea |
|---------|---------|-------|
| **Modelo ORM** | `app/Models/Notificacion.php` | - |
| **Servicio centralizado** | `app/Services/ActividadService.php` | - |
| **Controlador (marcar/eliminar)** | `app/Http/Controllers/Gestor/NotificacionController.php` | - |
| **Inyección en vistas** | `app/Providers/AppServiceProvider.php` | 40-90 |
| **Layout dropdown** | `resources/views/layouts/gestor.blade.php` | 46-100 |
| **Dashboard timeline** | `resources/views/admin/dashboard.blade.php` | 237-250 |
| **Config admin** | `resources/views/admin/configuracion.blade.php` | 50-100 |
| **JS eventos** | `public/js/admin/layout.js` | 150-210 |
| **JS validación formulario** | `public/js/admin/configuracion.js` | 1-50 |
| **Schema BD** | `database/spotstay_schema.sql` | 391-410 |
| **Rutas** | `routes/web.php` | (buscar: notificaciones) |
| **Migrations** | `database/migrations/` | (buscar: notificacion) |

---

## CONCLUSIÓN

El sistema de notificaciones de SpotStay es **simple pero funcional**, centralizado en `ActividadService`, sin complejidades de sync tiempo real. Es apropiado para una aplicación monolítica pero tiene espacio para mejoras en UX (histórico, preferencias, sync). La estructura de datos está normalizada y lista para escalar.
