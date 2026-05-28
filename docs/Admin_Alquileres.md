# 🏆 README: ADMIN ALQUILERES

**Vista:** `resources/views/admin/alquileres.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AlquilerController.php`  
**Ruta:** `GET /admin/alquileres`

---

## 🎯 Propósito

Gestiona **todos los alquileres activos, pendientes y finalizados**. Permite:
- Ver listado de alquileres
- Filtrar por estado, arrendador, fecha
- Buscar por propiedad o inquilino
- Cambiar estado de alquiler
- Ver detalles (fechas, cuotas, pagos)
- Aprobar alquiler (cambiar estado a activo)
- Cancelar alquiler

**Alquiler** = Contrato entre arrendador e inquilino para ocupar una propiedad

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Alquileres** | `tbl_alquiler` | Listado completo |
| **Propiedad** | `tbl_propiedad` | Qué se alquila |
| **Inquilino** | `tbl_usuario` | Quién alquila |
| **Arrendador** | `tbl_usuario` | Dueño propiedad |
| **Estado** | pendiente, activo, finalizado, cancelado | Situación actual |
| **Fecha Inicio** | fecha_inicio_alquiler | Cuándo comienza |
| **Fecha Fin** | fecha_fin_alquiler | Cuándo termina |
| **Monto Mensual** | monto_alquiler | Precio mes |
| **Cuotas Pendientes** | COUNT tbl_alquiler_cuota | Pagos faltantes |
| **Última Cuota Pagada** | MAX fecha_pago | Última paga |

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar** | `#buscadorAlq` | input text | Por propiedad o inquilino |
| **Estado** | `#selectEstadoAlq` | select | Activo, Pendiente, Finalizado, Rechazado |
| **Propiedad** | `#selectPropiedadAlq` | select | Listar todas las propiedades |
| **Mes** | `#selectMesAlq` | select | Enero a Diciembre |

**JavaScript de Filtros:**
```javascript
// Buscador en tiempo real
document.getElementById('buscadorAlq').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyAlquileres tr').forEach(fila => {
        const propiedad = fila.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
        const inquilino = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        fila.style.display = 
            propiedad.includes(valor) || inquilino.includes(valor) ? '' : 'none';
    });
});

// Filtro por estado
document.getElementById('selectEstadoAlq').addEventListener('change', function(e) {
    const estado = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyAlquileres tr').forEach(fila => {
        if (!estado) {
            fila.style.display = '';
        } else {
            const badge = fila.querySelector('td:nth-child(6) .badge-estado');
            const estadoFila = (badge?.textContent || '').toLowerCase();
            fila.style.display = estadoFila.includes(estado) ? '' : 'none';
        }
    });
});

// Filtro por propiedad
document.getElementById('selectPropiedadAlq').addEventListener('change', function(e) {
    const propiedadId = e.target.value;
    document.querySelectorAll('#tbodyAlquileres tr').forEach(fila => {
        if (!propiedadId || fila.dataset.propiedadId === propiedadId) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 7 columnas visibles (Propiedad, Inquilino, Arrendador, Inicio, Fin, Estado, Contrato)
- ✅ Filtros: Todos en toolbar
- ✅ Paginación: Visible con múltiples botones
- ✅ Avatar colores: Mostrados para inquilino y arrendador

**Columnas mostradas:**
```
PROPIEDAD | INQUILINO | ARRENDADOR | INICIO | FIN | ESTADO | CONTRATO
```

### 📱 **Mobile (< 768px)**
- ❌ ARRENDADOR: Oculta (clase `col-mobile-hide`)
- ❌ INICIO: Oculta (clase `col-tablet-hide`)
- ❌ FIN: Oculta (clase `col-tablet-hide`)
- ✅ PROPIEDAD, INQUILINO, ESTADO, CONTRATO: Siempre visibles

**Columnas mostradas en móvil:**
```
PROPIEDAD | INQUILINO | ESTADO | CONTRATO
```

**Comportamiento en móvil:**
```html
<!-- Cada celda tiene data-label para clarity -->
<td data-label="PROPIEDAD">Apartamento 3B</td>
<td data-label="INQUILINO">
    <div class="avatar-mini">JG</div>
    <span>Juan García</span>
