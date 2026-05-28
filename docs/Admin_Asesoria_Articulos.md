# ✍️ README: ADMIN ASESORÍA (ARTÍCULOS)

**Vista:** `resources/views/admin/asesoria-articulos.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AsesoriaController.php`  
**Ruta:** `GET /admin/asesoria/articulos` | `GET /admin/asesoria/{id}/articulos`

---

## 🎯 Propósito

Gestiona **artículos de asesoría y ayuda** (blog de asesoramiento). Permite:
- Ver todos los artículos
- Filtrar por categoría
- Crear nuevo artículo (con editor TinyMCE)
- Editar artículo
- Eliminar artículo
- Cambiar estado (publicado/borrador)
- Ver vistas del artículo

**Artículo** = Post de blog sobre temas de vivienda (ej: Cómo calcular rentabilidad)

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Categoría** | `#filtro-categoria` | select | Todas + activas |
| **Buscar Título** | `#filtro-busqueda` | input text | Por título |
| **Estado** | `#filtro-estado` | select | Todos, Activo, Inactivo |
| **Destacado** | `#filtro-destacado` | select | Todos, Solo destacados, No destacados |
| **Resultados/Página** | `#filtro-paginacion` | select | 10, 20, 50, Todos |

**JavaScript de Filtros:**
```javascript
// Buscador
document.getElementById('filtro-busqueda').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    document.querySelectorAll('#tabla-articulos-body tr').forEach(fila => {
        const titulo = fila.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        fila.style.display = titulo.includes(valor) ? '' : 'none';
    });
});

// Filtro categoría
document.getElementById('filtro-categoria').addEventListener('change', function(e) {
    const categoria = e.target.value;
    cargarArticulos({ categoria: categoria });
});

// Filtro estado
document.getElementById('filtro-estado').addEventListener('change', function(e) {
    cargarArticulos({ estado: e.target.value });
});

// Filtro destacado
document.getElementById('filtro-destacado').addEventListener('change', function(e) {
    cargarArticulos({ destacado: e.target.value });
});

// Limpiar filtros
document.getElementById('btn-limpiar-filtros').addEventListener('click', function() {
    document.getElementById('filtro-categoria').value = '';
    document.getElementById('filtro-busqueda').value = '';
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-destacado').value = '';
    document.getElementById('filtro-paginacion').value = '10';
    cargarArticulos();
});
```

---

## 🖊️ Editor TinyMCE

**Versión:** TinyMCE 5.10.9  
**Fuente:** `https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.9/tinymce.min.js`

**Inicialización en formulario:**
```javascript
// Dentro del modal o formulario
tinymce.init({
    selector: 'textarea.tinymce-editor',
    language: 'es',
    toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
    menubar: false,
    height: 400,
    plugins: ['link', 'image', 'lists', 'paste', 'code'],
    paste_as_text: true,
    setup: function(editor) {
        editor.on('change', function() {
            tinymce.triggerSave();
        });
    }
});
```

**Campos del formulario:**
```html
<form data-ajax-form-articulo="true" action="{{ route('admin.asesoria.articulos.crear') }}">
    @csrf
    
    <select name="categoria_id" required>
        <option>Selecciona categoría</option>
        @foreach($categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>
    
    <input type="text" name="titulo" maxlength="255" required placeholder="Título del artículo">
    
    <input type="text" name="slug" readonly placeholder="Se genera automáticamente">
    
    <textarea class="tinymce-editor" name="contenido" required></textarea>
    
    <input type="number" name="orden" min="0">
    
    <checkbox name="estado" value="1"> Publicado </checkbox>
    
    <checkbox name="destacado" value="1"> Destacado (FAQ) </checkbox>
    
    <input type="number" name="orden_destacado" min="0" placeholder="Orden en FAQ">
    
    <button type="submit">Crear artículo</button>
</form>
```

---

## 📐 Tabla Sorteable

