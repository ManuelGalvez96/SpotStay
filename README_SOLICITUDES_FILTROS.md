# Filtros de Solicitudes — SpotStay

## 📋 Descripción General

Sistema de filtrado avanzado para la tabla de solicitudes de arrendadores con filtros por período temporal, estado, ciudad y búsqueda por nombre.

---

## 📁 Archivos Involucrados

### Backend
- **`app/Http/Controllers/Admin/SolicitudController.php`**
  - Método `index()`: Carga la vista con datos iniciales (pendientes del mes)
  - Método `filtrar()`: Endpoint AJAX que devuelve solicitudes filtradas según parámetros

### Frontend - Blade
- **`resources/views/admin/solicitudes.blade.php`**
  - Vista principal con estructura HTML
  - Toolbar con selectores de filtro (período, estado, ciudad)
  - Tabla dinámica poblada por JavaScript
  - KPI cards con contadores

### Frontend - JavaScript
- **`public/js/admin/solicitudes.js`**
  - `asignarEventosFiltros()`: Registra eventos en selectores y búsqueda
  - `filtrarSolicitudes()`: Realiza fetch AJAX y actualiza tabla
  - `actualizarTabla()`: Pinta los datos en HTML
  - `actualizarPaginacionUI()`: Maneja la paginación

### Frontend - CSS
- **`public/css/admin/solicitudes.css`**
  - Estilos de tablas, cards, selectores, badges

---

## 🔧 Funcionamiento de los Filtros

### 1. **Filtro de Período** (selectRangoSol)

Parámetro: `?rango=`

| Valor | Descripción | Lógica |
|-------|-------------|--------|
| `mes` | Este mes (DEFAULT) | `whereMonth() + whereYear()` de la fecha actual |
| `3meses` | Últimos 3 meses | `where('fecha', '>=', now() - 3 meses)` |
| `anio` | Este año | `whereYear()` de la fecha actual |
| `all` | Todas las solicitudes | Sin filtro de fechas |

**Implementación en Controller:**
```php
switch ($rango) {
    case 'all':
        // Sin filtro - mostrar todas
        break;
    case 'mes':
        $query->whereMonth('actualizado_solicitud_arrendador', Carbon::now()->month)
              ->whereYear('actualizado_solicitud_arrendador', Carbon::now()->year);
        break;
    case '3meses':
        $fechaHace3Meses = Carbon::now()->subMonths(3);
        $query->where('actualizado_solicitud_arrendador', '>=', $fechaHace3Meses);
        break;
    case 'anio':
        $query->whereYear('actualizado_solicitud_arrendador', Carbon::now()->year);
        break;
}
```

### 2. **Filtro de Estado** (selectEstadoSol)

Parámetro: `?estado=`

| Valor | Estado |
|-------|--------|
| `` (vacío) | Todos los estados |
| `pendiente` | Solicitudes pendientes de revisar |
| `aprobada` | Solicitudes aprobadas |
| `rechazada` | Solicitudes rechazadas |

### 3. **Filtro de Ciudad** (selectCiudadSol)

Parámetro: `?ciudad=`

Filtra solicitudes por `datos_solicitud_arrendador->ciudad` (JSON):
- Madrid, Barcelona, Valencia, Sevilla, Bilbao
- O vacío para mostrar todas

### 4. **Búsqueda** (buscadorSolicitudes)

Parámetro: `?q=`

Busca en `nombre_usuario` (LIKE %q%)

---

## 🌐 Rutas Disponibles

### GET `/admin/solicitudes`
- **Método:** `SolicitudController@index`
- **Descripción:** Carga la vista principal
- **Datos devueltos:**
  - `$solicitudesPendientes`: Paginated (10 por página) - solicitudes pendientes del mes actual
  - `$aprobadas`: COUNT de aprobadas este mes
  - `$rechazadas`: COUNT de rechazadas este mes
  - `$totalSolicitudes`: COUNT total de todas las solicitudes

