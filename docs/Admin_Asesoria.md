# 📚 README: ADMIN ASESORÍA (CATEGORÍAS)

**Vista:** `resources/views/admin/asesoria.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AsesoriaController.php`  
**Ruta:** `GET /admin/asesoria`

---

## 🎯 Propósito

Gestiona **categorías de artículos de asesoría**. Permite:
- Ver categorías de temas de asesoría
- Crear nueva categoría
- Editar categoría
- Eliminar categoría (si no tiene artículos)
- Ver cantidad de artículos por categoría

**Categoría** = Grupo temático de artículos (Fiscalidad, Mantenimiento, Arrendamiento, etc)

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar Nombre** | `#filtro-busqueda` | input text | Por nombre de categoría |
| **Estado** | `#filtro-estado` | select | Todos, Activo (1), Inactivo (0) |
| **Resultados/Página** | `#filtro-paginacion` | select | 10, 20, 50, Todos (0) |

**Tabla Sorteable:**
Encabezados con clase `.sortable` permiten ordenar por:
- Orden
- Nombre
- Slug
- Artículos
- Estado

**JavaScript de Filtros:**
```javascript
// Buscador en tiempo real
document.getElementById('filtro-busqueda').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-categorias-body tr');
    filas.forEach(fila => {
        const nombre = fila.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        fila.style.display = nombre.includes(valor) ? '' : 'none';
    });
});

// Filtro estado
document.getElementById('filtro-estado').addEventListener('change', function(e) {
    const estado = e.target.value;
    cargarCategorias({ estado: estado });
});

// Filtro paginación
document.getElementById('filtro-paginacion').addEventListener('change', function(e) {
    const porPagina = e.target.value;
    cargarCategorias({ perPage: porPagina });
});

// Limpiar filtros
document.getElementById('btn-limpiar-filtros').addEventListener('click', function() {
    document.getElementById('filtro-busqueda').value = '';
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-paginacion').value = '10';
    cargarCategorias();
});

// Ordenar tabla al hacer click en encabezado
document.querySelectorAll('.tabla-admin .sortable').forEach(th => {
    th.addEventListener('click', function() {
        const columna = this.dataset.sort;
        const orden = this.classList.contains('sort-asc') ? 'desc' : 'asc';
        cargarCategorias({ sort: columna, order: orden });
    });
});
```

---

## 📐 Modal Nueva Categoría

**Campos del formulario:**
```html
<form data-ajax-form="true" action="{{ route('admin.asesoria.categoria.crear') }}">
    <input type="text" name="nombre" maxlength="255" required placeholder="Ej: Obras y reformas">
    <input type="text" name="slug" readonly placeholder="Se genera automáticamente">
    <select name="icono">
        <!-- Opciones de iconos disponibles -->
    </select>
    <input type="number" name="orden" min="0">
    <textarea name="descripcion" rows="3"></textarea>
    <checkbox name="estado"> Activo </checkbox>
    <button type="submit">Crear categoría</button>
</form>
```

**JavaScript de Modal:**
```javascript
function abrirModalNuevaCategoria() {
    document.getElementById('modal-nueva-categoria').style.display = 'flex';
}

function cerrarModalNuevaCategoria() {
    document.getElementById('modal-nueva-categoria').style.display = 'none';
}

// Auto-generar slug del nombre
document.querySelector('input[name="nombre"]').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    document.querySelector('input[name="slug"]').value = slug;
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 7 columnas (Orden, Nombre, Enlace, Artículos, Icono, Estado, Acciones)
- ✅ Filtros toolbar en una línea
- ✅ Sorting en encabezados
- ✅ Modal con contenido centrado

### 📱 **Mobile (< 768px)**
- ✅ Tabla: Scroll horizontal si es necesario
- ✅ Filtros: Stack vertical (full width)
- ✅ Modal: Full screen con margin
- ✅ Acciones: Dropdown o botones reducidos

**Tabla dinámica (usa DIVs):**
```html
<!-- En móvil, cada categoría es una fila flexible -->
<div class="tabla-row">
    <div data-label="Nombre">Obras y Reformas</div>
    <div data-label="Slug">obras-y-reformas</div>
    <div data-label="Artículos">5</div>
    <div data-label="Acciones">
        <button class="btn-editar">Editar</button>
        <button class="btn-eliminar">Eliminar</button>
    </div>
</div>
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'categorias'  // Collection de categorías con paginación
)
```

**Cada categoría:**
```php
{
    'id_categoria' => 1,
    'nombre_categoria' => 'Obras y Reformas',
    'slug_categoria' => 'obras-y-reformas',
    'descripcion_categoria' => 'Información sobre....',
    'icono_categoria' => 'bi-tools',
    'orden_categoria' => 1,
    'estado_categoria' => true,
    'total_articulos' => 5,
    'creado_categoria' => Carbon object
}
```

---

## 🔘 Botones y Acciones

| Botón | ID | Función | Endpoint |
|-------|-----|---------|----------|
| **+ Nueva Categoría** | (onclick) | Abre modal crear | — |
| **Editar** (fila) | .btn-editar | Abre modal editar | — |
| **Eliminar** (fila) | .btn-eliminar | Elimina categoría | DELETE `/admin/asesoria/categoria/{id}` |

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Categorías** | `tbl_categoria_asesoria` | Listado completo |
| **Nombre** | nombre_categoria | Nombre del tema |
| **Descripción** | descripcion_categoria | Breve resumen |
| **Icono** | icono_categoria | Emoji o clase CSS |
| **Total Artículos** | COUNT tbl_articulo_asesoria | Cuántos artículos incluye |
| **Activo** | activo_categoria | Si se muestra públicamente |

---

## 🔌 Tablas Consultadas

```
tbl_categoria_asesoria (PRINCIPAL)
├─ id_categoria
├─ nombre_categoria (Fiscalidad, Mantenimiento, etc)
├─ descripcion_categoria
├─ icono_categoria (📊, 🔧, etc)
├─ orden_categoria (1, 2, 3... para ordenar)
├─ activo_categoria (true|false)
├─ creado_categoria
├─ actualizado_categoria
└─ ...

