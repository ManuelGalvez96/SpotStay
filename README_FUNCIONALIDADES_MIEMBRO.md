# 🔍 Funcionalidades del Rol MIEMBRO - SpotStay

> Documentación técnica de las funcionalidades del portal público y búsqueda de propiedades para miembros registrados.

---

## 📍 Estructura General

| Elemento | Ubicación |
|----------|-----------|
| **Vistas** | `resources/views/miembro/`, `resources/views/inquilino/partials/` |
| **Controladores** | `app/Http/Controllers/Miembro/` |
| **CSS** | `public/css/miembro/` |
| **Rutas** | `routes/web.php` (líneas 223-250) |
| **Layout** | `resources/views/layouts/miembro.blade.php` |

---

## 🎯 INICIO / HOME

### Ubicación
- **Vista**: `resources/views/miembro/inicio.blade.php`
- **Controlador**: `app/Http/Controllers/Miembro/HomeController.php`
- **Ruta**: `GET /miembro/inicio`

### 🎨 Hero Section

**Contenido**:
- Logo SpotStay
- Título: "Encuentra el lugar perfecto para vivir"
- Descripción: "Explora miles de propiedades en alquiler..."
- Botones: Login, Registrarse

**Barra de Búsqueda**:
```html
<div class="search-bar" id="hero-search">
    <div class="search-input-group">
        <span class="search-label">Ubicación</span>
        <span class="search-value">¿Dónde quieres vivir?</span>
    </div>
    <div class="search-divider"></div>
    <div class="search-input-group">
        <span class="search-label">Tipo</span>
        <span class="search-value">Piso, Casa, Estudio...</span>
    </div>
    <div class="search-divider"></div>
    <div class="search-input-group">
        <span class="search-label">Precio Máx.</span>
        <span class="search-value">Sin límite</span>
    </div>
</div>
```

**Funcionalidad**:
- Click en cada campo abre selector
- Ubicación: Autocomplete de ciudades
- Tipo: Dropdown con opciones
- Precio: Slider de rango
- Botón "Buscar" → Filtra y navega a resultados

### 🔘 Navegación Principal

**Links en Header**:
- Buscar Propiedades → `/miembro/buscar` (o lista de propiedades)
- Cómo funciona → Sección en la página
- Soy Propietario → `/miembro/solicitud-arrendador`
- Iniciar sesión → `/login`
- Regístrate → `/register`

---

## 🏠 BÚSQUEDA Y LISTADO DE PROPIEDADES

### Ubicación (Estimada)
- **Vista**: `resources/views/miembro/listado_propiedades.blade.php`
- **Controlador**: `app/Http/Controllers/Miembro/HomeController.php` o `DetallePropiedadController.php`
- **Ruta**: `GET /miembro/propiedades` o desde búsqueda

### 📈 KPIs / Estadísticas

**Mostrado al usuario**:
- Total propiedades disponibles
- Propiedades nuevas este mes
- Ciudades más populares
- Precio promedio

### 🔍 Filtros de Búsqueda

**Campos**:
| Filtro | Tipo | Opciones |
|--------|------|----------|
| **Ubicación** | Autocomplete | Ciudades (dinámico) |
| **Tipo** | Select | Piso, Casa, Estudio, Ático, etc. |
| **Precio Mínimo** | Number | Desde 0 |
| **Precio Máximo** | Number | Ilimitado |
| **Habitaciones** | Range/Select | 1-5+ |
| **Superficie** | Range | m² mínimos |
| **Orden** | Select | Relevancia, Precio ↑/↓, Nuevo, |

**Envío**: 
- Método: GET
- URL: `/miembro/propiedades?ciudad=...&tipo=...&precio_min=...&precio_max=...&habitaciones=...&orden=...`
- Client-side filtering o AJAX

**Respuesta**:
- Grid/Lista de propiedades filtradas
- Paginación (20 por página)

### 📋 Tarjetas de Propiedad

**Mostrado en cada tarjeta**:
- Foto principal
- Ubicación (ciudad, calle)
- Precio/mes €
- Habitaciones, m²
- Amenidades (wifi, parking, etc.)
- Avatar arrendador
- Badge: "Nuevo", "Descuento", etc.
- Botón "Ver detalle"

