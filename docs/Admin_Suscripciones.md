# 💳 README: ADMIN SUSCRIPCIONES

**Vista:** `resources/views/admin/suscripciones.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/SuscripcionController.php`  
**Ruta:** `GET /admin/suscripciones`

---

## 🎯 Propósito

Gestiona **suscripciones a planes de pago** de usuarios. Permite:
- Ver todas las suscripciones activas y expiradas
- Filtrar por plan, usuario, estado
- Ver próximas a expirar (alarma)
- Renovar suscripción manualmente
- Cancelar suscripción
- Ver historial de pagos/cambios

**Suscripción** = Usuario paga plan para obtener funcionalidades premium

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar** | `#buscadorSus` | input text | Por nombre o email |
| **Plan** | `#selectPlanSus` | select | Pro, Básico, Gratuito, Todos |
| **Estado** | `#selectEstadoSus` | select | Activa, Expirada, Cancelada, Todos |

**JavaScript de Filtros:**
```javascript
// Buscador
document.getElementById('buscadorSus').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodySuscripciones .tabla-row').forEach(fila => {
        const nombre = fila.querySelector('[data-label="ARRENDADOR"]')?.textContent.toLowerCase() || '';
        fila.style.display = nombre.includes(valor) ? '' : 'none';
    });
});

// Filtro plan
document.getElementById('selectPlanSus').addEventListener('change', function(e) {
    const plan = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodySuscripciones .tabla-row').forEach(fila => {
        if (!plan) {
            fila.style.display = '';
        } else {
            const planFila = fila.querySelector('[data-label="PLAN"]')?.textContent.toLowerCase() || '';
            fila.style.display = planFila.includes(plan) ? '' : 'none';
        }
    });
});

// Filtro estado
document.getElementById('selectEstadoSus').addEventListener('change', function(e) {
    const estado = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodySuscripciones .tabla-row').forEach(fila => {
        if (!estado) {
            fila.style.display = '';
        } else {
            const estadoFila = fila.querySelector('[data-label="ESTADO"]')?.textContent.toLowerCase() || '';
            fila.style.display = estadoFila.includes(estado) ? '' : 'none';
        }
    });
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 7 columnas (Arrendador, Plan, Propiedades, Inicio, Fin, Estado, Acciones)
- ✅ Filtros: Todos visibles
- ✅ KPIs: 4 cards en fila
- ✅ Barra de progreso: Mostrada para propiedades usadas

**Columnas mostradas:**
```
ARRENDADOR | PLAN | PROPIEDADES | INICIO | FIN | ESTADO | ACCIONES
```

### 📱 **Mobile (< 768px)**
- ❌ PROPIEDADES: Oculta (clase `col-mobile-hide`)
- ❌ INICIO: Oculta (clase `col-tablet-hide`)
- ❌ FIN: Oculta (clase `col-tablet-hide`)
- ✅ ARRENDADOR, PLAN, ESTADO, ACCIONES: Siempre visibles

**Columnas mostradas en móvil:**
```
ARRENDADOR | PLAN | ESTADO | ACCIONES
```

**Estructura en móvil:**
```html
<!-- Usa divs en lugar de table para más flexibilidad -->
<div class="tabla-row" data-id="123">
    <div data-label="ARRENDADOR">
        <div class="usuario-celda">
            <div class="avatar-mini">JG</div>
            <span>Juan García</span>
        </div>
    </div>
    
    <!-- Ocultas -->
    <div data-label="PROPIEDADES" class="col-mobile-hide">3/10</div>
    <div data-label="INICIO" class="col-tablet-hide">15/01/2024</div>
    <div data-label="FIN" class="col-tablet-hide">15/01/2025</div>
    
    <div data-label="PLAN">Plan Pro</div>
    <div data-label="ESTADO">Activa</div>
    <div data-label="ACCIONES">...</div>
</div>
```

**CSS:**
```css
@media (max-width: 768px) {
    .col-mobile-hide { display: none; }
    .col-tablet-hide { display: none; }
}

