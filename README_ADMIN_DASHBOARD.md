# SpotStay Admin Dashboard — Documentación Técnica

Documentación sobre la funcionalidad del dashboard de administrador, incluyendo el layout con dropdown y navegación.

---

## 📋 Descripción General

El dashboard de SpotStay admin incluye:

- **Topbar fija**: Navegación entre secciones del admin (panel, usuarios, propiedades, etc.)
- **Dropdown funcional**: Botón Admin con menú desplegable para cerrar sesión
- **Campana de notificaciones**: Badge con contador
- **Contenedor de contenido**: Área donde se cargan las diferentes páginas del admin

---

## 🎯 Archivo de Layout

### `resources/views/layouts/admin.blade.php`

**Características**:
- Extiende el layout base de la aplicación
- Incluye topbar sticky con z-index 100
- CSS externo: `asset('css/admin/layout.css')`
- JavaScript externo: `asset('js/admin/layout.js')`
- CDN: Bootstrap 5.3.8, Bootstrap Icons, Chart.js

**Estructura HTML**:

```html
<div class="topbar">
    <!-- Zona izquierda: Logo -->
    <div class="topbar-izq">
        <svg>...</svg>
        <span>SpotStay</span>
    </div>
    
    <!-- Zona central: Botones de navegación -->
    <div class="topbar-central">
        <button class="btn-nav-icon activo" data-ruta="/admin/dashboard">
            <i class="bi bi-grid"></i>
        </button>
        <!-- ... más botones ... -->
    </div>
    
    <!-- Zona derecha: Campana + Dropdown Admin -->
    <div class="topbar-der">
        <!-- Campana de notificaciones -->
        <div class="campana-container">
            <i class="bi bi-bell icon-campana"></i>
            <span class="badge-campana">9</span>
        </div>
        
        <!-- Dropdown Admin -->
        <div class="admin-container" id="adminContainer">
            <div class="avatar-admin">A</div>
            <span class="admin-nombre">Admin</span>
            <i class="bi bi-chevron-down chevron-admin"></i>
            
            <div class="admin-dropdown" id="adminDropdown">
                <button class="dropdown-item">Perfil</button>
                <button class="dropdown-item">Configuración</button>
                <button class="dropdown-item dropdown-item-logout" id="btnLogout">
                    Cerrar sesión
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 🎨 Estilos CSS

### `public/css/admin/layout.css`

**Secciones principales**:

#### 1. **Topbar**
- Altura: 56px
- Fondo: blanco
- Position: sticky (se queda al scrollear)
- Z-index: 100

```css
.topbar {
    height: 56px;
    background: white;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 24px;
    position: sticky;
    top: 0;
    z-index: 100;
}
```

#### 2. **Botones de Navegación**
- Tamaño: 38x38px
- Icono color: #8C93A0
- Hover: fondo #F1F5F9
- Activo: fondo #EEF4FF, color #035498, punto en la base

```css
.btn-nav-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-nav-icon.activo::after {
    content: '';
    position: absolute;
    bottom: 4px;
    left: 50%;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #035498;
}
```

#### 3. **Dropdown Admin**
- Position: fixed (relativo al viewport, sobre todo)
- Aparece debajo de la topbar
- Transición instantánea (display: none/block)
- Z-index: 9999 (siempre en la parte superior)

```css
.admin-dropdown {
    position: fixed;
    top: 56px;
    right: 24px;
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    min-width: 200px;
    display: none;
    z-index: 9999;
}

.admin-dropdown.visible {
    display: block;
}
```

#### 4. **Items del Dropdown**
- Padding: 12px 16px
- Hover: fondo #F3F4F6
- Logout: color rojo, borde superior

```css
.dropdown-item {
    padding: 12px 16px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    width: 100%;
    text-align: left;
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: #F3F4F6;
}

.dropdown-item-logout {
    color: #EF4444;
    border-top: 1px solid #F3F4F6;
}
```

---

## ⚙️ Funcionalidad JavaScript

### `public/js/admin/layout.js`

**Variables Globales**:

```javascript
var csrfToken = document.querySelector('meta[name="csrf-token"]')
    .getAttribute('content');