**Click en tarjeta**: 
- Navega a `/miembro/propiedad/{id}`

### 📍 Atributos de Tarjeta

```html
<div class="propiedad-card" data-id="{{ $propiedad->id_propiedad }}"
     data-precio="{{ $propiedad->precio_propiedad }}"
     data-ciudad="{{ $propiedad->ciudad_propiedad }}"
     data-tipo="{{ $propiedad->tipo_propiedad }}">
```

---

## 🏘️ VER DETALLE DE PROPIEDAD

### Ubicación
- **Vista**: `resources/views/miembro/detalle_propiedad.blade.php`
- **Controlador**: `app/Http/Controllers/Miembro/DetallePropiedadController.php`
- **Ruta**: `GET /miembro/propiedad/{id}`

### 📸 Galería de Fotos

**Estructura**:
- Foto principal (grande)
- Miniaturas debajo (scroll horizontal)
- Navegación: Anterior, Siguiente
- Contador: "1 de 15 fotos"

### 📋 Información Principal

| Sección | Contenido |
|---------|----------|
| **Encabezado** | Título + Ubicación + Precio/mes |
| **Detalles** | m², habitaciones, baños, tipo, amueblado |
| **Descripción** | Párrafo largo del arrendador |
| **Amenidades** | Grid de iconos (wifi, cocina, ac, etc.) |
| **Ubicación Mapa** | Mapa interactivo con geolocalización |

### 🔘 Acciones Principales

| Botón | Acción | Ruta/Función |
|-------|--------|------------|
| **Contactar** | Abre chat/formulario | POST `/miembro/propiedad/{id}/chat` |
| **Solicitar Alquiler** | Abre formulario | Modal o GET `/miembro/solicitud-forma` |
| **Guardar** | Favoritos | AJAX → DB |
| **Compartir** | Social media | JS copy link |
| **Reportar** | Reporte abuso | Modal |

### 📋 Información del Arrendador

**Tarjeta de Contacto**:
- Avatar + nombre arrendador
- Email (oculto, solo si contacta)
- Número verificado ✓ (si aplica)
- Botón: "Contactar"

### 📋 Formulario de Solicitud de Alquiler

**Ubicación**: Modal o página separada

**Campos**:
```
- Nombre inquilino (auto-filled si registrado)
- Email (auto-filled)
- Teléfono
- Fecha inicio alquiler (date picker)
- Fecha fin alquiler (date picker)
- Ingresos mensuales (número)
- Ocupación (select)
- Número de ocupantes
- Mascotas (sí/no + cantidad)
- Mensaje al arrendador (textarea)
```

**Botones**: Cancelar, Enviar

**Envío**: 
```
POST /miembro/propiedad/{id}/solicitud-alquiler
{
    nombre_inquilino: ...,
    email_inquilino: ...,
    telefono_inquilino: ...,
    fecha_inicio: ...,
    fecha_fin: ...,
    ingresos: ...,
    ocupacion: ...,
    ocupantes: ...,
    mascotas: ...,
    mensaje: ...
}
```

**Transacción BD**:
```php
DB::beginTransaction();
try {
    $idSolicitud = DB::table('tbl_alquiler')->insertGetId([
        'id_propiedad_fk' => $idPropiedad,
        'id_inquilino_fk' => $userId,  // si está registrado
        'estado_alquiler' => 'pendiente',
        'fecha_inicio_alquiler' => ...,
        'fecha_fin_alquiler' => ...,
        'creado_alquiler' => now()
    ]);
    
    // Crear notificación para arrendador
    DB::table('tbl_notificacion')->insert([
        'id_usuario_fk' => $idArrendador,
        'titulo_notificacion' => 'Nueva solicitud',
        'mensaje_notificacion' => "Tienes una nueva solicitud de alquiler..."
    ]);
    
    DB::commit();
}
```

**Respuesta**:
- Éxito: Modal "Solicitud enviada" + Link a miembro/solicitudes
- Error: Mostrar errores de validación

---

## 💬 MENSAJES / CHAT