### GET `/admin/solicitudes/filtrar`
- **Método:** `SolicitudController@filtrar`
- **Parámetros Query:**
  - `rango`: mes|3meses|anio|all (default: mes)
  - `estado`: pendiente|aprobada|rechazada (default: todos)
  - `ciudad`: Madrid|Barcelona|Valencia|Sevilla|Bilbao (default: todas)
  - `q`: búsqueda por nombre (default: vacío)
  - `page`: número de página para paginación (default: 1)
- **Descripción:** Endpoint AJAX que devuelve solicitudes filtradas
- **Respuesta JSON:**
  ```json
  {
    "data": [
      {
        "id_solicitud_arrendador": 1,
        "id_usuario_fk": 5,
        "datos_solicitud_arrendador": { "ciudad": "Madrid", "direccion": "..." },
        "estado_solicitud_arrendador": "pendiente",
        "creado_solicitud_arrendador": "2026-04-22",
        "actualizado_solicitud_arrendador": "2026-04-22",
        "nombre_usuario": "Juan García",
        "email_usuario": "juan@example.com"
      }
    ],
    "total": 5,
    "current_page": 1,
    "last_page": 1,
    "per_page": 6,
    "from": 1,
    "to": 5
  }
  ```

---

## 🔄 Flujo de Ejecución

### Carga Inicial (index)

```
GET /admin/solicitudes
    ↓
SolicitudController@index()
    ├─ Query: solicitudesPendientes (DE ESTE MES)
    ├─ Query: aprobadas count (DE ESTE MES)
    ├─ Query: rechazadas count (DE ESTE MES)
    └─ return view('admin/solicitudes', compact(...))
        ↓
        Blade renderiza HTML + JavaScript
        ↓
        window.onload → asignarEventosFiltros()
        ↓
        filtrarSolicitudes() — Primera carga AJAX
```

### Al Cambiar Filtro

```
Usuario cambia select (selectRangoSol.onchange, etc)
    ↓
filtrarSolicitudes()
    ├─ Obtiene valores: rango, estado, ciudad, q
    ├─ Construye URL: /admin/solicitudes/filtrar?rango=...&estado=...&ciudad=...&q=...&page=1
    └─ fetch() POST
        ↓
        SolicitudController@filtrar()
        ├─ Aplica filtros según parámetros
        ├─ Pagina resultados (6 por página)
        └─ return response()->json([...])
            ↓
            .then(respuesta.json())
            ↓
            .then(actualizarTabla(datos))
            ├─ Limpia tbody
            ├─ Itera datos y pinta filas
            └─ Pinta badges de estado con colores
            ↓
            actualizarPaginacionUI(datos)
            └─ Genera links de paginación
```

---

## 🎨 CSS — Estructura de Clases

En `public/css/admin/solicitudes.css`:

```css
.toolbar-admin           /* Barra de filtros */
  .toolbar-izquierda    /* Selectores y búsqueda */
  .toolbar-derecha      /* Texto de conteo */

.select-filtro          /* Estilos de <select> */
.input-busqueda         /* Estilos de búsqueda */

.tabla-admin            /* Tabla principal */
.fila-solicitud         /* Fila de solicitud */
.badge-estado           /* Badge de estado */
  .badge-pendiente      /* Naranja */
  .badge-aprobada       /* Verde */
  .badge-rechazada      /* Rojo */

.tabla-footer           /* Paginación */
.paginacion-links       /* Links de página */
```

---

## ✅ Cumplimiento de Normativas

### Norma 1 — Separación de Archivos
✅ CSS en `public/css/admin/solicitudes.css`
✅ JS en `public/js/admin/solicitudes.js`
✅ Blade en `resources/views/admin/solicitudes.blade.php`

### Norma 2 — AJAX con Fetch y .then()
✅ `fetch()` con `.then(function(r) { return r.json() })`
✅ Sin async/await
✅ Sin Promises con arrow functions

### Norma 3 — Eventos sin addEventListener
✅ Todos los eventos dentro de `window.onload`
✅ Usando `.onclick`, `.onchange`, `.onkeyup`
✅ Sin `addEventListener()`

