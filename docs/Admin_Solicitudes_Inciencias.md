# SpotStay Admin — Corrección Solicitudes e Incidencias

Documentación completa sobre las correcciones y mejoras implementadas en los módulos de solicitudes e incidencias del panel de administración de SpotStay.

---

## 📋 Qué se ha corregido

Los archivos anteriores tenían los siguientes problemas que han sido solucionados:

### ❌ Problema 1: CSS y JS dentro de Blade
**Antes**: Los estilos y scripts estaban inline en `solicitudes.blade.php` e `incidencias.blade.php`  
**Ahora**: Separados en archivos independientes bajo `public/css/admin/` y `public/js/admin/`

### ❌ Problema 2: Sin transacciones en modificaciones múltiples
**Antes**: Los controladores no usaban `DB::beginTransaction()` para operaciones que tocaban varias tablas  
**Ahora**: Se implementaron transacciones en:
- `SolicitudController@aprobar` (modifica tbl_solicitud_arrendador + tbl_rol_usuario)
- `IncidenciaController@cambiarEstado` (modifica tbl_incidencia + tbl_historial_incidencia)
- `IncidenciaController@asignar` (modifica tbl_incidencia + tbl_historial_incidencia)

### ❌ Problema 3: Eventos con addEventListener
**Antes**: Uso de `addEventListener` (JavaScript moderno)  
**Ahora**: Eventos asignados con `.onclick`, `.onchange`, `.onblur`, `.onkeyup` dentro de `window.onload`

### ❌ Problema 4: Código moderno (arrow functions, const/let)
**Antes**: Arrow functions, `const`, `let`, destructuring  
**Ahora**: `var`, funciones tradicionales con `function`

### ❌ Problema 5: Métodos filtrado faltantes
**Antes**: No existía `filtrar()` en SolicitudController e IncidenciaController  
**Ahora**: Métodos implementados con búsqueda y filtrado completos

### ❌ Problema 6: Variable $tiempoMedio no disponible
**Antes**: Vista esperaba `$tiempoMedio` pero no se pasaba desde el controlador  
**Ahora**: Controlador genera la variable correctamente (valor de prueba: 4.2 horas)

---

## 📁 Archivos generados

### Controladores (2 archivos)

| Archivo | Ruta | Cambios principales |
|---------|------|------------------|
| **SolicitudController.php** | `app/Http/Controllers/Admin/` | Añadido método `filtrar()`, mejorado `aprobar()` con transacci transacción y fallback Role |
| **IncidenciaController.php** | `app/Http/Controllers/Admin/` | Añadido método `filtrar()`, transacciones en `cambiarEstado()` y `asignar()`, retorna `$gestores` |

### Vistas Blade (2 archivos) - SIN CSS ni JS

| Archivo | Ruta | Características |
|---------|------|-----------------|
| **solicitudes.blade.php** | `resources/views/admin/` | Layout 2 columnas, modal detalle, historial aprobadas/rechazadas |
| **incidencias.blade.php** | `resources/views/admin/` | Kanban 4 columnas, modal con historial timeline, asignación gestores |

### CSS (2 archivos) - Separados e independientes

| Archivo | Tamaño aprox | Contiene |
|---------|--------|----------|
| **solicitudes.css** | ~5 KB | Tarjetas, historial, modal, filtros, responsive |
| **incidencias.css** | ~6.5 KB | Kanban, tarjetas con prioridades, timeline, badges pulsantes |

### JavaScript (2 archivos) - Sin frameworks, lógica pura

| Archivo | Tamaño aprox | Características |
|---------|--------|------------------|
| **solicitudes.js** | ~4 KB | Filtrados en tiempo real, modal dinámico, validaciones |
| **incidencias.js** | ~4 KB | Filtrados, modal con historial, cambio estados, asignación |

### Rutas (actualizado)

