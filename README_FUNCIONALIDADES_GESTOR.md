# 🔧 Funcionalidades del Rol GESTOR - SpotStay

> Documentación técnica de las funcionalidades del panel de gestor operativo.

---

## 📍 Estructura General

| Elemento | Ubicación |
|----------|-----------|
| **Vistas** | `resources/views/gestor/` |
| **Controladores** | `app/Http/Controllers/Gestor/` |
| **CSS** | `public/css/gestor/` |
| **Rutas** | `routes/web.php` (líneas 160+) |
| **Layout** | `resources/views/layouts/gestor.blade.php` |

---

## 🎯 DASHBOARD GESTOR

### Ubicación
- **Vista**: `resources/views/gestor/dashboard.blade.php`
- **Controlador**: `app/Http/Controllers/Gestor/DashboardController.php`
- **Ruta**: `GET /gestor/dashboard`

### 📈 KPIs Principales

| KPI | Enlace | Cálculo | 
|-----|--------|---------|
| **Incidencias Nuevas** | Clickeable a `/gestor/incidencias?estado=abierta` | `COUNT(tbl_incidencia) WHERE estado = 'abierta'` |
| **En Proceso** | Clickeable a `/gestor/incidencias?estado=en_proceso` | `COUNT(...) WHERE estado = 'en_proceso'` |
| **En Espera** | Clickeable a `/gestor/incidencias?estado=esperando` | `COUNT(...) WHERE estado IN ('esperando_decision', 'esperando_pago')` |
| **Urgentes** | Clickeable a `/gestor/incidencias` | `COUNT(...) WHERE prioridad = 'urgente'` |

**Clases de KPI**:
- `.kpi-card-link` - KPI clickeable (A/href)
- `.kpi-numero-red`, `.kpi-numero-orange`, `.kpi-numero-blue` - Colores por tipo

**Cálculo en Controlador** (`DashboardController`):
```php
$incidenciasNuevas = DB::table('tbl_incidencia')
    ->where('id_asignado_fk', $gestorId)
    ->where('estado_incidencia', 'abierta')
    ->count();

$incidenciasUrgentes = DB::table('tbl_incidencia')
    ->where('id_asignado_fk', $gestorId)
    ->where('prioridad_incidencia', 'urgente')
    ->get();
```

### 📋 Tabla: Incidencias Recientes

**Ubicación**: Card `.card-admin` con tabla (línea ~57)

**Encabezados**:
| Encabezado | Visible en |
|-----------|----------|
| TÍTULO | Desktop+ |
| PROPIEDAD | Desktop+ |
| PRIORIDAD | Todas |
| ESTADO | Todas |
| FECHA | Todas |
| ACCIÓN | Todas |

**Vista Mobile Alternativa**:
- Tarjetas compactas (`.solicitud-item`)
- Mostrará: Avatar+iniciales, Título, Ubicación, Tiempo relativo, Botón "Abrir"

**Datos por Fila**:
```html
<tr>
    <td>{{ $incidencia->titulo_incidencia }}</td>
    <td>{{ $incidencia->direccion_propiedad }}</td>
    <td><span class="badge-prioridad badge-prioridad-{{ $prioridad }}">{{ ucfirst($prioridad) }}</span></td>
    <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</span></td>
    <td>{{ Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
    <td><a href="/gestor/incidencias/{{ $incidencia->id_incidencia }}">Ver</a></td>
</tr>
```

### 🔘 Botones en Dashboard

| Botón | Ubicación | Acción |
|-------|-----------|--------|
| **Ver todas** | Card arriba derecha | → `/gestor/incidencias` |
| **Ver** (en fila) | Última columna | → `/gestor/incidencias/{id}` |
| **Abrir** (móvil) | Card item compacto | → `/gestor/incidencias/{id}` |

---

## 🚨 LISTADO DE INCIDENCIAS

### Ubicación
- **Vista**: `resources/views/gestor/incidencias.blade.php`
- **Controlador**: `app/Http/Controllers/Gestor/IncidenciaController.php`
- **Ruta**: `GET /gestor/incidencias`

### 🔍 Filtros por GET

**Ubicación**: Form `.form-filtros-admin` (línea ~18)

| Filtro | Name | Tipo | Valores | Envío |
|--------|------|------|--------|-------|
| **Título** | `titulo` | Input text | Búsqueda libre | GET (form submit) |
| **Propiedad** | `propiedad` | Input text | Búsqueda libre | GET |
| **Estado** | `estado` | Select | abierta, en_proceso, esperando_decision, esperando_pago, resuelta, cerrada | GET |
| **Prioridad** | `prioridad` | Select | urgente, alta, media, baja | GET |
| **Fecha** | `fecha` | Input date | Rango permitido | GET |

