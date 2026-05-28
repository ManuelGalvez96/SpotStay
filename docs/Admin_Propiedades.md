# 🏠 README: ADMIN PROPIEDADES

**Vista:** `resources/views/admin/propiedades.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/PropiedadController.php`  
**Ruta:** `GET /admin/propiedades`

---

## 🎯 Propósito

Gestiona **todas las propiedades** registradas en SpotStay. Permite:
- Ver listado de todas las propiedades
- Filtrar por arrendador, ciudad, estado, rango de precio
- Buscar por título de propiedad
- Ver detalles de propiedad
- Editar propiedad
- Eliminar propiedad
- Ver alquileres activos de cada propiedad

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar** | `#buscadorPropiedades` | input text | Por dirección o ciudad |
| **Estado** | `#selectEstado` | select | Publicada, Alquilada, Borrador, Inactiva |
| **Ciudad** | `#selectCiudad` | select | Madrid, Barcelona, Valencia, Sevilla, Bilbao |
| **Precio** | `#selectPrecio` | select | Rangos (0-500€, 500-1000€, 1000-2000€, 2000+€) |

**JavaScript de Filtros:**
```javascript
// Buscador en tiempo real
document.getElementById('buscadorPropiedades').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyPropiedades tr').forEach(fila => {
        const propiedad = fila.querySelector('.propiedad-nombre')?.textContent.toLowerCase() || '';
        const ciudad = fila.querySelector('.propiedad-ciudad')?.textContent.toLowerCase() || '';
        fila.style.display = 
            propiedad.includes(valor) || ciudad.includes(valor) ? '' : 'none';
    });
});

// Filtro por estado
document.getElementById('selectEstado').addEventListener('change', function(e) {
    const estado = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyPropiedades tr').forEach(fila => {
        if (!estado) {
            fila.style.display = '';
        } else {
            const badge = fila.querySelector('.badge-estado');
            const estadoFila = (badge?.textContent || '').toLowerCase();
            fila.style.display = estadoFila.includes(estado) ? '' : 'none';
        }
    });
});

// Filtro por ciudad
document.getElementById('selectCiudad').addEventListener('change', function(e) {
    const ciudad = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyPropiedades tr').forEach(fila => {
        if (!ciudad) {
            fila.style.display = '';
        } else {
            const textoCiudad = fila.querySelector('.propiedad-ciudad')?.textContent.toLowerCase() || '';
            fila.style.display = textoCiudad.includes(ciudad) ? '' : 'none';
        }
    });
});

// Filtro por precio (rango)
document.getElementById('selectPrecio').addEventListener('change', function(e) {
    const rango = e.target.value;
    if (!rango) {
        document.querySelectorAll('#tbodyPropiedades tr').forEach(f => f.style.display = '');
        return;
    }
    
    const [minPrecio, maxPrecio] = rango.includes('+') 
        ? [parseInt(rango.split('+')[0]), 999999]
        : rango.split('-').map(Number);
    
    document.querySelectorAll('#tbodyPropiedades tr').forEach(fila => {
        const precio = parseInt(fila.dataset.precio || 0);
        fila.style.display = 
            precio >= minPrecio && precio <= maxPrecio ? '' : 'none';
    });
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 6 columnas visibles (Propiedad, Arrendador, Estado, Precio, Inquilinos, Acciones)
- ✅ Filtros: Todos en toolbar
- ✅ KPIs: 4 columnas
- ✅ Paginación: Visible

**Columnas mostradas:**
```
PROPIEDAD | ARRENDADOR | ESTADO | PRECIO | INQUILINOS | ACCIONES
```

### 📱 **Mobile (< 768px)**
- ❌ ARRENDADOR: Oculta (clase `col-mobile-hide`)
- ❌ PRECIO: Oculta (clase `col-mobile-hide`)
- ❌ INQUILINOS: Oculta (clase `col-tablet-hide`)
- ✅ PROPIEDAD, ESTADO, ACCIONES: Siempre visibles

**Columnas mostradas en móvil:**
```
PROPIEDAD | ESTADO | ACCIONES
```

**Estructura en móvil:**
```html
<td data-label="PROPIEDAD">
    <div class="propiedad-celda">
        <div class="thumb-propiedad"></div>
        <div>
            <p class="propiedad-nombre">Calle Mayor 14</p>
            <p class="propiedad-ciudad">Madrid, 28001</p>
        </div>
    </div>
