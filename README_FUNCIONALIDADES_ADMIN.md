# 📊 Funcionalidades del Rol ADMIN - SpotStay

> Documentación técnica detallada de dónde están ubicadas todas las funcionalidades del panel de administración.

---

## 📍 Estructura General

| Elemento | Ubicación |
|----------|-----------|
| **Vistas** | `resources/views/admin/` |
| **Controladores** | `app/Http/Controllers/Admin/` |
| **JavaScript** | `public/js/admin/` |
| **CSS** | `public/css/admin/` |
| **Rutas** | `routes/web.php` (líneas 53+) |
| **Layout** | `resources/views/layouts/admin.blade.php` |

---

## 🎯 DASHBOARD

### Ubicación
- **Vista**: `resources/views/admin/dashboard.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/DashboardController.php`
- **Ruta**: `GET /admin/dashboard`

### 📈 KPIs y sus Fuentes

| KPI | ID HTML | Cálculo | Fuente |
|-----|---------|---------|--------|
| **Usuarios Registrados** | No tiene (solo HTML) | `COUNT(tbl_usuario)` | `DashboardController->index()` línea: `$totalUsuarios = DB::table('tbl_usuario')->count();` |
| **Propiedades Activas** | No tiene | Estados: 'publicada', 'alquilada' | `$propiedadesActivas = DB::table('tbl_propiedad')->whereIn('estado_propiedad', ['publicada', 'alquilada'])->count();` |
| **Alquileres Pendientes** | No tiene | Estado: 'pendiente' en tbl_alquiler | `$alquileresPendientes = DB::table('tbl_alquiler')->where('estado_alquiler', 'pendiente')->count();` |
| **Solicitudes Nuevas** | No tiene | Estado: 'pendiente' en tbl_solicitud_arrendador | `$solicitudesNuevas = DB::table('tbl_solicitud_arrendador')->where('estado_solicitud_arrendador', 'pendiente')->count();` |

**Vista:** Los KPIs se renderizan directamente en HTML en líneas 19-54 usando variables Blade:
```blade
<div class="kpi-numero">{{ number_format($totalUsuarios) }}</div>
```

### 📋 Tabla de Alquileres Pendientes

**Ubicación en Vista**: Líneas 56-95
- **ID Tabla**: `#tablaAlquileres`
- **ID Body**: `#tbodyAlquileres`
- **Buscador**: `#buscadorAlquileres`

**Datos mostrados por fila**:
- Propiedad (titulo + ciudad)
- Inquilino (nombre)
- Estado (badge)
- Botones de acción (Aprobar/Rechazar)

**Atributos data en filas**:
```html
<tr data-id="..." data-nombre="..." data-inquilino="..." data-estado="...">
```

### 🔘 Botones de Acción

| Botón | ID | Acción | Ruta |
|-------|----|---------|----|
| **Aprobar** | `.btn-aprobar` | POST | `/admin/alquiler/{id}/aprobar` |
| **Rechazar** | `.btn-rechazar` | POST | `/admin/alquiler/{id}/rechazar` |
| **Ver Todos** | `.link-ver-todos` | GET | `/admin/alquileres` |

**Código en JS**: Los eventos se vinculan en `public/js/admin/dashboard.js`

### 📊 Tabla de Solicitudes Nuevas

**Ubicación**: Líneas 96-130 en la vista
- **Estructura**: Similar a alquileres
- **Ver Todas**: Enlace a `/admin/solicitudes`

---

## 👥 USUARIOS

### Ubicación
- **Vista**: `resources/views/admin/usuarios.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/UsuarioController.php`
- **Ruta**: `GET /admin/usuarios`

### 📈 KPIs Mini

| KPI | ID HTML | Cálculo | 
|-----|---------|---------|
| **Total Usuarios** | `#kpiTotalUsuarios` | `COUNT(tbl_usuario)` |
| **Activos** | `#kpiActivos` | `WHERE activo_usuario = 1` |
| **Inactivos** | `#kpiInactivos` | `WHERE activo_usuario = 0` |
| **Este Mes** | `#kpiEsteMes` | `WHERE creado_usuario >= DATE_SUB(NOW(), INTERVAL 1 MONTH)` |

