# 🚨 README: ADMIN INCIDENCIAS

**Vista:** `resources/views/admin/incidencias.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/IncidenciaController.php`  
**Ruta:** `GET /admin/incidencias`

---

## 🎯 Propósito

Gestiona **incidencias reportadas** por inquilinos (problemas, daños, mantenimiento). Permite:
- Ver incidencias en **vista Kanban** (5 estados) o **vista Lista**
- Cambiar estado de una incidencia
- Responder a incidencias (envía email)
- Crear categorías de incidencias
- Ver historial de cambios
- Filtrar por categoría, prioridad, propiedad
- Buscar incidencias

**Incidencia** = Problema reportado en una propiedad (ej: filtración de agua, electricidad rota, etc)

---

## 📊 Estados de Incidencias

| Estado | Significado | Quién actúa | Siguiente |
|--------|-------------|-------------|-----------|
| **abierta** | Recién reportada | Gestor asignado | → esperando_decision |
| **esperando_decision** | Esperando decisión arrendador | Arrendador | → esperando_pago o solucionada |
| **esperando_pago** | Aguardando que pague para reparar | Arrendador | → resuelta |
| **solucionada** | Reparada, pagada | Sistema | → resuelta |
| **resuelta** | Cerrada definitivamente | - | Final |

---

## 🔌 Tablas Consultadas

```
tbl_incidencia
├─ id_incidencia
├─ id_reporta_fk → tbl_usuario (inquilino que reporta)
├─ id_asignado_fk → tbl_usuario (gestor asignado)
├─ id_propiedad_fk → tbl_propiedad
├─ id_categoria_fk → tbl_categoria
├─ titulo_incidencia
├─ descripcion_incidencia
├─ estado_incidencia (abierta|esperando_decision|esperando_pago|solucionada|resuelta)
├─ creado_incidencia
├─ actualizado_incidencia
└─ url_documento_incidencia (foto/evidencia)

tbl_propiedad
├─ id_propiedad
├─ titulo_propiedad
├─ calle_propiedad
├─ numero_propiedad
├─ piso_propiedad
├─ puerta_propiedad
├─ ciudad_propiedad
└─ id_arrendador_fk → tbl_usuario

tbl_usuario (3 roles diferentes en la misma incidencia)
├─ reporta.id_usuario      (inquilino)
├─ asignado.id_usuario     (gestor)
├─ arrendador.id_usuario   (propietario)
└─ ...

tbl_categoria
├─ id_categoria
└─ nombre_categoria (Fontanería, Electricidad, etc)

tbl_respuesta_incidencia
├─ id_respuesta_incidencia
├─ id_incidencia_fk → tbl_incidencia
├─ respuesta (texto respuesta del gestor)
├─ respondido_por → tbl_usuario
└─ creado

tbl_historial_incidencia
├─ id_historial_incidencia
├─ id_incidencia_fk → tbl_incidencia
├─ estado_anterior
├─ estado_nuevo
├─ cambio_realizado_por → tbl_usuario
└─ creado_historial
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/incidencias`

```
GET /admin/incidencias
  ↓
Route::get('/incidencias', [IncidenciaController::class, 'index'])
  ↓
IncidenciaController::index()
```

### 2️⃣ Controlador obtiene incidencias por estados

