# Solicitudes — Documentación Admin Panel

## Actualización de Modales y SweetAlerts (v2.0)

### Cambios Realizados

Este documento describe todos los cambios implementados en el módulo de **Solicitudes de Arrendadores** para mejorar la experiencia de usuario y mantener coherencia con el resto del sistema.

---

## 1. Conversión de Modales CSS a Bootstrap 5

### Antes
- Modales construidos con CSS personalizado (`modal-overlay`, `modal-admin`, etc.)
- Control manual de clases para abrir/cerrar
- Problemas de accesibilidad y bloqueo de interacción

### Después
- Modales Bootstrap 5.3.8 nativos (`modal fade`)
- Gestión automática a través de `bootstrap.Modal()`
- Bloqueo de fondo automático y accesibilidad mejorada

**Archivo modificado:** `resources/views/admin/solicitudes.blade.php`

```blade
<!-- Modal Bootstrap 5 -->
<div class="modal fade" id="modalSolicitud" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Contenido... -->
        </div>
    </div>
</div>
```

**JavaScript:**
```javascript
var modalSolicitud = new bootstrap.Modal(document.getElementById('modalSolicitud'));
modalSolicitud.show();  // Abrir
modalSolicitud.hide();  // Cerrar
```

---

## 2. Integración de SweetAlert2 con Oso Custom