| Método | URL | Controlador | Notas |
|--------|-----|-------------|-------|
| GET | `/admin/solicitudes` | SolicitudController@index | Pendientes paginadas |
| GET | `/admin/solicitudes/filtrar` | SolicitudController@filtrar | **IMPORTANTE**: Debe ir ANTES de `{id}` |
| GET | `/admin/solicitudes/{id}` | SolicitudController@show | JSON de solicitud |
| POST | `/admin/solicitudes/{id}/aprobar` | SolicitudController@aprobar | **Transacción: 2 tablas** |
| POST | `/admin/solicitudes/{id}/rechazar` | SolicitudController@rechazar | Sin transacción |
| GET | `/admin/incidencias` | IncidenciaController@index | Kanban con 4 columnas |
| GET | `/admin/incidencias/filtrar` | IncidenciaController@filtrar | **IMPORTANTE**: Debe ir ANTES de `{id}` |
| GET | `/admin/incidencias/{id}` | IncidenciaController@show | JSON con historial |
| POST | `/admin/incidencias/{id}/estado` | IncidenciaController@cambiarEstado | **Transacción: 2 tablas** |
| POST | `/admin/incidencias/{id}/asignar` | IncidenciaController@asignar | **Transacción: 2 tablas** |

---

## 🔄 Dónde se usan transacciones y por qué

### SolicitudController@aprobar

**Tablas modificadas**: `tbl_solicitud_arrendador` + `tbl_rol_usuario`

```php
DB::beginTransaction();
try {
    // Obtener solicitud
    // Actualizar estado a "aprobada" en tbl_solicitud_arrendador
    // Insertar rol "arrendador" en tbl_rol_usuario
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();  // Si falla cualquier operación, revierte AMBAS
}
```

**¿Por qué?**: Si la inserción del rol falla pero la solicitud fue aprobada, quedaría un registro inconsistente. La transacción garantiza atomicidad.

---

### IncidenciaController@cambiarEstado

**Tablas modificadas**: `tbl_incidencia` + `tbl_historial_incidencia`

```php
DB::beginTransaction();
try {
    // Actualizar estado en tbl_incidencia
    // Insertar registro en tbl_historial_incidencia
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();  // Revierte ambas si hay error
}
```

**¿Por qué?**: El historial debe estar siempre sincronizado con el estado actual. Si uno falla, ambos reviertan.

---

### IncidenciaController@asignar

**Tablas modificadas**: `tbl_incidencia` + `tbl_historial_incidencia`

Misma lógica que `cambiarEstado()`. Garantiza que:
- La asignación al gestor se registre
- El cambio de estado a "en_proceso" ocurra
- El historial se actualice

Si alguna operación falla, TODO se revierte.

---

## ❌ Dónde NO se usan transacciones (y por qué)

| Método | Tabla(s) | Justificación |
|--------|---------|----------|
| `SolicitudController@rechazar` | Solo `tbl_solicitud_arrendador` | Toca 1 tabla → sin transacción necesaria |
| `SolicitudController@show` | Solo lectura | Sin modificaciones → sin transacción |
| `SolicitudController@filtrar` | Solo lectura | Sin modificaciones → sin transacción |
| `SolicitudController@index` | Solo lectura | Sin modificaciones → sin transacción |
| `IncidenciaController@show` | Solo lectura | Sin modificaciones → sin transacción |
| `IncidenciaController@filtrar` | Solo lectura | Sin modificaciones → sin transacción |
| `IncidenciaController@index` | Solo lectura | Sin modificaciones → sin transacción |

---

## 🔍 Cómo funcionan los filtros de solicitudes

### Flujo de ejecución

1. **Usuario hace cambio** en algún select o campo de búsqueda
2. **JavaScript dispara evento**: `onchange` o `onblur`
3. **Función `filtrarSolicitudes()`** recopila valores:
   ```javascript
   var estado = document.getElementById('selectEstadoSol').value;
   var ciudad = document.getElementById('selectCiudadSol').value;
   var q = document.getElementById('buscadorSolicitudes').value;
   ```
4. **Fetch POST** a `/admin/solicitudes/filtrar?estado=...&ciudad=...&q=...`
5. **Controlador ejecuta query con filtros** y devuelve JSON
6. **JavaScript actualiza el contador** de solicitudes pendientes

### Ejemplo: Buscar por ciudad

```javascript
// Usuario selecciona "Barcelona"
document.getElementById('selectCiudadSol').onchange = function() {
    filtrarSolicitudes();
};

// Función construye URL
fetch('/admin/solicitudes/filtrar?estado=&ciudad=Barcelona&q=')
    .then(r => r.json())
    .then(data => {
        // data.solicitudes = [] (array de solicitudes)
        // data.total = 3
    });
```