**Columnas:**
- Categoría (sorteable)
- Orden
- Título (sorteable)
- Contenido (preview)
- Estado (sorteable)
- Destacado (sorteable)
- Orden Destacado
- Acciones

**Ordenamiento:**
```javascript
document.querySelectorAll('#tabla-articulos-admin .sortable').forEach(th => {
    th.addEventListener('click', function() {
        const columna = this.dataset.sort;
        const orden = this.classList.contains('sort-asc') ? 'desc' : 'asc';
        
        // Limpiar flechas
        document.querySelectorAll('#tabla-articulos-admin .sortable').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        
        this.classList.add(`sort-${orden}`);
        cargarArticulos({ sort: columna, order: orden });
    });
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 8 columnas visibles
- ✅ Filtros: Todos en toolbar
- ✅ Editor: 400px altura
- ✅ Modal: Tamaño grande

### 📱 **Mobile (< 768px)**
- ✅ Tabla: Scroll horizontal
- ✅ Filtros: Stack vertical
- ✅ Editor: 250px altura
- ✅ Modal: Full screen

```css
@media (max-width: 768px) {
    .tinymce-editor { 
        height: 250px !important; 
    }
    .tabla-admin { 
        font-size: 0.875rem; 
    }
    .filtros-categorias {
        flex-direction: column;
        gap: 0.5rem;
    }
}
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'articulos',    // LengthAwarePaginator
    'categorias'    // Collection de categorías
)
```

**Cada artículo:**
```php
{
    'id_articulo' => 1,
    'categoria_id' => 2,
    'nombre_categoria' => 'Obras y Reformas',
    'titulo_articulo' => 'Cómo realizar reformas legalmente',
    'slug_articulo' => 'como-realizar-reformas-legalmente',
    'contenido_articulo' => '<p>Contenido HTML...</p>',
    'orden_articulo' => 1,
    'estado_articulo' => true,
    'destacado_articulo' => true,
    'orden_destacado' => 1,
    'creado_articulo' => Carbon object
}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint |
|-------|---------|----------|
| **+ Nuevo Artículo** | Abre modal crear | — |
| **Editar** | Abre modal editar | — |
| **Eliminar** | Confirma y elimina | DELETE `/admin/asesoria/articulos/{id}` |

---

## ⚠️ Puntos Importantes

1. **TinyMCE real:** Editor de HTML funcional, no Markdown
2. **Destacado:** Artículos mostrados en sección FAQ pública
3. **Ordenamiento:** Se puede en categoría y en FAQ independientemente
4. **Tabla dinámica:** Cargada con JavaScript
5. **Slug auto:** Se genera del título automáticamente
6. **Filtros combinables:** Categoría + Estado + Destacado juntos
7. **Paginación variable:** Según selector "Resultados por página"

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Artículos** | `tbl_articulo_asesoria` | Listado completo |
| **Título** | titulo_articulo | Nombre artículo |
| **Categoría** | tbl_categoria_asesoria | A qué grupo pertenece |
| **Autor** | tbl_usuario | Quién lo escribió |
| **Contenido** | contenido_articulo (HTML) | Texto del artículo |
| **Estado** | estado_articulo (borrador\|publicado) | Visible públicamente |
| **Vistas** | vistas_articulo | Cuánta gente lo leyó |
| **Creado** | creado_articulo | Fecha creación |
| **Actualizado** | actualizado_articulo | Última edición |

---

## 🔌 Tablas Consultadas

```
tbl_articulo_asesoria (PRINCIPAL)
├─ id_articulo
├─ id_categoria_fk → tbl_categoria_asesoria
├─ id_autor_fk → tbl_usuario (admin que lo escribió)
├─ titulo_articulo
├─ slug_articulo (para URL)
├─ contenido_articulo (HTML de TinyMCE)
├─ estado_articulo (borrador|publicado)
├─ vistas_articulo (contador)
├─ creado_articulo
├─ actualizado_articulo
├─ publicado_articulo (fecha)
└─ ...

tbl_categoria_asesoria
├─ id_categoria
├─ nombre_categoria
└─ ...

tbl_usuario (AUTOR)
├─ id_usuario
├─ nombre_usuario
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/asesoria/articulos`