</td>
<td data-label="ESTADO" class="col-tablet-hide">
    <span class="badge-estado badge-activo">Activo</span>
</td>

<!-- Ocultas en móvil -->
<td data-label="ARRENDADOR" class="col-mobile-hide">...</td>
<td data-label="INICIO" class="col-tablet-hide">01/01/2024</td>
<td data-label="FIN" class="col-tablet-hide">31/12/2024</td>
```

**CSS responsive:**
```css
@media (max-width: 768px) {
    .col-tablet-hide { display: none; }  /* Oculta inicio y fin */
}

@media (max-width: 480px) {
    .col-mobile-hide { display: none; }  /* Oculta arrendador */
    
    /* Tabla stack en móvil */
    .tabla-admin {
        font-size: 0.875rem;
    }
}
```

---

## 🔄 Paginación

**Tipo:** Bootstrap Pagination (`pagination` + `pagination-sm`)  
**Items por página:** 12 alquileres

**HTML generado:**
```html
<nav aria-label="Paginación de alquileres">
    <ul class="pagination pagination-sm mb-0" id="paginasAlq">
        <!-- Muestra solo primeras 3 páginas por defecto -->
        <li class="page-item active">
            <button type="button" class="page-link" data-pagina="1">1</button>
        </li>
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="2">2</button>
        </li>
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="3">3</button>
        </li>
    </ul>
</nav>
```

**JavaScript de Paginación:**
```javascript
document.querySelectorAll('#paginasAlq .page-link').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const pagina = this.dataset.pagina;
        
        // GET /admin/alquileres?page=2&estado=activo&...
        const params = new URLSearchParams({
            page: pagina,
            estado: document.getElementById('selectEstadoAlq').value || '',
            propiedad: document.getElementById('selectPropiedadAlq').value || '',
            mes: document.getElementById('selectMesAlq').value || '',
            buscar: document.getElementById('buscadorAlq').value || ''
        });
        
        window.location.href = `/admin/alquileres?${params}`;
    });
});
```

**Nota:** Solo se generan botones para las primeras 3 páginas (`min($alquileres->lastPage(), 3)`). Para ir a página 4+, es necesario agregar botón "Siguiente" o generar más dinámicamente.

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'alquileres',     // LengthAwarePaginator (12 items)
    'propiedades'     // Collection (para select filtro)
)
```

**Cada alquiler en tabla incluye:**
```php
{
    'id_alquiler' => 42,
    'id_propiedad_fk' => 10,
    'titulo_propiedad' => 'Apartamento 3B',
    'direccion_propiedad' => 'Calle Mayor 123, Piso 3',
    'id_inquilino_fk' => 5,
    'nombre_inquilino' => 'Juan García López',
    'id_arrendador' => 8,
    'nombre_arrendador' => 'Ana Martínez',
    'fecha_inicio_alquiler' => '2024-01-01',
    'fecha_fin_alquiler' => '2024-12-31',
    'estado_alquiler' => 'activo',
    'monto_alquiler' => 1200.00,
    'cuotas_pendientes' => 2,
    'cuotas_atrasadas' => 1,
    'ultima_cuota_pagada' => '2024-10-15'
}
```

---

## 🔌 Tablas Consultadas

```
tbl_alquiler (PRINCIPAL)
├─ id_alquiler
├─ id_propiedad_fk → tbl_propiedad
├─ id_inquilino_fk → tbl_usuario (inquilino)
├─ id_arrendador_fk → tbl_usuario (propietario)
├─ estado_alquiler (pendiente|activo|finalizado|cancelado)
├─ fecha_inicio_alquiler
├─ fecha_fin_alquiler
├─ monto_alquiler
├─ deposito_garantia
├─ creado_alquiler
├─ actualizado_alquiler
└─ ...

tbl_propiedad
├─ id_propiedad
├─ titulo_propiedad
├─ calle_propiedad
├─ ciudad_propiedad
├─ precio_propiedad
└─ ...

tbl_usuario (2 VECES - inquilino y arrendador)
├─ id_usuario
├─ nombre_usuario
├─ email_usuario
├─ telefono_usuario
└─ ...

tbl_alquiler_cuota (RELACIÓN - para contar pendientes)
├─ id_cuota
├─ id_alquiler_fk → tbl_alquiler
├─ numero_cuota
├─ monto_cuota
├─ estado_cuota (pendiente|pagada|atrasada)
├─ fecha_pago
└─ ...

tbl_pago (OPCIONAL - para historial)
├─ id_pago
├─ id_cuota_fk → tbl_alquiler_cuota
├─ monto_pago
├─ fecha_pago
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/alquileres`

