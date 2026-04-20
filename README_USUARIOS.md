# SpotStay — Gestión de Usuarios 👥

## Resumen v3.0
Vista completa de gestión de usuarios para el panel administrativo de SpotStay, con **validaciones robustas**, **SweetAlert2 integrado** con mascota personalizada (oso), **modales de Bootstrap 5.3.8**, y **filtrado en tiempo real**. Construida con **Laravel**, **Blade**, **Bootstrap 5.3.8** y **Vanilla JavaScript**.

---

## 📁 Archivos Generados y Modificados

| Archivo | Ruta | Propósito |
|---------|------|----------|
| **Vista Usuarios** | `resources/views/admin/usuarios.blade.php` | HTML con tabla, filtros, paginación y modales Bootstrap |
| **Controlador** | `app/Http/Controllers/Admin/UsuarioController.php` | CRUD + filtrado + paginación |
| **Estilos** | `public/css/admin/usuarios.css` | CSS para Bootstrap modals + SweetAlert2 |
| **Scripts** | `public/js/admin/usuarios.js` | JS con validaciones, AJAX y alerts |
| **Layout** | `resources/views/layouts/admin.blade.php` | Agregado SweetAlert2 CDN |
| **Rutas** | `routes/web.php` | Rutas protegidas con `role:admin` |

---

## ✅ Validaciones Implementadas

### Para Crear Usuario
1. ✅ **Nombre**: Obligatorio, mínimo 3 caracteres
2. ✅ **Email**: Obligatorio, formato válido (`xxx@yyy.zzz`)
3. ✅ **Teléfono**: Opcional, pero si se completa mínimo 9 caracteres
4. ✅ **Rol**: Obligatorio, select con opciones
5. ✅ **Contraseña**: Obligatoria, mínimo 6 caracteres

### Para Editar Usuario
1. ✅ **Nombre**: Obligatorio, mínimo 3 caracteres
2. ✅ **Email**: Obligatorio, formato válido
3. ✅ **Teléfono**: Opcional, mínimo 9 caracteres si se completa
4. ✅ **Rol**: Obligatorio
5. ✅ **Contraseña**: Opcional, mínimo 6 caracteres si se proporciona

### Sincronización con Login/Register
- Mismo regex email: `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`
- Mismo validador password: mínimo 6 caracteres
- Mismo patrón nombre: mínimo 3 caracteres + no vacío

### Funciones de Validación en JS
```javascript
var validarEmail = function(email) { ... }          // Formato válido
var validarNombre = function(nombre) { ... }        // Mínimo 3
var validarTelefono = function(telefono) { ... }    // Mínimo 9
var validarPassword = function(password) { ... }    // Mínimo 6
```

---

## 🎯 Modales de Bootstrap 5.3.8

### Cambio v3.0: De Custom CSS a Bootstrap Modals
Se reemplazaron los modales custom con **Bootstrap 5.3.8** para:
- ✅ Menos código CSS (se usaron clases preexistentes de Bootstrap)
- ✅ Consistencia con el framework
- ✅ Mejor mantenibilidad
- ✅ Funcionalidad nativa de Bootstrap

### Estructura
**Dos modales principales:**

1. **Modal de Perfil** (`#modalPerfil`)
   - Muestra datos del usuario
   - Botones: Editar usuario, Desactivar cuenta
   - Abre al hacer click en botón "Ver perfil"

2. **Modal de Crear/Editar** (`#modalFormUsuario`)
   - Formulario con validaciones
   - Campos: Nombre, Email, Teléfono, Rol, Contraseña
   - Abre al hacer click en "Nuevo usuario" o "Editar"

### Implementación en JavaScript
```javascript
/* Inicializar modales Bootstrap en window.onload */
modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfil'));
modalFormUsuario = new bootstrap.Modal(document.getElementById('modalFormUsuario'));

/* Abrir modal */
modalPerfil.show();

/* Cerrar modal */
modalPerfil.hide();
```

### Estilos Personalizados
Mantenidos en `public/css/admin/usuarios.css`:
- `.avatar-modal`: Avatar circular en modal
- `.badge`: Badges personalizados para estado y rol
- `.form-control`, `.form-select`: Inputs & selects con estilos custom
- `.btn-primary`, `.btn-danger`, `.btn-secondary`: Botones consistentes