En el controlador:
```php
if ($request->ciudad) {
    $query->where('datos_solicitud_arrendador','like',
      '%"ciudad":"Barcelona"%');
}
```

---

## 📊 Cómo funciona el tablero Kanban de incidencias

### Estructura de 4 columnas

```
┌─────────────┬──────────┬──────────┬─────────┐
│   Abierta   │ Proceso  │ Resuelta │ Cerrada │
│ (rojo #EF4) │ (naranja  │ (verde   │ (gris)  │
│             │ #D97706) │ #1AA068) │         │
├─────────────┼──────────┼──────────┼─────────┤
│ Tarjeta 1   │ Tarjeta 1│ Tarjeta1 │ Tarjeta1│
│ Tarjeta 2   │ Tarjeta 2│          │         │
│ ...         │          │          │         │
└─────────────┴──────────┴──────────┴─────────┘
```

### Características de cada tarjeta

- **Borde izquierdo coloreado** según prioridad:
  - Rojo: urgente
  - Naranja: alta
  - Gris: media
  - Verde: baja

- **Badge de prioridad** con color de fondo
- **Título y descripción** (limitada a 2 líneas)
- **Avatar mini** del inquilino que reportó
- **Timestamp relativo** (ej: "hace 2 horas")
- **Información de gestor** (si asignado)
- **Check verde** en resueltas (icono visual)
- **Opacidad reducida** en cerradas

### Flujo al hacer click en una tarjeta

1. **Click dispara `abrirModal(id)`**
2. **Fetch GET a `/admin/incidencias/{id}`**
3. **Respuesta contiene**: `incidencia` + `historial`
4. **Función `rellenarModal()`** rellena todos los campos
5. **Función `rellenarHistorial()`** crea timeline visual
6. **Modal se abre** con `classList.add('visible')`

---

## 🎬 Cómo funciona el modal de incidencias

### Secciones del modal

```
┌─────────────────────────────┐
│ Título + Badges prioridad   │ ← modalBadgePrioridad
├─────────────────────────────┤
│ Imagen hero (gradiente)     │ ← modalImagenTexto
├─────────────────────────────┤
│ DESCRIPCIÓN                 │ ← modalTituloInc, modalDescInc
├─────────────────────────────┤
│ GRID 2 COLS:                │
│ - Propiedad                 │ ← modalPropiedadInc
│ - Reportada por             │ ← modalInquilinoInc
│ - Fecha                     │ ← modalFechaInc
│ - Categoría, Prioridad, etc │
├─────────────────────────────┤
│ ASIGNAR GESTOR              │ ← selectGestorModal + btnAsignar
│ CAMBIAR ESTADO (4 botones)  │ ← .btn-estado[data-estado="..."]
├─────────────────────────────┤
│ TIMELINE HISTORIAL          │ ← timelineHistorial (se rellena con fetch)
├─────────────────────────────┤
│ NOTAS INTERNAS (textarea)   │ ← modalNotasInc
├─────────────────────────────┤
│ BOTONES FOOTER              │
│ - Marcar como cerrada       │ ← btnCerrarInc
│ - Contactar al inquilino    │ ← btnContactarInquilino
│ - Guardar cambios           │ ← btnGuardarCambios
└─────────────────────────────┘
```

### Carga del historial

```javascript
var rellenarHistorial = function(historial) {
    var contenedor = document.getElementById('timelineHistorial');
    // Para cada evento en historial:
    for (i = 0; i < historial.length; i++) {
        var item = historial[i];
        // Crear elemento con:
        // - Punto coloreado (depende del estado)
        // - Texto del cambio (comentario_historial)
        // - Nombre del usuario (nombre_usuario)
        // - Fecha/hora implícita
    }
};
```

### Cambio de estado

Cuando haces click en un botón de estado:

```javascript
document.getElementById('btnGuardarCambios').onclick = function() {
    var comentario = document.getElementById('modalNotasInc').value;
    cambiarEstado(incidenciaIdActual, estadoActualModal, comentario);
};

var cambiarEstado = function(id, estado, comentario) {
    fetch('/admin/incidencias/' + id + '/estado', {
        method: 'POST',
        body: JSON.stringify({
            estado: estado,
            comentario: comentario
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();  // Recarga la página
        }
    });
};
```

