# 🏠 Funcionalidades del Rol ARRENDADOR - SpotStay

> Documentación técnica detallada de todas las funcionalidades del panel de arrendador.

---

## 📍 Estructura General

| Elemento | Ubicación |
|----------|-----------|
| **Vistas** | `resources/views/arrendador/` |
| **Controladores** | `app/Http/Controllers/Arrendador/` |
| **JavaScript** | `public/js/arrendador/` |
| **CSS** | `public/css/arrendador/` |
| **Rutas** | `routes/web.php` (líneas 110+) |
| **Layout** | Vistas standalone HTML (no usan layout común) |

---

## 🎯 DASHBOARD

### Ubicación
- **Vista**: `resources/views/arrendador/dashboard.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/DashboardController.php`
- **Ruta**: `GET /arrendador/dashboard?arrendador_id={id}`

### 📈 KPIs y sus Fuentes

| KPI | Ubicación HTML | Cálculo | Fuente en Controlador |
|-----|---------------|---------|----|
| **Propiedades Activas** | Stat box 1 | `COUNT(tbl_propiedad) WHERE estado IN ('publicada', 'alquilada')` | `$propiedadesActivas = DB::table('tbl_propiedad')->where('id_arrendador_fk', $arrendadorId)->whereIn('estado_propiedad', ['publicada', 'alquilada'])->count();` |
| **Inquilinos Activos** | Stat box 2 | `COUNT(DISTINCT id_inquilino) WHERE estado_alquiler = 'activo'` | `$inquilinosActivos = DB::table('tbl_alquiler as a')->join('tbl_propiedad as p', ...)->where('a.estado_alquiler', 'activo')->distinct()->count('a.id_inquilino_fk');` |
| **Ingresos Este Mes** | Stat box 3 | SUM(precio_propiedad) entre primer día y último día del mes | `$ingresosEsteMes = DB::table('tbl_alquiler as a')->...->whereBetween(DATE(...), [$inicioMes, $finMes])->sum("p.{$columnaPrecio}");` |
| **Solicitudes Pendientes** | Stat box 4 | `COUNT(tbl_alquiler) WHERE estado = 'pendiente'` | `$solicitudesPendientes = DB::table('tbl_alquiler as a')->where('a.estado_alquiler', 'pendiente')->count();` |

**Notas**:
- Los precios se obtienen de columna dinámica según disponibilidad en BD
- Los ingresos se calculan entre `aprobado_alquiler` o `creado_alquiler`
- Los stat boxes se renderizan directamente en HTML sin IDs específicos

### 📋 Tablas en Dashboard

**1. Últimas Solicitudes** (Tarjetas):
- Límite: 5 registros
- Datos: Propiedad, solicitante, estado, fecha
- Botón: "Ver Solicitudes" → `/arrendador/solicitudes`

**2. Últimos Mensajes** (Tarjetas):
- Límite: 5 registros
- Datos: Remitente, cuerpo del mensaje (truncado), fecha
- Botón: "Ver Mensajes" → `/arrendador/mensajes`

**3. Propiedades Activas** (Grid):
- Límite: 10 registros
- Datos por tarjeta:
  - Título propiedad
  - Ubicación
  - Precio mensual
  - Estado
  - Inquilino actual (si aplica)
  - Botón: "Gestionar" → `/arrendador/propiedades?editar={id}`

### 🔘 Botones de Acción en Dashboard

| Botón | Ubicación | Acción | Ruta |
|-------|-----------|--------|------|
| **Publicar Propiedad** | Card 1 | Ir a módulo propiedades | `/arrendador/propiedades?arrendador_id={id}` |
| **Ver Solicitudes** | Card 2 | Listar solicitudes | `/arrendador/solicitudes?arrendador_id={id}` |
| **Precios y Gastos** | Card 3 | Ir a configuración | `/arrendador/precios-gastos?arrendador_id={id}` |
| **Gestionar Inquilinos** | Card 4 | Listar inquilinos | `/arrendador/inquilinos?arrendador_id={id}` |
| **Mensajes** | Card 5 | Conversaciones | `/arrendador/mensajes?arrendador_id={id}` |
| **Contratos** | Card 6 | Firmar contratos | `/arrendador/contratos?arrendador_id={id}` |

---

## 🏠 PROPIEDADES