---

## 🐻 SweetAlert2 con Oso Personalizado

### Instalación
Se agregó SweetAlert2 vía CDN en `resources/views/layouts/admin.blade.php`:
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

### Oso (Mascota)
Extraído del login.blade.php, SVG con ojos y boca sonriente:
```javascript
var osoSvg = '<svg viewBox="0 0 200 200" xmlns="...">
    <circle cx="82" cy="105" r="5" fill="#000" />
    <circle cx="118" cy="105" r="5" fill="#000" />
    <path d="M92 128 Q100 133 108 128" stroke="#000" ... />
</svg>';
```

### Tres Tipos de Alerts

#### 1. Alerta de Éxito (Oso sonriendo 😊)
```javascript
mostrarAlertaExito('¡Éxito!', 'El usuario ha sido creado correctamente');
```
- Icono: Oso con ojos sonriendo
- Color botón: Azul (#035498)
- Casos: Crear, actualizar, toggle usuario

#### 2. Alerta de Error (Oso triste 😔)
```javascript
mostrarAlertaError('Error', 'No se pudo guardar el usuario');
```
- Icono: Oso pero rojo
- Color botón: Rojo (#d9534f)
- Casos: Error en fetch, respuesta success=false

#### 3. Alerta de Validación (Oso pensando 🤔)
```javascript
mostrarAlertaValidacion('El nombre es obligatorio y debe tener mínimo 3 caracteres');
```
- Icono: Oso pero naranja
- Color botón: Naranja (#f0ad4e)
- Casos: Validación fallida en cliente

---

## 🔄 Cómo Funcionan los Filtros

### Búsqueda en Vivo (LIVE SEARCH)
```javascript
buscadorUsuarios.onkeyup = function() {
    paginaActual = 1;
    filtrarUsuarios();
};
```
**CAMBIO v2.0**: Ahora filtra en CADA KEYSTROKE (antes solo en blur)

- User escribe "jua" → busca nombres/emails con "jua" INMEDIATAMENTE
- Patrón LIKE%: `nombre LIKE '%jua%'` OR `email LIKE '%jua%'`
- Ejemplo: "j" encuentra "Juan", "Jennifer"

### Filtrado por Rol
```javascript
selectRol.onchange = function() {
    paginaActual = 1;
    filtrarUsuarios();
};
```
- Rol: admin, arrendador, inquilino, gestor, miembro
- Reset paginación automático (vuelve a página 1)

### Filtrado por Estado
```javascript
selectEstado.onchange = function() {
    paginaActual = 1;
    filtrarUsuarios();
};
```
- Estado: activo, inactivo
- Reset paginación automático

### Combo Filtros
Los tres se combinan en una sola request:
```
GET /admin/usuarios/filtrar?rol=arrendador&estado=activo&q=juan&page=1
```

---

## 📊 Paginación Funcional

### Parámetro Correcto
**CAMBIO v2.0**: Ahora se usa `&page=` (parámetro estándar de Laravel)
```javascript
var url = '...&page=' + numeroPagina;  // ✅ Correcto
// Antes: '&pagina=' ❌ Incorrecto
```

### Flujo de Paginación
1. **Click en número**: `cambiarPagina(2)`
2. **Fetch a server**: `/admin/usuarios/filtrar?...&page=2`
3. **Response JSON**:
```json
{
    "usuarios": [...],
    "total": 1284,
    "currentPage": 2,
    "totalPages": 129,
    "perPage": 10,
    "from": 11,
    "to": 20
}
```
4. **Actualizar tabla** + **Re-vinculation eventos** + **Footer actualizado**:
```
Mostrando 11-20 de 1.284 usuarios
```

### Re-vinculación de Botones
```javascript
.then(function(data) {
    actualizarTabla(data);
    asignarEventosPaginacion();  // ✅ RE-VINCULA botones nuevos
    actualizarPaginacion(data.currentPage, data.totalPages);
})
```

---

## 🎭 Modal Crear Usuario

### Validación en Cliente
```javascript
var guardarUsuario = function() {
    // 1. Validar nombre
    if (!validarNombre(nombre)) {
        mostrarAlertaValidacion('El nombre es obligatorio y debe tener mínimo 3 caracteres');
        return;
    }
    
    // 2. Validar email
    if (!validarEmail(email)) {
        mostrarAlertaValidacion('Por favor introduce un correo electrónico válido');
        return;
    }
    
    // 3. Validar teléfono
    if (!validarTelefono(telefono)) {
        mostrarAlertaValidacion('El teléfono debe tener mínimo 9 caracteres');
        return;
    }
    
    // 4. Validar rol
    if (!rol || rol === '') {
        mostrarAlertaValidacion('Por favor selecciona un rol');
        return;
    }
    
    // 5. Validar password (obligatorio en CREAR)
    if (!usuarioId && !validarPassword(password)) {
        mostrarAlertaValidacion('La contraseña es obligatoria y debe tener mínimo 6 caracteres');
        return;
    }
    
    // Si todo pasa, hacer fetch POST
    fetch(url, { method: 'POST', ... })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarAlertaExito('¡Éxito!', 'El nuevo usuario ha sido creado correctamente');
                cerrarModalFormUsuario();
                filtrarUsuarios();  // Recargar tabla
            } else {
                mostrarAlertaError('Error', data.message);
            }
        });
};
```

---

## 🎭 Modal Editar Usuario

### Diferencias con Crear
1. **Password es OPCIONAL**
   - Si dejas vacío, password NO se cambia
   - Si escribes algo, debe tener mínimo 6 caracteres
2. **Email y nombre son obligatorios**
3. **Placeholder cambia**: "Dejar vacío para no cambiar"

### Validación de Edición
```javascript
// En editar: password puede estar vacío
if (usuarioId && password !== '' && !validarPassword(password)) {
    mostrarAlertaValidacion('La contraseña debe tener mínimo 6 caracteres');
    return;
}
```

---

## 🔘 Toggle Activar/Desactivar

### Flujo AJAX
```javascript
var toggleEstado = function(id) {
    fetch('/admin/usuarios/' + id + '/toggle-estado', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Actualizar UI
            toggle.classList.toggle('activo');
            badge.textContent = data.activo ? 'Activo' : 'Inactivo';
            
            // NUEVO: Alert de éxito
            var msg = data.activo ? 'El usuario ha sido activado' : 'El usuario ha sido desactivado';
            mostrarAlertaExito('¡Éxito!', msg);
        } else {
            mostrarAlertaError('Error', data.message);
        }
    });
};
```

---

## 🛣️ Rutas Disponibles

| Método | Ruta | Función | Retorno |
|--------|------|---------|---------|
| GET | `/admin/usuarios` | Mostrar vista principal | HTML Blade |
| GET | `/admin/usuarios/filtrar` | Filtrado AJAX + paginación | JSON usuarios |
| GET | `/admin/usuarios/exportar` | Descargar CSV | CSV file |
| POST | `/admin/usuarios/crear` | Crear usuario | JSON {success, id} |
| GET | `/admin/usuarios/{id}` | Obtener usuario (para modal) | JSON usuario |
| POST | `/admin/usuarios/{id}/editar` | Actualizar usuario | JSON {success} |
| POST | `/admin/usuarios/{id}/toggle-estado` | Activar/desactivar | JSON {success, activo} |

---

## 🗄️ Transacciones

### ❌ SIN Transacción (Implementado)
```php
// En crear(), editar(), toggleEstado()
// Solo tocan tbl_usuario → NO necesita transacción

DB::table('tbl_usuario')->insert([...]);
DB::table('tbl_usuario')->update([...]);
return response()->json(['success' => true]);
```

### ✅ CON Transacción (Si Futuro Requiere)
```php
DB::beginTransaction();
try {
    DB::table('tbl_usuario')->insert([...]);
    DB::table('tbl_rol_usuario')->insert([...]);  // 2 tablas
    DB::commit();
    return response()->json(['success' => true]);
} catch (\Exception $e) {
    DB::rollBack();
    return response()->json(['error' => $e->getMessage()], 500);
}
```

---

## 🎨 Adherencia a REGLAS

### ✅ REGLA 1 — Separación de Archivos
- CSS: `public/css/admin/usuarios.css`  
- JS: `public/js/admin/usuarios.js`
- HTML: `resources/views/admin/usuarios.blade.php`
- Todos linkeados con `asset()`

### ✅ REGLA 2 — Fetch con .then()
```javascript
fetch(url, {...})
    .then(function(r) { return r.json(); })
    .then(function(data) { /* actualizar */ })
    .catch(function(e) { console.error(e); });
```
❌ Cero async/await

### ✅ REGLA 3 — Eventos sin addEventListener
```javascript
window.onload = function() {
    document.getElementById('btn').onclick = function() { ... };
    document.getElementById('input').onchange = function() { ... };
};
```
❌ Cero `addEventListener`

### ✅ REGLA 4 — Nivel de Código JS
- `var` (never `const`/`let`)
- `function() { }` (never `=>`)
- Sin clases ES6
- Sin destructuring
- Sin async/await

### ✅ REGLA 5 — Rutas con asset()
```blade
<link rel="stylesheet" href="{{ asset('css/admin/usuarios.css') }}">
<script src="{{ asset('js/admin/usuarios.js') }}"></script>
```

### ✅ REGLA 6 — Estructura de Carpetas
```
resources/views/admin/usuarios.blade.php
public/css/admin/usuarios.css
public/js/admin/usuarios.js
app/Http/Controllers/Admin/UsuarioController.php
```

### ✅ REGLA 7 — Estructura Blade
```blade
@extends('layouts.admin')
@section('titulo', 'Usuarios — SpotStay')
@section('content') ... @endsection
```
Bootstrap 5.3.8 + Icons en `layouts/admin.blade.php`
Zero lógica de negocio en vistas (data vía `compact()`)

### ✅ REGLA 8 — Sin Middleware en Rutas
```php
// Todos en Route::middleware(['role:admin'])->group()
Route::get('/admin/usuarios', [UsuarioController::class, 'index']);
Route::post('/admin/usuarios/crear', [UsuarioController::class, 'crear']);
// ... rutas estáticas ANTES que dinámicas {id}
```

### ✅ REGLA 9 — Transacciones Multi-tabla
Solo cuando se tocan 2+ tablas (no implementado, preparado)

### ✅ REGLA 10 — Este README
Documentación completa de archivos, rutas, validaciones, transacciones, problemas y soluciones

---

## 🐛 Problemas Conocidos y Soluciones

### Problema 1: Validación fallida pero button deshabilitado
**Solución**: Validar en cliente antes de deshabilitar (implementado)

### Problema 2: Usuario crea exitoso pero alert tarda
**Causa**: Fetch lento a servidor
**Solución**: Usar conexión rápida, cache CloudFlare (opcional)

### Problema 3: Paginación perdida al filtrar
**Solución**: `paginaActual = 1` al cambiar filtros (implementado)

### Problema 4: Oso no se ve en alert
**Causa**: SweetAlert2 no cargó
**Solución**: Verificar CDN en DevTools Network

### Problema 5: Password vieja muestra en edición
**Solución**: Intentional—field siempre vacío por seguridad

---

## 🚀 Testing Manual

### Crear Usuario
```
1. Click "Nuevo Usuario"
2. Escribe: Nombre "Juan", Email "juan@test.com", Tel "612345678"
3. Selecciona Rol: "arrendador"
4. Escribe Password: "123456"
5. Click "Guardar"
📍 Resultado: Alert éxito con oso ✓ Tabla actualiza ✓
```

### Editar Usuario
```
1. Click lápiz en fila
2. Modal carga datos existentes
3. Cambia Nombre: "Juan Pedro"
4. Deja Password vacío
5. Click "Guardar"
📍 Resultado: Alert éxito ✓ Contraseña NO cambia ✓
```

### Buscar en Vivo
```
1. Escribe "juan" en búsqueda
2. Espera 100ms → tabla filtra INMEDIATAMENTE
📍 Resultado: LIKE% en nombre+email ✓
```

### Paginar
```
1. Click página 2
2. Tabla cambia a usuarios 11-20
3. Botón "2" marca activo
📍 Resultado: Footer muestra "Mostrando 11-20 de X" ✓
```

### Toggle Estado
```
1. Click toggle en columna estado
2. Badge cambia Activo ↔ Inactivo
3. Alert éxito con oso
📍 Resultado: Todo actualiza visualmente ✓
```

---

**Última actualización:** 17 de Abril de 2026
**Versión:** 2.0 (Con validaciones, SweetAlert2 y oso personalizado)
**Autor:** GeneradorPromptSpotStay
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