**Cálculo en Controlador** (`UsuarioController->index()`):
```php
$totalUsuarios = DB::table('tbl_usuario')->count();
$activos = DB::table('tbl_usuario')->where('activo_usuario', 1)->count();
$inactivos = DB::table('tbl_usuario')->where('activo_usuario', 0)->count();
$esteMes = DB::table('tbl_usuario')->where('creado_usuario', '>=', Carbon::now()->subMonth())->count();
```

**Actualización en Tiempo Real**: Los IDs en JavaScript (`public/js/admin/usuarios.js`) se notifican cuando cambian datos.

### 🔍 Filtros y Búsqueda

**Ubicación en Vista**: Líneas 20-38

| Filtro | ID HTML | Tipo | Envío |
|--------|---------|------|-------|
| **Búsqueda** | `#buscadorUsuarios` | Input text | AJAX a `/admin/usuarios/filtrar` (onChange con debounce) |
| **Rol** | `#selectRol` | Select | Valores: admin, arrendador, inquilino, gestor, miembro |
| **Estado** | `#selectEstado` | Select | Valores: activo, inactivo |

**JavaScript**: `public/js/admin/usuarios.js`
- `asignarEventosFiltros()` vincula eventos
- `filtrarUsuarios()` hace la llamada AJAX
- Debounce de 300ms en búsqueda

**Ruta AJAX**: 
```
GET /admin/usuarios/filtrar?buscar=...&rol=...&estado=...&pagina=...
```

### 📄 Tabla de Usuarios

**Ubicación**: Líneas 58-115

| Columna | Visible en | Data Atributo |
|---------|-----------|---|
| Usuario | Todas | `data-nombre`, `data-email` |
| Rol | Todas | `data-rol` |
| Estado | Desktop | `data-estado` |
| Propiedades | Tablet+ | - |
| Fecha Registro | Tablet+ | - |
| Acciones | Todas | - |

**ID Tabla**: `#tablaUsuarios`
**ID Body**: `#tbodyUsuarios`

### ➕ Crear Nuevo Usuario

**Botón**: Línea 47, ID `#btnNuevoUsuario`
- **Ubicación Modal**: `resources/views/admin/usuarios.blade.php` (al final, líneas 150+)
- **ID Modal**: `#modalNuevoUsuario` (Bootstrap 5)
- **Eventos**: `public/js/admin/usuarios.js` función `abrirModalNuevo()`

**Flujo**:
1. Click en `#btnNuevoUsuario` → Abre modal
2. Validación en cliente (JS)
   - Búsqueda de email disponible: `GET /admin/usuarios/check-email?email=...`
   - Búsqueda de teléfono disponible: `GET /admin/usuarios/check-telefono?telefono=...`
3. Envío: `POST /admin/usuarios/crear`
   - Cuerpo: `nombre_usuario`, `email_usuario`, `telefono_usuario`, `password`, `id_rol`, etc.

**Transacción BD**: Sí (usuario + rol_usuario)
```php
DB::beginTransaction();
// INSERT usuario
// INSERT rol_usuario
DB::commit();
```

### ✏️ Editar Usuario

**Botón**: En tabla de usuarios, columna Acciones
- **ID**: `.btn-editar` con `data-id`
- **Modal ID**: `#modalEditarUsuario`

**Flujo**:
1. Click → Carga datos: `GET /admin/usuarios/{id}`
2. Abre modal con datos
3. Validaciones igual que crear
4. Envío: `POST /admin/usuarios/{id}/editar`

### 🔘 Toggle Estado

**Botón**: En tabla, columna Acciones
- **Evento**: Toggle activo/inactivo
- **Ruta**: `POST /admin/usuarios/{id}/toggle-estado`
- **Respuesta**: JSON con nuevo estado

**Transacción**: No necesita (tabla única)

### 📄 Información de Paginación

**Ubicación**: Líneas 60-70

**HTML**:
```html
<span id="contadorResultados" class="info-paginacion">
    {{ number_format($totalUsuarios) }} usuarios encontrados
</span>
<ul class="pagination pagination-sm mb-0" id="paginas">
    <!-- Botones de página dinámicos -->
</ul>
```

**Paginación**: 
- Por defecto: 15 registros por página
- Generada por Laravel Paginate
- Botones: Anterior, números de página, Siguiente

**JavaScript**: Evento click en `#paginas` llama a `filtrarUsuarios()` con nueva página

