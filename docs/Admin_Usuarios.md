# 👥 README: ADMIN USUARIOS

**Vista:** `resources/views/admin/usuarios.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/UsuarioController.php`  
**Ruta:** `GET /admin/usuarios`

---

## 🎯 Propósito

Gestiona **todos los usuarios** de la plataforma. Permite:
- Ver listado con paginación
- Filtrar por rol (admin, arrendador, gestor, inquilino)
- Buscar por nombre/email
- Ver detalles de cada usuario
- Crear nuevos usuarios
- Editar usuarios
- Eliminar usuarios
- Cambiar rol a usuario

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Usuarios Listado** | `tbl_usuario` + `tbl_rol_usuario` + `tbl_rol` | Todos los usuarios con sus roles |
| **Filtro Rol** | `tbl_rol` | Dropdown con roles disponibles |
| **Búsqueda** | `tbl_usuario` | Por nombre o email |
| **Total Usuarios** | `COUNT(tbl_usuario)` | Cantidad total de usuarios |
| **Usuarios Activos** | WHERE activo_usuario = 1 | Para KPI |
| **Usuarios Este Mes** | WHERE creado_usuario >= DATE_SUB(NOW(), INTERVAL 1 MONTH) | Para KPI |

---

## 🎛️ Filtros y Búsqueda

| Filtro | ID HTML | Tipo | Opciones |
|--------|---------|------|----------|
| **Buscar** | `#buscadorUsuarios` | input text | Búsqueda en tiempo real |
| **Rol** | `#selectRol` | select | Admin, Arrendador, Inquilino, Gestor, Miembro |
| **Estado** | `#selectEstado` | select | Activo, Inactivo |

**JavaScript de Filtros:**
```javascript
// Buscador en tiempo real
document.getElementById('buscadorUsuarios').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyUsuarios tr').forEach(fila => {
        const nombre = fila.querySelector('.usuario-nombre')?.textContent.toLowerCase() || '';
        const email = fila.querySelector('.usuario-email')?.textContent.toLowerCase() || '';
        fila.style.display = 
            nombre.includes(valor) || email.includes(valor) ? '' : 'none';
    });
});

// Filtro por rol
document.getElementById('selectRol').addEventListener('change', function(e) {
    const rol = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyUsuarios tr').forEach(fila => {
        if (!rol) {
            fila.style.display = '';
        } else {
            const badge = fila.querySelector('.badge-rol');
            const rolFila = (badge?.textContent || '').toLowerCase();
            fila.style.display = rolFila.includes(rol) ? '' : 'none';
        }
    });
});

// Filtro por estado
document.getElementById('selectEstado').addEventListener('change', function(e) {
    const estado = e.target.value.toLowerCase();
    document.querySelectorAll('#tbodyUsuarios tr').forEach(fila => {
        if (!estado) {
            fila.style.display = '';
        } else {
            const badge = fila.querySelector('.badge-estado');
            const estadoFila = (badge?.textContent || '').toLowerCase();
            fila.style.display = estadoFila.includes(estado) ? '' : 'none';
        }
    });
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Tabla: 7 columnas visibles (Usuario, Rol, Suscripción, Estado, Propiedades, Fecha, Acciones)
- ✅ Filtros: Todos en toolbar izquierda
- ✅ KPIs: 4 columnas en fila
- ✅ Paginación: Numerada (← 1 2 3 4 →)

**Columnas mostradas:**
```
USUARIO | ROL | SUSCRIPCIÓN | ESTADO | PROPIEDADES | FECHA REGISTRO | ACCIONES
```

### 📱 **Mobile (< 768px)**
- ❌ PROPIEDADES: Oculta (clase `col-tablet-hide`)
- ❌ FECHA REGISTRO: Oculta (clase `col-tablet-hide`)  
- ❌ ESTADO: Oculta en móvil muy pequeño (clase `col-mobile-hide`)
- ✅ USUARIO, ROL, SUSCRIPCIÓN, ACCIONES: Siempre visibles

**Columnas mostradas en móvil:**
```
USUARIO | ROL | SUSCRIPCIÓN | ACCIONES
```

**Comportamiento en móvil:**
```html
<!-- Cada celda tiene data-label para mostrar nombre en móvil -->
<td data-label="USUARIO">
    <div class="usuario-celda">
        <img class="avatar-tabla" src="..." />
        <div>
            <p class="usuario-nombre">Juan García</p>
            <p class="usuario-email">juan@mail.com</p>
        </div>
    </div>