```

Se obtiene del meta-tag en el `<head>` de la página para las peticiones POST.

**Inicialización**:

```javascript
window.onload = function() {
    asignarEventosAdmin();
};
```

Se ejecuta cuando la página ha terminado de cargar completamente (incluyendo imágenes, estilos, etc.).

---

### **Función: `asignarEventosAdmin()`**

Se ejecuta cuando la página carga completamente (`window.onload`).

**¿Qué hace?**:
1. Obtiene referencias a los elementos: `adminContainer`, `adminDropdown`, `btnLogout`
2. Asigna eventos a cada uno

**Nota**: Se usa `window.onload` en lugar de `addEventListener` (per las normas del proyecto).

---

### **Eventos Asignados**

#### 1. **Click en admin-container (Avatar + Nombre)**

```javascript
adminContainer.onclick = function(e) {
    e.stopPropagation();
    if (adminDropdown.classList.contains('visible')) {
        adminDropdown.classList.remove('visible');
    } else {
        adminDropdown.classList.add('visible');
    }
};
```

**Efecto**:
- Si el dropdown está visible (clase `visible`), lo oculta
- Si está oculto, lo muestra
- `e.stopPropagation()` evita que el click se propague al document (que lo cerraría inmediatamente)

---

#### 2. **Click en botón Logout**

```javascript
btnLogout.onclick = function(e) {
    e.preventDefault();
    hacerLogout();
};
```

**Efecto**:
- Previene el comportamiento por defecto del botón
- Llama a `hacerLogout()`

---

#### 3. **Click fuera del dropdown (document)**

```javascript
document.onclick = function(e) {
    if (!adminContainer.contains(e.target) && 
        !adminDropdown.contains(e.target)) {
        adminDropdown.classList.remove('visible');
        adminContainer.classList.remove('dropdown-open');
    }
};
```

**Efecto**:
- Si haces click fuera del dropdown, se cierra
- Verifica si el click fue en `adminContainer` o `adminDropdown`
- Si no, quita las clases `visible` y `dropdown-open`

---

### **Función: `hacerLogout()`**

```javascript
var hacerLogout = function() {
    fetch('/logout', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success || response.ok) {
            window.location.href = '/';
        }
    })
    .catch(function(error) {
        console.error('Error logout:', error);
        window.location.href = '/logout';
    });
};
```

**Flow de ejecución**:

1. **Fetch POST a `/logout`**
   - Headers: `X-CSRF-TOKEN` (para seguridad Laravel)
   - Body: JSON (vacío, pero indicamos que es JSON)

2. **Primera respuesta (.then)**
   - Convierte la respuesta a JSON

3. **Segunda respuesta (.then)**
   - Si hay `data.success` o la respuesta está ok
   - Redirige a `/` (home)

4. **Si hay error (.catch)**
   - Loguea el error en consola
   - Intenta redirigir a `/logout` como fallback

---

## 📱 Comportamiento Responsivo

El CSS no incluye media queries específicas porque el topbar es flexible:

- **Móvil**: Los botones de nav se comprimen, el dropdown funciona igual
- **Tablet**: Espaciado se ajusta con padding y gaps
- **Desktop**: Ancho completo con márgenes normales

---

## 🔄 Flujo Completo del Dropdown

```
Usuario hace click en avatar
    ↓
onclick en adminContainer
    ↓
Toggle clase 'visible' en dropdown
Toggle clase 'dropdown-open' en container
    ↓
Dropdown aparece con transición suave
Chevron rota 180°
    ↓
Usuario elige opción:
    - "Perfil" → No implementado aún
    - "Configuración" → No implementado aún
    - "Cerrar sesión" → Llama hacerLogout()
        ↓
Fetch POST a /logout con CSRF token
    ↓
Si success:
    Redirige a / (home)
Si error:
    Redirige a /logout como fallback
```

---

## 🔐 Seguridad

### Token CSRF

Obligatorio en peticiones POST a rutas protegidas de Laravel:

```html
<!-- En el <head> del layout -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```javascript
headers: {
    'X-CSRF-TOKEN': csrfToken
}
```

---

## 📁 Estructura de Carpetas

```
SpotStay/
├── resources/views/
│   └── layouts/
│       └── admin.blade.php          ← Layout principal
│
├── public/
│   ├── css/admin/
│   │   └── layout.css               ← Estilos del layout
│   │
│   └── js/admin/
│       └── layout.js                ← Funcionalidad del layout
```

---

## ✅ Estado Actual

**Dropdown Admin**: ✅ FUNCIONAL
- Click en avatar abre/cierra menú
- 3 opciones: Perfil, Configuración, Cerrar sesión
- "Cerrar sesión" hace logout seguro (POST /logout)
- Se cierra al hacer click fuera
- Estilo limpio y moderno

---

Después de cambios, verifica:

- [ ] El topbar no se mueve al scrollear (sticky)
- [ ] El botón Admin tiene dropdown al hacer click
- [ ] El chevron rota al abrir/cerrar dropdown
- [ ] Click fuera del dropdown lo cierra
- [ ] "Cerrar sesión" redirige a home
- [ ] No hay errores en consola (F12)
- [ ] El CSS se carga correctamente (no errores 404)
- [ ] El JS se carga correctamente (no errores en consola)

---

## 🚀 Próximos Pasos

1. **Implementar "Perfil"**: Llevar a `/admin/perfil` o modal
2. **Implementar "Configuración"**: Llevar a `/admin/configuracion`
3. **Agregar más opciones** al dropdown (Cambiar contraseña, etc.)
4. **Mejorar animaciones**: Añadir más transiciones suaves
5. **Notificaciones en tiempo real**: Websockets para la campana

---

**Última actualización**: Abril 2026  
**Laravel**: 13.4.0  
**Estado**: ✅ Dropdown funcional y seguro