**Flujo**:
1. Usuario selecciona filtros
2. Form submit → GET `/gestor/incidencias?titulo=...&estado=...&etc`
3. Controlador procesa query params
4. Retorna vista con resultados filtrados

**Método GET en Controlador** (`IncidenciaController->index()`):
```php
$titulo = trim((string) $request->query('titulo', ''));
$propiedad = trim((string) $request->query('propiedad', ''));
$estado = (string) $request->query('estado', '');
$prioridad = (string) $request->query('prioridad', '');
$fecha = (string) $request->query('fecha', '');

if ($titulo !== '') {
    $query->where('tbl_incidencia.titulo_incidencia', 'like', '%' . $titulo . '%');
}
// ... más filtros
```

### 📄 Tabla de Incidencias

**ID Tabla**: `.tabla-admin`
**Vista Desktop** (`.incidencias-tabla-desktop`):

| Columna | Datos |
|---------|-------|
| TÍTULO | $incidencia->titulo_incidencia |
| PROPIEDAD | $incidencia->direccion_propiedad |
| ARRENDADOR | $incidencia->nombre_arrendador |
| ESTADO | Badge con clase `badge-{{ $badgeEstado }}` |
| PRIORIDAD | Badge con clase `badge-prioridad-{{ $badgePrioridad }}` |
| FECHA | Formato d/m/Y |
| ACCIÓN | Link "Ver" → `/gestor/incidencias/{id}` |

**Estados Mapeados**:
```php
'abierta' => 'pendiente',
'en_proceso' => 'activo',
'esperando_decision' => 'pendiente',
'esperando_pago' => 'pendiente',
'resuelta' => 'activo',
'cerrada' => 'activo'
```