/* Tabla usa divs, no tablas HTML -->
.tabla-row {
    display: flex;
    gap: 1rem;
    align-items: center;
}
```

---

## 🔄 Paginación

**Tipo:** Bootstrap Pagination  
**Items por página:** 10 suscripciones  
**Muestra:** Primeras 3 páginas (min($suscripciones->lastPage(), 3))

```html
<ul class="pagination pagination-sm mb-0" id="paginasSus">
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
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'suscripciones',   // LengthAwarePaginator
    'totalActivas',    // Int (para KPI)
    'totalPro',        // Int (para KPI)
    'totalBasico',     // Int (para KPI)
    'totalExpiradas',  // Int (para KPI)
    'pctPro',          // Percentage
    'pctBasico'        // Percentage
)
```

**Cada suscripción:**
```php
{
    'id_suscripcion' => 42,
    'id_usuario_fk' => 10,
    'nombre_usuario' => 'Ana Martínez',
    'email_usuario' => 'ana@example.com',
    'avatar_usuario' => 'storage/avatares/ana.jpg',
    'plan_suscripcion' => 'pro|basico|gratuito',
    'estado_suscripcion' => 'activa|expirada|cancelada',
    'propiedades_usadas' => 7,
    'fecha_inicio_suscripcion' => '2024-01-15',
    'fecha_fin_suscripcion' => '2025-01-15',
    'dias_restantes' => 42  // Calculado en controller
}
```

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Suscripciones** | `tbl_suscripcion` | Listado completo |
| **Usuario** | `tbl_usuario` | Quién se suscribió |
| **Plan** | `tbl_plan` | Qué plan compró |
| **Estado** | activa, expirada, cancelada | Situación actual |
| **Fecha Inicio** | fecha_inicio_suscripcion | Cuándo comenzó |
| **Fecha Fin** | fecha_fin_suscripcion | Cuándo expira |
| **Precio Pagado** | monto_suscripcion | Cuánto paga |
| **Próxima Renovación** | fecha_fin - hoy | Días restantes |
| **Renovaciones** | COUNT de renovaciones | Cuántas veces renovó |

---

## 🔌 Tablas Consultadas

```
tbl_suscripcion (PRINCIPAL)
├─ id_suscripcion
├─ id_usuario_fk → tbl_usuario
├─ id_plan_fk → tbl_plan
├─ fecha_inicio_suscripcion
├─ fecha_fin_suscripcion
├─ monto_suscripcion
├─ estado_suscripcion (activa|expirada|cancelada)
├─ creado_suscripcion
├─ actualizado_suscripcion
└─ ...

tbl_usuario
├─ id_usuario
├─ nombre_usuario
├─ email_usuario
├─ tipo_documento_usuario
├─ documento_usuario
└─ ...

tbl_plan
├─ id_plan
├─ nombre_plan (Premium, Pro, etc)
├─ descripcion_plan
├─ precio_plan
├─ duracion_dias_plan
├─ caracteristicas_plan (JSON - NO recomendado)
└─ ...

tbl_caracteristica_plan (RELACIÓN)
├─ id_caracteristica
├─ id_plan_fk → tbl_plan
├─ nombre_caracteristica (uploads, propiedades, etc)
└─ activa

tbl_pago_suscripcion (RELACIÓN - pagos)
├─ id_pago
├─ id_suscripcion_fk → tbl_suscripcion
├─ monto_pago
├─ fecha_pago
├─ metodo_pago (tarjeta, transferencia)
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/suscripciones`

```
GET /admin/suscripciones?estado=activa&plan=1&proximas_expirar=30
  ↓
Route::get('/suscripciones', [SuscripcionController::class, 'index'])
  ↓
SuscripcionController::index(Request $request)
```

### 2️⃣ Controlador obtiene suscripciones

```php
// app/Http/Controllers/Admin/SuscripcionController.php

public function index(Request $request) {
    // PASO 1: Obtener filtros
    $estado = $request->input('estado');  // activa, expirada
    $planId = $request->input('plan');
    $proximasExpirar = $request->input('proximas_expirar', 30);  // días
    $q = $request->input('q');  // búsqueda usuario
    
    // PASO 2: Query base con JOINs
    $query = DB::table('tbl_suscripcion')
        ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_suscripcion.id_usuario_fk')
        ->join('tbl_plan', 'tbl_plan.id_plan', '=', 'tbl_suscripcion.id_plan_fk')
        ->leftJoin('tbl_pago_suscripcion', 'tbl_pago_suscripcion.id_suscripcion_fk', '=', 'tbl_suscripcion.id_suscripcion')
        ->select(
            'tbl_suscripcion.*',
            'tbl_usuario.nombre_usuario',
            'tbl_usuario.email_usuario',
            'tbl_usuario.documento_usuario',
            'tbl_plan.nombre_plan',
            'tbl_plan.precio_plan',
            'tbl_plan.duracion_dias_plan',
            DB::raw('COUNT(DISTINCT tbl_pago_suscripcion.id_pago) as total_pagos'),
            DB::raw('DATEDIFF(tbl_suscripcion.fecha_fin_suscripcion, CURDATE()) as dias_restantes')
        )
        ->groupBy('tbl_suscripcion.id_suscripcion');
    
    // PASO 3: Aplicar filtros
    if ($estado === 'activa') {
        $query->where('tbl_suscripcion.estado_suscripcion', 'activa')
              ->where('tbl_suscripcion.fecha_fin_suscripcion', '>=', today());
    } elseif ($estado === 'expirada') {
        $query->where('tbl_suscripcion.estado_suscripcion', 'expirada');
    } elseif ($estado === 'cancelada') {
        $query->where('tbl_suscripcion.estado_suscripcion', 'cancelada');
    }
    
    if ($planId) {
        $query->where('tbl_suscripcion.id_plan_fk', $planId);
    }
    
    if ($q) {
        $query->where('tbl_usuario.nombre_usuario', 'LIKE', "%{$q}%");
    }
    
    // PASO 4: Obtener contadores KPI
    $totalActivas = (clone $query)
        ->where('tbl_suscripcion.estado_suscripcion', 'activa')
        ->where('tbl_suscripcion.fecha_fin_suscripcion', '>=', today())
        ->count();
    
    $proximasExpirar = (clone $query)
        ->where('tbl_suscripcion.estado_suscripcion', 'activa')
        ->where('tbl_suscripcion.fecha_fin_suscripcion', '>=', today())
        ->where('tbl_suscripcion.fecha_fin_suscripcion', '<=', today()->addDays((int)$proximasExpirar))
        ->count();
    
    $ingresoMesActual = DB::table('tbl_pago_suscripcion')
        ->whereMonth('fecha_pago', now()->month)
        ->sum('monto_pago');
    
    $totalIngresoHistorico = DB::table('tbl_pago_suscripcion')
        ->sum('monto_pago');
    
    // PASO 5: Paginar
    $suscripciones = $query
        ->orderBy('tbl_suscripcion.fecha_fin_suscripcion', 'asc')  // próximas a expirar primero
        ->paginate(15);
    
    // PASO 6: Obtener planes disponibles
    $planes = DB::table('tbl_plan')
        ->select('id_plan', 'nombre_plan')
        ->orderBy('nombre_plan')
        ->get();
    
    return view('admin.suscripciones', compact(
        'suscripciones',
        'totalActivas',
        'proximasExpirar',
        'ingresoMesActual',
        'totalIngresoHistorico',
        'planes',
        'estado',
        'planId',
        'q'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/suscripciones.blade.php -->

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalActivas }}</span>
        <small>Suscripciones Activas</small>
    </div>
    <div class="stat-card">
        <span style="color: #ff6b6b;">{{ $proximasExpirar }}</span>
        <small>Próximas a Expirar (30 días)</small>
    </div>
    <div class="stat-card">
        <span>{{ number_format($ingresoMesActual, 2, ',', '.') }} €</span>
        <small>Ingresos Este Mes</small>
    </div>
    <div class="stat-card">
        <span>{{ number_format($totalIngresoHistorico, 2, ',', '.') }} €</span>
        <small>Ingresos Totales</small>
    </div>
</div>

<!-- Filtros -->
<div class="filtros-panel">
    <form method="GET" action="/admin/suscripciones" class="filtros-form">
        <input type="text" name="q" placeholder="Buscar usuario..." 
               value="{{ $q ?? '' }}" class="form-control">
        
        <select name="estado" class="form-control">
            <option value="">Todos los estados</option>
            <option value="activa" @selected($estado === 'activa')>Activas</option>
            <option value="expirada" @selected($estado === 'expirada')>Expiradas</option>
            <option value="cancelada" @selected($estado === 'cancelada')>Canceladas</option>
        </select>
        
        <select name="plan" class="form-control">
            <option value="">Todos los planes</option>
            @foreach($planes as $plan)
                <option value="{{ $plan->id_plan }}" @selected($planId == $plan->id_plan)>
                    {{ $plan->nombre_plan }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/suscripciones" class="btn btn-secondary">Limpiar</a>
    </form>
</div>

<!-- Tabla Suscripciones -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Plan</th>
            <th>Precio/Mes</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Días Restantes</th>
            <th>Estado</th>
            <th>Pagos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($suscripciones as $sub)
            <tr class="@if($sub->dias_restantes < 0) table-danger @elseif($sub->dias_restantes < 30) table-warning @endif">
                <td>
                    <strong>{{ $sub->nombre_usuario }}</strong>
                    <br>
                    <small>{{ $sub->email_usuario }}</small>
                </td>
                <td>{{ $sub->nombre_plan }}</td>
                <td>{{ number_format($sub->precio_plan, 2, ',', '.') }} €</td>
                <td>{{ $sub->fecha_inicio_suscripcion->format('d/m/Y') }}</td>
                <td>{{ $sub->fecha_fin_suscripcion->format('d/m/Y') }}</td>
                <td>
                    @if($sub->dias_restantes >= 0)
                        <span class="badge bg-success">{{ $sub->dias_restantes }} días</span>
                    @else
                        <span class="badge bg-danger">Expirada hace {{ abs($sub->dias_restantes) }} días</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $sub->estado_suscripcion === 'activa' ? 'success' : ($sub->estado_suscripcion === 'cancelada' ? 'danger' : 'secondary') }}">
                        {{ ucfirst($sub->estado_suscripcion) }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info">{{ $sub->total_pagos }}</span>
                </td>
                <td>
                    @if($sub->estado_suscripcion === 'activa')
                        <button onclick="renovarSuscripcion({{ $sub->id_suscripcion }})" class="btn btn-sm btn-success">
                            Renovar
                        </button>
                    @endif
                    <button onclick="verDetallesSuscripcion({{ $sub->id_suscripcion }})" class="btn btn-sm btn-info">
                        Detalles
                    </button>
                    <button onclick="cancelarSuscripcion({{ $sub->id_suscripcion }})" class="btn btn-sm btn-danger">
                        Cancelar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No hay suscripciones</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $suscripciones->links() }}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Renovar** | Extiende suscripción | POST `/admin/suscripciones/{id}/renovar` | UPDATE fecha_fin, UPDATE estado |
| **Detalles** | Ver información completa | GET `/admin/suscripciones/{id}` | Modal con historial pagos |
| **Cancelar** | Cancela suscripción | POST `/admin/suscripciones/{id}/cancelar` | UPDATE estado = 'cancelada' |

---

## 📋 Filtros

| Filtro | Parámetro | Tipo | Efecto |
|--------|-----------|------|--------|
| **Estado** | `estado` | select | WHERE estado_suscripcion = valor |
| **Plan** | `plan` | select | WHERE id_plan = valor |
| **Búsqueda Usuario** | `q` | texto | WHERE nombre LIKE '%texto%' |
| **Próximas a Expirar** | `proximas_expirar` | número | Filtro: fecha_fin BETWEEN hoy y +N días |

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'suscripciones',           // Paginator[15]
    'totalActivas',            // int
    'proximasExpirar',         // int (próximas 30 días)
    'ingresoMesActual',        // float
    'totalIngresoHistorico',   // float
    'planes',                  // Collection
    'estado',                  // string filtro
    'planId',                  // int filtro
    'q'                        // string filtro
)
```

---

## 🔄 Flujo Resumido

```
Admin accede /admin/suscripciones
            ↓
SuscripcionController::index()
            ↓
1. Query base con 3 JOINs
2. GROUP BY para contar pagos
3. Aplicar filtros (estado, plan, usuario)
4. Obtener KPIs (activas, próximas expirar, ingresos)
5. Paginar 15 suscripciones
            ↓
Blade renderiza tabla + KPIs
            ↓
Admin ve suscripciones ordenadas por fecha fin
            ↓
Si clickea "Renovar"
            ↓
POST /admin/suscripciones/{id}/renovar
            ↓
UPDATE fecha_fin = fecha_actual + duración plan
UPDATE estado = 'activa'
INSERT pago_suscripcion
            ↓
JSON response
```

---

## ⚠️ Puntos Importantes

1. **Días Restantes:** Calculado con DATEDIFF en SQL
2. **Colores de alerta:** Tabla se torna naranja si < 30 días, roja si expirada
3. **Orden de listado:** Por fecha de expiración (próximas primero)
4. **KPIs financieros:** Ingresos mes actual + total histórico
5. **Paginación:** 15 suscripciones por página

---

## 🐛 Debugging

Ver suscripciones próximas a expirar:

```php
$proximas = DB::table('tbl_suscripcion')
    ->where('estado_suscripcion', 'activa')
    ->where('fecha_fin_suscripcion', '>=', today())
    ->where('fecha_fin_suscripcion', '<=', today()->addDays(30))
    ->count();
    
dd("Próximas expirar: $proximas");
```

Ver ingresos por plan:

```php
$ingresosPorPlan = DB::table('tbl_pago_suscripcion')
    ->join('tbl_suscripcion', 'tbl_suscripcion.id_suscripcion', '=', 'tbl_pago_suscripcion.id_suscripcion_fk')
    ->join('tbl_plan', 'tbl_plan.id_plan', '=', 'tbl_suscripcion.id_plan_fk')
    ->select('tbl_plan.nombre_plan', DB::raw('SUM(tbl_pago_suscripcion.monto_pago) as total'))
    ->groupBy('tbl_plan.nombre_plan')
    ->get();
    
dd($ingresosPorPlan);
```
