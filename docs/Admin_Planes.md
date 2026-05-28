# 📊 README: ADMIN PLANES

**Vista:** `resources/views/admin/planes.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/PlanController.php`  
**Ruta:** `GET /admin/planes`

---

## 🎯 Propósito

Gestiona **planes de suscripción** disponibles. Permite:
- Ver todos los planes
- Crear nuevo plan
- Editar características del plan
- Cambiar precio
- Ver suscriptores de cada plan
- Activar/desactivar plan

**Plan** = Producto de suscripción (ej: Plan Premium, Plan Pro)

---

## � Estructura de Vista

**NO usa tabla ni paginación**, sino **Grid de Cards**:

1. **Sección "Crear Plan"** - Formulario en card
2. **Sección "Listado de Planes"** - Cards en grid

Cada plan es una card que incluye:
- Nombre, Slug, Rol destino, Precio
- Máximo de propiedades
- Descripción
- Timestamps (creado, actualizado)
- Formulario de edición integrado
- Botones: Guardar / Eliminar

---

## 🎛️ KPIs Mostrados

| KPI | Datos |
|-----|-------|
| Planes totales | Total de planes |
| Planes arrendador | Para arrendadores |
| Planes miembro | Para miembros |
| Planes activos | Que están habilitados |

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'planes'  // Collection de todos los planes
)
```

**Cada plan:**
```php
{
    'id_plan' => 1,
    'nombre_plan' => 'Pro',
    'slug_plan' => 'pro',
    'rol_destino' => 'arrendador|miembro|inquilino|gestor',
    'precio_plan' => 29.99,
    'max_propiedades_plan' => 10,
    'descripcion_plan' => 'Plan con funcionalidades avanzadas',
    'activo_plan' => true,
    'creado_plan' => Carbon object,
    'actualizado_plan' => Carbon object
}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Validación |
|-------|---------|----------|-----------|
| **Crear Plan** | Crea nuevo plan | POST `/admin/planes/crear` | Nombre única, slug única |
| **Guardar cambios** | Edita plan | POST `/admin/planes/{id}/actualizar` | Valida cambios |
| **Eliminar** | Elimina plan | DELETE `/admin/planes/{id}/eliminar` | ❌ No puede eliminar si hay suscripciones activas |

**JavaScript de eliminación:**
```javascript
// Confirmación antes de eliminar
document.querySelectorAll('.btn-eliminar-plan').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const planId = this.dataset.planId;
        
        // SweetAlert2 para confirmación
        Swal.fire({
            title: '¿Eliminar plan?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`form-eliminar-${planId}`).submit();
            }
        });
    });
});
```

**Backend:**
```php
public function eliminar($planId) {
    $plan = DB::table('tbl_plan')->find($planId);
    
    // Validar que NO haya suscripciones activas
    $suscriptoresActivos = DB::table('tbl_suscripcion')
        ->where('id_plan_fk', $planId)
        ->where('estado_suscripcion', 'activa')
        ->count();
    
    if ($suscriptoresActivos > 0) {
        return redirect()->back()->with('error', 
            "No puedes eliminar un plan con {$suscriptoresActivos} suscriptores activos");
    }
    
    // Eliminar plan
    DB::table('tbl_plan')->where('id_plan', $planId)->delete();
    
    return redirect()->back()->with('success', 'Plan eliminado');
}
```

---

## ⚠️ Puntos Importantes

1. **Sin paginación:** Muestra TODOS los planes (generalmente 2-5)
2. **Grid responsive:** Cards se ajustan al tamaño de pantalla
3. **Edición inline:** Formularios dentro de cada card
4. **Protección en delete:** No permite eliminar si hay suscripciones activas
5. **Validaciones:** Nombre y slug únicos
6. **Estado visual:** Cards con colores según activo/inactivo
7. **SweetAlert2:** Confirmación antes de eliminar
8. **Roles destino:** Cada plan se destina a un rol específico

---

## �📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Planes** | `tbl_plan` | Listado completo |
| **Nombre** | nombre_plan | Nombre del plan |
| **Descripción** | descripcion_plan | Qué incluye |
| **Precio** | precio_plan | Costo mensual |
| **Duración** | duracion_dias_plan | Duración en días |
| **Características** | tbl_caracteristica_plan | Funciones incluidas |
| **Total Suscriptores** | COUNT tbl_suscripcion | Cuánta gente se suscribió |
| **Ingresos** | SUM pagos | Dinero generado |
| **Activo** | activo_plan | Si está disponible |

---

## 🔌 Tablas Consultadas

```
tbl_plan (PRINCIPAL)
├─ id_plan
├─ nombre_plan (Premium, Pro, etc)
├─ descripcion_plan
├─ precio_plan
├─ duracion_dias_plan
├─ activo_plan (true|false)
├─ creado_plan
├─ actualizado_plan
└─ ...

tbl_caracteristica_plan (RELACIÓN)
├─ id_caracteristica
├─ id_plan_fk → tbl_plan
├─ nombre_caracteristica (Uploads ilimitados, etc)
├─ activa_caracteristica (true|false)
└─ ...

tbl_suscripcion (RELACIÓN - para contar)
├─ id_suscripcion
├─ id_plan_fk → tbl_plan
└─ ...

tbl_pago_suscripcion (RELACIÓN - para ingresos)
├─ id_pago
├─ id_suscripcion_fk → tbl_suscripcion
├─ monto_pago
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/planes`

```
GET /admin/planes
  ↓
Route::get('/planes', [PlanController::class, 'index'])
  ↓
PlanController::index()
```

### 2️⃣ Controlador obtiene planes