**Vista Mobile** (`.incidencias-lista-mobile`):
- Cards compactas con clase `.solicitud-item`
- Avatar con iniciales + color fijo (#EF4444)
- Mostrá: Título, ubicación, tiempo relativo, botón "Abrir"

### 📄 Resumen de Filtros Aplicados

**Ubicación**: Card `.card-admin` con clase `.resumen-filtros` (línea ~50)

**Contenido**:
```html
<p><strong>{{ $incidencias->total() }}</strong> incidencias encontradas</p>
<p>Estado: <span>{{ $estado !== '' ? ucfirst(str_replace('_', ' ', $estado)) : 'Todos' }}</span></p>
<p>Prioridad: <span>{{ $prioridad !== '' ? ucfirst($prioridad) : 'Todas' }}</span></p>
<p>Fecha: <span>{{ $fecha !== '' ? $fecha : 'Cualquier fecha' }}</span></p>
```

### 📄 Paginación

**Ubicación**: Debajo de la tabla

```blade
{{ $incidencias->links() }}
```

**Por defecto**: 15 registros por página
**Query string preservado**: `withQueryString()` mantiene filtros

---

## 📌 VER DETALLE INCIDENCIA

### Ubicación
- **Vista**: `resources/views/gestor/incidencia.blade.php` (si existe)
- **Ruta**: `GET /gestor/incidencias/{id}`

### 📋 Información Completa

**Datos Mostrados**:
- Título
- Descripción completa
- Propiedad (con ubicación)
- Arrendador (nombre)
- Inquilino reportante
- Categoría
- Prioridad
- Estado actual
- Fecha reportada
- Historial de cambios

**Cálculo en Controlador** (`IncidenciaController->show($id)`):
```php
$incidencia = DB::table('tbl_incidencia')
    ->join('tbl_propiedad', ...)
    ->join('tbl_usuario as reporta', ...)
    ->leftJoin('tbl_usuario as asignado', ...)  // Gestor asignado
    ->join('tbl_usuario as arrendador', ...)
    ->where('tbl_incidencia.id_incidencia', $id)
    ->select('tbl_incidencia.*', 'tbl_propiedad.*', ...)
    ->first();

// Historial de cambios
$historial = DB::table('tbl_historial_incidencia')
    ->join('tbl_usuario', ...)
    ->where('id_incidencia_fk', $id)
    ->orderBy('creado_historial', 'asc')
    ->get();
```

### 🔘 Acciones Disponibles

| Acción | Botón | Condición | Ruta/Función |
|--------|-------|-----------|------------|
| **Cambiar Estado** | Select dropdown | Siempre | POST `/gestor/incidencias/{id}/estado` |
| **Agregar Comentario** | Textarea + Botón | Siempre | POST `/gestor/incidencias/{id}/comentar` |
| **Descargar PDF** | Link/Botón | Si existe PDF | GET `/gestor/incidencias/{id}/pdf` |
| **Solicitar Presupuesto** | Modal/Form | Si estado abierta/en_proceso | POST `/gestor/incidencias/{id}/presupuesto` |

### 🔄 Cambios de Estado

**Form/Modal**: Select con opciones permitidas

**Estados disponibles**:
```
abierta → en_proceso
en_proceso → esperando_decision
esperando_decision → esperando_pago
esperando_pago → resuelta
resuelta → cerrada
```

**Envío**: `POST /gestor/incidencias/{id}/estado`
**Parámetros**: `{ nuevo_estado: 'en_proceso' }`

**Transacción BD**:
```php
DB::beginTransaction();
try {
    DB::table('tbl_incidencia')
        ->where('id_incidencia', $id)
        ->update(['estado_incidencia' => $nuevoEstado]);
    
    DB::table('tbl_historial_incidencia')->insert([
        'id_incidencia_fk' => $id,
        'id_usuario_fk' => $gestorId,
        'accion_historial' => "Cambió estado a " . ucfirst(str_replace('_', ' ', $nuevoEstado)),
        'creado_historial' => now()
    ]);
    
    // Notificar a arrendador/inquilino
    
    DB::commit();
}
```

---

## 🏠 PROPIEDADES ASIGNADAS

### Ubicación
- **Vista**: `resources/views/gestor/propiedades.blade.php` (si existe)
- **Ruta**: `GET /gestor/propiedades`

### 📋 Listado de Propiedades

**Mostrado**:
- Título propiedad
- Ubicación
- Arrendador
- Inquilinos activos
- Últimas incidencias
- Botones: Ver, Ver incidencias

**Filtros Esperados**:
- Búsqueda por nombre
- Filtrar por ciudad
- Estado (activas, inactivas)

---

## 🎯 Lógica de Asignación a Gestor

**En Admin**:
- Al crear/editar incidencia, se puede asignar a gestor
- Ruta: `POST /admin/incidencias/{id}/asignar`

**O por Propiedad**:
- Propiedad tiene campo `id_gestor_fk`
- Todas las incidencias de esa propiedad se asignen automáticamente

**Visibilidad en Gestor**:
```php
->where(function ($scope) use ($gestorId) {
    $scope->where('tbl_incidencia.id_asignado_fk', $gestorId)
        ->orWhere(function ($legacy) use ($gestorId) {
            $legacy->whereNull('tbl_incidencia.id_asignado_fk')
                ->where('tbl_propiedad.id_gestor_fk', $gestorId);
        });
})
```

---

## 🎨 CSS

| Archivo | Ubicación | Contenido |
|---------|-----------|----------|
| dashboard.css | `public/css/gestor/` | KPIs, grid, tablas |
| incidencias.css | `public/css/gestor/` | Filtros, vistas, responsividad |

### Responsividad

**Desktop**:
- Tabla completa (`.incidencias-tabla-desktop`)
- Filtros laterales

**Mobile** (< 768px):
- Se muestra `.incidencias-lista-mobile`
- Tabla ocultada
- Filtros apilados

```css
@media (max-width: 768px) {
    .incidencias-tabla-desktop {
        display: none;
    }
    
    .incidencias-lista-mobile {
        display: block;
    }
}
```

---

## 🔐 Control de Acceso

**Middleware**: `role:gestor`

**Validaciones**:
- Solo gestor autenticado can access
- Solo puede ver incidencias asignadas a él o de propiedades donde es gestor
- No puede editar usuarios ni propiedades (admin only)

---

## 🔗 Rutas Completas

| Acción | Método | Ruta |
|--------|--------|------|
| Dashboard | GET | `/gestor/dashboard` |
| Listar incidencias | GET | `/gestor/incidencias` |
| Filtrar incidencias | GET | `/gestor/incidencias?titulo=...&estado=...` |
| Ver incidencia | GET | `/gestor/incidencias/{id}` |
| Cambiar estado | POST | `/gestor/incidencias/{id}/estado` |
| Agregar comentario | POST | `/gestor/incidencias/{id}/comentar` |
| Descargar PDF | GET | `/gestor/incidencias/{id}/pdf` |
| Solicitar presupuesto | POST | `/gestor/incidencias/{id}/presupuesto` |
| Listar propiedades | GET | `/gestor/propiedades` |

---

## 📊 Resumen Técnico

| Aspecto | Detalles |
|--------|----------|
| **Vistas Principales** | 3 (dashboard, incidencias, detalle) |
| **KPIs** | 4 dinámicos (nuevas, proceso, espera, urgentes) |
| **Filtros** | GET params (6: título, propiedad, estado, prioridad, fecha, id) |
| **Paginación** | Sí, Laravel Paginate (15 por página) |
| **Transacciones BD** | Sí (cambiar estado + historial) |
| **Vistas Responsivas** | Sí (tabla desktop vs lista mobile) |
| **Acceso** | Middleware role:gestor |

---

**Última actualización**: 3 de mayo de 2026
**Versión**: 1.0