### Ubicación
- **Vista**: `resources/views/arrendador/propiedades.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/PropiedadController.php`
- **Rutas**:
  - `GET /arrendador/propiedades`
  - `POST /arrendador/propiedades/store` (crear/editar)

### 📈 KPIs

**Ubicación en Vista**: Section `.stats-grid` (línea ~23)

| KPI | Cálculo |
|-----|---------|
| **Total Propiedades** | `COUNT(tbl_propiedad) WHERE id_arrendador_fk = $arrendadorId` |
| **Publicadas** | `COUNT(...) WHERE estado = 'publicada'` |
| **Alquiladas** | `COUNT(...) WHERE estado = 'alquilada'` |
| **Inactivas** | `COUNT(...) WHERE estado = 'inactiva'` |

**Actualización**: Se recalcula cada vez que se carga la página

### 📝 Formulario de Creación/Edición

**Estructura**: Página dividida en 2 columnas
- **Izquierda**: Formulario de datos
- **Derecha**: Preview/Galería (oculta inicialmente)

**Campos del Formulario** (líneas 46-100):
```
- Título (text, required)
- Estado (select: borrador, publicada, alquilada, inactiva)
- Dirección (text, required)
- Ciudad (text, required)
- Código postal (text, required)
- Latitud (number)
- Longitud (number)
- Precio mensual (number, step 0.01, required)
- Descripción (textarea)
- Imágenes (file upload, máx 10)
- Características (checkboxes: wifi, tv, cocina, etc.)
```

**ID Formulario**: `.property-form`
- Atributo: `data-ajax-form="true"`
- Enctype: `multipart/form-data`
- Método: POST
- Action: `{{ route('arrendador.propiedades.store') }}`

### 🖼️ Gestión de Imágenes

**Campos Ocultos**:
- `#imagen-principal-indice` → Índice de imagen principal

**Funcionalidad**:
- Drag & drop o click para seleccionar
- Preview en tiempo real
- Marcar como imagen principal
- Eliminar seleccionada
- Reordenar

**Envío**: Base64 o FormData (según implementación)

### 💾 Guardado

**Flujo**:
1. Validación en cliente (JS)
2. Envío: `POST /arrendador/propiedades/store`
3. Parámetros:
   - `id_propiedad` (si edición)
   - `titulo_propiedad`, `estado_propiedad`, etc.
   - JSON de imágenes
   - JSON de características

**Transacción BD**: Sí
```php
DB::beginTransaction();
try {
    // UPDATE/INSERT tbl_propiedad
    $idPropiedad = ...
    // DELETE fotos antiguas (si edición)
    DB::table('tbl_foto')->where('id_propiedad_fk', $idPropiedad)->delete();
    // INSERT fotos nuevas
    foreach($fotos as $foto) {
        DB::table('tbl_foto')->insert([...]);
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

**Respuesta**: 
- Éxito: Redirect a lista de propiedades con mensaje
- Error: JSON con errores de validación

### 📋 Tabla de Propiedades Existentes

**Ubicación en Vista**: Panel derecho (después de formulario)

**Estructura** (si está en edición o si hay lista):
```html
<table>
    <thead>
        <tr>
            <th>Propiedad</th>
            <th>Estado</th>
            <th>Precio</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Filas dinámicas -->
    </tbody>
</table>
```

**Botones de Acción**:
- **Editar** → Carga datos en formulario
- **Ver** → Vista previa
- **Eliminar** → Confirmación + DELETE

---

## 📋 SOLICITUDES

### Ubicación
- **Vista**: `resources/views/arrendador/solicitudes.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/SolicitudController.php`
- **Ruta**: `GET /arrendador/solicitudes?arrendador_id={id}`

### 📈 KPIs

**Ubicación**: Section `.kpis` (línea ~26)

| KPI | HTML | Cálculo |
|-----|------|---------|
| **Total** | `<span>{{ $totales['total'] }}</span>` | `COUNT(tbl_alquiler) para propiedades del arrendador` |
| **Pendientes** | `<span>{{ $totales['pendientes'] }}</span>` | `WHERE estado_alquiler = 'pendiente'` |
| **Aprobadas** | `<span>{{ $totales['activos'] }}</span>` | `WHERE estado_alquiler = 'activo'` |
| **Rechazadas** | `<span>{{ $totales['rechazados'] }}</span>` | `WHERE estado_alquiler = 'rechazado'` |

**Actualización**: Se pasan como variables desde el controlador

### 📄 Tabla de Solicitudes

**ID Tabla**: `.tabla`
**Ubicación**: Línea 36

**Encabezados**:
| Encabezado | Datos |
|-----------|-------|
| Propiedad | Título + dirección |
| Solicitante | Nombre + email |
| Periodo | Fecha inicio - Fecha fin |
| Estado | Badge con clase `estado-{estado}` (pendiente, activo, rechazado) |
| Fecha | Fecha creación (formato d/m/Y) |
| Acciones | Ver, Editar (si pendiente), Eliminar |

**Atributos de Fila**:
```html
<tr id="fila-{{ $solicitud->id_alquiler }}"
    data-acciones="{{ $solicitud->id_alquiler }}"
    data-estado="{{ $estado }}"
    data-arrendador="{{ $arrendadorId }}">