```
GET /admin/asesoria/articulos?categoria=1&estado=publicado
  ↓
Route::get('/asesoria/articulos', [AsesoriaController::class, 'articulos'])
  ↓
AsesoriaController::articulos(Request $request)
```

### 2️⃣ Controlador obtiene artículos

```php
// app/Http/Controllers/Admin/AsesoriaController.php

public function articulos(Request $request) {
    // PASO 1: Obtener filtros
    $categoriaId = $request->input('categoria');
    $estado = $request->input('estado');  // borrador, publicado
    $q = $request->input('q');  // búsqueda título
    
    // PASO 2: Query base
    $query = DB::table('tbl_articulo_asesoria')
        ->join('tbl_categoria_asesoria', 
               'tbl_categoria_asesoria.id_categoria', '=', 
               'tbl_articulo_asesoria.id_categoria_fk')
        ->join('tbl_usuario', 
               'tbl_usuario.id_usuario', '=', 
               'tbl_articulo_asesoria.id_autor_fk')
        ->select(
            'tbl_articulo_asesoria.*',
            'tbl_categoria_asesoria.nombre_categoria',
            'tbl_usuario.nombre_usuario as nombre_autor'
        );
    
    // PASO 3: Aplicar filtros
    if ($categoriaId) {
        $query->where('tbl_articulo_asesoria.id_categoria_fk', $categoriaId);
    }
    
    if ($estado) {
        $query->where('tbl_articulo_asesoria.estado_articulo', $estado);
    }
    
    if ($q) {
        $query->where('tbl_articulo_asesoria.titulo_articulo', 'LIKE', "%{$q}%");
    }
    
    // PASO 4: Obtener contadores
    $totalArticulos = (clone $query)->count();
    $publicados = (clone $query)
        ->where('tbl_articulo_asesoria.estado_articulo', 'publicado')
        ->count();
    $borradores = (clone $query)
        ->where('tbl_articulo_asesoria.estado_articulo', 'borrador')
        ->count();
    $totalVistas = (clone $query)
        ->sum('tbl_articulo_asesoria.vistas_articulo');
    
    // PASO 5: Paginar
    $articulos = $query
        ->orderBy('tbl_articulo_asesoria.creado_articulo', 'desc')
        ->paginate(10);
    
    // PASO 6: Obtener categorías para dropdown
    $categorias = DB::table('tbl_categoria_asesoria')
        ->select('id_categoria', 'nombre_categoria')
        ->where('activo_categoria', true)
        ->orderBy('nombre_categoria')
        ->get();
    
    return view('admin.asesoria-articulos', compact(
        'articulos',
        'totalArticulos',
        'publicados',
        'borradores',
        'totalVistas',
        'categorias',
        'categoriaId',
        'estado',
        'q'
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/asesoria-articulos.blade.php -->

<!-- KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <span>{{ $totalArticulos }}</span>
        <small>Artículos Totales</small>
    </div>
    <div class="stat-card">
        <span>{{ $publicados }}</span>
        <small>Publicados</small>
    </div>
    <div class="stat-card">
        <span>{{ $borradores }}</span>
        <small>Borradores</small>
    </div>
    <div class="stat-card">
        <span>{{ $totalVistas ?? 0 }}</span>
        <small>Vistas Totales</small>
    </div>
</div>

<!-- Botón Crear -->
<a href="/admin/asesoria/articulos/crear" class="btn btn-success mb-3">+ Nuevo Artículo</a>

<!-- Filtros -->
<form method="GET" action="/admin/asesoria/articulos" class="filtros-form mb-3">
    <input type="text" name="q" placeholder="Buscar artículo..." 
           value="{{ $q ?? '' }}" class="form-control">
    
    <select name="categoria" class="form-control">
        <option value="">Todas las categorías</option>
        @foreach($categorias as $cat)
            <option value="{{ $cat->id_categoria }}" @selected($categoriaId == $cat->id_categoria)>
                {{ $cat->nombre_categoria }}
            </option>
        @endforeach
    </select>
    
    <select name="estado" class="form-control">
        <option value="">Todos los estados</option>
        <option value="publicado" @selected($estado === 'publicado')>Publicado</option>
        <option value="borrador" @selected($estado === 'borrador')>Borrador</option>
    </select>
    
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="/admin/asesoria/articulos" class="btn btn-secondary">Limpiar</a>
</form>

<!-- Tabla Artículos -->
<table class="table table-hover">
    <thead>
        <tr>
            <th>Título</th>
            <th>Categoría</th>
            <th>Autor</th>
            <th>Estado</th>
            <th>Vistas</th>
            <th>Creado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($articulos as $art)
            <tr>
                <td>
                    <strong>{{ $art->titulo_articulo }}</strong>
                    <br>
                    <small class="text-muted">/articulos/{{ $art->slug_articulo }}</small>
                </td>
                <td>{{ $art->nombre_categoria }}</td>
                <td>{{ $art->nombre_autor }}</td>
                <td>
                    <span class="badge bg-{{ $art->estado_articulo === 'publicado' ? 'success' : 'warning' }}">
                        {{ ucfirst($art->estado_articulo) }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info">{{ $art->vistas_articulo ?? 0 }}</span>
                </td>
                <td>{{ $art->creado_articulo->format('d/m/Y') }}</td>
                <td>
                    <a href="/admin/asesoria/articulos/{{ $art->id_articulo }}/editar" 
                       class="btn btn-sm btn-primary">
                        Editar
                    </a>
                    <button onclick="eliminarArticulo({{ $art->id_articulo }})" 
                            class="btn btn-sm btn-danger">
                        Eliminar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No hay artículos</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $articulos->links() }}
```