### Ubicación
- **Vista**: `resources/views/miembro/chat.blade.php` (índice), `resources/views/miembro/chat-show.blade.php` (conversación)
- **Controlador**: `app/Http/Controllers/Miembro/MensajesController.php`
- **Rutas**:
  - `GET /miembro/chat` - Lista de conversaciones
  - `GET /miembro/chat/{id}` - Detalle conversación
  - `POST /miembro/chat/{id}/mensaje` - Enviar mensaje

### 📋 Lista de Conversaciones

**Mostrado**:
- Avatar contacto
- Nombre (arrendador o inquilino)
- Último mensaje (texto truncado)
- Fecha último mensaje
- Badge: "Nuevo" si hay mensajes sin leer
- Botón: "Abrir conversación"

**Click**: 
- Navega a `/miembro/chat/{id_conversacion}`

### 💭 Detalle de Conversación

**Estructura**:
- Cabecera con nombre contacto + estado online
- Thread de mensajes (chat style)
- Input para escribir nuevo mensaje
- Botón Enviar

**Mensajes**:
- Lados alterno: yo vs otro
- Hora relativa (ej: "hace 5 min")
- Colores diferentes por remitente

**Envío de Mensaje**: 
```
POST /miembro/chat/{id_conversacion}/mensaje
{ mensaje: "..." }
```

**Respuesta**:
- JSON con nuevo mensaje
- Actualización en tiempo real (WebSocket o polling)

### 🔔 Notificaciones de Chat

**Trigger**:
- Nuevo mensaje recibido → Badge rojo en navbar
- Banner notificación (opcional)

---

## 🗺️ MAPA DE PROPIEDADES

### Ubicación
- **Vista**: `resources/views/miembro/mapa.blade.php`
- **Controlador**: `app/Http/Controllers/Miembro/MapaController.php`
- **Ruta**: `GET /miembro/mapa`

### 🗺️ Mapa Interactivo

**Librería**: Leaflet o Google Maps

**Funcionalidad**:
- Mapa base con todas las propiedades activas
- Pines/markers para cada propiedad
- Click en marker → Info window con:
  - Foto thumb
  - Título
  - Precio €
  - Botón "Ver"
- Zoom/Paneo
- Controles de capas (tipo de propiedad, rango precio)

### 📍 Carga de Datos

**Ruta AJAX**: `GET /miembro/mapa/propiedades`
- Query params: `?sudoeste_lat=...&sudoeste_lng=...&noreste_lat=...&noreste_lng=...`
- Retorna GeoJSON o JSON con propiedades visibles en mapa

**Respuesta**:
```json
{
    "propiedades": [
        {
            "id": 1,
            "titulo": "...",
            "precio": 1500,
            "latitud": 40.4168,
            "longitud": -3.7038,
            "foto_thumb": "..."
        }
    ]
}
```

---

## 📝 SOLICITUD PARA SER ARRENDADOR

### Ubicación
- **Vista**: `resources/views/miembro/solicitud_arrendador.blade.php`
- **Controlador**: `app/Http/Controllers/Miembro/SolicitudArrendadorController.php`
- **Rutas**:
  - `GET /miembro/solicitud-arrendador` - Formulario
  - `POST /miembro/solicitud-arrendador` - Envío

### 📝 Formulario

**Campos**:
```
- Nombre legal (text)
- Email (email)
- Teléfono (tel)
- Empresa/Razón Social (text, opcional)
- Tipo de propietario (select: persona, empresa, otros)
- Ciudades donde tiene propiedades (multiselect)
- Número aproximado de propiedades (number)
- Descripción (textarea)
- Documento identidad (file upload)
- Contrato de propiedad/escritura (file upload)
```

**Validaciones**:
- Email único
- Teléfono válido
- Archivos: PDF/JPG, máx 10MB

**Envío**: 
```
POST /miembro/solicitud-arrendador
Content-Type: multipart/form-data
{
    nombre_arrendador: ...,
    email_arrendador: ...,
    telefono_arrendador: ...,
    empresa: ...,
    tipo_propietario: ...,
    ciudades: [...],
    num_propiedades: ...,
    descripcion: ...,
    documento_identidad: <file>,
    contrato_propiedad: <file>
}
```