---

## 🏠 PROPIEDADES

### Ubicación
- **Vista**: `resources/views/admin/propiedades.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/PropiedadController.php`
- **Rutas**: 
  - `GET /admin/propiedades`
  - `POST /admin/propiedades/crear`
  - `GET /admin/propiedades/{id}/editar`
  - `POST /admin/propiedades/{id}/editar`

### 📈 KPIs

| KPI | Cálculo |
|-----|---------|
| **Total Propiedades** | `COUNT(tbl_propiedad)` |
| **Activas** | `WHERE estado_propiedad IN ('publicada', 'alquilada')` |
| **Inactivas** | `WHERE estado_propiedad = 'inactiva'` |
| **Este Mes** | `WHERE creado_propiedad >= DATE_SUB(NOW(), INTERVAL 1 MONTH)` |

### 🔍 Filtros

| Filtro | ID | Envío |
|--------|----|----|
| **Búsqueda** | `#buscadorPropiedades` | AJAX /admin/propiedades/filtrar |
| **Estado** | `#selectEstado` | Valores: publicada, alquilada, inactiva |
| **Tipo** | `#selectTipo` | Valores: apartamento, casa, etc. |
| **Ciudad** | `#selectCiudad` | Dinámico desde BD |

### ➕ Crear Propiedad

**Ruta**: `GET /admin/propiedades/nueva`
- **Vista**: Se carga en una página completa (no modal)
- **Campos**: Descripción, ubicación, precio, tipo, características, etc.

**Envío**: `POST /admin/propiedades/crear`

**Transacción BD**: Sí
- INSERT tbl_propiedad
- INSERT tbl_foto (si se suben)
- INSERT tbl_caracteristica (si aplica)

### ✏️ Editar Propiedad

**Flujo**:
1. Click en propiedad → `GET /admin/propiedades/{id}/editar`
2. Carga formulario con datos actuales
3. Envío: `POST /admin/propiedades/{id}/editar`

**Transacción BD**: Sí (actualiza múltiples tablas)

### 📥 Descargar PDF

**Ruta**: `GET /admin/propiedades/{id}/descargar-pdf`
- Usa `PdfMonkeyService`
- Genera PDF con fotos y detalles

### 📊 Exportar Datos

**Ruta**: `GET /admin/propiedades/exportar`
- Formato: CSV o Excel (según parámetro)
- Incluye: Todas las propiedades con campos principales

---

## 📋 SOLICITUDES

### Ubicación
- **Vista**: `resources/views/admin/solicitudes.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/SolicitudController.php`
- **Ruta**: `GET /admin/solicitudes`

### 📈 KPIs Mini

**IDs HTML**:
- `#kpiPendientes` - Solicitudes en estado 'pendiente'
- `#kpiAprobadas` - Solicitudes en estado 'aprobada'
- `#kpiRechazadas` - Solicitudes en estado 'rechazada'
- `#kpiEsteMes` - Creadas últimos 30 días

**Cálculo en Controlador** (`SolicitudController->index()` y `getKpisSolicitudes()`):
```php
$pendientes = DB::table('tbl_solicitud_arrendador')
    ->where('estado_solicitud_arrendador', 'pendiente')->count();
$aprobadas = DB::table('tbl_solicitud_arrendador')
    ->where('estado_solicitud_arrendador', 'aprobada')->count();
// etc.
```

**Ruta AJAX**: `GET /admin/solicitudes/kpis`

### 🔍 Filtros

| Filtro | ID | Tipo | Envío |
|--------|----|----|------|
| **Búsqueda** | `#buscadorSolicitudes` | Input | AJAX /admin/solicitudes/filtrar |
| **Estado** | `#selectEstado` | Select | pendiente, aprobada, rechazada |
| **Propiedad** | `#selectPropiedad` | Select | Dinámico desde BD |
| **Fecha** | `#selectFecha` | Select | Rango de fechas predefinido |

### 📄 Tabla de Solicitudes

**ID Tabla**: `#tablaSolicitudes`

| Columna | Datos |
|---------|-------|
| Usuario | Nombre del solicitante |
| Propiedad | Título y ubicación |
| Estado | Badge con color |
| Fecha | Fecha de creación |
| Acciones | Ver, Aprobar, Rechazar |