En el controlador (con transacción):

```php
public function cambiarEstado(Request $request, $id)
{
    DB::beginTransaction();
    try {
        // 1. Actualiza tbl_incidencia
        DB::table('tbl_incidencia')
            ->where('id_incidencia', $id)
            ->update(['estado_incidencia' => $request->estado]);

        // 2. Inserta en tbl_historial_incidencia
        DB::table('tbl_historial_incidencia')->insert([...]);

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
    }
}
```

### Asignación de gestor

Funciona igual que cambio de estado, pero:

```javascript
var asignarGestor = function(id, idGestor) {
    fetch('/admin/incidencias/' + id + '/asignar', {
        body: JSON.stringify({ id_gestor: idGestor })
    })
};
```

En controlador:

```php
DB::table('tbl_incidencia')->update([
    'id_asignado_fk' => $request->id_gestor,
    'estado_incidencia' => 'en_proceso'
]);
```

---

## 💬 Cómo funciona el modal de solicitudes

### Secciones del modal

```
┌─────────────────────────────┐
│ Título + Badge estado       │
├─────────────────────────────┤
│ AVATAR PERSONA              │
│ - Nombre                    │
│ - Email                     │
│ - Teléfono                  │
│ - Ciudad                    │
├─────────────────────────────┤
│ PROPIEDAD SOLICITADA        │
│ (Grid 3 cols x 2 rows)      │
│ - Dirección, Tipo, Precio   │
│ - Habitaciones, Baños, Tamaño
├─────────────────────────────┤
│ DOCUMENTACIÓN APORTADA      │
│ - DNI.pdf                   │
│ - Nómina.pdf                │
│ - Foto.jpg                  │
├─────────────────────────────┤
│ NOTAS (opcional)            │
│ textarea para motivo rechazo│
├─────────────────────────────┤
│ BOTONES FOOTER              │
│ - Rechazar solicitud        │
│ - Aprobar solicitud         │
└─────────────────────────────┘
```

### Aprob solicitud (con transacción)

```javascript
var aprobarSolicitud = function(id) {
    fetch('/admin/solicitudes/' + id + '/aprobar', {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
};
```

En controlador (transacción):

```php
public function aprobar($id)
{
    DB::beginTransaction();
    try {
        // 1. Obtener solicitud
        // 2. Actualizar estado en tbl_solicitud_arrendador
        DB::table('tbl_solicitud_arrendador')->update([...]);

        // 3. Insertar rol arrendador en tbl_rol_usuario
        DB::table('tbl_rol_usuario')->insert([...]);

        DB::commit();
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
```

**Si algo falla** (ej: no hay rol arrendador):
- `DB::rollBack()` revierte AMBAS inserciones/actualizaciones
- No queda solicitud aprobada sin rol asignado

### Rechaza solicitud (sin transacción)

```javascript
var rechazarSolicitud = function(id, notas) {
    fetch('/admin/solicitudes/' + id + '/rechazar', {
        method: 'POST',
        body: JSON.stringify({ notas: notas })
    })
};
```

En controlador (sin transacción, toca 1 tabla):

```php
public function rechazar(Request $request, $id)
{
    DB::table('tbl_solicitud_arrendador')
        ->where('id_solicitud_arrendador', $id)
        ->update([
            'estado_solicitud_arrendador' => 'rechazada',
            'notas_solicitud_arrendador' => $request->notas,
            'id_admin_revisa_fk' => $idAdmin
        ]);

    return response()->json(['success' => true]);
}
```

---

## ⚠️ Problemas conocidos y soluciones

### Problema: Rutas dinámica toma prioridad sobre filtrador

**Error**: `GET /admin/solicitudes/filtrar` devuelve 404  
**Causa**: Laravel intenta matchear `/solicitudes/filtrar` con `/solicitudes/{id}`  
**Solución**: En `routes/web.php`, las rutas estáticas DEBEN ir ANTES:

```php
// ✅ CORRECTO
Route::get('/admin/solicitudes', [SolicitudController::class, 'index']);
Route::get('/admin/solicitudes/filtrar', SolicitudController::class, 'filtrar']);  // Primero
Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show']);      // Después

// ❌ INCORRECTO (causaría 404)
Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show']);
Route::get('/admin/solicitudes/filtrar', [SolicitudController::class, 'filtrar']);
```