**Transacción BD**:
```php
DB::beginTransaction();
try {
    // Crear usuario o usuario-rol si no existe
    $idSolicitud = DB::table('tbl_solicitud_arrendador')->insertGetId([
        'id_usuario_fk' => $userId,
        'estado_solicitud_arrendador' => 'pendiente',
        'creado_solicitud_arrendador' => now()
    ]);
    
    // Guardar documentos en storage
    $rutaIdentidad = $request->file('documento_identidad')->store('arrendadores');
    
    DB::commit();
}
```

**Respuesta**:
- Éxito: "Solicitud enviada. Revisaremos tu información."
- Error: Mostrar errores

### 🔔 Admin Notification

**Para Admin**:
- Notificación de nueva solicitud arrendador
- Link a revisión en panel admin

---

## 🔐 Acceso y Autenticación

### Usuarios No Autenticados

**Ruta Pública**: `GET /`
- Inicio (hero + búsqueda)
- Botón "Buscar"
- Botón "Registrarse" / "Iniciar sesión"

### Usuarios Autenticados (Miembro)

**Acceso a**:
- Búsqueda completa de propiedades
- Ver detalles de propiedad
- Enviar solicitud de alquiler
- Contactar propietarios (chat)
- Ver mapa de propiedades
- Solicitar convertirse en arrendador

### Middleware

```blade
Route::middleware(['role:miembro,inquilino,propietario'])->group(function () {
    // Rutas miembro
});
```

**Roles que acceden**: miembro, inquilino, propietario

---

## 🎨 CSS y Responsividad

### Archivos CSS

| Archivo | Ubicación | Contenido |
|---------|-----------|----------|
| inicio.css | `public/css/inicio/` | Hero, navbar, footer |
| propiedades.css | `public/css/miembro/` | Grid, tarjetas, filtros |
| detalle_propiedad.css | `public/css/miembro/` | Galería, info, formulario |
| chat.css | `public/css/miembro/` | Conversaciones, mensajes |
| mapa.css | `public/css/miembro/` | Leaflet/Maps styling |

### Breakpoints

```css
/* Móvil */
@media (max-width: 640px) {
    .hero-search { flex-direction: column; }
    .propiedad-grid { grid-template-columns: 1fr; }
}

/* Tablet */
@media (min-width: 641px) and (max-width: 1024px) {
    .propiedad-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Desktop */
@media (min-width: 1025px) {
    .propiedad-grid { grid-template-columns: repeat(4, 1fr); }
}
```

---

## 🔗 Rutas Completas

| Acción | Método | Ruta |
|--------|--------|------|
| Home público | GET | `/` |
| Inicio miembro | GET | `/miembro/inicio` |
| Buscar propiedades | GET | `/miembro/propiedades?...` |
| Ver propiedad | GET | `/miembro/propiedad/{id}` |
| Enviar solicitud alquiler | POST | `/miembro/propiedad/{id}/solicitud-alquiler` |
| Chat (lista) | GET | `/miembro/chat` |
| Chat (conversación) | GET | `/miembro/chat/{id}` |
| Enviar mensaje | POST | `/miembro/chat/{id}/mensaje` |
| Ver mapa | GET | `/miembro/mapa` |
| Cargar propiedades mapa | GET | `/miembro/mapa/propiedades` |
| Solicitud arrendador (form) | GET | `/miembro/solicitud-arrendador` |
| Enviar solicitud arrendador | POST | `/miembro/solicitud-arrendador` |
| Iniciar conversación | POST | `/miembro/propiedad/{id}/chat` |

---

## 📊 Resumen Técnico

| Aspecto | Detalles |
|--------|----------|
| **Vistas Principales** | 7+ (inicio, propiedades, detalle, chat, mapa, solicitud) |
| **Acceso** | Público (visitantes) + Autenticado (miembro/inquilino) |
| **Autenticación** | Opcional (algunos contenidos sin login) |
| **Transacciones BD** | Sí (solicitudes, mensajes, conversaciones) |
| **Filtros Dinámicos** | GET params + Client-side |
| **Mapas** | Leaflet o Google Maps API |
| **Notificaciones** | Real-time (chat, solicitudes) |
| **Upload de Archivos** | Sí (solicitud arrendador, fotos en futuros) |
| **Storage Externos** | Firebase u otro para imágenes |

---

**Última actualización**: 3 de mayo de 2026
**Versión**: 1.0
