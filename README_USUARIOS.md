# SpotStay — Gestión de Usuarios 👥

## Resumen
Vista completa de gestión de usuarios para el panel administrativo de SpotStay, construida con **Laravel 13.4.0**, **Blade**, **Bootstrap 5.3.8** y **Vanilla JavaScript** (sin frameworks).

---

## 📁 Archivos Generados

| Archivo | Ruta | Propósito |
|---------|------|----------|
| **Vista Usuarios** | `resources/views/admin/usuarios.blade.php` | HTML con tabla, filtros y modal |
| **Estilos** | `public/css/admin/usuarios.css` | CSS completo (sin Bootstrap adicional) |
| **Scripts** | `public/js/admin/usuarios.js` | JavaScript vanilla para interactividad |
| **Documentación** | `README.md` | Este archivo |

---

## 🎨 Estructura de la Vista

### 1. **Hero Section**
- Título: "Gestión de usuarios"
- Subtítulo: "Administra los usuarios registrados en la plataforma"
- Fondo azul (#035498) con círculos decorativos

### 2. **Barra de Herramientas (Toolbar)**
- **Búsqueda**: Busca en nombre y email (con icono)
- **Filtro Rol**: Admin, Arrendador, Inquilino, Gestor, Miembro
- **Filtro Estado**: Activo, Inactivo
- **Botón Exportar**: Descarga datos en CSV/Excel
- **Botón Nuevo Usuario**: Abre formulario para crear usuario

### 3. **KPI Miniatura (4 tarjetas)**
- Total usuarios: 1.284
- Usuarios activos: 1.156
- Usuarios inactivos: 128
- Registrados este mes: 47

### 4. **Tabla de Usuarios**
- **Encabezado**: Contador de resultados + paginación
- **10 filas** con datos completos:
  - Avatar + nombre + email
  - Rol (badge de color)
  - Estado (Activo/Inactivo)
  - Número de propiedades
  - Fecha de registro
  - Acciones: Ver perfil, Editar, Toggle activo/inactivo

### 5. **Modal de Perfil de Usuario**
- Avatar grande
- Nombre, email, teléfono
- Grid de datos: registro, propiedades, último acceso, alquileres, suscripción
- Lista de propiedades del usuario
- Botones: Desactivar cuenta, Editar usuario

### 6. **Footer de Tabla**
- Contador: "Mostrando 1-10 de 1.284 usuarios"

---

## 🔄 Cómo Funcionan los Filtros

### Flujo Completo
1. User cambia valor en uno de los 3 filtros:
   - Select rol `.onchange`
   - Select estado `.onchange`
   - Input búsqueda `.onblur` o `.onkeyup` (si vacío)

2. Se ejecuta `filtrarUsuarios()`
   - Recoge valores de los 3 filtros
   - Construye URL con parámetros query string
   - Hace **fetch GET** a `/admin/usuarios/filtrar`

3. Server devuelve JSON:
```json
{
    "usuarios": [
        {
            "id": 1,
            "nombre": "Carlos García",
            "email": "carlos.garcia@email.com",
            "rol": "arrendador",
            "rolLabel": "Arrendador",
            "estado": "activo",
            "propiedades": 3,
            "fechaRegistro": "12 ene 2025",
            "avatarColor": "#B8CCE4",
            "avatarText": "CG"
        }
    ],
    "total": 1284
}
```

4. **DOM se actualiza** sin recargar página:
   - Limpia tbody
   - Crea filas nuevas con innerHTML
   - Actualiza contador de resultados
   - Reasigna eventos a los botones nuevos

### Código JavaScript
```javascript
var filtrarUsuarios = function() {
    var rol = document.getElementById('selectRol').value;
    var estado = document.getElementById('selectEstado').value;
    var busqueda = document.getElementById('buscadorUsuarios').value;
    
    var url = '/admin/usuarios/filtrar?rol=' + encodeURIComponent(rol) +
              '&estado=' + encodeURIComponent(estado) +
              '&q=' + encodeURIComponent(busqueda);
    
    fetch(url, { method: 'GET', headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(function(r) { return r.json(); })
        .then(function(data) { actualizarTabla(data); })
        .catch(function(error) { console.error(error); });
};
```

**Nota**: Se usa **fetch + .then()**, NUNCA async/await.

---

## 🔍 Buscador de Usuarios

### Funcionamiento
- **Filter local + remote**: Input `.onblur` busca en servidor
- **Filter local**: Input `.onkeyup` si length === 0 (limpia búsqueda)
- Busca en:
  - Nombre del usuario
  - Email del usuario

### Evento Asignado
```javascript
window.onload = function() {
    document.getElementById('buscadorUsuarios').onblur = function() {
        filtrarUsuarios();
    };
    
    document.getElementById('buscadorUsuarios').onkeyup = function() {
        if (this.value.length === 0) {
            filtrarUsuarios();
        }
    };
};
```

### Búsqueda en Servidor (GET)
```
/admin/usuarios/filtrar?q=carlos&rol=&estado=
```

---

## 🔘 Toggle Activar/Desactivar Usuario

### Flujo AJAX
1. User hace click en el toggle (círculo deslizable)
2. Se ejecuta `toggleEstado(id)`
3. **fetch POST** a `/admin/usuarios/{id}/toggle-estado`

### Request POST
```javascript
fetch('/admin/usuarios/1/toggle-estado', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    }
})
```

### Response Esperada
```json
{
    "success": true,
    "nuevoEstado": "inactivo"
}
```

### Actualización del DOM
Si `success === true`:
- Alterna clase `.activo` del toggle
- Actualiza `data-activo` del `<tr>`
- Cambia badge `badge-activo` ↔ `badge-inactivo`
- Alterna opacidad con clase `.fila-inactiva`

### Código JavaScript
```javascript
var toggleEstado = function(id) {
    fetch('/admin/usuarios/' + id + '/toggle-estado', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            var toggle = tr.querySelector('.toggle-switch');
            toggle.classList.toggle('activo');
            
            var nuevoActivo = tr.getAttribute('data-activo') === '1' ? '0' : '1';
            tr.setAttribute('data-activo', nuevoActivo);
            
            var badge = tr.querySelector('.badge-estado');
            if (nuevoActivo === '1') {
                badge.textContent = 'Activo';
                badge.className = 'badge-estado badge-activo';
            } else {
                badge.textContent = 'Inactivo';
                badge.className = 'badge-estado badge-inactivo';
            }
            
            tr.classList.toggle('fila-inactiva');
        }
    });
};
```

---

## 🎭 Modal de Perfil de Usuario

### Cómo Se Abre
1. User hace click en botón **"Ver perfil"** (icono ojo)
2. Se ejecuta `abrirModal(id)` con fetch
3. Los datos vienen del servidor o de hardcodeados (en dev)

### Flujo Modal
```javascript
var abrirModal = function(id) {
    // Obtener usuario (hardcodeado en dev)
    var usuario = usuariosData[id];
    
    // Rellenar campos del modal
    document.getElementById('modalAvatar').innerHTML = usuario.avatarText;
    document.getElementById('modalAvatar').style.background = usuario.avatar;
    document.getElementById('modalNombre').textContent = usuario.nombre;
    document.getElementById('modalEmail').textContent = usuario.email;
    // ... más campos ...
    
    // Mostrar modal
    document.getElementById('modalOverlay').classList.add('visible');
    document.getElementById('modalPerfil').classList.add('visible');
};
```

### Estructura del Modal
```
┌─────────────────────────────────┐
│ Perfil de usuario    [✕]        │
├─────────────────────────────────┤
│  [Avatar]  Carlos García        │
│            carlos.garcia@email   │
│            +34 612 345 678       │
│            [Arrendador]          │
│                                  │
│  Teléfono     +34 612 345 678   │
│  Registro     12 ene 2025        │
│  Propiedades  3                  │
│  ... más datos ...               │
│                                  │
│  PROPIEDADES DEL USUARIO         │
│  · Calle Mayor 14, Madrid        │
│  · Gran Vía 22, Barcelona        │
│  · Av. Diagonal 88, BCN          │
├─────────────────────────────────┤
│ [Desactivar]  [Editar usuario]  │
└─────────────────────────────────┘
```

### Cerrar Modal
- Click en botón X
- Click en overlay (fondo oscuro)
- Automáticamente después de desactivar/editar

---

## 📊 Paginación

### Elementos
- Botón "← Anterior"
- Números de página (1, 2, 3, ...)
- Botón "Siguiente →"

### Funcionamiento
```javascript
var cambiarPagina = function(numeroPagina) {
    paginaActual = numeroPagina;
    
    fetch('/admin/usuarios?pagina=' + paginaActual)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            actualizarTabla(data);
            actualizarPaginacion(data.paginaActual, data.totalPaginas);
            // Scroll al top
            document.getElementById('tablaUsuarios').scrollIntoView();
        });
};
```

---

## 🛣️ Rutas Necesarias en el Controlador

### 1. Ver Usuarios (GET)
```
GET /admin/usuarios
GET /admin/usuarios?pagina=2

Response:
{
    "usuarios": [...],
    "total": 1284,
    "paginaActual": 1,
    "totalPaginas": 129
}
```

### 2. Filtrar Usuarios (GET)
```
GET /admin/usuarios/filtrar?rol=arrendador&estado=activo&q=carlos

Response:
{
    "usuarios": [...],
    "total": 45
}
```

### 3. Toggle Estado (POST)
```
POST /admin/usuarios/{id}/toggle-estado

Response:
{
    "success": true,
    "nuevoEstado": "inactivo"
}
```

### 4. Exportar Usuarios (GET)
```
GET /admin/usuarios/exportar

Retorna: Archivo CSV o Excel descargado
```

---

## 📝 Ejemplo de Controller en Laravel

```php
<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminUsuariosController extends Controller
{
    public function index(Request $request)
    {
        $pagina = $request->input('pagina', 1);
        
        $usuarios = Usuario::paginate(10, ['*'], 'page', $pagina);
        
        return response()->json([
            'usuarios' => $usuarios->items(),
            'total' => $usuarios->total(),
            'paginaActual' => $pagina,
            'totalPaginas' => $usuarios->lastPage()
        ]);
    }
    
    public function filtrar(Request $request)
    {
        $query = Usuario::query();
        
        if ($request->has('rol') && $request->rol !== '') {
            $query->where('rol', $request->rol);
        }
        
        if ($request->has('estado') && $request->estado !== '') {
            $estado = $request->estado === 'activo' ? 1 : 0;
            $query->where('activo', $estado);
        }
        
        if ($request->has('q') && $request->q !== '') {
            $busqueda = $request->q;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%$busqueda%")
                  ->orWhere('email', 'like', "%$busqueda%");
            });
        }
        
        $usuarios = $query->get();
        
        return response()->json([
            'usuarios' => $usuarios,
            'total' => $usuarios->count()
        ]);
    }
    
    public function toggleEstado($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->activo = !$usuario->activo;
        $usuario->save();
        
        return response()->json([
            'success' => true,
            'nuevoEstado' => $usuario->activo ? 'activo' : 'inactivo'
        ]);
    }
    
    public function exportar()
    {
        $usuarios = Usuario::all();
        
        // Generar CSV
        $filename = 'usuarios_' . now()->format('Y-m-d_His') . '.csv';
        
        return response()->streamDownload(function() use ($usuarios) {
            echo "Nombre,Email,Rol,Estado,Propiedades,Registro\n";
            foreach ($usuarios as $u) {
                echo "{$u->nombre},{$u->email},{$u->rol}," .
                     ($u->activo ? 'Activo' : 'Inactivo') . ",0," .
                     $u->created_at->format('d M Y') . "\n";
            }
        }, $filename);
    }
}
```

---

## 🎨 Colores y Estilos

### Badges por Rol
| Rol | Color | Fondo |
|-----|-------|-------|
| Admin | #D97706 | #FEF3C7 |
| Arrendador | #035498 | #E8F0FB |
| Inquilino | #1AA068 | #E6F7F1 |
| Gestor | #7C3AED | #F3E8FF |
| Miembro | #475569 | #F1F5F9 |

### Badges por Estado
| Estado | Color | Fondo |
|--------|-------|-------|
| Activo | #065F46 | #D1FAE5 |
| Inactivo | #991B1B | #FEE2E2 |

---

## 🎯 Variables Globales JavaScript

```javascript
var csrfToken;          // Token CSRF
var paginaActual = 1;   // Página actual de paginación

var usuariosData = {    // Datos hardcodeados (dev)
    1: { nombre: '...', email: '...', ... },
    2: { ... },
    // ...
};
```

---

## 📱 Responsive Design

- **Desktop (1024px+)**: Layout completo, 4 columnas KPI
- **Tablet (768px-1024px)**: 2 columnas KPI, tabla reduce padding
- **Mobile (<768px)**: 1 columna, toolbar stacked, botones 100% width

---

## 🔒 Seguridad

- **CSRF Token**: Se obtiene de `meta[name=csrf-token]` en el header
- **Headers POST**: Incluyen `X-CSRF-TOKEN` en todas las solicitudes
- **Validación Server**: El controlador debe validar permisos admin
- **Sanitización**: Inputs usan `encodeURIComponent()` en URL

---

## 🐛 Debugging

### Console Logs
El JS incluye `console.error()` para errores en fetch:
```javascript
.catch(function(error) {
    console.error('Error en fetch filtrar:', error);
});
```

Abre DevTools (F12) para ver errores.

### Test de Filtros
```javascript
// En la consola:
document.getElementById('selectRol').value = 'arrendador';
filtrarUsuarios();
```

---

## ✨ Cómo Personalizar

### Cambiar los 10 Usuarios
En `public/js/admin/usuarios.js`, modifica `usuariosData`:
```javascript
var usuariosData = {
    1: { 
        nombre: 'Tu Nombre',
        email: 'tu@email.com',
        telefono: '+34 123 456 789',
        rol: 'arrendador',
        estado: 'Activo',
        // ... más campos
    }
};
```

### Cambiar Colores KPI
En `public/css/admin/usuarios.css`:
```css
.kpi-mini-azul {
    background: #E8F0FB;
    color: #035498;
}
```

### Agregar Más Columnas a la Tabla
En `resources/views/admin/usuarios.blade.php`:
1. Agregar `<th>` en el header
2. Agregar `<td>` en el tbody

---

## 🔌 APIs/Endpoints Esperados

```
GET    /admin/usuarios                    → Lista usuarios
GET    /admin/usuarios?pagina=2           → Paginación
GET    /admin/usuarios/filtrar            → Filtrados
POST   /admin/usuarios/{id}/toggle-estado → Toggle activo
GET    /admin/usuarios/exportar           → Descargar CSV
```

---

## 📚 Stack Tecnológico

| Tecnología | Versión | Uso |
|-----------|---------|-----|
| Laravel | 13.4.0 | Framework |
| PHP | 8.2+ | Server |
| Blade | 13.x | Templates |
| Bootstrap | 5.3.8 | CSS (CDN) |
| Bootstrap Icons | 1.11.3 | Iconos (CDN) |
| Vanilla JS | ES5 | Scripts |

---

## ✅ Características Implementadas

✅ Tabla de usuarios con 10 filas hardcodeadas
✅ Filtros por rol y estado (fetch con onchange)
✅ Buscador por nombre/email (fetch con onblur/onkeyup)
✅ Toggle activar/desactivar usuarios (AJAX POST)
✅ Modal de perfil con datos del usuario
✅ Paginación funcional
✅ KPI miniatures
✅ Responsive design
✅ Borrar/inactivas con estilos
✅ Exportar usuarios (placeholder)

---

**SpotStay Gestión de Usuarios** — Desarrollado con ❤️ siguiendo las reglas del proyecto.