```php
// app/Http/Controllers/Admin/IncidenciaController.php

public function index() {
    // Query base reutilizable
    $queryBase = DB::table('tbl_incidencia')
        ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
        ->join('tbl_usuario as reporta', 'reporta.id_usuario', '=', 'tbl_incidencia.id_reporta_fk')
        ->leftJoin('tbl_usuario as asignado', 'asignado.id_usuario', '=', 'tbl_incidencia.id_asignado_fk')
        ->leftJoin('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
        ->leftJoin('tbl_categoria', 'tbl_categoria.id_categoria', '=', 'tbl_incidencia.id_categoria_fk')
        ->select(
            'tbl_incidencia.*',
            'tbl_propiedad.titulo_propiedad',
            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
            'tbl_propiedad.ciudad_propiedad',
            'tbl_categoria.nombre_categoria',
            'reporta.nombre_usuario as nombre_inquilino',
            'asignado.nombre_usuario as nombre_gestor',
            'arrendador.nombre_usuario as nombre_arrendador'
        );
    
    // PASO 1: Obtener incidencias por cada estado (usando clone para reutilizar query)
    $abiertas = (clone $queryBase)
        ->where('tbl_incidencia.estado_incidencia', 'abierta')
        ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
        ->get();
    
    $esperandoDecision = (clone $queryBase)
        ->where('tbl_incidencia.estado_incidencia', 'esperando_decision')
        ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
        ->get();
    
    $esperandoPago = (clone $queryBase)
        ->where('tbl_incidencia.estado_incidencia', 'esperando_pago')
        ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
        ->get();
    
    $solucionadas = (clone $queryBase)
        ->where('tbl_incidencia.estado_incidencia', 'solucionada')
        ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
        ->get();
    
    $resueltas = (clone $queryBase)
        ->where('tbl_incidencia.estado_incidencia', 'resuelta')
        ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
        ->get();
    
    // PASO 2: Marcar incidencias inactivas (sin cambios > 14 días)
    $marcarInactividad = function($col) {
        $collection = collect($col);
        return $collection->map(function($inc) {
            $inc->inactivo = Carbon::parse($inc->actualizado_incidencia)
                ->lt(Carbon::now()->subWeeks(2));
            return $inc;
        });
    };
    
    $abiertas = $marcarInactividad($abiertas);
    $esperandoDecision = $marcarInactividad($esperandoDecision);
    $esperandoPago = $marcarInactividad($esperandoPago);
    
    return view('admin.incidencias', compact(
        'abiertas',
        'esperandoDecision',
        'esperandoPago',
        'solucionadas',
        'resueltas'
    ));
}
```

### 3️⃣ Vista renderiza en Kanban (por estados)

```blade
<!-- resources/views/admin/incidencias.blade.php -->

<div class="kanban-container">
    <!-- Columna 1: Abiertas -->
    <div class="kanban-column">
        <h3>Abiertas ({{ count($abiertas) }})</h3>
        <div class="kanban-items">
            @forelse($abiertas as $inc)
                <div class="kanban-card @if($inc->inactivo) inactivo @endif" 
                     data-id="{{ $inc->id_incidencia }}" 
                     data-estado="abierta">
                    
                    <h4>{{ $inc->titulo_incidencia }}</h4>
                    <p class="propiedad">{{ $inc->titulo_propiedad }}</p>
                    <p class="direccion">{{ $inc->direccion_propiedad }}, {{ $inc->ciudad_propiedad }}</p>
                    
                    <div class="info">
                        <small>Reportante: {{ $inc->nombre_inquilino }}</small>
                        <small>Gestor: {{ $inc->nombre_gestor ?? 'Sin asignar' }}</small>
                        <small>Creado: {{ $inc->creado_incidencia->format('d/m/Y') }}</small>
                    </div>
                    
                    @if($inc->inactivo)
                        <span class="badge bg-danger">Inactiva 14+ días</span>
                    @endif
                    
                    <div class="acciones">
                        <button onclick="abrirModalRespuesta({{ $inc->id_incidencia }})">
                            Responder
                        </button>
                        <select onchange="cambiarEstado({{ $inc->id_incidencia }}, this.value)">
                            <option value="">Cambiar estado...</option>
                            <option value="esperando_decision">Esperando Decisión</option>
                            <option value="solucionada">Solucionada</option>
                            <option value="resuelta">Resuelta</option>
                        </select>
                    </div>
                </div>
            @empty
                <p class="text-muted">Sin incidencias</p>
            @endforelse
        </div>
    </div>
    
    <!-- Columna 2: Esperando Decisión -->
    <div class="kanban-column">
        <h3>Esperando Decisión ({{ count($esperandoDecision) }})</h3>
        <!-- Similar... -->
    </div>
    
    <!-- Columna 3: Esperando Pago -->
    <div class="kanban-column">
        <h3>Esperando Pago ({{ count($esperandoPago) }})</h3>
        <!-- Similar... -->
    </div>
    
    <!-- Columna 4: Solucionadas -->
    <div class="kanban-column">
        <h3>Solucionadas ({{ count($solucionadas) }})</h3>
        <!-- Similar... -->
    </div>
    
    <!-- Columna 5: Resueltas -->
    <div class="kanban-column">
        <h3>Resueltas ({{ count($resueltas) }})</h3>
        <!-- Similar... -->
    </div>
</div>

<!-- Modal Responder Incidencia -->
<div id="modal-respuesta" class="modal" style="display:none;">
    <h3>Responder a Incidencia</h3>
    <textarea id="textarea-respuesta" placeholder="Escribe respuesta..."></textarea>
    <button onclick="enviarRespuesta()">Enviar Respuesta</button>
</div>
```