</td>

<!-- Columnas ocultas -->
<td data-label="PROPIEDADES" class="col-tablet-hide">3</td>
<td data-label="FECHA REGISTRO" class="col-tablet-hide">15 Jan 2024</td>
<td data-label="ESTADO" class="col-mobile-hide">
    <span class="badge-estado badge-activo">Activo</span>
</td>
```

**CSS responsive:**
```css
@media (max-width: 768px) {
    .col-tablet-hide { display: none; }  /* Oculta propiedades y fecha */
}

@media (max-width: 480px) {
    .col-mobile-hide { display: none; }  /* Oculta estado */
    
    /* En tabla móvil, cada fila ocupa más espacio */
    .tabla-admin td {
        padding: 1rem 0.5rem;
    }
}
```

---

## 🔄 Paginación

**Tipo:** Bootstrap Pagination (`pagination` + `pagination-sm`)  
**Items por página:** 15 usuarios

**HTML generado:**
```html
<nav aria-label="Paginación de usuarios">
    <ul class="pagination pagination-sm mb-0" id="paginas">
        <!-- Botón previo -->
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="1">←</button>
        </li>
        
        <!-- Números páginas -->
        <li class="page-item active">
            <button type="button" class="page-link" data-pagina="1">1</button>
        </li>
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="2">2</button>
        </li>
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="3">3</button>
        </li>
        
        <!-- Botón siguiente -->
        <li class="page-item">
            <button type="button" class="page-link" data-pagina="4">→</button>
        </li>
    </ul>
</nav>
```

**JavaScript de Paginación:**
```javascript
document.querySelectorAll('#paginas .page-link').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const pagina = this.dataset.pagina;
        
        // Construir query string con filtros actuales
        const params = new URLSearchParams({
            page: pagina,
            rol: document.getElementById('selectRol').value || '',
            estado: document.getElementById('selectEstado').value || '',
            buscar: document.getElementById('buscadorUsuarios').value || ''
        });
        
        // GET /admin/usuarios?page=2&rol=arrendador&...
        window.location.href = `/admin/usuarios?${params}`;
    });
});
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'usuarios',           // LengthAwarePaginator (15 items por página)
    'totalUsuarios',      // Int (para KPI)
    'activos',            // Int (para KPI)
    'inactivos',          // Int (para KPI)
    'esteMes'             // Int (para KPI: registrados este mes)
)
```

**Cada usuario en tabla incluye:**
```php
{
    'id_usuario' => 123,
    'nombre_usuario' => 'Juan García López',
    'email_usuario' => 'juan@example.com',
    'avatar_usuario' => 'storage/avatares/juan.jpg' o null,
    'nombre_rol' => 'Arrendador',
    'suscripcion_label' => 'Plan Pro (Vence 15 Nov)' o 'Sin suscripción',
    'activo_usuario' => true o false,
    'total_propiedades' => 3,
    'creado_usuario' => '2024-01-15 14:30:00'
}
```

**Mostrado en tabla:**
| Columna | Datos Mostrados |
|---------|-----------------|
| USUARIO | Avatar (imagen o iniciales en color) + Nombre + Email |
| ROL | Badge con color (azul para Admin, verde para Arrendador, etc) |
| SUSCRIPCIÓN | Label con plan (ej: "Plan Pro (Vence 15 Nov)") |
| ESTADO | Badge (Verde "Activo", Rojo "Inactivo") |
| PROPIEDADES | Número (ej: 3) o "—" si no tiene |
| FECHA REGISTRO | Formato: "15 Jan 2024" |
| ACCIONES | Ver + Editar + Toggle switch estado |

---

## 🔌 Tablas Consultadas

```
tbl_usuario
├─ id_usuario
├─ nombre_usuario
├─ email_usuario
├─ telefono_usuario
├─ documento_usuario
├─ tipo_documento_usuario
├─ ciudad_usuario
├─ estado_usuario (activo|inactivo|bloqueado)
├─ creado_usuario
├─ actualizado_usuario
└─ avatar_usuario (foto)