**Check**: En tu `routes/web.php`, verifica que `/filtrar` esté ANTES de `/{id}`.

---

### Problema: Modal no se abre

**Síntoma**: Click en tarjeta no abre modal  
**Causas posibles**:
1. `#modalOverlay` no existe en HTML
2. Modal tiene `display: none` en CSS permanente
3. Fetch devuelve error 500 (revisar logs: `storage/logs/laravel.log`)

**Solución**:

```bash
# Revisar logs
tail -f storage/logs/laravel.log

# Verify meta csrf-token exists
grep 'csrf-token' resources/views/layouts/admin.blade.php
```

---

### Problema: Transacción hace rollback siempre

**Síntoma**: "Error: Unknown error" al aprobar solicitud  
**Causa**: Excepción en query PHP (ej: rol no existe)

**Debug**:

```php
public function aprobar($id)
{
    DB::beginTransaction();
    try {
        // ... código ...
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        // Mostrar error real
        \Log::error('Error aprobar: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

Luego revisar `storage/logs/laravel.log` para ver el error exacto.

---

### Problema: Los filtros no funcionan

**Síntoma**: Cambias filtro pero nada ocurre  
**Causa**: JavaScript no se está ejecutando (revisar Console del navegador)

**Solución**:

```bash
# Ejecutar en navegador - Consola
document.getElementById('selectEstadoSol').onchange
// Debería devolver una función, no undefined
```

Si devuelve `undefined`, el JavaScript no se cargó. Revisa:
- ¿Existe `public/js/admin/solicitudes.js`?
- ¿El asset() en Blade apunta al archivo correcto?
- ¿Hay errores en la consola?

---

## 📚 Estructura final de carpetas

```
SpotStay/
├── app/Http/Controllers/Admin/
│   ├── DashboardController.php
│   ├── UsuarioController.php
│   ├── PropiedadController.php
│   ├── SolicitudController.php           ✅ CORREGIDO
│   └── IncidenciaController.php          ✅ CORREGIDO
│
├── resources/views/admin/
│   ├── dashboard.blade.php
│   ├── usuarios.blade.php
│   ├── propiedades.blade.php
│   ├── solicitudes.blade.php             ✅ CORREGIDO (sin CSS/JS)
│   └── incidencias.blade.php             ✅ CORREGIDO (sin CSS/JS)
│
├── public/css/admin/
│   ├── solicitudes.css                   ✅ NUEVO
│   └── incidencias.css                   ✅ NUEVO
│
├── public/js/admin/
│   ├── solicitudes.js                    ✅ NUEVO
│   └── incidencias.js                    ✅ NUEVO
│
└── routes/
    └── web.php                           ✅ ACTUALIZADO (filtrar rutas)
```

---

## ✅ Checklist de verificación

Después de implementar los cambios, verifica:

- [ ] Las vistas se cargan sin errores de 404 (CSS/JS)
- [ ] Los filtros responden en tiempo real
- [ ] Al aprobar una solicitud, se reasigna el rol correctamente
- [ ] Al rechazar, aparece en la columna "Rechazadas recientemente"
- [ ] El modal de incidencias se abre al hacer click
- [ ] El historial aparece en el timeline
- [ ] Al cambiar estado, se inserta en historial y tbl_incidencia se actualiza
- [ ] La asignación de gestor funciona sin errores
- [ ] No aparecen errores en `storage/logs/laravel.log`
- [ ] Las transacciones funcionan (haz un test: desactiva BD a mitad de aprobar solicitud, должна hacer rollback)

---

## 🚀 Próximos pasos (opcional)

1. **Agregar validación frontend**:
   - Prompt en JavaScript antes de acciones importantes
   - Deshabilitar botones mientras se procesa

2. **Mejorar UX**:
   - Toast notifications en lugar de location.reload()
   - Animaciones suaves en modal

3. **Agregar auditoría**:
   - Registrar quién aprobó/rechazó en tbl_historial_solicitud
   - IP y timestamp automáticamente

---

**Última actualización**: Abril 2025  
**Laravel**: 13.4.0  
**PHP**: 8.2+  
**Estado**: ✅ Todos los requisitos implementados