</td>

<!-- Ocultas -->
<td data-label="ARRENDADOR" class="col-mobile-hide">...</td>
<td data-label="PRECIO" class="col-mobile-hide">...</td>
<td data-label="INQUILINOS" class="col-tablet-hide">...</td>
```

**CSS:**
```css
@media (max-width: 768px) {
    .col-mobile-hide { display: none; }
    .col-tablet-hide { display: none; }
}
```

---

## 🔄 Paginación

**Tipo:** Bootstrap Pagination  
**Items por página:** 10 propiedades  

**Estructura:**
```html
<nav aria-label="Paginación de propiedades">
    <ul class="pagination pagination-sm mb-0" id="paginas">
        <!-- Generado dinámicamente con JS -->
        <li class="page-item"><button class="page-link" data-pagina="1">1</button></li>
        <li class="page-item"><button class="page-link" data-pagina="2">2</button></li>
    </ul>
</nav>
```

**JavaScript:**
```javascript
document.querySelectorAll('#paginas .page-link').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const pagina = this.dataset.pagina;
        const params = new URLSearchParams({
            page: pagina,
            estado: document.getElementById('selectEstado').value || '',
            ciudad: document.getElementById('selectCiudad').value || '',
            precio: document.getElementById('selectPrecio').value || '',
            buscar: document.getElementById('buscadorPropiedades').value || ''
        });
        window.location.href = `/admin/propiedades?${params}`;
    });
});
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'propiedades',       // LengthAwarePaginator
    'totalPropiedades',  // Int
    'alquiladas',        // Int
    'publicadas',        // Int
    'borradores'         // Int
)
```

**Cada propiedad:**
```php
{
    'id_propiedad' => 1,
    'titulo_propiedad' => 'Calle Mayor 14',
    'calle_propiedad' => 'Calle Mayor, 14',
    'ciudad_propiedad' => 'Madrid',
    'estado_propiedad' => 'publicada|alquilada|borrador|inactiva',
    'precio_propiedad' => 1200.00,
    'nombre_arrendador' => 'Ana Martínez',
    'inquilinos_actuales' => 2,
    'foto_propiedad' => 'storage/fotos/propiedad1.jpg'
}
```

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Propiedades** | `tbl_propiedad` | Listado completo |
| **Arrendador** | `tbl_usuario` (FK) | Propietario |
| **Ubicación** | Dirección + Ciudad | Dónde está |
| **Título** | titulo_propiedad | Nombre de la propiedad |
| **Precio** | precio_propiedad | Valor alquiler mes |
| **Estado** | estado_propiedad (activa\|inactiva\|mantenimiento) | Disponibilidad |
| **Fecha Creación** | creado_propiedad | Cuándo se registró |
| **Total Alquileres** | COUNT JOIN tbl_alquiler | Cuántos alquileres ha tenido |
| **Fotos** | COUNT JOIN tbl_foto | Cuántas imágenes |

---

## 🔌 Tablas Consultadas

```
tbl_propiedad (PRINCIPAL)
├─ id_propiedad
├─ id_arrendador_fk → tbl_usuario
├─ titulo_propiedad
├─ descripcion_propiedad
├─ calle_propiedad
├─ numero_propiedad
├─ piso_propiedad
├─ puerta_propiedad
├─ ciudad_propiedad
├─ codigo_postal_propiedad
├─ precio_propiedad
├─ dormitorios_propiedad
├─ banyos_propiedad
├─ estado_propiedad (activa|inactiva|mantenimiento)
├─ creado_propiedad
├─ actualizado_propiedad
└─ url_documento_propiedad (referencia a fotos)