```
GET /admin/alquileres?estado=activo&arrendador=Juan&fecha_inicio=2024-01-01
  ↓
Route::get('/alquileres', [AlquilerController::class, 'index'])
  ↓
AlquilerController::index(Request $request)
```

### 2️⃣ Controlador obtiene alquileres filtrados

```php
// app/Http/Controllers/Admin/AlquilerController.php

public function index(Request $request) {
    // PASO 1: Obtener filtros
    $estado = $request->input('estado');
    $arrendador = $request->input('arrendador');
    $inquilino = $request->input('inquilino');
    $q = $request->input('q');  // búsqueda por propiedad
    $pagina = (int) $request->input('page', 1);
    
    // PASO 2: Query base con JOINs (3 tablas principales)
    $query = DB::table('tbl_alquiler')
        ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
        ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')
        ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_alquiler.id_arrendador_fk')
        ->leftJoin('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
        ->select(
            'tbl_alquiler.*',
            'tbl_propiedad.titulo_propiedad',
            'tbl_propiedad.calle_propiedad',
            'tbl_propiedad.numero_propiedad',
            'tbl_propiedad.ciudad_propiedad',
            'inquilino.nombre_usuario as nombre_inquilino',
            'inquilino.email_usuario as email_inquilino',
            'arrendador.nombre_usuario as nombre_arrendador',
            DB::raw('COUNT(CASE WHEN tbl_alquiler_cuota.estado_cuota = "pendiente" THEN 1 END) as cuotas_pendientes'),
            DB::raw('COUNT(CASE WHEN tbl_alquiler_cuota.estado_cuota = "atrasada" THEN 1 END) as cuotas_atrasadas'),
            DB::raw('MAX(CASE WHEN tbl_alquiler_cuota.estado_cuota = "pagada" THEN tbl_alquiler_cuota.fecha_pago END) as ultima_cuota_pagada')
        )
        ->groupBy('tbl_alquiler.id_alquiler');
    
    // PASO 3: Aplicar filtros
    if ($estado) {
        $query->where('tbl_alquiler.estado_alquiler', $estado);
    }
    
    if ($arrendador) {
        $query->where('arrendador.nombre_usuario', 'LIKE', "%{$arrendador}%");
    }
    
    if ($inquilino) {
        $query->where('inquilino.nombre_usuario', 'LIKE', "%{$inquilino}%");
    }
    
    if ($q) {
        $query->where('tbl_propiedad.titulo_propiedad', 'LIKE', "%{$q}%");
    }
    
    // PASO 4: Obtener contadores (antes de paginar)
    $totalAlquileres = (clone $query)->count();
    $activosActuales = (clone $query)
        ->where('tbl_alquiler.estado_alquiler', 'activo')
        ->where('tbl_alquiler.fecha_inicio_alquiler', '<=', today())
        ->where('tbl_alquiler.fecha_fin_alquiler', '>=', today())
        ->count();
    $pendientesAprobacion = (clone $query)
        ->where('tbl_alquiler.estado_alquiler', 'pendiente')
        ->count();
    $proximosAFinalizar = (clone $query)
        ->where('tbl_alquiler.estado_alquiler', 'activo')
        ->where('tbl_alquiler.fecha_fin_alquiler', '>=', today())
        ->where('tbl_alquiler.fecha_fin_alquiler', '<=', today()->addDays(30))
        ->count();
    
    // PASO 5: Paginar
    $alquileres = $query
        ->orderBy('tbl_alquiler.creado_alquiler', 'desc')
        ->paginate(10);  // 10 alquileres por página
    
    // PASO 6: Obtener dropdown de arrendadores
    $arrendadores = DB::table('tbl_usuario')
        ->whereIn('id_usuario', 
            DB::table('tbl_alquiler')
                ->select('id_arrendador_fk')
                ->distinct()
        )
        ->select('id_usuario', 'nombre_usuario')
        ->orderBy('nombre_usuario')
        ->get();
    
    return view('admin.alquileres', compact(
        'alquileres',
        'totalAlquileres',
        'activosActuales',
        'pendientesAprobacion',
        'proximosAFinalizar',
        'arrendadores',
        'estado',
        'arrendador',
        'inquilino',
        'q'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/alquileres.blade.php -->

<!-- Filtros -->
<div class="filtros-panel">
    <form method="GET" action="/admin/alquileres" class="filtros-form">
        <!-- Búsqueda -->
        <input type="text" name="q" placeholder="Buscar propiedad..." 
               value="{{ $q ?? '' }}" class="form-control">
        
        <!-- Filtro estado -->
        <select name="estado" class="form-control">
            <option value="">Todos los estados</option>
            <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
            <option value="activo" @selected($estado === 'activo')>Activo</option>
            <option value="finalizado" @selected($estado === 'finalizado')>Finalizado</option>
            <option value="cancelado" @selected($estado === 'cancelado')>Cancelado</option>
        </select>
        
        <!-- Filtro arrendador -->
        <select name="arrendador" class="form-control">
            <option value="">Todos los arrendadores</option>
            @foreach($arrendadores as $arr)
                <option value="{{ $arr->nombre_usuario }}" @selected($arrendador === $arr->nombre_usuario)>
                    {{ $arr->nombre_usuario }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/alquileres" class="btn btn-secondary">Limpiar</a>
    </form>
</div>

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalAlquileres }}</span>
        <small>Total Alquileres</small>
    </div>
    <div class="stat-card">
        <span>{{ $activosActuales }}</span>
        <small>Alquileres Activos (Hoy)</small>
    </div>
    <div class="stat-card">
        <span>{{ $pendientesAprobacion }}</span>
        <small>Pendientes Aprobación</small>
    </div>
    <div class="stat-card">
        <span>{{ $proximosAFinalizar }}</span>
        <small>Próximos a Finalizar (30 días)</small>
    </div>
</div>

<!-- Tabla Alquileres -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Propiedad</th>
            <th>Inquilino</th>
            <th>Arrendador</th>
            <th>Monto/Mes</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Estado</th>
            <th>Cuotas</th>
            <th>Última Cuota</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($alquileres as $alq)
            <tr>
                <td>
                    <strong>{{ $alq->titulo_propiedad }}</strong>
                    <br>
                    <small>{{ $alq->calle_propiedad }}, {{ $alq->ciudad_propiedad }}</small>
                </td>
                <td>
                    {{ $alq->nombre_inquilino }}
                    <br>
                    <small>{{ $alq->email_inquilino }}</small>
                </td>
                <td>{{ $alq->nombre_arrendador }}</td>
                <td>{{ number_format($alq->monto_alquiler, 2, ',', '.') }} €</td>
                <td>{{ $alq->fecha_inicio_alquiler->format('d/m/Y') }}</td>
                <td>{{ $alq->fecha_fin_alquiler->format('d/m/Y') }}</td>
                <td>
                    <span class="badge bg-{{ 
                        $alq->estado_alquiler === 'activo' ? 'success' : 
                        ($alq->estado_alquiler === 'pendiente' ? 'warning' :
                        ($alq->estado_alquiler === 'cancelado' ? 'danger' : 'secondary'))
                    }}">
                        {{ ucfirst($alq->estado_alquiler) }}
                    </span>
                </td>
                <td>
                    @if($alq->cuotas_pendientes > 0)
                        <span class="badge bg-warning">{{ $alq->cuotas_pendientes }} pendientes</span>
                    @endif
                    @if($alq->cuotas_atrasadas > 0)
                        <span class="badge bg-danger">{{ $alq->cuotas_atrasadas }} atrasadas</span>
                    @endif
                </td>
                <td>
                    @if($alq->ultima_cuota_pagada)
                        {{ Carbon\Carbon::parse($alq->ultima_cuota_pagada)->format('d/m/Y') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($alq->estado_alquiler === 'pendiente')
                        <button onclick="aprobarAlquiler({{ $alq->id_alquiler }})" class="btn btn-sm btn-success">
                            Aprobar
                        </button>
                    @endif
                    <button onclick="verDetalles({{ $alq->id_alquiler }})" class="btn btn-sm btn-info">
                        Detalles
                    </button>
                    <button onclick="cancelarAlquiler({{ $alq->id_alquiler }})" class="btn btn-sm btn-danger">
                        Cancelar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No hay alquileres</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
{{ $alquileres->links() }}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Aprobar** | Aprueba alquiler pendiente | POST `/admin/alquileres/{id}/aprobar` | UPDATE estado = 'activo' |
| **Detalles** | Ver información completa | GET `/admin/alquileres/{id}` | Abre modal/página detalle |
| **Cancelar** | Cancela alquiler activo | POST `/admin/alquileres/{id}/cancelar` | UPDATE estado = 'cancelado' |

### Ejemplo: Aprobar Alquiler

```php
public function aprobar($id) {
    $alquiler = DB::table('tbl_alquiler')->find($id);
    
    if (!$alquiler) {
        return response()->json(['ok' => false, 'message' => 'No encontrado'], 404);
    }
    
    // Actualizar estado
    DB::table('tbl_alquiler')
        ->where('id_alquiler', $id)
        ->update([
            'estado_alquiler' => 'activo',
            'actualizado_alquiler' => now()
        ]);
    
    // Crear notificación para inquilino
    app(ActividadService::class)->alquilerAprobado($id);
    
    return response()->json(['ok' => true, 'message' => 'Alquiler aprobado']);
}
```

---

## 📋 Filtros

| Filtro | Parámetro | Tipo | Efecto |
|--------|-----------|------|--------|
| **Búsqueda Propiedad** | `q` | texto | WHERE titulo LIKE '%texto%' |
| **Estado** | `estado` | select | WHERE estado_alquiler = valor |
| **Arrendador** | `arrendador` | select | WHERE nombre_usuario LIKE '%nombre%' |
| **Inquilino** | `inquilino` | texto | WHERE nombre_inquilino LIKE '%nombre%' |

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'alquileres',                  // Paginator[10]
    'totalAlquileres',             // int
    'activosActuales',             // int
    'pendientesAprobacion',        // int
    'proximosAFinalizar',          // int (30 días)
    'arrendadores',                // Collection
    'estado',                      // string filtro
    'arrendador',                  // string filtro
    'inquilino',                   // string filtro
    'q'                            // string filtro
)
```

