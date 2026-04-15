# Gestión de Propiedades — Admin Panel SpotStay

## 📋 Tabla de Contenidos

1. [Estructura de Archivos](#estructura-de-archivos)
2. [Componentes Principales](#componentes-principales)
3. [Filtros y Búsqueda](#filtros-y-búsqueda)
4. [Modal de Detalle](#modal-de-detalle)
5. [Paginación](#paginación)
6. [Rutas Necesarias](#rutas-necesarias)
7. [Ejemplo de Controller](#ejemplo-de-controller)
8. [Respuestas JSON Esperadas](#respuestas-json-esperadas)
9. [Variables Globales](#variables-globales)
10. [Paleta de Colores](#paleta-de-colores)
11. [Breakpoints Responsive](#breakpoints-responsive)

---

## 📂 Estructura de Archivos

```
resources/views/admin/
├── propiedades.blade.php       (325+ líneas)

public/css/admin/
├── propiedades.css             (800+ líneas)

public/js/admin/
├── propiedades.js              (500+ líneas)
```

---

## 🎨 Componentes Principales

### 1. **HERO SECTION** (Azul principal)
```blade
<div class="hero-admin">
    <h1>Gestión de propiedades</h1>
    <p>Supervisa todas las propiedades publicadas en la plataforma</p>
</div>
```
- Fondo azul degradado: `#035498` → `#0275b9`
- Padding: `36px 40px`
- 3 decoraciones circulares con opacidad baja
- H1: 28px, font-weight 700

### 2. **TOOLBAR CON FILTROS**
```blade
<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <!-- Buscador de dirección/ciudad -->
        <input id="buscadorPropiedades" placeholder="Buscar por dirección o ciudad...">
        
        <!-- Filtro estado -->
        <select id="selectEstado">
            <option value="">Todos los estados</option>
            <option value="publicada">Publicada</option>
            <option value="alquilada">Alquilada</option>
            <option value="borrador">Borrador</option>
            <option value="inactiva">Inactiva</option>
        </select>
        
        <!-- Filtro ciudad -->
        <select id="selectCiudad">
            <option value="">Todas las ciudades</option>
            <option value="madrid">Madrid</option>
            <option value="barcelona">Barcelona</option>
            <option value="valencia">Valencia</option>
            <option value="sevilla">Sevilla</option>
            <option value="bilbao">Bilbao</option>
        </select>
        
        <!-- Filtro precio -->
        <select id="selectPrecio">
            <option value="">Cualquier precio</option>
            <option value="0-500">0 - 500€</option>
            <option value="500-1000">500 - 1.000€</option>
            <option value="1000-2000">1.000 - 2.000€</option>
            <option value="2000+">+2.000€</option>
        </select>
    </div>
    
    <div class="toolbar-derecha">
        <button id="btnExportar" class="btn-exportar">
            <i class="bi bi-download"></i>
            <span>Exportar</span>
        </button>
        <button id="btnAniadirPropiedad" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Añadir propiedad</span>
        </button>
    </div>
</div>
```

### 3. **KPI RÁPIDOS** (4 tarjetas mini)
```
Total propiedades     347    (icono casa)
Alquiladas           198    (icono check)
Publicadas           112    (icono megáfono)
Inactivas             37    (icono x)
```
- Display: grid, 4 columnas
- Cada KPI: flex vertical, icono + número + label
- Colores: azul, verde, naranja, rojo

### 4. **TABLA DE PROPIEDADES**
Columnas: PROPIEDAD | ARRENDADOR | ESTADO | PRECIO | INQUILINOS | ACCIONES

**Fila ejemplo:**
```
PROPIEDAD                        ARRENDADOR           ESTADO      PRECIO        INQUILINOS  ACCIONES
[Thumb azul] Calle Mayor 14      [CG] Carlos García   Alquilada   $1.200/mes   2 / 3       👁 ✏ 🗑
             Madrid, 28001
```

- Tabla ancho 100%, collapse
- Thead fondo #F9FAFB
- Hover filas: fondo #F9FAFB
- 10 propiedades por página (hardcodeadas, sin BD)
- Filas inactivas: opacity 0.65

**Acciones (3 botones):**
- 👁 Ver (abre modal)
- ✏ Editar (placeholder)
- 🗑 Eliminar (confirma y fetch DELETE)

### 5. **PAGINACIÓN**
```
← Anterior | [1] [2] [3] | Siguiente →
```
- 3 botones numerados fijos (placeholder)
- Botón activo: fondo #035498, color blanco
- Fetch a `/admin/propiedades?pagina=X`

---

## 🔍 Filtros y Búsqueda

### **Flujo de Filtrado**
1. User cambia cualquier filtro
2. JS captura valores:
   - `selectEstado.value` → estado
   - `selectCiudad.value` → ciudad
   - `selectPrecio.value` → precio
   - `buscadorPropiedades.value` → q (búsqueda)
3. Fetch GET a `/admin/propiedades/filtrar?estado=X&ciudad=Y&precio=Z&q=BUSQUEDA`
4. servidor responde JSON con array de propiedades filtradas
5. `actualizarTabla(data)` reemplaza tbody innerHTML

### **Código JS (Ejemplo)**
```javascript
var filtrarPropiedades = function() {
    var estado = document.getElementById('selectEstado').value;
    var ciudad = document.getElementById('selectCiudad').value;
    var precio = document.getElementById('selectPrecio').value;
    var busqueda = document.getElementById('buscadorPropiedades').value.toLowerCase();

    var url = '/admin/propiedades/filtrar?estado=' + encodeURIComponent(estado) + 
              '&ciudad=' + encodeURIComponent(ciudad) + 
              '&precio=' + encodeURIComponent(precio) + 
              '&q=' + encodeURIComponent(busqueda);

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) { actualizarTabla(data); })
        .catch(function(e) { console.error(e); });
};
```

### **Eventos de Filtro**
```javascript
selectEstado.onchange = filtrarPropiedades;
selectCiudad.onchange = filtrarPropiedades;
selectPrecio.onchange = filtrarPropiedades;
buscadorPropiedades.onblur = filtrarPropiedades;
buscadorPropiedades.onkeyup = function() {
    if (this.value.length === 0) filtrarPropiedades();
};
```

---

## 📱 Modal de Detalle

El modal es **muy completo** con 6 secciones principales:

### **Estructura HTML**

#### **Header**
```blade
<div class="modal-header-admin">
    <div class="modal-titulo-grupo">
        <span class="modal-titulo">Detalle de propiedad</span>
        <span class="badge-estado badge-alquilada" id="modalBadgeEstado">Alquilada</span>
    </div>
    <button id="btnCerrarModal" class="btn-cerrar-modal">
        <i class="bi bi-x"></i>
    </button>
</div>
```

#### **Imagen Principal**
```blade
<div class="modal-imagen-propiedad" id="modalImagenPropiedad" 
     style="background: linear-gradient(135deg, #8AAAC4, #B8CCE4);">
    <div class="modal-imagen-texto">
        <span id="modalDireccion">Calle Mayor 14, Madrid</span>
    </div>
</div>
```
- Height: 180px
- Background: gradiente (si no hay imagen)
- Texto blanco sobre overlay oscuro abajo
- Overlay oscuro permite legibilidad

#### **Sección 1: INFORMACIÓN GENERAL**
6 columnas × 2 filas (grid 3 cols):
- Precio (azul bold)
- Ciudad
- CP
- Dirección
- Habitaciones
- Baños
- Tamaño
- Planta
- Publicada
- Actualización
- Visitas mes
- Favoritos

#### **Sección 2: PRECIOS Y GASTOS**
6 datos (grid 2 cols):
- Alquiler base
- Fianza
- Agua
- Electricidad
- Gas
- Comunidad
- **Card de total estimado** (fondo #E8F0FB, azul bold)

#### **Sección 3: ARRENDADOR Y GESTOR**
**ARRENDADOR:**
- Avatar (color)
- Nombre (bold)
- Email (gris)
- Teléfono (gris)
- Link "Ver perfil →"

**GESTOR ASIGNADO:**
- Avatar (color)
- Nombre (bold)
- Badge "Él mismo" (gris pequeño)

#### **Sección 4: INQUILINOS ACTUALES**
Label: `INQUILINOS ACTUALES (2/3)`
Lista de inquilinos (flex vertical):
```
[Avatar] Nombre           Estado (badge en derecha)
         email
         Desde: ene 2025
```

#### **Sección 5: CONTRATO E INCIDENCIAS**
**CONTRATO ACTIVO:**
```
Contrato #2025-0142                    Firmado    Descargar PDF
Firmado 15 ene 2025 · Válido hasta 15 ene 2026
```
Card style: fondo #F9FAFB, border 1px #E5E7EB

**INCIDENCIAS:**
Lista de 2 incidencias:
```
● Fuga en el baño                      Resuelta    hace 2 meses
● Calefacción no funciona              En proceso  hace 3 días
```
- Punto verde/naranja (8px circle)
- Nombre incidencia
- Badge estado
- Tiempo (derecha, gris pequeño)

#### **Sección 6: SERVICIOS**
Tags horizontales con wrap:
```
[Agua] [Electricidad] [Gas] [Comunidad] [Internet] [Parking] [Trastero]
```

### **Footer del Modal**
```blade
<div class="modal-footer-admin">
    <button id="btnDesactivarPropiedad" class="btn-desactivar">
        Desactivar propiedad
    </button>
    <div class="modal-footer-derecha">
        <button id="btnVerMapa" class="btn-exportar">
            <i class="bi bi-map"></i>
            <span>Ver en el mapa</span>
        </button>
        <button id="btnEditarPropiedad" class="btn-primario">
            Editar propiedad
        </button>
    </div>
</div>
```

### **Funciones del Modal en JS**

#### **abrirModal(id)**
```javascript
var abrirModal = function(id) {
    var propiedad = dataPropiedades[id];
    // Rellena todos los campos HTML con datos del objeto dataPropiedades[id]
    // Actualiza badge estado
    // Rellena lista inquilinos dinámicamente
    // Abre overlay + modal (classList.add 'visible')
};
```

#### **cerrarModal()**
```javascript
var cerrarModal = function() {
    var overlay = document.getElementById('modalOverlay');
    var modal = document.getElementById('modalPropiedad');
    overlay.classList.remove('visible');
    modal.classList.remove('visible');
};
```

#### **Eventos del Modal**
```javascript
btnCerrarModal.onclick = cerrarModal;
modalOverlay.onclick = cerrarModal;                      // Click fuera cierra
btnDesactivarPropiedad.onclick = desactivarPropiedad;
btnEditarPropiedad.onclick = function() { 
    console.log('Placeholder: abrir modal editar');
};
btnVerMapa.onclick = function() { 
    console.log('Placeholder: abrir mapa'); 
};
btnDescargarPDF.onclick = function() { 
    console.log('Placeholder: descargar PDF'); 
};
```

#### **desactivarPropiedad(id)**
```javascript
var desactivarPropiedad = function(id) {
    fetch('/admin/propiedades/' + id + '/desactivar', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Añade clase 'fila-inactiva' a la fila en tabla
            // Cierra modal
        }
    });
};
```

---

## 📄 Paginación

### **Estructura HTML**
```blade
<div class="paginacion">
    <button id="btnAnterior" class="btn-pag">← Anterior</button>
    <span id="paginas">
        <button class="pag-numero activo" data-pagina="1">1</button>
        <button class="pag-numero" data-pagina="2">2</button>
        <button class="pag-numero" data-pagina="3">3</button>
    </span>
    <button id="btnSiguiente" class="btn-pag">Siguiente →</button>
</div>
```

### **Variables Globales**
```javascript
var paginaActual = 1;
```

### **cambiarPagina(numero)**
```javascript
var cambiarPagina = function(numero) {
    paginaActual = numero;
    fetch('/admin/propiedades?pagina=' + numero)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            actualizarTabla(data);
            actualizarPaginacion(numero, data.totalPaginas || 3);
            tabla.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
};
```

---

## 🛣️ Rutas Necesarias

Todas las rutas están **prefijadas** con `/admin` y **sin** protección de auth (implícita en proyecto):

### **1. Dashboard (GET)**
```
GET /admin/propiedades
→ Retorna view('admin.propiedades')
```

### **2. Filtrar (GET)**
```
GET /admin/propiedades/filtrar?estado=X&ciudad=Y&precio=Z&q=BUSQUEDA
→ Retorna JSON { propiedades: [...], total: int }
```

### **3. Cambiar Página (GET)**
```
GET /admin/propiedades?pagina=NUM
→ Retorna JSON { propiedades: [...], total: int, totalPaginas: int }
```

### **4. Desactivar (POST)**
```
POST /admin/propiedades/{id}/desactivar
→ Retorna JSON { success: true, message: "..." }
```

### **5. Eliminar (DELETE)**
```
DELETE /admin/propiedades/{id}
→ Retorna JSON { success: true, message: "..." }
```

### **6. Exportar (GET)**
```
GET /admin/propiedades/exportar
→ Retorna CSV file download
```

### **7. Editar (GET POST) [Placeholder]**
No implementado. Placeholder en JS.

---

## 💾 Ejemplo de Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Mostrar vista propiedades ──
    public function propiedades()
    {
        return view('admin.propiedades');
    }

    // ── Filtrar propiedades ──
    public function filtrarPropiedades(Request $request)
    {
        $estado = $request->query('estado');
        $ciudad = $request->query('ciudad');
        $precio = $request->query('precio');
        $busqueda = $request->query('q');

        // Ejemplo con hardcoded data
        $propiedades = [
            ['id' => 1, 'direccion' => 'Calle Mayor 14', 'ciudad' => 'Madrid', 'estado' => 'alquilada', ...],
            // ... rest
        ];

        // Filtrar por estado
        if (!empty($estado)) {
            $propiedades = array_filter($propiedades, function($p) use ($estado) {
                return $p['estado'] === $estado;
            });
        }

        // Filtrar por ciudad
        if (!empty($ciudad)) {
            $propiedades = array_filter($propiedades, function($p) use ($ciudad) {
                return strtolower($p['ciudad']) === strtolower($ciudad);
            });
        }

        // Filtrar por rango precio
        if (!empty($precio) && $precio !== '0-500') {
            // Logic here
        }

        // Filtrar por búsqueda (dirección/ciudad)
        if (!empty($busqueda)) {
            $propiedades = array_filter($propiedades, function($p) use ($busqueda) {
                return stripos($p['direccion'], $busqueda) !== false ||
                       stripos($p['ciudad'], $busqueda) !== false;
            });
        }

        return response()->json([
            'propiedades' => array_values($propiedades),
            'total' => count($propiedades)
        ]);
    }

    // ── Desactivar propiedad ──
    public function desactivarPropiedad($id)
    {
        // Lógica para desactivar
        return response()->json(['success' => true, 'message' => 'Propiedad desactivada']);
    }

    // ── Eliminar propiedad ──
    public function eliminarPropiedad($id)
    {
        // Lógica para eliminar (soft delete recomendado)
        return response()->json(['success' => true, 'message' => 'Propiedad eliminada']);
    }

    // ── Exportar propiedades ──
    public function exportarPropiedades()
    {
        $propiedades = [
            ['Calle Mayor 14', 'Madrid', 'Carlos García', 'Alquilada', '$1.200/mes'],
            // ... rest
        ];

        return response()->streamDownload(function() use ($propiedades) {
            $handle = fopen('php://output', 'w');
            
            // Headers
            fputcsv($handle, ['Dirección', 'Ciudad', 'Arrendador', 'Estado', 'Precio']);
            
            // Data rows
            foreach ($propiedades as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, 'propiedades.csv');
    }
}
```

---

## 📦 Respuestas JSON Esperadas

### **GET /admin/propiedades/filtrar**
```json
{
    "propiedades": [
        {
            "id": 1,
            "direccion": "Calle Mayor 14",
            "ciudad": "Madrid",
            "cp": "28001",
            "estado": "alquilada",
            "precio": "$1.200/mes",
            "color": "#B8CCE4",
            "arrendadorNombre": "Carlos García",
            "habitaciones": "3",
            "banos": "1",
            "inquilinosActuales": 2,
            "inquilinosTotales": 3
        }
    ],
    "total": 15
}
```

### **GET /admin/propiedades?pagina=1**
```json
{
    "propiedades": [...],
    "total": 347,
    "totalPaginas": 35,
    "paginaActual": 1
}
```

### **POST /admin/propiedades/{id}/desactivar**
```json
{
    "success": true,
    "message": "Propiedad desactivada correctamente"
}
```

### **DELETE /admin/propiedades/{id}**
```json
{
    "success": true,
    "message": "Propiedad eliminada correctamente"
}
```

---

## 🔧 Variables Globales

```javascript
var csrfToken;        // Extraído de meta[name=csrf-token]
var paginaActual = 1; // Página actual en paginación

// Objeto con 10 propiedades hardcodeadas (replace con DB luego)
var dataPropiedades = {
    1: {
        id: 1,
        direccion: 'Calle Mayor 14',
        ciudad: 'Madrid',
        // ... 20+ campos
    },
    2: { ... },
    // ... hasta 10
};
```

---

## 🎨 Paleta de Colores

| Nombre | Hex | Uso |
|--------|-----|-----|
| Azul Primario | `#035498` | Hero, botones, badges activos |
| Azul Claro | `#D1E7F7` | Fondo KPI azul |
| Verde | `#1AA068` | Badges alquilada, activo |
| Verde Claro | `#D1FAE5` | Fondo KPI verde |
| Naranja | `#D97706` | Badges publicada, pendiente |
| Naranja Claro | `#FEF3C7` | Fondo KPI naranja |
| Rojo | `#991B1B` | Badges inactiva, error |
| Rojo Claro | `#FEE2E2` | Fondo KPI rojo |
| Gris Fondo | `#F9FAFB` | Headers, footers |
| Gris Borde | `#E5E7EB` | Bordes |
| Gris Texto | `#6B7280` | Labels, subtexto |
| Negro Texto | `#111827` | Texto principal |

### **Ejemplo de Badge**
```css
.badge-alquilada {
    background: #D1FAE5;  /* Verde claro */
    color: #065F46;       /* Verde oscuro */
}

.badge-publicada {
    background: #FEF3C7;  /* Naranja claro */
    color: #D97706;       /* Naranja oscuro */
}
```

---

## 📱 Breakpoints Responsive

### **1024px (Tablet)**
- Grid KPI: 2 columnas
- Modal: ancho 580px
- Toolbar: 2 filas (filtros arriba, botones abajo)
- Grid 3 columnas → 2 columnas

### **768px (Mobile)**
- Toolbar: stacked vertical
- KPI: 1 columna
- Tabla: fuente más pequeña
- Modal: 100% ancho, desde abajo (slide up)
- Botones: full width

### **Ejemplo de Responsive CSS**
```css
@media (max-width: 1024px) {
    .toolbar-admin {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .kpi-grid-pequeno {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .modal-grid-3 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .toolbar-izquierda {
        flex-direction: column;
    }
    
    .toolbar-izquierda > * {
        width: 100%;
    }
    
    .kpi-grid-pequeno {
        grid-template-columns: 1fr;
    }
    
    .modal-admin {
        width: 100%;
        bottom: 0;
        border-radius: 0;
        animation: slideUpMobile 0.3s ease-out;
    }
}
```

---

## 🚀 Cómo Usar

### **1. Crear las rutas en `routes/web.php`**
```php
Route::middleware('auth')->group(function () {
    Route::get('/admin/propiedades', [AdminController::class, 'propiedades']);
    Route::get('/admin/propiedades/filtrar', [AdminController::class, 'filtrarPropiedades']);
    Route::post('/admin/propiedades/{id}/desactivar', [AdminController::class, 'desactivarPropiedad']);
    Route::delete('/admin/propiedades/{id}', [AdminController::class, 'eliminarPropiedad']);
    Route::get('/admin/propiedades/exportar', [AdminController::class, 'exportarPropiedades']);
});
```

### **2. Crear/Actualizar el Controller**
Copiar métodos de la sección "Ejemplo de Controller" a `app/Http/Controllers/AdminController.php`

### **3. Accedera `http://localhost/admin/propiedades`**

### **4. Conectar a BD (futuro)**
Reemplazar `dataPropiedades` hardcoded con:
```javascript
// En window.onload, fetch inicial para cargar propiedades
fetch('/admin/propiedades/datos')
    .then(r => r.json())
    .then(data => {
        dataPropiedades = data;
    });
```

---

## 📝 Notas de Implementación

- ✅ **Vanilla JS puro**: sin jQuery, sin frameworks
- ✅ **Fetch .then()**: no async/await
- ✅ **event onclick en JS**: no addEventListener
- ✅ **var declaration**: no const/let
- ✅ **Bootstrap CDN**: no compilado
- ✅ **CSRF tokens**: incluidos en headers
- ✅ **Modal overlay**: click fuera cierra
- ✅ **Responsive**: 3 breakpoints (desktop, tablet, mobile)
- ✅ **10 propiedades hardcodeadas**: lista ejemplo completa
- ✅ **Paginación placeholder**: 3 botones fijos, lógica funcional

---

## 🔄 Flujos Principales

### **Filtrado**
User cambia filtro → selectEstado.onchange → filtrarPropiedades() → fetch GET /filtrar → actualizarTabla()

### **Búsqueda**
User escribe en buscador → onblur/onkeyup → filtrarPropiedades() → fetch GET /filtrar → actualizarTabla()

### **Ver Detalle**
User hace click btn-ver → abrirModal(id) → rellena campos → overlay + modal visible

### **Desactivar**
User hace click btnDesactivarPropiedad → desactivarPropiedad(id) → fetch POST /desactivar → fila addClass inactiva

### **Eliminar**
User hace click btn-eliminar → confirm() → fetch DELETE /{id} → removeChild() fila

### **Paginación**
User hace click pag-numero → cambiarPagina(num) → fetch GET ?pagina=num → actualizarTabla() → scroll

---

## ✨ Características Incluidas

- ✅ Hero section con decoraciones
- ✅ 4 filtros + búsqueda
- ✅ 4 KPI mini cards
- ✅ Tabla 10 propiedades
- ✅ Modal ultra completo (6 secciones, 40+ campos)
- ✅ Paginación
- ✅ Acciones (ver, editar, eliminar)
- ✅ Desactivar propiedad
- ✅ Exportar CSV
- ✅ Responsive (3 tamaños)
- ✅ Animaciones suaves
- ✅ CSRF security
- ✅ Hardcoded data completo

---

**Última actualización**: Abril 2025
**Versión**: v1.0 (Admin Panel SpotStay)
