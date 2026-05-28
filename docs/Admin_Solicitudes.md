# Admin Solicitudes

**Vista:** `resources/views/admin/solicitudes.blade.php`
**Controlador:** `app/Http/Controllers/Admin/SolicitudController.php`
**Ruta:** `GET /admin/solicitudes`

---

## Proposito

Gestiona **solicitudes de usuarios** para convertirse en arrendadores o gestores. Permite:
- Ver solicitudes pendientes/aprobadas/rechazadas
- Filtrar por estado, tipo y fecha
- Buscar usuario
- Aprobar solicitud (crea rol, perfil, codigos, suscripcion)
- Rechazar solicitud (con motivo)
- Ver detalles de solicitud

**Nota:** Una solicitud es cuando alguien quiere ser arrendador o gestor, no cuando solicita un alquiler.

---

## Filtros y Busqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar** | `#buscadorSolicitudes` | input text | Por nombre, email o detalle |
| **Rango Fecha** | `#selectRangoSol` | select | Este mes, 3 meses, Año, Todas |
| **Tipo** | `#selectTipoSol` | select | Todos, Arrendador, Gestor |
| **Estado** | `#selectEstadoSol` | select | Todos, Pendiente, Aprobada, Rechazada |
| **Ubicacion** | `#selectCiudadSol` | select | Todas, Madrid, Barcelona, Valencia, Sevilla, Bilbao |

**Metodo:** Los filtros son **backend** (se envian con formulario GET)

```html
<form class="toolbar-admin" id="formFiltrosSolicitudes" method="GET">
    <input type="text" id="buscadorSolicitudes" name="q" placeholder="Buscar...">
    <select id="selectRangoSol" name="rango">
        <option value="mes">Este mes</option>
        <option value="3meses">Ultimos 3 meses</option>
        <option value="anio">Este año</option>
        <option value="all">Todas</option>
    </select>
    <!-- Mas selects... -->
</form>
```

---

## Responsive Design

### Desktop (1200px+)
- Tabla: 7 columnas (Solicitante, Tipo, Detalle, Fecha, Estado, Pago, Acciones)
- Filtros: Todos visibles en toolbar
- KPIs: 4 columnas
- Panel detalle: A la derecha

**Columnas mostradas:**
```
SOLICITANTE | TIPO | DETALLE | FECHA | ESTADO | PAGO | ACCIONES
```

### Mobile (< 768px)
- TIPO: Oculta (clase `col-tablet-hide`)
- DETALLE: Oculta (clase `col-mobile-hide`)
- FECHA: Oculta (clase `col-tablet-hide`)
- SOLICITANTE, ESTADO, PAGO, ACCIONES: Siempre visibles

**Columnas mostradas en movil:**
```
SOLICITANTE | ESTADO | PAGO | ACCIONES
```

---

## Paginacion

**Tipo:** Bootstrap Pagination (`pagination::bootstrap-5`)
**Items por pagina:** 7 solicitudes

```php
{{ $solicitudesPendientes->withQueryString()->links('pagination::bootstrap-5') }}
```

---

## Datos Pasados a la Vista

```php
compact(
    'solicitudesPendientes',  // LengthAwarePaginator
    'pendientesMes',          // Int (para KPI)
    'aprobadas',              // Int (para KPI)
    'rechazadas',             // Int (para KPI)
    'totalSolicitudes',       // Int (para KPI)
    'tiempoMedio',            // Float
    'ultimasAprobadas',       // Collection (3 items)
    'ultimasRechazadas'       // Collection (3 items)
)
```

**Cada solicitud:**
```php
{
    'id_solicitud' => 42,
    'nombre_usuario' => 'Juan Garcia Lopez',
    'email_usuario' => 'juan@example.com',
    'tipo_solicitud' => 'arrendador|gestor',
    'tipo_label' => 'Arrendador / Empresa / Particular',
    'estado_solicitud' => 'pendiente|aprobada|rechazada',
    'descripcion_solicitud' => 'Descripcion de la solicitud...',
    'experiencia_solicitud' => 'Experiencia (si es gestor)',
    'direccion_fiscal_solicitud' => 'Direccion fiscal',
    'creado_solicitud' => '2024-11-15',
    'pagado' => true|false
}
```

---

## Datos que Muestra