---

## 🔘 Botones y Acciones

### Botones Principales (Toolbar)

| Botón | ID | Función | Endpoint | Acción |
|-------|-----|---------|----------|--------|
| **Kanban** | `#btnVistaKanban` | Alterna a vista Kanban | — | Frontend: cambia display |
| **Lista** | `#btnVistaLista` | Alterna a vista Lista/Tabla | — | Frontend: cambia display |
| **Nueva Categoría** | `#btnCrearCategoria` | Abre modal crear | — | Frontend: abre `#modalCrearCategoria` |

### Acciones sobre Tarjetas Kanban

Al hacer **click en una tarjeta** (`.tarjeta-inc`):

```javascript
document.querySelectorAll('.tarjeta-inc').forEach(tarjeta => {
    tarjeta.addEventListener('click', function() {
        const incidenciaId = this.dataset.id;
        const estado = this.dataset.estado;
        
        // Abre modal detalle de incidencia
        fetch(`/admin/incidencias/${incidenciaId}/detalle`)
            .then(r => r.json())
            .then(data => {
                mostrarModalDetalleIncidencia(data);
            });
    });
});
```

**Modal de Detalle incluye:**
- Botón "Responder" → POST `/admin/incidencias/{id}/responder`
- Selector "Cambiar Estado" → POST `/admin/incidencias/{id}/cambiar-estado`
- Botón "Ver Historial" → GET `/admin/incidencias/{id}/historial`
- Botón "Crear Presupuesto" → GET `/admin/incidencias/{id}/presupuesto`

---

### Ejemplo: Cambiar Estado (Frontend → Backend)

**Frontend (JavaScript):**
```javascript
function cambiarEstado(incidenciaId, nuevoEstado) {
    if (!nuevoEstado) return;
    
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    fetch(`/admin/incidencias/${incidenciaId}/cambiar-estado`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            // Animar cambio visual
            const tarjeta = document.querySelector(`[data-id="${incidenciaId}"]`);
            tarjeta.classList.add('animate-fade');
            
            // Recargar después de 500ms
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message || 'Error al cambiar estado');
        }
    });
}
```

**Backend (PHP):**
```php
public function cambiarEstado(Request $request, $id) {
    $nuevoEstado = $request->input('estado');
    $estadosValidos = ['abierta', 'esperando_decision', 'esperando_pago', 'solucionada', 'resuelta'];
    
    if (!in_array($nuevoEstado, $estadosValidos)) {
        return response()->json(['ok' => false, 'message' => 'Estado inválido']);
    }
    
    $incidencia = DB::table('tbl_incidencia')->find($id);
    
    // Validar cambios permitidos
    $cambiosPermitidos = [
        'abierta' => ['esperando_decision', 'solucionada'],
        'esperando_decision' => ['esperando_pago', 'solucionada'],
        'esperando_pago' => ['solucionada', 'resuelta'],
        'solucionada' => ['resuelta'],
        'resuelta' => [] // Cerrada, no puede cambiar
    ];
    
    if (!in_array($nuevoEstado, $cambiosPermitidos[$incidencia->estado_incidencia] ?? [])) {
        return response()->json(['ok' => false, 'message' => 'Transición no permitida']);
    }
    
    // BEGIN TRANSACTION
    DB::beginTransaction();
    try {
        // UPDATE estado
        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update([
                'estado_incidencia' => $nuevoEstado,
                'actualizado_incidencia' => now()
            ]);
        
        // INSERT historial
        DB::table('tbl_historial_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'estado_anterior' => $incidencia->estado_incidencia,
            'estado_nuevo' => $nuevoEstado,
            'cambio_realizado_por' => auth()->id(),
            'creado_historial' => now()
        ]);
        
        // SEND EMAIL a inquilino
        $incidencia_actualizada = DB::table('tbl_incidencia')
            ->join('tbl_usuario', 'tbl_usuario.id_usuario', 'tbl_incidencia.id_reporta_fk')
            ->select('tbl_usuario.email_usuario', 'tbl_incidencia.*')
            ->where('tbl_incidencia.id_incidencia', $id)
            ->first();
        
        Mail::to($incidencia_actualizada->email_usuario)
            ->send(new CambioEstadoIncidencia($incidencia_actualizada, $nuevoEstado));
        
        DB::commit();
        
        return response()->json(['ok' => true, 'message' => 'Estado actualizado']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['ok' => false, 'message' => $e->getMessage()]);
    }
}
```