tbl_rol_usuario
├─ id_usuario_fk → tbl_usuario
├─ id_rol_fk → tbl_rol
└─ creado_rol_usuario

tbl_rol
├─ id_rol
├─ nombre_rol (admin|arrendador|gestor|inquilino)
└─ descripcion_rol

tbl_perfil_arrendador (relación)
├─ id_arrendador_fk → tbl_usuario
└─ ... datos específicos arrendador

tbl_perfil_gestor (relación)
├─ id_gestor_fk → tbl_usuario
└─ ... datos específicos gestor
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/usuarios`

```
GET /admin/usuarios?rol=arrendador&q=Juan&page=1
  ↓
Route::get('/usuarios', [UsuarioController::class, 'index'])
  ↓
UsuarioController::index(Request $request)
```

### 2️⃣ Controlador procesa filtros

```php
// app/Http/Controllers/Admin/UsuarioController.php

public function index(Request $request) {
    // PASO 1: Obtener parámetros de filtrado
    $rol = $request->input('rol', null);           // null = todos
    $busqueda = $request->input('q', '');          // string vacío
    $estado = $request->input('estado', null);     // activo|inactivo|bloqueado
    
    // PASO 2: Query base
    $queryBase = DB::table('tbl_usuario')
        ->leftJoin('tbl_rol_usuario', 'tbl_rol_usuario.id_usuario_fk', '=', 'tbl_usuario.id_usuario')
        ->leftJoin('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
        ->select(
            'tbl_usuario.id_usuario',
            'tbl_usuario.nombre_usuario',
            'tbl_usuario.email_usuario',
            'tbl_usuario.telefono_usuario',
            'tbl_usuario.ciudad_usuario',
            'tbl_usuario.estado_usuario',
            'tbl_usuario.creado_usuario',
            'tbl_rol.nombre_rol',
            'tbl_rol.id_rol'
        );
    
    // PASO 3: Aplicar filtros
    // Filtro por rol
    if ($rol && $rol !== '') {
        $queryBase = $queryBase->where('tbl_rol.nombre_rol', $rol);
    }
    
    // Filtro por búsqueda (nombre o email)
    if ($busqueda !== '') {
        $queryBase = $queryBase->where(function($q) use ($busqueda) {
            $q->where('tbl_usuario.nombre_usuario', 'LIKE', "%{$busqueda}%")
              ->orWhere('tbl_usuario.email_usuario', 'LIKE', "%{$busqueda}%");
        });
    }
    
    // Filtro por estado
    if ($estado && $estado !== '') {
        $queryBase = $queryBase->where('tbl_usuario.estado_usuario', $estado);
    }
    
    // PASO 4: Ordenar y paginar
    $usuarios = $queryBase
        ->orderBy('tbl_usuario.creado_usuario', 'desc')
        ->paginate(15);  // ← 15 usuarios por página
    
    // SQL resultante (ejemplo):
    // SELECT tbl_usuario.*, tbl_rol.nombre_rol FROM tbl_usuario
    // LEFT JOIN tbl_rol_usuario ON ...
    // LEFT JOIN tbl_rol ON ...
    // WHERE (nombre_usuario LIKE '%Juan%' OR email_usuario LIKE '%Juan%')
    // AND tbl_rol.nombre_rol = 'arrendador'
    // AND estado_usuario = 'activo'
    // ORDER BY creado_usuario DESC
    // LIMIT 15 OFFSET 0
    
    // PASO 5: Obtener roles disponibles (para dropdown filtro)
    $roles = DB::table('tbl_rol')
        ->select('nombre_rol', DB::raw('COUNT(tbl_rol_usuario.id_usuario_fk) as total'))
        ->leftJoin('tbl_rol_usuario', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
        ->groupBy('tbl_rol.id_rol', 'tbl_rol.nombre_rol')
        ->get();
    
    // PASO 6: Pasar a vista
    return view('admin.usuarios', compact(
        'usuarios',     // Paginator
        'roles',        // Collection
        'busqueda',     // string
        'rol',          // string|null
        'estado'        // string|null
    ));
}
```

### 3️⃣ Vista renderiza tabla

```blade
<!-- resources/views/admin/usuarios.blade.php -->

<form method="GET" action="/admin/usuarios">
    <!-- Filtro Rol -->
    <select name="rol">
        <option value="">Todos los roles</option>
        @foreach($roles as $r)
            <option value="{{ $r->nombre_rol }}" @selected($rol === $r->nombre_rol)>
                {{ $r->nombre_rol }} ({{ $r->total }})
            </option>
        @endforeach
    </select>
    
    <!-- Búsqueda -->
    <input type="text" name="q" placeholder="Buscar por nombre o email" value="{{ $busqueda }}">
    
    <!-- Filtro Estado -->
    <select name="estado">
        <option value="">Todos los estados</option>
        <option value="activo" @selected($estado === 'activo')>Activo</option>
        <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
        <option value="bloqueado" @selected($estado === 'bloqueado')>Bloqueado</option>
    </select>
    
    <button type="submit">Filtrar</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Teléfono</th>
            <th>Ciudad</th>
            <th>Estado</th>
            <th>Registrado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->nombre_usuario }}</td>
                <td>{{ $usuario->email_usuario }}</td>
                <td>
                    <span class="badge bg-primary">
                        {{ $usuario->nombre_rol ?? 'Sin rol' }}
                    </span>
                </td>
                <td>{{ $usuario->telefono_usuario ?? '-' }}</td>
                <td>{{ $usuario->ciudad_usuario ?? '-' }}</td>
                <td>
                    <span class="badge bg-{{ $usuario->estado_usuario === 'activo' ? 'success' : 'danger' }}">
                        {{ $usuario->estado_usuario }}
                    </span>
                </td>
                <td>{{ $usuario->creado_usuario->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="/admin/usuarios/{{ $usuario->id_usuario }}/editar" class="btn btn-sm btn-primary">
                        Editar
                    </a>
                    <button onclick="cambiarRol({{ $usuario->id_usuario }})" class="btn btn-sm btn-warning">
                        Cambiar Rol
                    </button>
                    <button onclick="eliminarUsuario({{ $usuario->id_usuario }})" class="btn btn-sm btn-danger">
                        Eliminar
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No hay usuarios</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
{{ $usuarios->links() }}
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Crear Usuario** | Abre formulario crear | GET `/admin/usuarios/crear` | View form |
| **Editar** | Edita un usuario | GET `/admin/usuarios/{id}/editar` | View form |
| **Guardar** (form) | Guarda cambios | POST `/admin/usuarios/{id}` | UPDATE BD |
| **Cambiar Rol** | Modal para cambiar rol | POST `/admin/usuarios/{id}/cambiar-rol` | UPDATE rol |
| **Eliminar** | Elimina usuario (cascada) | DELETE `/admin/usuarios/{id}` | DELETE BD |
| **Filtrar** | Aplica filtros | GET `/admin/usuarios?rol=...&q=...` | Reloads con params |

### Ejemplo: Cambiar Rol

```javascript
function cambiarRol(usuarioId) {
    // Modal con selector de roles
    const nuevoRol = prompt('Nuevo rol (admin|arrendador|gestor|inquilino):');
    
    if (!nuevoRol) return;
    
    fetch(`/admin/usuarios/${usuarioId}/cambiar-rol`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ rol: nuevoRol })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert('Rol actualizado');
            location.reload();
        }
    });
}
```

**Backend:**
```php
public function cambiarRol(Request $request, $usuarioId) {
    $rol = $request->input('rol');
    
    // 1. Validar rol existe
    $idRol = DB::table('tbl_rol')
        ->where('nombre_rol', $rol)
        ->value('id_rol');
    
    if (!$idRol) {
        return response()->json(['ok' => false, 'message' => 'Rol inválido']);
    }
    
    // 2. Eliminar rol anterior
    DB::table('tbl_rol_usuario')
        ->where('id_usuario_fk', $usuarioId)
        ->delete();
    
    // 3. Insertar nuevo rol
    DB::table('tbl_rol_usuario')->insert([
        'id_usuario_fk' => $usuarioId,
        'id_rol_fk' => $idRol,
        'creado_rol_usuario' => now()
    ]);
    
    return response()->json(['ok' => true]);
}
```

---

## 📋 Filtros

### Filtro 1: Por Rol
- **Parámetro:** `rol=arrendador` (en URL)
- **Efecto:** WHERE tbl_rol.nombre_rol = 'arrendador'
- **Valores:** admin, arrendador, gestor, inquilino

### Filtro 2: Por Búsqueda
- **Parámetro:** `q=Juan`
- **Efecto:** WHERE nombre_usuario LIKE '%Juan%' OR email_usuario LIKE '%Juan%'
- **Busca en:** nombre y email

### Filtro 3: Por Estado
- **Parámetro:** `estado=activo`
- **Efecto:** WHERE estado_usuario = 'activo'
- **Valores:** activo, inactivo, bloqueado

### Paginación
- **Por defecto:** 15 usuarios por página
- **URL:** `?page=2` para ir a página 2
- **Total:** Mostrado en paginador

---

## 📊 Datos Pasados a la Vista (compact)

```php
compact(
    'usuarios',     // Paginator[15] — usuario actual + otros
    'roles',        // Collection[4] — {nombre_rol, total}
    'busqueda',     // string — "Juan"
    'rol',          // string|null — "arrendador"
    'estado'        // string|null — "activo"
)
```

---

## 🔄 Flujo Resumido

```
Usuario accede /admin/usuarios
            ↓
GET /admin/usuarios?rol=arrendador&q=Juan&estado=activo
            ↓
UsuarioController::index()
            ↓
1. Query base: SELECT * FROM tbl_usuario
2. LEFT JOIN tbl_rol_usuario
3. LEFT JOIN tbl_rol
4. WHERE rol = 'arrendador'
5. WHERE nombre LIKE '%Juan%' OR email LIKE '%Juan%'
6. WHERE estado = 'activo'
7. ORDER BY creado DESC
8. PAGINATE 15
            ↓
Obtiene roles para dropdown
            ↓
Blade renderiza tabla + filtros
            ↓
Usuario ve 15 usuarios filtrados
            ↓
Si clickea "Editar"
            ↓
GET /admin/usuarios/{id}/editar
            ↓
View form con datos del usuario
            ↓
Si envía form
            ↓
POST /admin/usuarios/{id}
            ↓
UPDATE tbl_usuario SET ...
            ↓
Redirect /admin/usuarios (con mensaje éxito)
```

---

## ⚠️ Puntos Importantes

1. **Transacción en cambiar rol:** DELETE viejo + INSERT nuevo (no usa transacción, riesgo si falla entre medio)
2. **LEFT JOIN en roles:** Muestra usuarios sin rol también
3. **Paginación:** 15 por página, siempre
4. **Búsqueda:** LIKE (case-insensitive en MySQL)
5. **Eliminación:** Cascada FK borra también sus relaciones en otras tablas
6. **Filtros combinables:** rol + búsqueda + estado actúan juntos

---

## 🐛 Debugging

Ver usuarios y sus roles:

```php
$usuarios = DB::table('tbl_usuario')
    ->leftJoin('tbl_rol_usuario', 'tbl_rol_usuario.id_usuario_fk', '=', 'tbl_usuario.id_usuario')
    ->leftJoin('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
    ->select('tbl_usuario.*', 'tbl_rol.nombre_rol')
    ->get();

dd($usuarios);
```

Ver resultados de búsqueda:

```php
$busqueda = 'Juan';
$usuarios = DB::table('tbl_usuario')
    ->where('nombre_usuario', 'LIKE', "%{$busqueda}%")
    ->orWhere('email_usuario', 'LIKE', "%{$busqueda}%")
    ->get();

dd($usuarios);
```