```

### 🔘 Acciones en Solicitudes

| Acción | ID Botón | Condición | Ruta/Función |
|--------|----------|-----------|------------|
| **Ver** | `.btn-ver` data-ver="{id}" | Siempre | Modal o redirección |
| **Editar** | `.btn-editar` data-editar="{id}" | Solo si estado != 'activo' | Abre formulario |
| **Eliminar** | `.btn-eliminar` data-eliminar="{id}" | Siempre | `DELETE /arrendador/solicitudes/{id}` |

**Flujo de Edición** (pendiente):
1. Click en Editar
2. Carga datos de solicitud: `GET /arrendador/solicitudes/{id}/edit`
3. Abre modal o página con formulario
4. Campos editables: fechas, notas, términos especiales
5. Envío: `POST /arrendador/solicitudes/{id}`

**Transacción BD**: Sí (si hay cambios en múltiples campos)

### 📄 Paginación

**Ubicación**: Línea 70 (`.paginacion`)

```blade
<div class="paginacion">{{ $solicitudes->withQueryString()->links() }}</div>
```

**Comportamiento**:
- Laravel Paginate automático
- Por defecto: 15 registros por página
- Preserva query strings

---

## 💰 PRECIOS Y GASTOS

### Ubicación
- **Vista**: `resources/views/arrendador/precios-gastos.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/PrecioGastoController.php`
- **Ruta**: `GET /arrendador/precios-gastos?arrendador_id={id}`

### 📈 KPIs

**Ubicación**: Section `.kpis` (línea ~26)

| KPI | HTML |
|-----|------|
| **Propiedades Totales** | `<span>{{ $totalPropiedades }}</span>` |
| **Precio Medio Mensual** | `<span>{{ number_format($precioMedio, 2, ',', '.') }} €</span>` |

**Cálculo**:
```php
$totalPropiedades = DB::table('tbl_propiedad')
    ->where('id_arrendador_fk', $arrendadorId)->count();

$precioMedio = DB::table('tbl_propiedad')
    ->where('id_arrendador_fk', $arrendadorId)
    ->avg("{$columnaPrecio}");  // Columna dinámica según disponibilidad BD
```

### 📝 Tabla de Configuración por Propiedad

**Ubicación**: Table `.tabla` (línea 36)

**Encabezados**:
| Encabezado | Contenido |
|-----------|-----------|
| Propiedad | Título + Ubicación + Ciudad |
| Estado | Badge con estado actual |
| Configuración | Formulario inline |

### ⚙️ Formulario Inline de Precios y Gastos

**Ubicación**: Dentro de cada fila de tabla

**Campos**:

1. **Precio Mensual**
   - ID: `input[name="precio_propiedad"]`
   - Tipo: `number`
   - Step: `0.01`
   - Min: `0`
   - Requerido: Sí
   - Prefijo: "EUR"

2. **Gastos**
   - ID: `textarea[name="gastos_propiedad"]`
   - Tipo: Textarea (rows=2)
   - Formato: JSON o texto libre
   - Placeholder: `{"agua":30,"luz":45} o texto libre`
   - Nota: "Puedes usar JSON o texto simple"

**Almacenamiento en BD**:
- Campo: `gastos_propiedad` (TEXT o JSON)
- Formato: JSON normalizado o string
- Nota especial: Instrucciones dicen "Sin JSON en BD", pero aquí se acepta ambos

### 💹 Resumen Mensual

**Ubicación**: Dentro formulario, antes del botón guardar

**Elemento**: `<div data-resumen-mensual>`

**Contenido**:
```html
<small>Total mensual estimado</small>
<strong data-total-mensual>--</strong>
<span class="muted" data-estado-gastos>Completa los campos para calcular.</span>
```

**Cálculo JS** (público/js/arrendador/precios-gastos.js):
- Suma precio + gastos parseados
- Actualiza en tiempo real
- Valida si gastos son JSON válido

### 💾 Guardado

**Button**: `.btn-guardar` con clase "Guardar cambios"

**Envío**: 
```
POST /arrendador/precios-gastos/actualizar/{id_propiedad}?arrendador_id={id}
```

**Cuerpo**:
```json
{
    "precio_propiedad": 1500.00,
    "gastos_propiedad": "{\"agua\":30,\"luz\":45}" // o string libre
}
```

**Respuesta**:
- Éxito: Toast con mensaje "Cambios guardados"
- Error: Toast con mensaje "Error al guardar"

**Transacción BD**: No (tabla única)

### 🔔 Notificación (Toast)

**ID Toast**: `#toastPrecios`
**Ubicación**: Línea 82