### 🔘 Acciones en Solicitudes

| Acción | Botón ID | Ruta | Método |
|--------|----------|------|--------|
| **Ver Detalle** | `.btn-ver` | `/admin/solicitudes/{id}` | GET → Modal |
| **Aprobar** | `.btn-aprobar` | `/admin/solicitudes/{id}/aprobar` | POST |
| **Rechazar** | `.btn-rechazar` | `/admin/solicitudes/{id}/rechazar` | POST |

**Modal de Detalles**: ID `#modalSolicitud`
- Carga datos via AJAX
- Mostración de atributos del solicitante y propiedad

---

## 🚨 INCIDENCIAS

### Ubicación
- **Vista**: `resources/views/admin/incidencias.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/IncidenciaController.php`
- **Ruta**: `GET /admin/incidencias`
- **JavaScript**: `public/js/admin/incidencias.js`
- **CSS**: `public/css/admin/incidencias.css`

### 📈 KPIs Mini

**IDs HTML** (línea ~45 en vista):
- `#kpiAbiertasIncidencias` → `{{ $totalAbiertas }}`
- `#kpiEnProcesoIncidencias` → `{{ $totalEnProceso }}`
- `#kpiResueltasIncidencias` → `{{ $totalResueltas }}`
- `#kpiUrgentesIncidencias` → `{{ $urgentes }}`

**Cálculo en Controlador** (`IncidenciaController->index()`):
```php
$totalAbiertas = $abiertas->count();  // WHERE estado = 'abierta'
$totalEnProceso = $enProceso->count();  // WHERE estado = 'en_proceso'
$totalResueltas = $resueltas->count();  // WHERE estado = 'resuelta'
$urgentes = DB::table('tbl_incidencia')
    ->where('prioridad_incidencia','urgente')
    ->whereIn('estado_incidencia',['abierta','en_proceso'])
    ->count();
```

### 🔍 Filtros

**Ubicación en Vista**: Líneas 66-87

| Filtro | ID HTML | Opciones | Envío |
|--------|---------|----------|-------|
| **Búsqueda** | `#buscadorInc` | Texto | 300ms debounce → `filtrarIncidencias()` |
| **Categoría** | `#selectCategoria` | fontaneria, electricidad, calefaccion, climatizacion, humedades, cerrajeria, otro | `onChange` → `filtrarIncidencias()` |
| **Prioridad** | `#selectPrioridad` | urgente, alta, media, baja | `onChange` → `filtrarIncidencias()` |
| **Propiedad** | `#selectPropiedad` | @foreach $propiedades | `onChange` → `filtrarIncidencias()` |

**Ruta AJAX**:
```
GET /admin/incidencias/filtrar?buscar=...&categoria=...&prioridad=...&propiedad=...&pagina=...
```

### 🎛️ CAMBIO DE VISTA: KANBAN ↔ LISTA

**Ubicación de Botones**: Línea 89-97
- ID Kanban: `#btnVistaKanban` 
- ID Lista: `#btnVistaLista`

**HTML/CSS**:
```blade
<!-- VISTA KANBAN (por defecto visible) -->
<div class="kanban-board" id="kanbanBoard" style="display: grid;">
    <!-- 4 columnas: Abierta, En proceso, Resuelta, Cerrada -->
</div>

<!-- VISTA LISTA (por defecto oculta) -->
<div class="card-admin" id="vistaLista" style="display: none;">
    <table class="tabla-admin" id="tablaIncidencias">
        <!-- Tabla con incidencias -->
    </table>
</div>
```

**JavaScript** (`public/js/admin/incidencias.js`, función `asignarEventosVista()`):
```javascript
btnKanban.onclick = function() {
    kanban.style.display = 'grid';  // Muestra kanban
    tabla.style.display = 'none';   // Oculta lista
    btnKanban.classList.add('activo');
    btnLista.classList.remove('activo');
};

btnLista.onclick = function() {
    kanban.style.display = 'none';  // Oculta kanban
    tabla.style.display = 'block';  // Muestra lista
    btnLista.classList.add('activo');
    btnKanban.classList.remove('activo');
};
```

**CSS** (`public/css/admin/incidencias.css`):
```css
.kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

#vistaLista {
    display: none;  /* Oculta por defecto */
}

#vistaLista.activo {
    display: block;
}
```