tbl_articulo_asesoria (RELACIÓN)
├─ id_articulo
├─ id_categoria_fk → tbl_categoria_asesoria
├─ titulo_articulo
├─ contenido_articulo (HTML de TinyMCE)
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/asesoria`

```
GET /admin/asesoria
  ↓
Route::get('/asesoria', [AsesoriaController::class, 'index'])
  ↓
AsesoriaController::index()
```

### 2️⃣ Controlador obtiene categorías

```php
// app/Http/Controllers/Admin/AsesoriaController.php

public function index() {
    // Query con conteo de artículos
    $categorias = DB::table('tbl_categoria_asesoria')
        ->leftJoin('tbl_articulo_asesoria', 
                   'tbl_articulo_asesoria.id_categoria_fk', '=', 
                   'tbl_categoria_asesoria.id_categoria')
        ->select(
            'tbl_categoria_asesoria.*',
            DB::raw('COUNT(tbl_articulo_asesoria.id_articulo) as total_articulos')
        )
        ->groupBy('tbl_categoria_asesoria.id_categoria')
        ->orderBy('tbl_categoria_asesoria.orden_categoria', 'asc')
        ->get();
    
    // Estadísticas
    $totalCategorias = $categorias->count();
    $categoriasActivas = $categorias->where('activo_categoria', true)->count();
    $totalArticulos = DB::table('tbl_articulo_asesoria')->count();
    
    return view('admin.asesoria', compact(
        'categorias',
        'totalCategorias',
        'categoriasActivas',
        'totalArticulos'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/asesoria.blade.php -->

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalCategorias }}</span>
        <small>Categorías Totales</small>
    </div>
    <div class="stat-card">
        <span>{{ $categoriasActivas }}</span>
        <small>Categorías Activas</small>
    </div>
    <div class="stat-card">
        <span>{{ $totalArticulos }}</span>
        <small>Artículos Totales</small>
    </div>
</div>

<!-- Botón Crear -->
<a href="/admin/asesoria/crear" class="btn btn-success mb-3">+ Crear Categoría</a>

<!-- Tabla Categorías -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Icono</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Artículos</th>
            <th>Orden</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($categorias as $cat)
            <tr>
                <td>
                    <span style="font-size: 24px;">{{ $cat->icono_categoria }}</span>
                </td>
                <td><strong>{{ $cat->nombre_categoria }}</strong></td>
                <td>{{ $cat->descripcion_categoria }}</td>
                <td>
                    <a href="/admin/asesoria/{{ $cat->id_categoria }}/articulos" class="badge bg-info">
                        {{ $cat->total_articulos }}
                    </a>
                </td>
                <td>{{ $cat->orden_categoria }}</td>
                <td>
                    <span class="badge bg-{{ $cat->activo_categoria ? 'success' : 'secondary' }}">
                        {{ $cat->activo_categoria ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <a href="/admin/asesoria/{{ $cat->id_categoria }}/editar" class="btn btn-sm btn-primary">
                        Editar
                    </a>
                    @if($cat->total_articulos == 0)
                        <button onclick="eliminarCategoria({{ $cat->id_categoria }})" class="btn btn-sm btn-danger">
                            Eliminar
                        </button>
                    @else
                        <button disabled class="btn btn-sm btn-danger" title="Tiene {{ $cat->total_articulos }} artículos">
                            Eliminar
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay categorías</td>
            </tr>
        @endforelse
    </tbody>
</table>
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Crear Categoría** | Abre formulario | GET `/admin/asesoria/crear` | Carga vista |
| **Editar** | Abre editor | GET `/admin/asesoria/{id}/editar` | Carga con datos |
| **Ver Artículos** | Lista artículos | GET `/admin/asesoria/{id}/articulos` | Otra vista |
| **Eliminar** | Borra categoría | DELETE `/admin/asesoria/{id}` | Solo sin artículos |

---

## ⚠️ Puntos Importantes

1. **No se puede eliminar:** Si tiene artículos asociados
2. **Orden:** Define cómo aparecen en menú público
3. **Icono:** Emoji (📊, 🔧, 🏠, etc) o clase Font Awesome
4. **Activo:** Solo visible si está activo
5. **Artículos:** Vinculados por FK a categoría

---

## 🔄 Flujo de Creación

```
Admin accede /admin/asesoria/crear
            ↓
GET /admin/asesoria/crear
            ↓
Carga formulario (nombre, descripción, icono, orden)
            ↓
Admin rellena y envía
            ↓
POST /admin/asesoria
            ↓
INSERT en tbl_categoria_asesoria
            ↓
Redirect a /admin/asesoria
```