| Dato | Fuente | Que es |
|------|--------|--------|
| **Solicitudes** | `tbl_solicitud_arrendador` + `tbl_solicitud_gestor` | Ambas combinadas |
| **Usuario Solicitante** | `tbl_usuario` | Nombre, email, telefono |
| **Estado** | pendiente, aprobada, rechazada | Actual de solicitud |
| **Fecha Solicitud** | creado_solicitud_* | Cuando se creo |
| **Revisor** | Admin que aprobo/rechazo | Si ya esta revisada |
| **Filtro por Estado** | dropdown | pendiente, aprobada, rechazada |
| **Filtro por Tipo** | dropdown | arrendador, gestor, all |
| **Filtro por Rango Fecha** | dropdown | mes, 3meses, año, all |

---

## Tablas Consultadas

```
tbl_solicitud_arrendador
├─ id_solicitud_arrendador
├─ id_usuario_fk → tbl_usuario
├─ id_plan_fk → tbl_plan (nullable, añadido Abril 2026)
├─ estado_solicitud_arrendador (pendiente|aprobada|rechazada)
├─ motivo_rechazo_solicitud_arrendador
├─ id_admin_revisa_fk → tbl_usuario (quien revisa)
├─ creado_solicitud_arrendador
└─ actualizado_solicitud_arrendador

tbl_solicitud_gestor
├─ id_solicitud_gestor
├─ id_usuario_fk → tbl_usuario
├─ estado_solicitud_gestor (pendiente|aprobada|rechazada)
├─ motivo_rechazo_solicitud_gestor
├─ id_admin_revisa_fk → tbl_usuario (quien revisa)
├─ creado_solicitud_gestor
└─ actualizado_solicitud_gestor

tbl_usuario
├─ id_usuario
├─ nombre_usuario
├─ email_usuario
├─ telefono_usuario
├─ documento_usuario
├─ tipo_documento_usuario
├─ stripe_status (reset a null al aprobar con plan)
└─ ... (otros campos)

tbl_rol_usuario (para asignar rol)
├─ id_usuario_fk → tbl_usuario
├─ id_rol_fk → tbl_rol
└─ creado_rol_usuario

tbl_perfil_arrendador (para crear perfil despues de aprobar)
├─ id_arrendador_fk → tbl_usuario
└─ datos especificos del arrendador

tbl_perfil_gestor (para crear perfil despues de aprobar)
├─ id_gestor_fk → tbl_usuario
└─ datos especificos del gestor

tbl_plan (plan de suscripcion seleccionado)
├─ id_plan
├─ nombre_plan
├─ slug_plan
├─ precio_plan
├─ max_propiedades_plan
├──rol_destino (arrendador|gestor)
└─ activo_plan

tbl_suscripcion (creada al aprobar con plan)
├─ id_suscripcion
├─ id_usuario_fk → tbl_usuario
├─ id_plan_fk → tbl_plan
├─ plan_suscripcion
├─ precio_pagado_suscripcion
├─ max_propiedades_suscripcion
├─ inicio_suscripcion
├─ fin_suscripcion
├─ estado_suscripcion (pendiente_pago|activa|expirada|cancelada)
├─ creado_suscripcion
└─ actualizado_suscripcion
```

---

## Flujo Tecnico Detallado

### 1. Usuario accede a `/admin/solicitudes`

```
GET /admin/solicitudes?estado=pendiente&tipo=arrendador&rango=mes
  ↓
Route::get('/solicitudes', [SolicitudController::class, 'index'])
  ↓
SolicitudController::index(Request $request)
```

### 2. Controlador obtiene solicitudes combinadas

```php
public function index(Request $request) {
    $estado = $request->has('estado') ? $request->input('estado') : 'pendiente';
    $rango = $request->input('rango', 'mes');
    $tipoSolicitud = $request->input('tipo', 'all');
    $q = $request->input('q');
    $ciudad = $request->input('ciudad');

    $solicitudesFiltradas = $this->obtenerSolicitudesCombinadas(
        $estado !== '' ? $estado : null, $rango, $tipoSolicitud, $q, $ciudad
    );

    $solicitudesPendientes = $this->paginarColeccion(
        $solicitudesFiltradas, 7, (int) $request->input('page', 1)
    );

    $pendientesMes = $this->contarSolicitudesCombinadas('pendiente', 'mes');
    $aprobadas = $this->contarSolicitudesCombinadas('aprobada', 'mes');
    $rechazadas = $this->contarSolicitudesCombinadas('rechazada', 'mes');
    $totalSolicitudes = $this->contarSolicitudesCombinadas(null, 'all');

    $ultimasAprobadas = $this->obtenerSolicitudesCombinadas('aprobada', 'mes')->take(3);
    $ultimasRechazadas = $this->obtenerSolicitudesCombinadas('rechazada', 'mes')->take(3);

    return view('admin.solicitudes', compact(
        'solicitudesPendientes', 'pendientesMes', 'aprobadas',
        'rechazadas', 'totalSolicitudes', 'tiempoMedio',
        'ultimasAprobadas', 'ultimasRechazadas'
    ));
}
```