---

### Ejemplo: Responder Incidencia

**Frontend:**
```javascript
function abrirModalRespuesta(incidenciaId) {
    const modal = new bootstrap.Modal(document.getElementById('modalRespuesta'));
    document.getElementById('incidenciaIdInput').value = incidenciaId;
    modal.show();
}

function enviarRespuesta() {
    const incidenciaId = document.getElementById('incidenciaIdInput').value;
    const respuesta = document.getElementById('textareaRespuesta').value.trim();
    
    if (!respuesta) {
        alert('Escribe una respuesta');
        return;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    fetch(`/admin/incidencias/${incidenciaId}/responder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ respuesta })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert('Respuesta enviada al inquilino');
            bootstrap.Modal.getInstance(document.getElementById('modalRespuesta')).hide();
            location.reload();
        }
    });
}
```

**Backend:**
```php
public function responder(Request $request, $id) {
    $respuesta = $request->input('respuesta');
    
    if (!$respuesta) {
        return response()->json(['ok' => false]);
    }
    
    // INSERT respuesta
    DB::table('tbl_respuesta_incidencia')->insert([
        'id_incidencia_fk' => $id,
        'respuesta' => $respuesta,
        'respondido_por' => auth()->id(),
        'creado' => now()
    ]);
    
    // UPDATE última respuesta
    DB::table('tbl_incidencia')
        ->where('id_incidencia', $id)
        ->update(['actualizado_incidencia' => now()]);
    
    // SEND EMAIL al inquilino
    $incidencia = DB::table('tbl_incidencia')
        ->join('tbl_usuario', 'tbl_usuario.id_usuario', 'tbl_incidencia.id_reporta_fk')
        ->select('tbl_usuario.email_usuario', 'tbl_incidencia.titulo_incidencia')
        ->where('tbl_incidencia.id_incidencia', $id)
        ->first();
    
    Mail::to($incidencia->email_usuario)
        ->send(new RespuestaIncidencia($incidencia, $respuesta));
    
    return response()->json(['ok' => true]);
}
```

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Efecto |
|--------|---------|------|--------|
| **Buscar** | `#buscadorInc` | input text | Búsqueda en tiempo real |
| **Categoría** | `#selectCategoria` | select | Filtra por tipo (Fontanería, Electricidad, etc) |
| **Prioridad** | `#selectPrioridad` | select | Filtra por urgencia (Urgente, Alta, Media, Baja) |
| **Propiedad** | `#selectPropiedad` | select | Filtra por ubicación |

**JavaScript de Filtros:**
```javascript
// Buscador
document.getElementById('buscadorInc').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    // Filtra tarjetas Kanban o filas tabla según vista activa
    document.querySelectorAll('.tarjeta-inc').forEach(tarjeta => {
        const titulo = tarjeta.querySelector('.tarjeta-titulo').textContent.toLowerCase();
        const propiedad = tarjeta.querySelector('.tarjeta-propiedad').textContent.toLowerCase();
        tarjeta.style.display = 
            titulo.includes(valor) || propiedad.includes(valor) ? '' : 'none';
    });
});

// Filtro por categoría
document.getElementById('selectCategoria').addEventListener('change', function(e) {
    const categoriaId = e.target.value;
    // Filtra por data-categoria
    document.querySelectorAll('.tarjeta-inc').forEach(tarjeta => {
        if (!categoriaId || tarjeta.dataset.categoria === categoriaId) {
            tarjeta.style.display = '';
        } else {
            tarjeta.style.display = 'none';
        }
    });
});
```

---

## 📺 Vista Kanban vs Lista

Hay **2 botones** en toolbar derecha para cambiar visualización:

| Botón | ID | Función |
|-------|-----|---------|
| **Kanban** | `#btnVistaKanban` | Muestra 5 columnas por estado |
| **Lista** | `#btnVistaLista` | Muestra tabla con scroll horizontal |

**JavaScript Toggle Vistas:**
```javascript
let vistaActual = 'kanban'; // Por defecto Kanban

document.getElementById('btnVistaKanban').addEventListener('click', function() {
    vistaActual = 'kanban';
    document.getElementById('kanbanBoard').style.display = 'flex';
    document.getElementById('tablaIncidencias').style.display = 'none';
    this.classList.add('activo');
    document.getElementById('btnVistaLista').classList.remove('activo');
});

document.getElementById('btnVistaLista').addEventListener('click', function() {
    vistaActual = 'lista';
    document.getElementById('kanbanBoard').style.display = 'none';
    document.getElementById('tablaIncidencias').style.display = 'table';
    this.classList.add('activo');
    document.getElementById('btnVistaKanban').classList.remove('activo');
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ KPI grid: 5 columnas visibles
- ✅ Kanban: 5 columnas lado a lado (scroll horizontal si es necesario)
- ✅ Filtros: Todos visibles en toolbar
- ✅ Tabla: Todas las columnas visibles

**Clases CSS aplicadas:** Ninguna ocultación

### 📱 **Mobile (< 768px)**
- ❌ KPIs: 2 columnas (carousel con scroll)
- ❌ Kanban: 1 columna visible (scroll horizontal para ver otras)
- ❌ Filtros: En collapse/accordion
- ❌ Tabla: Solo columnas esenciales (`col-mobile-hide` oculta algunas)

**Clases CSS aplicadas:**
```html
<!-- En tabla o componentes -->
<th class="col-mobile-hide">ESTADO</th>  <!-- Se oculta en móvil -->
<th class="col-tablet-hide">FECHA</th>  <!-- Se oculta en tablet/móvil -->
```

**Comportamiento en tabla:**
- En móvil, se muestra `data-label` antes de cada celda para claridad
- Scroll horizontal si tabla es muy ancha

---

## 📋 Filtros Reales de Incidencias

**Actualmente hay 4 filtros interactivos** (no solo visualización):
- Incidencias organizadas por sus 5 estados (SIEMPRE)
- Pueden filtrarse por categoría, prioridad, propiedad
- Búsqueda en tiempo real
- Dentro de cada estado, ordenadas por fecha descendente

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'abiertas',                      // Collection
    'esperandoDecision',             // Collection
    'esperandoPago',                 // Collection
    'solucionadas',                  // Collection
    'resueltas',                     // Collection
    'totalAbiertas',                 // Int (para KPI)
    'totalEsperandoDecision',        // Int (para KPI)
    'totalEsperandoPago',            // Int (para KPI)
    'totalSolucionadas',             // Int (para KPI)
    'urgentes',                      // Int (para KPI badge rojo pulsante)
    'categorias',                    // Collection (para select filtro)
    'propiedades'                    // Collection (para select filtro)
)
```

**Cada incidencia en tarjeta Kanban incluye:**
```php
{
    'id_incidencia' => 42,
    'titulo_incidencia' => 'Filtración de agua',
    'descripcion_incidencia' => 'Gotea agua del techo en la cocina...',
    'estado_incidencia' => 'abierta',
    'prioridad_incidencia' => 'urgente|alta|media|baja',
    'titulo_propiedad' => 'Apartamento 3B',
    'direccion_propiedad' => 'Calle Mayor 123, Piso 3, Puerta B',
    'ciudad_propiedad' => 'Madrid',
    'nombre_inquilino' => 'Juan García López',
    'nombre_gestor' => 'Carlos López',
    'nombre_categoria' => 'Fontanería',
    'creado_incidencia' => Carbon object,
    'actualizado_incidencia' => Carbon object,
    'inactivo' => false
}
```

**En tarjeta se muestran:**
- Badge de prioridad (color según urgencia)
- Tiempo transcurrido desde creación (diffForHumans: "hace 2 días")
- Título incidencia (máx 60 caracteres)
- Descripción resumida (máx 60 caracteres)
- Icono categoría (`bi-droplet` para Fontanería, `bi-lightning` para Electricidad, etc)
- Avatar inquilino (iniciales en colores aleatorios)
- Propiedad dirección truncada (máx 15 caracteres)