---

## 🔄 Flujo Resumido

```
Admin accede /admin/alquileres?estado=activo
            ↓
AlquilerController::index()
            ↓
1. Query base con 4 JOINs
2. GROUP BY para contar cuotas
3. Aplicar filtros
4. Obtener contadores KPI
5. Paginar 10 alquileres
6. Obtener dropdown arrendadores
            ↓
Blade renderiza tabla
            ↓
Admin ve alquileres con estado de cuotas
            ↓
Si clickea "Aprobar"
            ↓
POST /admin/alquileres/{id}/aprobar
            ↓
UPDATE estado = 'activo'
CREATE notificación
            ↓
JSON response
```

---

## ⚠️ Puntos Importantes

1. **GROUP BY necesario:** Para contar cuotas correctamente
2. **Usuarios duplicados:** Inquilino y arrendador en mismo alquiler
3. **Estados:** pendiente, activo, finalizado, cancelado
4. **Cuotas:** Contadas por estado (pendiente, atrasada, pagada)
5. **Paginación:** 10 alquileres por página
6. **KPIs:** Activos hoy, pendientes, próximos a finalizar

---

## 🐛 Debugging

Ver alquileres activos hoy:

```php
$activos = DB::table('tbl_alquiler')
    ->where('estado_alquiler', 'activo')
    ->where('fecha_inicio_alquiler', '<=', today())
    ->where('fecha_fin_alquiler', '>=', today())
    ->count();
    
dd("Activos hoy: $activos");
```

Ver cuotas pendientes:

```php
$cuotasPendientes = DB::table('tbl_alquiler_cuota')
    ->where('estado_cuota', 'pendiente')
    ->count();
    
dd("Cuotas pendientes: $cuotasPendientes");
```