```html
<div id="toastPrecios" class="toast" hidden></div>
```

Mostrado por JS después de respuesta AJAX

### 📄 Paginación

**Ubicación**: Última línea

```blade
<div class="paginacion">{{ $propiedades->withQueryString()->links() }}</div>
```

---

## 💍 CONTRATOS DIGITALES

### Ubicación
- **Vista**: `resources/views/arrendador/contratos.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/ContratoController.php`
- **Ruta**: `GET /arrendador/contratos?arrendador_id={id}`

### 📈 KPIs

**Ubicación**: Section `.kpis` (línea ~26)

| KPI | HTML | Cálculo |
|-----|------|---------|
| **Total** | `<span>{{ $totales['total'] }}</span>` | `COUNT(tbl_contrato)` |
| **Firmados** | `<span>{{ $totales['firmados'] }}</span>` | `COUNT(...) WHERE firmado_arrendador = 1 AND firmado_inquilino = 1` |
| **Pendientes** | `<span>{{ $totales['pendientes'] }}</span>` | `Total - Firmados` |

**Cálculo en Controlador** (`ContratoController->inicio()`):
```php
$total = DB::table('tbl_contrato as c')
    ->join('tbl_alquiler', ...)
    ->join('tbl_propiedad as p', ...)
    ->where('p.id_arrendador_fk', $arrendadorId)
    ->count('c.id_contrato');

$firmados = $this->contarFirmados($arrendadorId, $columnas);
// Cuenta registros donde ambas firmas = 1
```

### 📄 Tabla de Contratos

**Ubicación**: `<table class="tabla">` (línea 36)

| Encabezado | Contenido | Data |
|-----------|-----------|------|
| **Contrato** | ID + ID Alquiler | - |
| **Propiedad** | Título + Ubicación | - |
| **Inquilino** | Nombre | - |
| **Firma Arrendador** | "Firmado" o "Pendiente" + Fecha | `id="firma-arrendador-{id}"` |
| **Firma Inquilino** | "Firmado" o "Pendiente" + Fecha | - |
| **Estado** | Badge con clase `estado-{estado}` | `id="estado-{id}"` |
| **Acciones** | Botones | - |

### 🔘 Botones de Acción en Contratos

| Botón | ID | Condición | Acción | Ruta |
|-------|----|---------|---------|----|
| **Firmar** | `.btn-firmar` data-firmar-arrendador="{id}" | Si NO firmado_arrendador | Abre modal/canvas de firma | POST `/arrendador/contratos/{id}/firmar` |
| **Ver PDF** | `.btn-ver` | Si url_pdf_contrato existe | Abre/descarga PDF | GET `/arrendador/contratos/{id}/descargar-pdf` |
| **Sin acciones** | - | Si ya está firmado por arrendador | Texto "Sin acciones" | - |

### ✍️ Flujo de Firma del Arrendador

**Trigger**: Click en `.btn-firmar` con `data-firmar-arrendador="{id}"`

**Modal/Canvas**:
1. Cargacontrato en canvas digital
2. Permite dibujar firma
3. Botones: Limpiar, Cancelar, Firmar