---

## ✏️ Crear/Editar Artículo (Formulario con TinyMCE)

### Vista del Formulario

```blade
<!-- resources/views/admin/asesoria-articulos-crear.blade.php -->

<form method="POST" action="/admin/asesoria/articulos" class="articulo-form">
    @csrf
    
    <div class="form-group">
        <label>Título</label>
        <input type="text" name="titulo_articulo" value="{{ $articulo->titulo_articulo ?? '' }}" 
               class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Categoría</label>
        <select name="id_categoria_fk" class="form-control" required>
            <option value="">Selecciona categoría...</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id_categoria }}" 
                        @selected(($articulo->id_categoria_fk ?? null) == $cat->id_categoria)>
                    {{ $cat->nombre_categoria }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group">
        <label>Contenido (Editor HTML)</label>
        <textarea id="editor-contenido" name="contenido_articulo" class="form-control" 
                  required>{{ $articulo->contenido_articulo ?? '' }}</textarea>
    </div>
    
    <div class="form-group">
        <label>Estado</label>
        <select name="estado_articulo" class="form-control">
            <option value="borrador" @selected(($articulo->estado_articulo ?? 'borrador') === 'borrador')>
                Borrador
            </option>
            <option value="publicado" @selected(($articulo->estado_articulo ?? null) === 'publicado')>
                Publicado
            </option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-success">Guardar Artículo</button>
    <a href="/admin/asesoria/articulos" class="btn btn-secondary">Cancelar</a>
</form>

<!-- Inicializar TinyMCE -->
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor-contenido',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pagebreak permanentpen footnotes mergetags',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        language: 'es'
    });
</script>
```

### Controlador para Guardar

