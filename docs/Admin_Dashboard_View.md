# SpotStay — Dashboard Admin 📊

## Resumen
Dashboard administrativo completo para SpotStay construido con **Laravel 13.4.0**, **Blade**, **Bootstrap 5.3.8** y **Vanilla JavaScript** (sin frameworks).

---

## 📁 Archivos Generados

| Archivo | Ruta | Propósito |
|---------|------|----------|
| **Layout Admin** | `resources/views/layouts/admin.blade.php` | Template base con topbar, navegación y estructura HTML |
| **Dashboard View** | `resources/views/admin/dashboard.blade.php` | Vista del dashboard con todos los componentes |
| **Estilos** | `public/css/admin/dashboard.css` | CSS completo del dashboard (sin Bootstrap de más) |
| **Scripts** | `public/js/admin/dashboard.js` | JavaScript vanilla para interactividad |
| **Documentación** | `README.md` | Este archivo |

---

## 🎨 Arquitectura del Dashboard

### 1. **Topbar (56px, fixed)**
Navegación superior con:
- **Logo SpotStay** con icono de casa (color azul #035498)
- **8 botones de navegación** con iconos Bootstrap:
  - 📊 Dashboard (activo por defecto)
  - 👥 Usuarios
  - 🏠 Propiedades
  - 📄 Alquileres
  - 📮 Solicitudes
  - ⚠️ Incidencias
  - 💳 Suscripciones
  - ⚙️ Configuración
- **Icono de campana** con badge (9 notificaciones)
- **Avatar + nombre admin**

### 2. **Hero Section**
- Saludo dinámico: "Buenos días, Admin 👋"
- Fecha del día: "Miércoles, 14 de abril de 2025"
- Fondo azul (#035498) con círculos decorativos

### 3. **KPI Grid (4 tarjetas)**
Cuatro tarjetas con números clave:
1. **USUARIOS REGISTRADOS**: 1.284 (icono azul)
2. **PROPIEDADES ACTIVAS**: 347 (icono verde)
3. **ALQUILERES PENDIENTES**: 23 (icono naranja)
4. **SOLICITUDES NUEVAS**: 9 (icono rojo)

Cada tarjeta tiene ícono, número grande y descripción.

### 4. **Central Grid (2 columnas: 3fr 2fr)**

#### Izquierda: Tabla de Alquileres Pendientes
- **Buscador local** en tiempo real (onblur + onkeyup)
- **4 filas de ejemplo** con estados:
  - Pendiente (naranja)
  - Activo (verde)
  - Rechazado (rojo)
- **Botones Aprobar y Rechazar** con AJAX
- Enlace "Ver todos →"

#### Derecha: Solicitudes Nuevas
- **Badge contador** (3 nuevas)
- **3 items** con avatar, nombre, ciudad y tiempo
- Botones "Revisar →"
- Footer con enlace a todas las solicitudes

### 5. **Inferior Grid (2 columnas: 1fr 1fr)**

#### Izquierda: Gráfico Donut
- **Chart.js** con datos hardcodeados
- **Leyenda interactiva** con colores
- Centro del donut muestra total de usuarios (1.284)
- Categorías: Inquilinos (687), Arrendadores (342), Miembros (166), Gestores (89)

#### Derecha: Timeline de Actividad Reciente
- **5 eventos hardcodeados**
- Línea vertical decorativa
- Cada evento con punto de color, texto y timestamp
- Colores según tipo: azul, verde, rojo

---

## ⚙️ Cómo Funciona Cada Sección

### Hero
```blade
<div class="hero-admin">
    <h1>Buenos días, Admin 👋</h1>
    <p>Miércoles, 14 de abril de 2025</p>
    <div class="hero-deco hero-deco-1"></div>
    <!-- Círculos decorativos -->
</div>
```
- Background: #035498
- Texto: Blanco
- Círculos decorativos con opacity para efecto visual

### Tarjetas KPI
Cada tarjeta es una `.kpi-card` con:
- Encabezado: Etiqueta + icono
- Número grande: El KPI
- Subtítulo: Descripción adicional

```blade
<div class="kpi-card">
    <div class="kpi-header">
        <span class="kpi-label">USUARIOS REGISTRADOS</span>
        <div class="kpi-icon kpi-icon-blue"><i class="bi bi-people"></i></div>
    </div>
    <div class="kpi-numero">1.284</div>
    <div class="kpi-sub">usuarios en total</div>
</div>
```

### Tabla de Alquileres
```blade
<table class="tabla-admin" id="tablaAlquileres">
    <thead>
        <tr><th>PROPIEDAD</th><th>INQUILINO</th><th>ESTADO</th><th>ACCIÓN</th></tr>
    </thead>
    <tbody id="tbodyAlquileres">
        <tr data-id="1">
            <td>Calle Mayor 14, Madrid</td>
            <td>Laura Martínez</td>
            <td><span class="badge-estado badge-pendiente">Pendiente</span></td>
            <td>
                <div class="acciones-tabla">
                    <button class="btn-aprobar" data-id="1">✓ Aprobar</button>
                    <button class="btn-rechazar" data-id="1">✕ Rechazar</button>
                </div>
            </td>
        </tr>
    </tbody>
</table>
```

---

## 🔄 AJAX: Botones Aprobar y Rechazar

### Flujo Completo
1. User hace click en **"✓ Aprobar"** o **"✕ Rechazar"**
2. JavaScript captura `data-id` del botón
3. **fetch POST** a `/admin/alquiler/{id}/aprobar` o `/admin/alquiler/{id}/rechazar`
4. Servidor devuelve JSON: `{ success: true, message: "..." }`
5. **DOM se actualiza**:
   - Badge cambia a "Activo" o "Rechazado"
   - Botones se reemplazan con "—" (sin acción)

### Código JavaScript
```javascript
function aprobarAlquiler(id) {
    var url = '/admin/alquiler/' + id + '/aprobar';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Actualizar badge y botones en el DOM
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            var badgeElement = tr.querySelector('.badge-estado');
            badgeElement.textContent = 'Activo';
            badgeElement.className = 'badge-estado badge-activo';
            // ...
        }
    })
    .catch(function(error) {
        console.error('Error en fetch:', error);
    });
}
```

**Nota**: Se usa **fetch + .then()**, NUNCA async/await.

---

## 🔍 Buscador de Tabla

### Funcionamiento
- **Filtro local** (sin servidor)
- Se activa en:
  - **onblur**: Cuando se pierde el foco
  - **onkeyup**: Si el input está vacío, muestra todas las filas

### Búsqueda en
- Columna 1: **PROPIEDAD**
- Columna 2: **INQUILINO**

### Código JavaScript
```javascript
function filtrarTabla(termino) {
    var tbody = document.getElementById('tbodyAlquileres');
    var filas = tbody.querySelectorAll('tr');
    
    if (termino === '') {
        // Mostrar todas
        for (var i = 0; i < filas.length; i++) {
            filas[i].style.display = 'table-row';
        }
        return;
    }
    
    var terminoLower = termino.toLowerCase();
    
    // Ocultar filas que no coincidan
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var celdas = fila.querySelectorAll('td');
        var coincide = false;
        
        for (var j = 0; j < celdas.length; j++) {
            if (j === 0 || j === 1) { // Propiedad e Inquilino
                var texto = celdas[j].textContent.toLowerCase();
                if (texto.indexOf(terminoLower) !== -1) {
                    coincide = true;
                    break;
                }
            }
        }
        
        fila.style.display = coincide ? 'table-row' : 'none';
    }
}
```

---

## 📊 Gráfico Donut (Chart.js)

### Datos Hardcodeados
```javascript
var ctx = document.getElementById('donutChart').getContext('2d');

var donutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Inquilinos', 'Arrendadores', 'Miembros', 'Gestores'],
        datasets: [{
            data: [687, 342, 166, 89],
            backgroundColor: ['#1AA068', '#035498', '#94A3B8', '#CBD5E1'],
            borderColor: '#FFFFFF',
            borderWidth: 2
        }]
    },
    options: {
        cutout: '72%',  // Tamaño del "agujero" del donut
        responsive: false,
        plugins: {
            legend: { display: false },
            tooltip: { /* ... */ }
        },
        animation: false
    }
});
```

### Cómo Cambiar Datos
Modificar en `public/js/admin/dashboard.js`, función `iniciarDonut()`:
- **data**: [687, 342, 166, 89] → Nuevos números
- **labels**: ['Inquilinos', ...] → Nuevas categorías
- **backgroundColor**: Colores de los segmentos

---

## ➕ Cómo Añadir Nuevos Items al Timeline

### En la Vista (Blade)
```blade
<div class="timeline-item">
    <div class="timeline-punto" style="background: #035498;"></div>
    <div class="timeline-contenido">
        <p class="timeline-texto">Nuevo evento aquí</p>
        <span class="timeline-hora">hace X tiempo</span>
    </div>
</div>
```

**Colores predefinidos**:
- Azul: `#035498` (eventos de propiedad/usuario)
- Verde: `#1AA068` (eventos positivos)
- Rojo: `#EF4444` (incidencias)

---

## 🛣️ Rutas Necesarias en el Controlador

El JavaScript espera estas rutas y respuestas JSON:

### 1. Aprobar Alquiler
```
POST /admin/alquiler/{id}/aprobar

Respuesta esperada:
{
    "success": true,
    "message": "Alquiler aprobado correctamente"
}
```

### 2. Rechazar Alquiler
```
POST /admin/alquiler/{id}/rechazar

Respuesta esperada:
{
    "success": true,
    "message": "Alquiler rechazado correctamente"
}
```

### Ejemplo en Laravel (Controller)
```php
public function aprobar($id)
{
    $alquiler = Alquiler::findOrFail($id);
    $alquiler->update(['estado' => 'aprobado']);
    
    return response()->json(['success' => true, 'message' => 'Aprobado']);
}

public function rechazar($id)
{
    $alquiler = Alquiler::findOrFail($id);
    $alquiler->update(['estado' => 'rechazado']);
    
    return response()->json(['success' => true, 'message' => 'Rechazado']);
}
```

---

## 🎯 Stack Tecnológico

| Tecnología | Versión | Uso |
|-----------|---------|-----|
| Laravel | 13.4.0 | Framework backend |
| PHP | 8.2+ | Lenguaje server |
| Blade | 13.x | Motor de plantillas |
| Bootstrap | 5.3.8 | CSS framework (CDN) |
| Bootstrap Icons | 1.11.3 | Iconos (CDN) |
| Chart.js | Última | Gráficos (CDN) |
| Vanilla JS | ES5 | Interactividad (sin frameworks) |

---

## 📋 Reglas del Proyecto (Respetadas)

✅ **Separación de archivos**: CSS en `public/css/admin/`, JS en `public/js/admin/`
✅ **AJAX con fetch + .then()**: Nunca async/await
✅ **Eventos sin addEventListener**: Solo `.onclick`, `.onblur`, etc. en `window.onload`
✅ **Código Vanilla jQuery-style**: `var`, sin arrow functions, sin destructuring
✅ **Rutas con asset()**: Todas usan `{{ asset(...) }}`
✅ **Bootstrap CDN**: v5.3.8
✅ **Chart.js CDN**: Desde jsdelivr
✅ **Sin Livewire, Vue, Alpine.js**: Puro HTML y JS

---

## 🚀 Cómo Usar

### 1. Registrar una ruta en Laravel
```php
// routes/web.php
Route::middleware(['admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/alquiler/{id}/aprobar', [AdminController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AdminController::class, 'rechazar']);
});
```

### 2. En el Controller
```php
public function dashboard()
{
    return view('admin.dashboard');
}
```

### 3. Los archivos ya están listos
- ✅ `resources/views/layouts/admin.blade.php`
- ✅ `resources/views/admin/dashboard.blade.php`
- ✅ `public/css/admin/dashboard.css`
- ✅ `public/js/admin/dashboard.js`

---

## 🎨 Colores del Proyecto

| Color | Hex | Uso |
|-------|-----|-----|
| Azul Primario | #035498 | Logo, activos, datos principales |
| Verde Secundario | #1AA068 | Estados positivos, éxitos |
| Naranja | #D97706 | Alertas, pendientes |
| Rojo | #EF4444 | Rechazos, errores, incidencias |
| Fondo | #F0F4F8 | Background general |
| Tarjetas | #FFFFFF | Cards, modales |
| Texto | #111827 | Texto principal |
| Gris | #6B7280 | Texto secundario |
| Bordes | #E5E7EB | Separadores |

---

## 📱 Responsive Design

- **Desktop (1024px+)**: Grid 4 columnas (KPI), 3fr 2fr (central)
- **Tablet (768px-1024px)**: Grid 2 columnas (KPI), 1 columna (central)
- **Mobile (<768px)**: Grid 1 columna en todo, padding reducido

---

## 🔒 Seguridad

- **CSRF Token**: Se obtiene de `meta[name=csrf-token]` y se envía en cada POST
- **Headers POST**: `Content-Type: application/json` + `X-CSRF-TOKEN`
- **Validación Server**: El controlador debe validar que el usuario es admin

---

## 🐛 Debugging

### Console Logs
El JS incluye `console.error()` para fallos en fetch:
```javascript
.catch(function(error) {
    console.error('Error en fetch:', error);
});
```

Abre DevTools (F12) para ver errores de conexión o respuesta.

### Inspeccionar Elementos
Usa DevTools para verificar que los datos `data-id` se asignan correctamente:
```html
<button class="btn-aprobar" data-id="1">✓ Aprobar</button>
```

---

## ✨ Personalización

### Cambiar el Nombre del Admin
```blade
<span class="admin-nombre">Tu Nombre</span>
```

### Cambiar Números KPI
```blade
<div class="kpi-numero">Nuevo número aquí</div>
```

### Añadir Más Filas a la Tabla
```blade
<tr data-id="5">
    <td>Nueva propiedad</td>
    <td>Nuevo inquilino</td>
    <td><span class="badge-estado badge-pendiente">Pendiente</span></td>
    <td>
        <div class="acciones-tabla">
            <button class="btn-aprobar" data-id="5">✓ Aprobar</button>
            <button class="btn-rechazar" data-id="5">✕ Rechazar</button>
        </div>
    </td>
</tr>
```

---

## 📞 Soporte

Si el dashboard no funciona:
1. Verificar que Bootstrap 5, Icon y Chart.js se cargan desde CDN
2. Verificar que CSRF token existe en el `<head>`
3. Verificar rutas POST en el controlador
4. Abrir DevTools (F12) y buscar errores en Console
5. Verificar que `public/js/admin/dashboard.js` carga sin errores

---

## 📝 Changelog

**v1.0** — Lanzamiento inicial
- ✅ Layout admin con topbar
- ✅ Dashboard con 4 KPI
- ✅ Tabla de alquileres con AJAX
- ✅ Panel de solicitudes
- ✅ Gráfico donut con Chart.js
- ✅ Timeline de actividad
- ✅ Responsive design

---

**SpotStay Admin Dashboard** — Desarrollado con ❤️ siguiendo las reglas del proyecto.