### 3. Metodos auxiliares

```php
private function obtenerSolicitudesCombinadas($estado, $rango, $tipo, $q, $ciudad) {
    $solicitudesArrendador = $this->obtenerSolicitudesArrendador($estado, $rango, $q, $ciudad);
    $solicitudesGestor = $this->obtenerSolicitudesGestor($estado, $rango, $q, $ciudad);

    $todas = $solicitudesArrendador->concat($solicitudesGestor);

    if ($tipo === 'arrendador') return $solicitudesArrendador;
    if ($tipo === 'gestor') return $solicitudesGestor;

    return $todas->sortByDesc('creado_solicitud');
}
```

### 4. Vista renderiza tabla

```blade
<form method="GET" action="/admin/solicitudes">
    <select name="estado">
        <option value="">Todos</option>
        <option value="pendiente" @selected($estado === 'pendiente')>Pendiente ({{ $pendientesMes }})</option>
        <option value="aprobada" @selected($estado === 'aprobada')>Aprobada ({{ $aprobadas }})</option>
        <option value="rechazada" @selected($estado === 'rechazada')>Rechazada ({{ $rechazadas }})</option>
    </select>

    <select name="tipo">
        <option value="all">Todos los tipos</option>
        <option value="arrendador">Arrendador</option>
        <option value="gestor">Gestor</option>
    </select>

    <select name="rango">
        <option value="mes">Este mes</option>
        <option value="3meses">Ultimos 3 meses</option>
        <option value="anio">Este año</option>
        <option value="all">Todas</option>
    </select>

    <input type="text" name="q" placeholder="Buscar usuario..." value="{{ $q ?? '' }}">
    <button type="submit">Filtrar</button>
</form>

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalSolicitudes }}</span>
        <small>Total Solicitudes</small>
    </div>
    <div class="stat-card">
        <span>{{ $pendientesMes }}</span>
        <small>Pendientes Este Mes</small>
    </div>
    <div class="stat-card">
        <span>{{ $aprobadas }}</span>
        <small>Aprobadas Este Mes</small>
    </div>
    <div class="stat-card">
        <span>{{ $tiempoMedio }}</span>
        <small>Tiempo Medio Revision (dias)</small>
    </div>
</div>
```

---

## Botones y Acciones

| Boton | Funcion | Endpoint | Accion |
|-------|---------|----------|--------|
| **Aprobar** | Aprueba solicitud | POST `/admin/solicitudes/{id}/aprobar?tipo=arrendador` | BEGIN TRANSACTION: UPDATE estado, INSERT rol, INSERT perfil, INSERT codigos, INSERT suscripcion pendiente_pago (si tiene plan), RESET stripe_status, LIMPIA sesiones |
| **Rechazar** | Rechaza con motivo | POST `/admin/solicitudes/{id}/rechazar?tipo=arrendador` | UPDATE estado = 'rechazada', UPDATE notas |

### Ejemplo: Aprobar Solicitud Arrendador (Transaccion Compleja con Plan)