### 📐 VISTA KANBAN

**Ubicación**: Líneas 98-260 en la vista

**Estructura de Columnas**:
```blade
<div class="kanban-col kanban-col-abierta">
    <div class="kanban-col-header">
        <span class="badge-kanban">{{ $totalAbiertas }}</span>
    </div>
    <div class="kanban-col-body">
        @foreach($abiertas as $inc)
            <div class="tarjeta-inc" data-id="{{ $inc->id_incidencia }}">
                <!-- Contenido de tarjeta -->
            </div>
        @endforeach
    </div>
</div>
```

**Clases de Columnas**:
- `.kanban-col-abierta` - Rojo
- `.kanban-col-proceso` - Naranja
- `.kanban-col-resuelta` - Verde
- `.kanban-col-cerrada` - Gris

**Tarjetas** (clase `.tarjeta-inc`):
- Atributo: `data-id="{{ $inc->id_incidencia }}"`
- Estilo inline: `style="border-left: 3px solid {{ $bordeColor }}"`
  - rojo (#EF4444) = urgente
  - naranja (#D97706) = alta
  - gris (#6B7280) = media
  - verde (#1AA068) = baja

**Campos mostrados en tarjeta**:
- Prioridad (badge)
- Tiempo creación (diffForHumans)
- Título
- Descripción (truncada 60 caracteres)
- Propiedad (ubicación)
- Inquilino (nombre + avatar)
- Categoría con icono
- Gestor asignado (si existe)

### 📋 VISTA LISTA

**Ubicación**: Líneas 262-290
- **ID Vista**: `#vistaLista`
- **ID Tabla**: `#tablaIncidencias`
- **ID Body**: `#tbodyIncidencias`

**Encabezados**:
| Columna | Visible en |
|---------|-----------|
| TÍTULO | Todas |
| PROPIEDAD | Desktop+ |
| CATEGORÍA | Desktop+ |
| PRIORIDAD | Todas |
| ESTADO | Todas |
| REPORTADA POR | Tablet+ |
| ACCIONES | Todas |

**Datos Dinámicos**: Cargados via JavaScript `filtrarIncidencias()` cuando se activa vista lista

**Paginación**: ID `#paginasInc` (generado dinámicamente)

### 🔘 Botones de Acción en Incidencias

**En Vista Kanban**:
- Click en tarjeta → Abre modal de detalle

**En Vista Lista**:
- Botón `.btn-ver` → Abre modal con ID del registro
- Botón `.btn-editar` → Permite cambiar estado
- Botón `.btn-asignar` → Asigna a gestor

### 🔄 Estado de Incidencia

**Cambio de Estado**: `POST /admin/incidencias/{id}/estado`

**Estados disponibles** (vista kanban):
- abierta (rojo)
- en_proceso (naranja)
- resuelta (verde)
- cerrada (gris)

**En Modal**: Select dropdown para cambiar estado
- Actualiza BD: `UPDATE tbl_incidencia SET estado_incidencia = ? WHERE id_incidencia = ?`
- Inserta en historial: `INSERT INTO tbl_historial_incidencia`
- Respuesta: JSON con confirmación

### 👨‍💼 Asignar a Gestor

**Botón**: En modal de detalle
- **Ruta**: `POST /admin/incidencias/{id}/asignar`
- **Cuerpo**: `{ id_gestor: ... }`

**Transacción BD**: No
```php
DB::table('tbl_incidencia')
    ->where('id_incidencia', $id)
    ->update(['id_asignado_fk' => $idGestor]);
```

**Respuesta JSON**:
```json
{
    "success": true,
    "message": "Incidencia asignada a gestor",
    "data": { "nombre_gestor": "..." }
}
```

### 📄 Modal de Detalle

**ID Modal**: `#modalIncidencia` (Bootstrap 5)

**Ubicación en Vista**: Línea 292+

**Contenido**:
- Información completa de incidencia
- Historial de cambios
- Botones de acción (cambiar estado, asignar gestor, ver PDF)
- Panel de comentarios (si existe)

**Carga de Datos**: 
```javascript
function abrirModal(id) {
    fetch(`/admin/incidencias/${id}`)
        .then(r => r.json())
        .then(data => {
            // Poblar modal con data
            modalIncidencia.show();
        });
}
```

---

## 💳 ALQUILERES

### Ubicación
- **Vista**: `resources/views/admin/alquileres.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/AlquilerController.php`
- **Ruta**: `GET /admin/alquileres`

### 📈 KPIs

| KPI | Cálculo |
|-----|---------|
| **Total Alquileres** | `COUNT(tbl_alquiler)` |
| **Activos** | `WHERE estado_alquiler = 'activo'` |
| **Pendientes** | `WHERE estado_alquiler = 'pendiente'` |
| **Finalizados** | `WHERE estado_alquiler = 'finalizado'` |

### 🔍 Filtros y Búsqueda

Similar a usuarios y propiedades

### 📄 Tabla

Mostrará:
- Propiedad
- Inquilino
- Arrendador
- Fecha inicio
- Fecha fin
- Estado
- Acciones

---

## 💰 SUSCRIPCIONES

### Ubicación
- **Vista**: `resources/views/admin/suscripciones.blade.php`
- **Controlador**: `app/Http/Controllers/Admin/SuscripcionController.php`
- **Ruta**: `GET /admin/suscripciones`

### 📈 KPIs

| KPI | Cálculo |
|-----|---------|
| **Total Planes** | Distintos planes en BD |
| **Usuarios Activos** | `WHERE activa_suscripcion = 1` |
| **Ingresos Recurrentes** | Suma de todos los pagos mensuales |

### 🔍 Filtros

- Por plan (Free, Premium, VIP, etc.)
- Por estado (activa, cancelada, expirada)
- Por fecha de registro

---

## 🎨 CSS y Ocultamiento de Elementos

### Archivos CSS Principales

| Archivo | Ubicación | Responsable |
|---------|-----------|-------------|
| `responsive-tablas.css` | `public/css/admin/` | Responsividad de tablas (oculta/muestra columnas) |
| `incidencias.css` | `public/css/admin/` | Kanban, tarjetas, vistas |
| `usuarios.css` | `public/css/admin/` | Usuarios tabla |
| `dashboard.css` | `public/css/admin/` | KPIs, layout |
| `layout.css` | `public/css/admin/` | Estilos generales del admin |

### Clases para Ocultar Elementos

**Responsive Breakpoints**:
```css
/* En responsive-tablas.css */

.col-mobile-hide {
    /* Oculta en móvil */
    display: none;
}

.col-tablet-hide {
    /* Oculta en tablet */
    display: none;
}

@media (min-width: 768px) {
    .col-mobile-hide {
        display: table-cell;  /* Muestra en tablet+ */
    }
}

@media (min-width: 1024px) {
    .col-tablet-hide {
        display: table-cell;  /* Muestra en desktop+ */
    }
}
```

### Ocultamiento de Vistas

**En incidencias.css**:
```css
#kanbanBoard {
    display: grid;  /* Por defecto visible */
}

#vistaLista {
    display: none;  /* Por defecto oculta */
}

/* Cuando se activa vista lista */
#kanbanBoard.d-none {
    display: none;
}

#vistaLista.d-block {
    display: block;
}
```

### KPIs Mini - Ocultamiento Responsivo

**En dashboard.css**:
```css
.kpi-grid-pequeno {
    display: grid;
    grid-template-columns: repeat(4, 1fr);  /* 4 columnas en desktop */
    gap: 16px;
}

@media (max-width: 1024px) {
    .kpi-grid-pequeno {
        grid-template-columns: repeat(2, 1fr);  /* 2 columnas en tablet */
    }
}

@media (max-width: 768px) {
    .kpi-grid-pequeno {
        grid-template-columns: 1fr;  /* 1 columna en móvil */
    }
}
```

### Paginación - Ocultamiento

**En responsive-tablas.css**:
```css
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

@media (max-width: 600px) {
    .pagination .page-item:nth-child(n+5) {
        display: none;  /* Oculta números de página en móvil */
    }
    
    .pagination .page-item:first-child,
    .pagination .page-item:last-child {
        display: block;  /* Muestra botones anterior/siguiente */
    }
}
```

---

## 🔐 Validaciones y Transacciones BD

### Transacciones en Operaciones Múltiples

**Crear Usuario**:
```php
DB::beginTransaction();
try {
    $idUsuario = DB::table('tbl_usuario')->insertGetId([...]);
    DB::table('tbl_rol_usuario')->insert([
        'id_usuario_fk' => $idUsuario,
        'id_rol_fk' => $rolId
    ]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
}
```

**Crear Propiedad**:
```php
DB::beginTransaction();
try {
    $idPropiedad = DB::table('tbl_propiedad')->insertGetId([...]);
    // Insertar fotos
    foreach($fotos as $foto) {
        DB::table('tbl_foto')->insert([
            'id_propiedad_fk' => $idPropiedad,
            'url_foto' => $url
        ]);
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

**Cambiar Estado Incidencia**:
```php
DB::beginTransaction();
try {
    DB::table('tbl_incidencia')
        ->where('id_incidencia', $id)
        ->update(['estado_incidencia' => $nuevoEstado]);
    
    DB::table('tbl_historial_incidencia')->insert([
        'id_incidencia_fk' => $id,
        'id_usuario_fk' => $usuarioId,
        'accion_historial' => "Cambio estado a $nuevoEstado",
        'creado_historial' => now()
    ]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

---

## 🔗 Rutas Completas

| Acción | Método | Ruta |
|--------|--------|------|
| Listar usuarios | GET | `/admin/usuarios` |
| Filtrar usuarios | GET | `/admin/usuarios/filtrar` |
| KPIs usuarios | GET | `/admin/usuarios/kpis` |
| Ver usuario | GET | `/admin/usuarios/{id}` |
| Crear usuario | POST | `/admin/usuarios/crear` |
| Editar usuario | POST | `/admin/usuarios/{id}/editar` |
| Toggle estado usuario | POST | `/admin/usuarios/{id}/toggle-estado` |
| Listar propiedades | GET | `/admin/propiedades` |
| Nueva propiedad | GET | `/admin/propiedades/nueva` |
| Crear propiedad | POST | `/admin/propiedades/crear` |
| Filtrar propiedades | GET | `/admin/propiedades/filtrar` |
| Editar propiedad | GET/POST | `/admin/propiedades/{id}/editar` |
| Descargar PDF propiedad | GET | `/admin/propiedades/{id}/descargar-pdf` |
| Exportar propiedades | GET | `/admin/propiedades/exportar` |
| Listar solicitudes | GET | `/admin/solicitudes` |
| Filtrar solicitudes | GET | `/admin/solicitudes/filtrar` |
| KPIs solicitudes | GET | `/admin/solicitudes/kpis` |
| Ver solicitud | GET | `/admin/solicitudes/{id}` |
| Aprobar solicitud | POST | `/admin/solicitudes/{id}/aprobar` |
| Rechazar solicitud | POST | `/admin/solicitudes/{id}/rechazar` |
| Listar incidencias | GET | `/admin/incidencias` |
| Filtrar incidencias | GET | `/admin/incidencias/filtrar` |
| KPIs incidencias | GET | `/admin/incidencias/kpis` |
| Ver incidencia | GET | `/admin/incidencias/{id}` |
| Cambiar estado incidencia | POST | `/admin/incidencias/{id}/estado` |
| Asignar incidencia | POST | `/admin/incidencias/{id}/asignar` |
| Listar alquileres | GET | `/admin/alquileres` |
| Listar suscripciones | GET | `/admin/suscripciones` |
| Dashboard | GET | `/admin/dashboard` |

---

## 📝 Resumen Técnico

| Aspecto | Detalles |
|--------|----------|
| **Total Vistas** | 10 (dashboard, usuarios, propiedades, solicitudes, incidencias, alquileres, contratos, etc.) |
| **Total Controladores** | 7 (Dashboard, Usuario, Propiedad, Solicitud, Incidencia, Alquiler, Suscripción) |
| **Archivos JS** | 9 archivos especializados por módulo |
| **Archivos CSS** | 10 archivos + responsive |
| **Transacciones BD** | Sí, en operaciones de múltiples tablas |
| **Paginación** | Sí, 15 registros por página por defecto |
| **Filtros Dinámicos** | AJAX con debounce (300ms) |
| **Modales** | Bootstrap 5 nativa |
| **Cambio de Vistas** | Kanban ↔ Lista (incidencias) |

---

**Última actualización**: 3 de mayo de 2026
**Versión**: 1.0