```php
// app/Http/Controllers/Admin/PlanController.php

public function index() {
    // Query con agregaciones
    $planes = DB::table('tbl_plan')
        ->leftJoin('tbl_suscripcion', 'tbl_suscripcion.id_plan_fk', '=', 'tbl_plan.id_plan')
        ->leftJoin('tbl_pago_suscripcion', function($join) {
            $join->on('tbl_pago_suscripcion.id_suscripcion_fk', '=', 'tbl_suscripcion.id_suscripcion');
        })
        ->select(
            'tbl_plan.*',
            DB::raw('COUNT(DISTINCT tbl_suscripcion.id_suscripcion) as total_suscriptores'),
            DB::raw('SUM(tbl_pago_suscripcion.monto_pago) as total_ingresos')
        )
        ->groupBy('tbl_plan.id_plan')
        ->orderBy('tbl_plan.precio_plan', 'asc')
        ->get();
    
    // Para cada plan, obtener características
    $planes->each(function($plan) {
        $plan->caracteristicas = DB::table('tbl_caracteristica_plan')
            ->where('id_plan_fk', $plan->id_plan)
            ->where('activa_caracteristica', true)
            ->select('nombre_caracteristica')
            ->pluck('nombre_caracteristica')
            ->toArray();
    });
    
    // Obtener estadísticas generales
    $totalIngresos = DB::table('tbl_pago_suscripcion')->sum('monto_pago');
    $totalSuscriptores = DB::table('tbl_suscripcion')
        ->where('estado_suscripcion', 'activa')
        ->count();
    $planesActivos = $planes->where('activo_plan', true)->count();
    
    return view('admin.planes', compact(
        'planes',
        'totalIngresos',
        'totalSuscriptores',
        'planesActivos'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/planes.blade.php -->

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ count($planes) }}</span>
        <small>Planes Totales</small>
    </div>
    <div class="stat-card">
        <span>{{ $planesActivos }}</span>
        <small>Planes Activos</small>
    </div>
    <div class="stat-card">
        <span>{{ $totalSuscriptores }}</span>
        <small>Suscriptores Activos</small>
    </div>
    <div class="stat-card">
        <span>{{ number_format($totalIngresos, 2, ',', '.') }} €</span>
        <small>Ingresos Totales</small>
    </div>
</div>

<!-- Botón Crear Plan -->
<a href="/admin/planes/crear" class="btn btn-success mb-3">+ Crear Nuevo Plan</a>

<!-- Tabla Planes -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio/Mes</th>
            <th>Duración</th>
            <th>Características</th>
            <th>Suscriptores</th>
            <th>Ingresos</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($planes as $plan)
            <tr>
                <td><strong>{{ $plan->nombre_plan }}</strong></td>
                <td>{{ $plan->descripcion_plan }}</td>
                <td>{{ number_format($plan->precio_plan, 2, ',', '.') }} €</td>
                <td>{{ $plan->duracion_dias_plan }} días</td>
                <td>
                    @forelse($plan->caracteristicas as $car)
                        <span class="badge bg-light text-dark">{{ $car }}</span>
                    @empty
                        <span class="text-muted">-</span>
                    @endforelse
                </td>
                <td>
                    <span class="badge bg-info">{{ $plan->total_suscriptores ?? 0 }}</span>
                </td>
                <td>{{ number_format($plan->total_ingresos ?? 0, 2, ',', '.') }} €</td>
                <td>
                    <span class="badge bg-{{ $plan->activo_plan ? 'success' : 'secondary' }}">
                        {{ $plan->activo_plan ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <a href="/admin/planes/{{ $plan->id_plan }}/editar" class="btn btn-sm btn-primary">
                        Editar
                    </a>
                    <button onclick="toggleActivoPlan({{ $plan->id_plan }}, {{ $plan->activo_plan ? 'false' : 'true' }})" 
                            class="btn btn-sm btn-{{ $plan->activo_plan ? 'warning' : 'info' }}">
                        {{ $plan->activo_plan ? 'Desactivar' : 'Activar' }}
                    </button>
                    <button onclick="eliminarPlan({{ $plan->id_plan }})" class="btn btn-sm btn-danger">
                        Eliminar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No hay planes creados</td>
            </tr>
        @endforelse
    </tbody>
</table>
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Crear Plan** | Abre formulario | GET `/admin/planes/crear` | Carga vista form |
| **Editar** | Abre editor | GET `/admin/planes/{id}/editar` | Carga vista con datos |
| **Activar/Desactivar** | Toggle estado | POST `/admin/planes/{id}/toggle` | UPDATE activo_plan |
| **Eliminar** | Elimina plan | DELETE `/admin/planes/{id}` | DELETE si no tiene suscriptores |

---

## 🔄 Flujo Resumido

```
Admin accede /admin/planes
            ↓
PlanController::index()
            ↓
1. Query con JOINs (suscripción, pagos)
2. GROUP BY para agregaciones
3. Para cada plan, obtener características activas
4. Obtener KPIs (total ingresos, suscriptores)
            ↓
Blade renderiza tabla planes
            ↓
Si clickea "Crear Plan"
            ↓
GET /admin/planes/crear
            ↓
Carga formulario (nombre, precio, duración, características)
            ↓
Admin rellena y envía
            ↓
POST /admin/planes
            ↓
INSERT nuevo plan
INSERT características asociadas
            ↓
Redirect a /admin/planes
```

---

## ⚠️ Puntos Importantes

1. **Características:** Se guardan en tabla separada
2. **No se puede eliminar:** Si tiene suscriptores activos
3. **Duración:** En días (30, 365, etc)
4. **Activación:** Solo los planes activos aparecen en ofertas públicas
5. **Ingresos:** Calculados desde tabla de pagos