```php
public function guardarArticulo(Request $request, $id = null) {
    $request->validate([
        'titulo_articulo' => 'required|string|max:255',
        'id_categoria_fk' => 'required|exists:tbl_categoria_asesoria,id_categoria',
        'contenido_articulo' => 'required|string',
        'estado_articulo' => 'required|in:borrador,publicado'
    ]);
    
    // Generar slug desde el título
    $slug = Str::slug($request->input('titulo_articulo'));
    
    if ($id) {
        // Editar existente
        DB::table('tbl_articulo_asesoria')
            ->where('id_articulo', $id)
            ->update([
                'titulo_articulo' => $request->input('titulo_articulo'),
                'id_categoria_fk' => $request->input('id_categoria_fk'),
                'contenido_articulo' => $request->input('contenido_articulo'),
                'estado_articulo' => $request->input('estado_articulo'),
                'slug_articulo' => $slug,
                'actualizado_articulo' => now()
            ]);
    } else {
        // Crear nuevo
        DB::table('tbl_articulo_asesoria')->insert([
            'titulo_articulo' => $request->input('titulo_articulo'),
            'id_categoria_fk' => $request->input('id_categoria_fk'),
            'id_autor_fk' => auth()->id(),
            'contenido_articulo' => $request->input('contenido_articulo'),
            'estado_articulo' => $request->input('estado_articulo'),
            'slug_articulo' => $slug,
            'vistas_articulo' => 0,
            'creado_articulo' => now(),
            'actualizado_articulo' => now(),
            'publicado_articulo' => $request->input('estado_articulo') === 'publicado' ? now() : null
        ]);
    }
    
    return redirect('/admin/asesoria/articulos')
        ->with('success', 'Artículo guardado correctamente');
}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Nuevo Artículo** | Formulario | GET `/admin/asesoria/articulos/crear` | Carga vista |
| **Editar** | Editor | GET `/admin/asesoria/articulos/{id}/editar` | Carga con datos |
| **Eliminar** | Borra | DELETE `/admin/asesoria/articulos/{id}` | Confirma |
| **Guardar** | POST | POST `/admin/asesoria/articulos` | INSERT o UPDATE |

---

## 📋 Filtros

| Filtro | Parámetro | Efecto |
|--------|-----------|--------|
| **Categoría** | `categoria` | WHERE id_categoria_fk = valor |
| **Estado** | `estado` | WHERE estado_articulo = valor |
| **Búsqueda** | `q` | WHERE titulo LIKE '%texto%' |

---

## 🔌 Editor TinyMCE

**Características incluidas:**
- Formatos: negrita, cursiva, subrayado, tachado
- Listas: numeradas y con viñetas
- Tablas
- Imágenes (upload)
- Videos (embebidos)
- Código fuente (HTML)
- Links
- Emojis

**Configuración necesaria:**
1. Obtener API key de [TinyMCE Cloud](https://www.tiny.cloud/)
2. Incluir `<script>` con tu API key
3. Configurar idioma español

---

## ⚠️ Puntos Importantes

1. **TinyMCE:** Editor visual para HTML
2. **Slug:** Se genera automáticamente del título
3. **Vistas:** Se incrementan cuando alguien lo visualiza
4. **Borradores:** No visibles públicamente
5. **Autor:** Asignado automáticamente al usuario logueado
6. **Contenido HTML:** Se guarda como HTML limpio

---

## 🐛 Debugging

Ver artículos más visitados:

```php
$masVistos = DB::table('tbl_articulo_asesoria')
    ->where('estado_articulo', 'publicado')
    ->orderBy('vistas_articulo', 'desc')
    ->limit(10)
    ->get();
```

Ver artículos por categoría:

```php
$porCategoria = DB::table('tbl_articulo_asesoria')
    ->join('tbl_categoria_asesoria', ...)
    ->select('nombre_categoria', DB::raw('COUNT(*) as total'))
    ->groupBy('nombre_categoria')
    ->get();
```