### Características
- **Oso con expresiones:** Feliz (✓) para éxitos, Triste (✗) para errores
- **Animaciones:** Transiciones suaves con CSS del sistema
- **Colores:** Consistentes con el branding SpotStay (#035498 azul principal)

### Funciones Implementadas

#### `crearOsoExito()`
Retorna SVG HTML del oso sonriendo con checkmark verde.
```javascript
var crearOsoExito = function() {
    return `<svg><!-- SVG del oso feliz --></svg>`;
};
```

#### `crearOsoError()`
Retorna SVG HTML del oso triste con X roja.
```javascript
var crearOsoError = function() {
    return `<svg><!-- SVG del oso triste --></svg>`;
};
```

#### `mostrarAlertaExito(titulo, mensaje)`
Muestra alerta de éxito con el oso feliz.
```javascript
mostrarAlertaExito('¡Éxito!', 'Solicitud aprobada correctamente');
```

#### `mostrarAlertaError(titulo, mensaje)`
Muestra alerta de error con el oso triste.
```javascript
mostrarAlertaError('Error', 'No se pudo procesar la solicitud');
```

---

## 3. Flujo de Operaciones

### Aprobar Solicitud
```
1. Click botón "Aprobar" ✓
2. Carga datos con fetch GET /admin/solicitudes/{id}
3. Abre modal Bootstrap con datos completos
4. Usuario confirma en modal
5. POST /admin/solicitudes/{id}/aprobar
6. Si éxito: SweetAlert oso feliz → Recarga página
7. Si error: SweetAlert oso triste → Mantiene modal
```

### Rechazar Solicitud
```
1. Click botón "Rechazar" ✗
2. Carga datos con fetch GET /admin/solicitudes/{id}
3. Abre modal con campo de notas visible
4. Usuario escribe motivo del rechazo
5. Click "Rechazar solicitud"
6. POST /admin/solicitudes/{id}/rechazar + notas
7. Si éxito: SweetAlert oso feliz → Recarga página
8. Si error: SweetAlert oso triste → Mantiene modal
```

### Filtrado y Búsqueda
```
Usuario escribe/cambia filtro (estado, ciudad, búsqueda)
    ↓
AJAX GET /admin/solicitudes/filtrar?params
    ↓
Actualiza tabla sin recargar página
    ↓
Re-asigna eventos a nuevos botones
```

---

## 4. Estructura de Archivos

```
resources/views/admin/
├── solicitudes.blade.php          ← Vista principal
|   ├── Hero section
|   ├── KPI cards (pendientes, aprobadas, rechazadas)
|   ├── Toolbar (búsqueda + filtros)
|   ├── Tabla de solicitudes
|   ├── Cards estadísticas (derecha)
|   └── Modal Bootstrap para detalles

public/js/admin/
├── solicitudes.js                 ← Lógica principal
|   ├── crearOsoExito()
|   ├── crearOsoError()
|   ├── mostrarAlertaExito()
|   ├── mostrarAlertaError()
|   ├── abrirModal()
|   ├── abrirModalAprobacion()
|   ├── abrirModalRechazo()
|   ├── aprobarSolicitud()
|   ├── rechazarSolicitud()
|   ├── filtrarSolicitudes()
|   └── ... (más funciones de utilidad)

public/css/admin/
├── solicitudes.css                ← Estilos
    ├── Hero section
    ├── KPI cards
    ├── Toolbar
    ├── Tabla
    ├── Modal Bootstrap
    ├── Oso SVG styles (.oso-icon)
    └── Responsive design

routes/
├── web.php
    ├── GET  /admin/solicitudes              → SolicitudController@index
    ├── GET  /admin/solicitudes/filtrar      → SolicitudController@filtrar
    ├── GET  /admin/solicitudes/{id}         → SolicitudController@show
    ├── POST /admin/solicitudes/{id}/aprobar → SolicitudController@aprobar
    └── POST /admin/solicitudes/{id}/rechazar → SolicitudController@rechazar
```

---

## 5. Normativas Aplicadas

### ✅ REGLA 1 — Separación de Archivos
- CSS en: `public/css/admin/solicitudes.css`
- JS en: `public/js/admin/solicitudes.js`
- Blade en: `resources/views/admin/solicitudes.blade.php`
- **Nunca** código incrustado en las vistas

### ✅ REGLA 2 — AJAX con Fetch y .then()
```javascript
fetch(url)
    .then(function(respuesta) { return respuesta.json(); })
    .then(function(datos) { /* procesar datos */ })
    .catch(function(error) { /* manejar error */ });
```
**Nunca** async/await ni then().catch() moderno

### ✅ REGLA 3 — Eventos sin addEventListener
```javascript
window.onload = function() {
    document.getElementById('btn').onclick = function() { /*...*/ };
};
```
**Nunca** addEventListener ni event listeners modernos

### ✅ REGLA 4 — Nivel de Código JS
- ✅ `var` en lugar de `const`/`let`
- ✅ Sin arrow functions `() =>`
- ✅ Sin clases ES6
- ✅ Sin destructuring
- ✅ Sin async/await

### ✅ REGLA 5 — Rutas con asset()
```blade
<link rel="stylesheet" href="{{ asset('css/admin/solicitudes.css') }}">
<script src="{{ asset('js/admin/solicitudes.js') }}"></script>
```

### ✅ REGLA 6 — Estructura de Carpetas
```
resources/views/admin/solicitudes.blade.php
public/css/admin/solicitudes.css
public/js/admin/solicitudes.js
```

### ✅ REGLA 7 — Estructura Blade
```blade
@extends('layouts.admin')
@section('titulo', 'Solicitudes — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/solicitudes.css') }}">
@endsection
@section('content')
    <!-- Contenido sin lógica de negocio -->
@endsection
@section('scripts')
    <script src="{{ asset('js/admin/solicitudes.js') }}"></script>
@endsection
```

### ✅ REGLA 8 — Sin Middleware en Rutas
Las rutas están directamente accesibles por URL en `web.php`:
```php
Route::get('/admin/solicitudes', [SolicitudController::class, 'index']);
```

### ✅ REGLA 9 — Transacciones (si aplica)
En el controlador `SolicitudController`:
- **aprobar()**: Actualiza 1 tabla → SIN transacción
- **rechazar()**: Actualiza 1 tabla → SIN transacción

---

## 6. Variables Globales y Configuración

### CSS
```css
/* Colores principales */
#035498  /* Azul */
#1AA068  /* Verde */
#EF4444  /* Rojo */
#D97706  /* Naranja */

/* Iconografía */
Bootstrap Icons (bi bi-*)
```

### JavaScript
```javascript
var csrfToken;              // Token CSRF del formulario
var solicitudIdActual;      // ID de solicitud en edición
var modalSolicitud;         // Instancia Bootstrap Modal
```

---

## 7. Flujo de Datos (FETCH)

### GET /admin/solicitudes/{id}
**Respuesta JSON:**
```json
{
    "id_solicitud_arrendador": 1,
    "nombre_usuario": "Juan García",
    "email_usuario": "juan@example.com",
    "datos_solicitud_arrendador": "{
        \"ciudad\": \"Madrid\",
        \"direccion\": \"Calle Mayor 14\",
        \"tipo\": \"Piso\",
        \"precio_estimado\": \"1200\",
        \"habitaciones\": \"2\",
        \"banos\": \"1\",
        \"tamano\": \"85\"
    }",
    "creado_solicitud_arrendador": "2025-04-20 10:30:00"
}
```

### POST /admin/solicitudes/{id}/aprobar
**Headers:**
```
Content-Type: application/json
X-CSRF-TOKEN: <token>
```
**Body:** (vacío)

**Respuesta:**
```json
{
    "success": true,
    "message": "Solicitud aprobada correctamente"
}
```

### POST /admin/solicitudes/{id}/rechazar
**Headers:**
```
Content-Type: application/json
X-CSRF-TOKEN: <token>
```
**Body:**
```json
{
    "notas": "El usuario no ha completado la documentación"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Solicitud rechazada correctamente"
}
```

---

## 8. Clases CSS Utilizadas

### Componentes
- `.hero-admin` — Encabezado azul
- `.kpi-mini` — Tarjetas de estadísticas pequeñas
- `.toolbar-admin` — Barra de filtros y búsqueda
- `.tabla-admin` — Tabla principal de datos
- `.card-admin` — Contenedores generales
- `.modal fade` — Modal Bootstrap nativo

### Estados
- `.badge bg-warning` — Pendiente (amarillo)
- `.badge bg-success` — Aprobado (verde)
- `.badge bg-danger` — Rechazado (rojo)

### Oso SweetAlert
- `.oso-icon` — Contenedor del oso
- `.oso-icon .yeti-part` — Cabeza y cuerpo
- `.oso-icon .suit-jacket` — Traje azul
- `.oso-icon .suit-shirt` — Camisa blanca
- `.oso-icon .suit-tie` — Corbata verde

---

## 9. Problemas Conocidos y Soluciones

### Problema: Modal bloqueada
**Síntoma:** Modal aparece pero no se puede interactuar
**Solución:** Asegurar que `new bootstrap.Modal()` se inicializa en `window.onload`

### Problema: SweetAlert no muestra
**Síntoma:** No aparece alerta tras aprobar/rechazar
**Solución:** Verificar que SweetAlert2 está en el layout principal (`layouts.admin`)

### Problema: Filtros no funcionan
**Síntoma:** Click en filtro no actualiza tabla
**Solución:** Verificar que `/admin/solicitudes/filtrar` retorna JSON con estructura correcta

### Problema: Botones desaparecen tras filtrar
**Síntoma:** Nueva tabla no responde a clicks
**Solución:** La función `asignarEventosTabla()` se llama automáticamente en `actualizarTabla()`

---

## 10. Testing Checklist

- [ ] Cargar página `/admin/solicitudes` sin errores
- [ ] Filtrar por estado: muestra solo pendientes
- [ ] Filtrar por ciudad: filtra correctamente
- [ ] Buscar por nombre: busca en 3+ caracteres
- [ ] Click "Ver detalles": abre modal con datos correctos
- [ ] Click "Aprobar": muestra SweetAlert oso feliz
- [ ] Click "Rechazar": abre modal con campo notas
- [ ] Escribir notas y rechazar: muestra SweetAlert oso feliz
- [ ] Error en backend: muestra SweetAlert oso triste
- [ ] Paginación: navega entre páginas
- [ ] Responsive: funciona en móvil

---

## 11. Contacto y Soporte

Para reportar issues o solicitar mejoras:
- Revisar `web.php` para rutas disponibles
- Revisar `SolicitudController` para lógica backend
- Revisar `solicitudes.js` para lógica frontend
- Verificar `solicitudes.css` para estilos personalizados

---

**Versión:** 2.0  
**Última actualización:** 20/04/2025  
**Desarrollador:** Assistant (SpotStay Admin Panel)