**Envío**:
```
POST /arrendador/contratos/{id_contrato}/firmar
Method: POST
Body: {
    id_arrendador: ...,
    firma_base64: "data:image/png;base64,..."
}
```

**Actualización BD**:
- UPDATE tbl_contrato SET firmado_arrendador = 1
- UPDATE tbl_contrato SET fecha_firma_arrendador = NOW()
- UPDATE tbl_contrato SET url_pdf_contrato = ... (si genera PDF nuevo)

**Transacción BD**: Sí (si hay múltiples updates)

**Respuesta JSON**:
```json
{
    "success": true,
    "message": "Contrato firmado exitosamente",
    "data": {
        "fecha_firma_arrendador": "2026-05-03 14:30:00",
        "url_pdf": "..."
    }
}
```

**Actualización Visual**:
- `#firma-arrendador-{id}` → "Firmado" + fecha
- `#estado-{id}` → Actualiza estado (ej: "Parcialmente Firmado" → "Firmado por Ambos")
- Botón desaparece (reemplazado por "Sin acciones")

### 📄 Descarga de PDF

**Ruta**: `GET /arrendador/contratos/{id}/descargar-pdf?arrendador_id={id}`

**Headers de Respuesta**:
- Content-Type: application/pdf
- Content-Disposition: attachment; filename="contrato_{id}.pdf"

**Generación**:
- Usa `PdfMonkeyService` (config/pdfmonkey.php)
- Incluye datos del alquiler, inquilino, fechas, términos
- Integra firmas digitales si existen

### 📄 Paginación

```blade
{{ $contratos->withQueryString()->links() }}
```

**Por defecto**: 10 registros por página

---

## 👥 INQUILINOS

### Ubicación
- **Vista**: `resources/views/arrendador/inquilinos.blade.php` (si existe)
- **Controlador**: `app/Http/Controllers/Arrendador/InquilinoController.php`
- **Ruta**: `GET /arrendador/inquilinos?arrendador_id={id}`

### 📈 KPIs (Esperado)

- Total Inquilinos
- Inquilinos Activos
- Inquilinos Inactivos
- Contratos Vigentes

### 📋 Tabla

Listará inquilinos con alquileres activos o vigentes filtrados por propiedades del arrendador

**Columnas esperadas**:
- Nombre
- Email
- Propiedad
- Fecha inicio alquiler
- Fecha fin alquiler
- Estado
- Acciones

---

## 💬 MENSAJES

### Ubicación
- **Vista**: `resources/views/arrendador/mensajes.blade.php` (si existe)
- **Controlador**: `app/Http/Controllers/Arrendador/MensajeController.php`
- **Ruta**: `GET /arrendador/mensajes?arrendador_id={id}`

### 📋 Conversaciones

**Listado**:
- Lado izquierdo: Lista de conversaciones (inquilino/propiedad)
- Lado derecho: Mensaje thread
- Buscar conversaciones

**Envío de Mensajes**:
- Input de texto
- Botón Enviar → `POST /arrendador/mensajes/enviar`

**Datos enviados**:
- `id_conversacion`
- `cuerpo_mensaje` o `mensaje`
- `id_arrendador`

---

## 🔧 GESTOR

### Ubicación (si aplica)
- **Vista**: `resources/views/arrendador/gestor.blade.php`
- **Controlador**: `app/Http/Controllers/Arrendador/GestorController.php`
- **Ruta**: `GET /arrendador/gestor?arrendador_id={id}`

**Funcionalidad**: Gestión de asignación de gestores a propiedades para manejo de incidencias/mantenimiento

---

## 🎨 CSS y Responsividad

### Archivos CSS

| Archivo | Ubicación | Responsable |
|---------|-----------|-------------|
| dashboard.css | `public/css/arrendador/` | Stats, cards, layout |
| propiedades.css | `public/css/arrendador/` | Formulario, tabla propiedades |
| solicitudes.css | `public/css/arrendador/` | Tabla solicitudes, estados |
| precios-gastos.css | `public/css/arrendador/` | Tabla inline forms, KPIs |
| contratos.css | `public/css/arrendador/` | Tabla contratos, botones firma |
| inquilinos.css | `public/css/arrendador/` | Si existe |
| mensajes.css | `public/css/arrendador/` | Si existe |

### Ocultamiento Responsivo