```php
public function aprobar(Request $request, $id) {
    DB::beginTransaction();
    try {
        $tipoSolicitud = $this->resolverTipoSolicitud($request->query('tipo', 'arrendador'));

        if ($tipoSolicitud === 'gestor') {
            // Aprobacion gestor (sin cambios de plan)
            $solicitud = DB::table('tbl_solicitud_gestor')->where('id_solicitud_gestor', $id)->first();
            DB::table('tbl_solicitud_gestor')->where('id_solicitud_gestor', $id)->update([
                'estado_solicitud_gestor' => 'aprobada',
                'id_admin_revisa_fk' => $idAdmin,
                'actualizado_solicitud_gestor' => Carbon::now()
            ]);
            $this->asegurarRolUsuario((int) $solicitud->id_usuario_fk, 'gestor');
            $this->asegurarPerfilGestor((int) $solicitud->id_usuario_fk);
            $codigoGestor = CodigoGestorService::obtenerOCrearCodigoParaGestor((int) $solicitud->id_usuario_fk);
            $this->limpiarSesionesUsuario((int) $solicitud->id_usuario_fk);
        } else {
            // Aprobacion arrendador (con plan)
            $solicitud = DB::table('tbl_solicitud_arrendador')->where('id_solicitud_arrendador', $id)->first();

            DB::table('tbl_solicitud_arrendador')->where('id_solicitud_arrendador', $id)->update([
                'estado_solicitud_arrendador' => 'aprobada',
                'id_admin_revisa_fk' => $idAdmin,
                'actualizado_solicitud_arrendador' => Carbon::now()
            ]);

            $this->asegurarRolUsuario((int) $solicitud->id_usuario_fk, 'arrendador');

            // Crear suscripcion pendiente de pago si se selecciono un plan
            if (!empty($solicitud->id_plan_fk)) {
                $plan = Plan::find($solicitud->id_plan_fk);
                if ($plan) {
                    Suscripcion::create([
                        'id_usuario_fk' => $solicitud->id_usuario_fk,
                        'plan_suscripcion' => $plan->slug_plan,
                        'id_plan_fk' => $plan->id_plan,
                        'precio_pagado_suscripcion' => $plan->precio_plan,
                        'max_propiedades_suscripcion' => $plan->max_propiedades_plan,
                        'inicio_suscripcion' => Carbon::now()->toDateString(),
                        'fin_suscripcion' => null,
                        'estado_suscripcion' => 'pendiente_pago',
                        'creado_suscripcion' => Carbon::now(),
                        'actualizado_suscripcion' => Carbon::now(),
                    ]);
                }
            }

            // Resetear stripe_status para forzar pago al iniciar sesion
            DB::table('tbl_usuario')->where('id_usuario', $solicitud->id_usuario_fk)->update([
                'stripe_status' => null,
                'actualizado_usuario' => Carbon::now(),
            ]);

            $this->limpiarSesionesUsuario((int) $solicitud->id_usuario_fk);
        }

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Solicitud aprobada correctamente.']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
```

---

## Filtros

| Filtro | Parametro | Valores | Efecto |
|--------|-----------|---------|--------|
| **Estado** | `estado` | pendiente, aprobada, rechazada | Filtra por estado |
| **Tipo** | `tipo` | arrendador, gestor, all | Filtra por tipo |
| **Rango** | `rango` | mes, 3meses, año, all | WHERE creado >= fecha |
| **Busqueda** | `q` | texto libre | WHERE nombre LIKE '%texto%' |
| **Ciudad** | `ciudad` | texto libre | WHERE ciudad_usuario LIKE '%text%' |

---

## Nuevo Flujo de Aprobacion con Plan (Anyadido Abril 2026)

**Contexto:** Los arrendadores ahora deben seleccionar un plan de suscripcion al enviar su solicitud. El admin al aprobar crea automaticamente una suscripcion pendiente de pago.

### Cambios Implementados

1. **Migracion:** `2026_04_24_100002_add_id_plan_fk_to_tbl_solicitud_arrendador_table.php` anyade `id_plan_fk` nullable FK a `tbl_plan`

2. **Modelo `SolicitudArrendador`:** Anyadido `id_plan_fk` a `$fillable`, nueva relacion `plan()` (BelongsTo)

3. **Controlador `SolicitudArrendadorController`:**
   - `create()` pasa `Plan::where('rol_destino','arrendador')->where('activo_plan',true)` a la vista
   - `store()` valida `id_plan_fk` como required + exists en tbl_plan

4. **Vista `form_volverse_arrendador.blade.php`:** Nuevo `.planes-grid` con tarjetas de plan entre `num_propiedades` y `descripcion`

5. **JS `solicitud_arrendador.js`:** Funcion `validarPlanSeleccionado()`, plan card click handler

6. **Admin `SolicitudController@aprobar()`:**
   - Lee `id_plan_fk` de la solicitud aprobada
   - Crea registro en `tbl_suscripcion` con `estado='pendiente_pago'`
   - Resetea `stripe_status` a null en `tbl_usuario`
   - Limpia sesiones para forzar re-login + pago

7. **CSS `miembro.css`:** Clases `.planes-grid`, `.plan-tarjeta`, `.plan-tarjeta-seleccionada`

### Flujo Completo