---

## 🔄 Flujo Resumido

```
Admin accede /admin/incidencias
            ↓
IncidenciaController::index()
            ↓
1. Query base con JOINs (usuarios, propiedad, categoría)
2. Clone query y filtra por CADA estado (5 clones)
3. Ejecuta 5 queries: obtiene todas las incidencias
4. Calcula totales para KPIs
5. Pasa colecciones + totales a Blade
            ↓
Blade renderiza:
├─ Hero + KPIs (5 mini cards)
├─ Toolbar (buscador + 3 selects + 2 botones vista)
├─ Kanban Board (div.kanban-board)
│  ├─ 5 columnas (5 divs .kanban-col)
│  └─ Tarjetas incidencias (.tarjeta-inc)
└─ (Tabla oculta, se muestra si cambia a vista Lista)
            ↓
JavaScript escucha eventos:
├─ Click en tarjeta → abre modal detalle
├─ Input buscar → filtra tarjetas en tiempo real
├─ Change selects → filtra tarjetas
├─ Click btn Kanban/Lista → alterna display
└─ Click cambiar estado → POST endpoint + reload
            ↓
Si admin cambia estado:
            ↓
POST /admin/incidencias/{id}/cambiar-estado
            ↓
Backend:
├─ UPDATE estado_incidencia
├─ INSERT tbl_historial_incidencia
├─ SEND EMAIL al inquilino
└─ COMMIT transaction
            ↓
Frontend reload página → se ejecutan 5 queries nuevas
```

---

## ⚠️ Puntos Importantes

1. **5 columnas Kanban:** Los datos YA están separados por estado en controlador
2. **Vista Kanban es default:** Lista es alternativa con mismo set de datos
3. **Filtros son frontend:** Buscan en tarjetas/tablas ya renderizadas (no nuevas queries)
4. **Responsive real:**
   - Desktop: Kanban 5 columnas, KPI 5 items, todos los filtros visibles
   - Móvil: Kanban 1 columna (scroll), KPI 2-3 items, filtros en collapse
5. **Prioridad:** Campo real en BD, usado para badges coloreadas
6. **Email transaccional:** Se envía al cambiar estado y responder
7. **Historial:** Se registra CADA cambio de estado
8. **Icons Bootstrap:** Mapeo automático según categoría (Fontanería→droplet, Electricidad→lightning, etc)
9. **Avatar avatares:** Colores basados en hash del ID para consistencia
10. **Sin paginación:** Muestra TODAS las incidencias (puede ser lento si hay 1000+)

---

## 🐛 Debugging y Desarrollo

### Ver incidencias por estado en consola:

```php
// En controlador
$counts = [
    'abiertas' => count($abiertas),
    'esperando_decision' => count($esperandoDecision),
    'esperando_pago' => count($esperandoPago),
    'solucionadas' => count($solucionadas),
    'resueltas' => count($resueltas),
];
dd($counts);
```

### Probar filtros en navegador (DevTools Console):

```javascript
// Ver tarjetas filtradas
const tarjetas = document.querySelectorAll('.tarjeta-inc');
console.log(`Total tarjetas: ${tarjetas.length}`);
console.log(`Visibles: ${Array.from(tarjetas).filter(t => t.style.display !== 'none').length}`);

// Simular cambio estado (ver request)
cambiarEstado(42, 'esperando_decision');

// Ver datos de tarjeta
const tarjeta = document.querySelector('[data-id="42"]');
console.log({
    id: tarjeta.dataset.id,
    estado: tarjeta.dataset.estado,
    titulo: tarjeta.querySelector('.tarjeta-titulo').textContent
});
```

### CSS para responsive:

```css
/* responsive-tablas.css */
@media (max-width: 768px) {
    .col-tablet-hide { display: none; }  /* Oculta en tablet/móvil */
}

@media (max-width: 480px) {
    .col-mobile-hide { display: none; }  /* Oculta en móvil */
    .kanban-board { flex-direction: column; } /* 1 columna en móvil */
    .kpi-grid-pequeno { grid-template-columns: repeat(2, 1fr); } /* 2 columnas */
}
```