tbl_usuario (ARRENDADOR)
├─ id_usuario
├─ nombre_usuario
├─ email_usuario
├─ telefono_usuario
└─ ciudad_usuario

tbl_alquiler (RELACIÓN - para contar activos)
├─ id_alquiler
├─ id_propiedad_fk → tbl_propiedad
├─ estado_alquiler (activo|finalizado|cancelado)
└─ ...

tbl_foto (RELACIÓN - para contar fotos)
├─ id_foto
├─ id_propiedad_fk → tbl_propiedad
├─ url_foto
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/propiedades`

```
GET /admin/propiedades?ciudad=Madrid&estado=activa&min_precio=500&max_precio=2000&q=apartamento
  ↓
Route::get('/propiedades', [PropiedadController::class, 'index'])
  ↓
PropiedadController::index(Request $request)
```

### 2️⃣ Controlador obtiene propiedades filtradas

```php
// app/Http/Controllers/Admin/PropiedadController.php

public function index(Request $request) {
    // PASO 1: Obtener filtros
    $ciudad = $request->input('ciudad');
    $arrendador = $request->input('arrendador');
    $estado = $request->input('estado');
    $minPrecio = $request->input('min_precio');
    $maxPrecio = $request->input('max_precio');
    $q = $request->input('q');  // búsqueda por título
    $pagina = (int) $request->input('page', 1);
    
    // PASO 2: Query base con JOINs
    $query = DB::table('tbl_propiedad')
        ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
        ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
        ->leftJoin('tbl_foto', 'tbl_foto.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
        ->select(
            'tbl_propiedad.*',
            'tbl_usuario.nombre_usuario as nombre_arrendador',
            'tbl_usuario.email_usuario as email_arrendador',
            'tbl_usuario.telefono_usuario as telefono_arrendador',
            DB::raw('COUNT(DISTINCT tbl_alquiler.id_alquiler) as total_alquileres'),
            DB::raw('COUNT(DISTINCT tbl_foto.id_foto) as total_fotos')
        )
        ->groupBy('tbl_propiedad.id_propiedad');
    
    // PASO 3: Aplicar filtros
    if ($ciudad) {
        $query->where('tbl_propiedad.ciudad_propiedad', 'LIKE', "%{$ciudad}%");
    }
    
    if ($arrendador) {
        $query->where('tbl_usuario.nombre_usuario', 'LIKE', "%{$arrendador}%");
    }
    
    if ($estado) {
        $query->where('tbl_propiedad.estado_propiedad', $estado);
    }
    
    if ($minPrecio) {
        $query->where('tbl_propiedad.precio_propiedad', '>=', (float) $minPrecio);
    }
    
    if ($maxPrecio) {
        $query->where('tbl_propiedad.precio_propiedad', '<=', (float) $maxPrecio);
    }
    
    if ($q) {
        $query->where('tbl_propiedad.titulo_propiedad', 'LIKE', "%{$q}%");
    }
    
    // PASO 4: Obtener contadores (antes de paginar)
    $totalPropiedades = (clone $query)->count();
    $activasMes = (clone $query)
        ->where('tbl_propiedad.estado_propiedad', 'activa')
        ->where('tbl_propiedad.creado_propiedad', '>=', now()->subMonth())
        ->count();
    
    // PASO 5: Paginar
    $propiedades = $query
        ->orderBy('tbl_propiedad.creado_propiedad', 'desc')
        ->paginate(12);  // 12 propiedades por página
    
    // PASO 6: Obtener lista arrendadores (para dropdown filter)
    $arrendadores = DB::table('tbl_usuario')
        ->whereIn('id_usuario', 
            DB::table('tbl_propiedad')->pluck('id_arrendador_fk')
        )
        ->select('id_usuario', 'nombre_usuario')
        ->orderBy('nombre_usuario')
        ->get();
    
    // PASO 7: Ciudades disponibles
    $ciudades = DB::table('tbl_propiedad')
        ->select('ciudad_propiedad')
        ->distinct()
        ->orderBy('ciudad_propiedad')
        ->pluck('ciudad_propiedad');
    
    return view('admin.propiedades', compact(
        'propiedades',
        'totalPropiedades',
        'activasMes',
        'arrendadores',
        'ciudades',
        'ciudad',
        'arrendador',
        'estado',
        'minPrecio',
        'maxPrecio',
        'q'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/propiedades.blade.php -->

<!-- Filtros -->
<div class="filtros-panel">
    <form method="GET" action="/admin/propiedades" class="filtros-form">
        <!-- Búsqueda por título -->
        <input type="text" name="q" placeholder="Buscar por título..." 
               value="{{ $q ?? '' }}" class="form-control">
        
        <!-- Filtro ciudad -->
        <select name="ciudad" class="form-control">
            <option value="">Todas las ciudades</option>
            @foreach($ciudades as $c)
                <option value="{{ $c }}" @selected($ciudad === $c)>{{ $c }}</option>
            @endforeach
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
        
        <!-- Filtro estado -->
        <select name="estado" class="form-control">
            <option value="">Todos los estados</option>
            <option value="activa" @selected($estado === 'activa')>Activa</option>
            <option value="inactiva" @selected($estado === 'inactiva')>Inactiva</option>
            <option value="mantenimiento" @selected($estado === 'mantenimiento')>Mantenimiento</option>
        </select>
        
        <!-- Filtro rango precio -->
        <input type="number" name="min_precio" placeholder="Precio mínimo" 
               value="{{ $minPrecio ?? '' }}" class="form-control">
        <input type="number" name="max_precio" placeholder="Precio máximo" 
               value="{{ $maxPrecio ?? '' }}" class="form-control">
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/propiedades" class="btn btn-secondary">Limpiar</a>
    </form>
</div>

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalPropiedades }}</span>
        <small>Total Propiedades</small>
    </div>
    <div class="stat-card">
        <span>{{ $activasMes }}</span>
        <small>Activas Este Mes</small>
    </div>
    <div class="stat-card">
        <span>€</span>
        <small>Ingresos Totales</small>
    </div>
</div>

<!-- Tabla Propiedades -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Título</th>
            <th>Arrendador</th>
            <th>Ubicación</th>
            <th>Precio/Mes</th>
            <th>Habitaciones</th>
            <th>Estado</th>
            <th>Alquileres</th>
            <th>Fotos</th>
            <th>Creado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($propiedades as $prop)
            <tr>
                <td>
                    <strong>{{ $prop->titulo_propiedad }}</strong>
                </td>
                <td>{{ $prop->nombre_arrendador }}</td>
                <td>
                    <small>
                        {{ $prop->calle_propiedad }} {{ $prop->numero_propiedad }},
                        @if($prop->piso_propiedad)
                            Piso {{ $prop->piso_propiedad }}
                            @if($prop->puerta_propiedad)
                                Puerta {{ $prop->puerta_propiedad }}
                            @endif
                        @endif
                        <br>
                        {{ $prop->ciudad_propiedad }} ({{ $prop->codigo_postal_propiedad }})
                    </small>
                </td>
                <td>{{ number_format($prop->precio_propiedad, 2, ',', '.') }} €</td>
                <td>{{ $prop->dormitorios_propiedad }}</td>
                <td>
                    <span class="badge bg-{{ $prop->estado_propiedad === 'activa' ? 'success' : ($prop->estado_propiedad === 'inactiva' ? 'secondary' : 'warning') }}">
                        {{ ucfirst($prop->estado_propiedad) }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info">{{ $prop->total_alquileres }}</span>
                </td>
                <td>
                    <span class="badge bg-secondary">{{ $prop->total_fotos }}</span>
                </td>
                <td>{{ $prop->creado_propiedad->format('d/m/Y') }}</td>
                <td>
                    <a href="/admin/propiedades/{{ $prop->id_propiedad }}/editar" class="btn btn-sm btn-primary">
                        Editar
                    </a>
                    <button onclick="eliminarPropiedad({{ $prop->id_propiedad }})" class="btn btn-sm btn-danger">
                        Eliminar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No hay propiedades que coincidan</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
{{ $propiedades->links() }}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Editar** | Abre formulario edición | GET `/admin/propiedades/{id}/editar` | Carga vista con formulario |
| **Eliminar** | Elimina propiedad | DELETE `/admin/propiedades/{id}` | DELETE donde id_propiedad = {id} |
| **Crear Nueva** | Abre formulario creación | GET `/admin/propiedades/crear` | Carga vista vacía |

---

## 📋 Filtros

| Filtro | Parámetro | Tipo | Efecto |
|--------|-----------|------|--------|
| **Búsqueda Título** | `q` | texto | WHERE titulo LIKE '%texto%' |
| **Ciudad** | `ciudad` | select | WHERE ciudad_propiedad LIKE '%ciudad%' |
| **Arrendador** | `arrendador` | select | WHERE nombre_usuario LIKE '%nombre%' |
| **Estado** | `estado` | select | WHERE estado_propiedad = valor |
| **Precio Mínimo** | `min_precio` | number | WHERE precio >= valor |
| **Precio Máximo** | `max_precio` | number | WHERE precio <= valor |

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'propiedades',         // Paginator[12]
    'totalPropiedades',    // int
    'activasMes',          // int
    'arrendadores',        // Collection de usuarios
    'ciudades',            // Collection de strings
    'ciudad',              // string filtro actual
    'arrendador',          // string filtro actual
    'estado',              // string filtro actual
    'minPrecio',           // int filtro actual
    'maxPrecio',           // int filtro actual
    'q'                    // string filtro actual
)
```

---

## 🔄 Flujo Resumido

```
Admin accede /admin/propiedades?ciudad=Madrid&estado=activa
            ↓
PropiedadController::index()
            ↓
1. Query base con JOINs (usuario, alquiler, foto)
2. GROUP BY para contar alquileres y fotos
3. Aplicar filtros (ciudad, estado, precio, búsqueda)
4. Obtener contadores (total, activas mes)
5. Paginar 12 propiedades por página
6. Obtener dropdown de arrendadores
7. Obtener dropdown de ciudades
            ↓
Blade renderiza tabla + filtros + KPIs
            ↓
Admin ve 12 propiedades con datos agregados
            ↓
Si clickea "Editar"
            ↓
GET /admin/propiedades/{id}/editar
            ↓
Carga vista con formulario pre-rellenado
```

---

## ⚠️ Puntos Importantes

1. **GROUP BY necesario:** Para contar alquileres y fotos correctamente
2. **JOINs múltiples:** Usuario (arrendador), alquileres, fotos
3. **Paginación:** 12 propiedades por página
4. **Filtros combinables:** Se aplican todos juntos (AND)
5. **Dropdowns dinámicos:** Arrendadores y ciudades desde DB
6. **Estados:** activa, inactiva, mantenimiento

---

## 🐛 Debugging

Ver propiedades activas:

```php
$activas = DB::table('tbl_propiedad')
    ->where('estado_propiedad', 'activa')
    ->count();
    
dd("Activas: $activas");
```

Ver propiedades sin alquileres:

```php
$sinAlquileres = DB::table('tbl_propiedad')
    ->leftJoin('tbl_alquiler', 'tbl_alquiler.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
    ->whereNull('tbl_alquiler.id_alquiler')
    ->count();
    
dd("Sin alquileres: $sinAlquileres");
```