```
Usuario (miembro) llena formulario de arrendador
    ↓
Selecciona un plan de suscripcion (gratuito o de pago)
    ↓
Admin recibe solicitud con id_plan_fk
    ↓
Admin hace clic en "Aprobar"
    ↓
SolicitudController@aprobar():
    ├─ UPDATE tbl_solicitud_arrendador estado='aprobada'
    ├─ INSERT tbl_rol_usuario (rol arrendador)
    ├─ INSERT tbl_perfil_arrendador
    ├─ INSERT tbl_suscripcion (id_plan_fk, estado='pendiente_pago')
    ├─ UPDATE tbl_usuario stripe_status=null
    ├─ Limpia sesiones del usuario
    └─ Crea notificacion
    ↓
Usuario renovado: debe hacer login otra vez
    ↓
Sistema detecta stripe_status=null → redirige a pago
    ↓
Usuario paga → Suscripcion pasa a 'activa'
```

### Plan Tarjeta UI

```blade
<div class="planes-grid" id="planes-container">
    @foreach ($planes as $plan)
        <label class="plan-tarjeta {{ old('id_plan_fk') == $plan->id_plan ? 'plan-tarjeta-seleccionada' : '' }}"
               data-plan-id="{{ $plan->id_plan }}">
            <input type="radio" name="id_plan_fk" value="{{ $plan->id_plan }}"
                   class="plan-radio" {{ old('id_plan_fk') == $plan->id_plan ? 'checked' : '' }}>
            <div class="plan-tarjeta-contenido">
                <div class="plan-tarjeta-header">
                    <span class="plan-tarjeta-nombre">{{ $plan->nombre_plan }}</span>
                    <span class="plan-tarjeta-precio">
                        {{ number_format($plan->precio_plan, 2) }}€<small>/mes</small>
                    </span>
                </div>
                <p class="plan-tarjeta-descripcion">{{ $plan->descripcion_plan }}</p>
                <div class="plan-tarjeta-propiedades">
                    <i class="bi bi-house"></i>
                    <span>Hasta {{ $plan->max_propiedades_plan }} {{ $plan->max_propiedades_plan == 1 ? 'propiedad' : 'propiedades' }}</span>
                </div>
            </div>
            <div class="plan-tarjeta-check">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </label>
    @endforeach
</div>
```

---

## Puntos Importantes

1. **Transaccion en aprobar:** 5-7 operaciones conjuntas (rollback si falla cualquiera)
2. **Solicitudes combinadas:** De 2 tablas diferentes, combinadas en memoria
3. **Paginacion en memoria:** Los datos se filtran en codigo PHP, no en BD (menos eficiente)
4. **Roles y perfiles:** Se crean juntos al aprobar
5. **Notificaciones:** Se crean automaticamente
6. **Estados finales:** Aprobada o rechazada (no vuelven a pendiente)
7. **Plan + Suscripcion (Nuevo):** Al aprobar arrendador con plan, se crea suscripcion `pendiente_pago`, se resetea `stripe_status`, y se fuerzan re-login + pago
8. **Sesiones limpiadas:** `limpiarSesionesUsuario()` invalida todas las sesiones activas del usuario aprobado para que tenga que volver a iniciar sesion
9. **Stripe Status null:** Al tener `stripe_status = null`, el middleware `CheckSuscripcion` redirige automaticamente al usuario a la pagina de pago al iniciar sesion

---

## Flujo Resumido

```
Admin accede /admin/solicitudes
            ↓
GET /admin/solicitudes?estado=pendiente&tipo=all&rango=mes
            ↓
SolicitudController::index()
            ↓
1. Obtener solicitudes arrendador (filtradas)
2. Obtener solicitudes gestor (filtradas)
3. Combinar y ordenar ambas
4. Paginar (7 por pagina)
5. Obtener contadores para KPIs
            ↓
Blade renderiza tabla + filtros + KPIs
            ↓
Admin ve 7 solicitudes filtradas
            ↓
Si clickea "Aprobar" en solicitud arrendador
            ↓
POST /admin/solicitudes/{id}/aprobar?tipo=arrendador
            ↓
BEGIN TRANSACTION
├─ UPDATE estado = 'aprobada'
├─ INSERT rol (arrendador)
├─ INSERT perfil arrendador
├─ INSERT codigos acceso
├─ [NUEVO] INSERT suscripcion (pendiente_pago) si tiene plan
├─ [NUEVO] UPDATE stripe_status = null (fuerza pago)
├─ [NUEVO] Limpiar sesiones del usuario
└─ CREATE notificacion
            ↓
COMMIT
            ↓
JSON response + reload
            ↓
Usuario hace login otra vez
            ↓
Sistema redirige a pago de suscripcion
            ↓
Usuario paga → Suscripcion 'activa'
```