### Norma 4 — Nivel de Código JS
✅ `var` en lugar de `const`/`let`
✅ Sin arrow functions `() =>`
✅ Sin clases ES6
✅ Sin destructuring
✅ Sin async/await

### Norma 5 — Rutas con asset()
✅ `{{ asset('css/admin/solicitudes.css') }}`
✅ `{{ asset('js/admin/solicitudes.js') }}`
✅ Sin @vite ni @mix

### Norma 6 — Estructura de Carpetas
✅ `resources/views/admin/solicitudes.blade.php`
✅ `public/css/admin/solicitudes.css`
✅ `public/js/admin/solicitudes.js`

### Norma 7 — Estructura Blade
✅ `@extends('layouts.admin')`
✅ Secciones: titulo, css, content
✅ Datos desde controller con `compact()`
✅ Bootstrap 5.3.8 ya en layout

### Norma 8 — Sin Middleware en Rutas
✅ Rutas directo en `routes/web.php`
✅ Sin middleware
✅ Rutas estáticas (`/filtrar`) ANTES que dinámicas (`/{id}`)

### Norma 9 — Transacciones
✅ NO hay transacciones (solo lectura en filtrar())
✅ No se modifican múltiples tablas

### Norma 10 — README al Final
✅ Este mismo documento

---

## 📊 Ejemplos de URLs

### Cargar solicitudes pendientes de este mes
```
GET /admin/solicitudes/filtrar?rango=mes&estado=pendiente&cidade=&q=&page=1
```

### Ver todas las solicitudes de Barcelona de los últimos 3 meses
```
GET /admin/solicitudes/filtrar?rango=3meses&estado=&ciudad=Barcelona&q=&page=1
```

### Buscar solicitudes aprobadas de este año por nombre "Juan"
```
GET /admin/solicitudes/filtrar?rango=anio&estado=aprobada&ciudad=&q=Juan&page=1
```

### Ver todas las solicitudes (sin límite de fecha)
```
GET /admin/solicitudes/filtrar?rango=all&estado=&ciudad=&q=&page=1
```

---

## 🐛 Problemas Conocidos y Soluciones

### Problema 1: Tabla vacía al cargar
**Causa:** El rango por defecto es `mes`, pero las tablas iniciales muestran solicitudes pendientes de `este mes`.
**Solución:** Asegurar que `index()` devuelve al menos un registro pendiente del mes actual.

### Problema 2: Total no coincide con KPI
**Causa:** Los KPI (`$aprobadas`, `$rechazadas`) están filtrados por mes, pero la búsqueda inicial muestra pendientes.
**Solución:** El texto "pendientes de revisión este mes" es correcto; si cambias a `rango=all`, verás más totales.

### Problema 3: Paginación se reinicia al filtrar
**Comportamiento:** Correcto. Cuando cambias un filtro, vuelves a página 1.
**Por qué:** `paginaActualSol = 1` se ejecuta en cada `filtrarSolicitudes()` para evitar "páginas vacías".

### Problema 4: JSON decode de datos_solicitud_arrendador
**Causa:** Los datos vienen como string JSON desde BD.
**Solución:** Se decodifica antes de devolver: `json_decode($datos, true) ?? []`

---

## 🚀 Mejoras Futuras

- [ ] Filtro por rangos de fechas personalizadas (datepicker)
- [ ] Exportar solicitudes a CSV
- [ ] Asignación de solicitudes a evaluadores
- [ ] Notificaciones en tiempo real (WebSocket)
- [ ] Historial de cambios de estado

---

## 📞 Soporte

Para problemas, verificar:
1. Que `selectRangoSol` existe en HTML
2. Que el parámetro `rango` llega al endpoint
3. La consola del navegador (`F12 → Console`) para errores de fetch
4. Las queries PDF en `app/Http/Controllers/Admin/SolicitudController.php`

---

**Última actualización:** 22 de Abril, 2026  
**Versión:** 2.0 (Con filtro de período)