**Breakpoints comunes**:
- Móvil: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

**Clases**:
```css
.col-mobile-hide { display: none; }  /* Oculta en móvil */
.col-tablet-hide { display: none; }  /* Oculta en tablet */

@media (min-width: 768px) {
    .col-mobile-hide { display: table-cell; }
}

@media (min-width: 1024px) {
    .col-tablet-hide { display: table-cell; }
}
```

---

## 📝 Archivo Principal de Configuración

**Archivo**: `config/pdfmonkey.php`
- API key para generación de PDFs
- Plantillas disponibles
- Configuración de contratos

---

## 🔐 Validaciones y Transacciones

### Validaciones Comunes

**Crear/Editar Propiedad**:
- Precio > 0
- Título no vacío
- Al menos una imagen
- Ubicación válida (coordenadas si aplica)

**Precios y Gastos**:
- Precio es número >= 0
- Gastos son JSON válido o string
- Máx 999.99 €

**Firmar Contrato**:
- Firma no puede estar vacía
- Arrendador debe ser propietario de propiedad
- Contrato no puede estar ya completamente firmado

### Transacciones BD

**Crear Propiedad**:
```php
DB::beginTransaction();
try {
    $idPropiedad = DB::table('tbl_propiedad')->insertGetId([...]);
    foreach($fotos as $foto) {
        DB::table('tbl_foto')->insert([...]);
    }
    DB::commit();
}
```

**Firmar Contrato**:
```php
DB::beginTransaction();
try {
    DB::table('tbl_contrato')
        ->where('id_contrato', $id)
        ->update([
            'firmado_arrendador' => true,
            'fecha_firma_arrendador' => now()
        ]);
    // Generar/guardar PDF
    DB::commit();
}
```

---

## 🔗 Rutas Completas

| Acción | Método | Ruta |
|--------|--------|------|
| Dashboard | GET | `/arrendador/dashboard?arrendador_id={id}` |
| Listar propiedades | GET | `/arrendador/propiedades?arrendador_id={id}` |
| Crear/Editar propiedad | POST | `/arrendador/propiedades/store` |
| Listar solicitudes | GET | `/arrendador/solicitudes?arrendador_id={id}` |
| Ver solicitud | GET | `/arrendador/solicitudes/{id}/edit` |
| Eliminar solicitud | DELETE | `/arrendador/solicitudes/{id}` |
| Actualizar solicitud | POST | `/arrendador/solicitudes/{id}` |
| Listar contratos | GET | `/arrendador/contratos?arrendador_id={id}` |
| Firmar contrato | POST | `/arrendador/contratos/{id}/firmar` |
| Descargar PDF contrato | GET | `/arrendador/contratos/{id}/descargar-pdf?arrendador_id={id}` |
| Precios y Gastos | GET | `/arrendador/precios-gastos?arrendador_id={id}` |
| Actualizar precio/gasto | POST | `/arrendador/precios-gastos/actualizar/{id}?arrendador_id={id}` |
| Listar inquilinos | GET | `/arrendador/inquilinos?arrendador_id={id}` |
| Mensajes | GET | `/arrendador/mensajes?arrendador_id={id}` |
| Enviar mensaje | POST | `/arrendador/mensajes/enviar` |
| Gestores | GET | `/arrendador/gestor?arrendador_id={id}` |

---

## 📊 Resumen Técnico

| Aspecto | Detalles |
|--------|----------|
| **Total Vistas** | 8+ (dashboard, propiedades, solicitudes, contratos, precios-gastos, inquilinos, mensajes, gestor) |
| **Total Controladores** | 8 (Dashboard, Propiedad, Solicitud, Contrato, PrecioGasto, Inquilino, Mensaje, Gestor) |
| **Tipo de Vistas** | HTML standalone (sin layout común) |
| **Transacciones BD** | Sí, en operaciones con múltiples tablas (crear propiedad, firmar contrato) |
| **Paginación** | Sí, Laravel Paginate (10-15 registros por defecto) |
| **Servicios Externos** | PdfMonkeyService (generación de PDF) |
| **Autenticación** | Query param `?arrendador_id={id}` (válida según estructura proyecto) |
| **Firmas Digitales** | Canvas HTML5 + Base64 |

---

**Última actualización**: 3 de mayo de 2026
**Versión**: 1.0
