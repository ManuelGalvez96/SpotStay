# 👤 Funcionalidades del Rol INQUILINO - SpotStay

> Documentación técnica de las funcionalidades del panel de inquilino/miembro.

---

## 📍 Estructura General

| Elemento | Ubicación |
|----------|-----------|
| **Vistas** | `resources/views/inquilino/` |
| **Controladores** | `app/Http/Controllers/inquilino/` |
| **CSS** | `public/css/inquilino/` |
| **Rutas** | `routes/web.php` (líneas 148+) |
| **Layout** | `resources/views/layouts/miembro.blade.php` |

---

## 🎯 GESTIÓN DE PROPIEDADES (Dashboard Inquilino)

### Ubicación
- **Vista**: `resources/views/inquilino/gestionar_propiedades.blade.php`
- **Controlador**: `app/Http/Controllers/inquilino/InquilinoController.php`
- **Ruta**: `GET /inquilino/gestionar-propiedades`

### 📈 KPIs

| KPI | HTML ID | Cálculo | 
|-----|---------|---------|
| **Contratos Activos** | Renderizado directo | `COUNT(DISTINCT tbl_alquiler) WHERE estado = 'activo' AND (id_inquilino_fk = $userId OR id_arrendador_fk = $userId)` |
| **Incidencias En Proceso** | Renderizado directo | `COUNT(DISTINCT tbl_incidencia) WHERE estado IN ('abierta', 'en_proceso')` |

**Cálculo Avanzado en Controlador** (`InquilinoController->gestionarPropiedades()`):

```php
// Total Contratos Activos
$totalContratos = DB::table('tbl_alquiler')
    ->join('tbl_propiedad', ...)
    ->where('tbl_alquiler.estado_alquiler', 'activo')
    ->where(function ($query) use ($userId) {
        $query->where('tbl_alquiler.id_inquilino_fk', $userId)
            ->orWhere('tbl_propiedad.id_arrendador_fk', $userId);
    })
    ->count(DB::raw('DISTINCT tbl_propiedad.id_propiedad'));

// Total Incidencias
$totalIncidencias = DB::table('tbl_incidencia')
    ->join('tbl_propiedad', ...)
    ->leftJoin('tbl_alquiler', ...)
    ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
    ->where(function ($query) use ($userId) {
        $query->where('tbl_propiedad.id_arrendador_fk', $userId)
            ->orWhereNotNull('tbl_alquiler.id_alquiler');
    })
    ->count(DB::raw('DISTINCT tbl_incidencia.id_incidencia'));
```

### 🔍 Filtros y Búsqueda

**Ubicación en Vista**: Sección `.filtros-gestion-container`

| Filtro | ID | Tipo | Envío |
|--------|----|----|------|
| **Búsqueda por Nombre** | `#busqueda-nombre` | Input text | onChange → Filtro local JS |
| **Filtro por Ciudad** | `#custom-select-ciudad` | Custom select | onChange → Filtro local JS |

**Comportamiento**:
- Búsqueda en tiempo real (client-side)
- Filtra lista de propiedades mostradas
- No requiere llamada AJAX

### 📋 Listado de Alquileres

**Ubicación**: `.listado-propiedades-gestion`

**Estructura**:
- Tarjetas o lista de propiedades del usuario
- Cada una muestra:
  - Título propiedad
  - Ubicación
  - Precio mensual
  - Estado del alquiler
  - Botones de acción

**Datos**:
- Propiedades donde el usuario es **inquilino** (estado_alquiler = 'activo')
- O propiedades donde el usuario es **arrendador**

### 🔘 Botones de Acción

| Botón | Acción | Ruta |
|-------|--------|------|
| **Ver Propiedad** | Abre vista detalle | `/inquilino/ver-propiedad/{id_alquiler}` |
| **Pagos** | Ir a lista de pagos | `/inquilino/pagos` |
| **Incidencias** | Reportar/ver incidencias | `/inquilino/incidencias` |
| **Contrato** | Ver/descargar PDF contrato | `/inquilino/contrato/{id}` |

---

## 🏠 VER PROPIEDAD (Detalle Inquilino)

### Ubicación
- **Vista**: `resources/views/inquilino/ver_propiedad.blade.php`
- **Ruta**: `GET /inquilino/ver-propiedad/{id_alquiler}`

### 📋 Contenido

**Sección Izquierda**:
1. **Galería de Fotos**
   - Foto principal (grande)
   - Miniaturas debajo
   - Click en miniatura → actualiza principal
   
2. **Información Detallada**
   - Superficie (m²)
   - Habitaciones
   - Tipo (apartamento, casa, etc.)
   - Precio/mes
   - Compañeros de vivienda (si aplica)
   - Descripción completa

**Sección Derecha**:
1. **Estado del Contrato**
   - Aviso si próximo a finalizar (< 30 días)
   - Badge con estado
   
2. **Pagos**
   - Últimos pagos
   - Cuotas pendientes
   - Botón: "Ver documentos de pago"
   
3. **Incidencias**
   - Listado de incidencias activas
   - Botón: "Reportar nueva incidencia"

4. **Documentos**
   - Link al contrato (PDF)
   - Link a recibos de pago

### 🔄 Datos Dinámicos

**Compañeros de Vivienda**:
```php
$companeros = DB::table('tbl_alquiler as a2')
    ->join('tbl_usuario as u', 'u.id_usuario', '=', 'a2.id_inquilino_fk')
    ->where('a2.id_propiedad_fk', $idPropiedad)
    ->where('a2.estado_alquiler', 'activo')
    ->where('a2.id_inquilino_fk', '!=', $userId)
    ->get();
```

**Próxima Finalización**:
```php
$proximaFinalizacion = Carbon::parse($alquiler->fecha_fin_alquiler)
    ->diffInDays(now()) < 30;
```

---

## 💳 PAGOS / CUOTAS

### Ubicación
- **Vista**: `resources/views/inquilino/pagos.blade.php` (si existe)
- **Controlador**: Manejo en `InquilinoController`
- **Ruta**: `GET /inquilino/pagos?id_alquiler={id}`

### 📈 KPIs (Esperado)

- Total Cuotas
- Pagadas
- Pendientes
- Atrasadas
- Próximo vencimiento

### 📄 Tabla de Cuotas

**Columnas esperadas**:
| Columna | Tipo |
|---------|------|
| Mes | Date |
| Monto | Currency |
| Estado | Badge (pagado, pendiente, atrasado) |
| Fecha pago | Date |
| Recibo | Link PDF |

**Estados**:
- `pagado` - Verde
- `pendiente` - Amarillo
- `atrasado` - Rojo

### 💾 Realizar Pago

**Botón**: En tabla o sección de cuotas
- Abre modal/formulario
- Selecciona cuota(s) a pagar
- Elige método de pago
- Envío: `POST /inquilino/pagar`

**Parámetros**:
```json
{
    "id_cuota": 123,
    "metodo_pago": "tarjeta",  // o transferencia, efectivo
    "monto": 500.00
}
```

**Transacción BD**:
```php
DB::beginTransaction();
try {
    // INSERT tbl_pago
    $idPago = DB::table('tbl_pago')->insertGetId([...]);
    
    // UPDATE tbl_alquiler_cuota
    DB::table('tbl_alquiler_cuota')
        ->where('id_cuota', $idCuota)
        ->update(['estado' => 'pagado',  'id_pago_fk' => $idPago]);
    
    // INSERT tbl_notificacion (notif a arrendador)
    DB::commit();
}
```

---

## 🚨 INCIDENCIAS (Vista Inquilino)

### Ubicación
- **Vista**: `resources/views/inquilino/incidencias.blade.php` (si existe)
- **Ruta**: `GET /inquilino/incidencias?id_alquiler={id}`

### 📋 Listado de Incidencias

**Tabla/Lista**:
| Campo | Mostrado |
|-------|----------|
| Título | Sí |
| Descripción | Sí (truncada) |
| Categoría | Sí |
| Prioridad | Sí (badge) |
| Estado | Sí (badge) |
| Fecha | Sí |
| Acciones | Ver, Descargar PDF |

### ➕ Reportar Nueva Incidencia

**Botón**: "Reportar incidencia"
- Abre formulario/modal

**Campos**:
- Título (obligatorio)
- Descripción (obligatorio)
- Categoría (select: fontanería, electricidad, etc.)
- Prioridad (select: alta, media, baja)
- Fotos (file upload, opcional)

**Envío**: `POST /inquilino/incidencias/crear`

**Parámetros**:
```json
{
    "id_alquiler": 123,
    "titulo_incidencia": "Grifo roto",
    "descripcion_incidencia": "El grifo de la cocina gotea",
    "categoria_incidencia": "fontaneria",
    "prioridad_incidencia": "media"
}
```

**Transacción BD**:
```php
DB::beginTransaction();
try {
    $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
        'titulo_incidencia' => $titulo,
        'id_propiedad_fk' => $idPropiedad,
        'id_reporta_fk' => $userId,  // inquilino
        'estado_incidencia' => 'abierta',
        'creado_incidencia' => now()
    ]);
    
    // Crear registros de fotos si aplica
    // Insertar en historial
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

**Respuesta**:
- Éxito: Redirect con mensaje "Incidencia reportada"
- Error: JSON con errores

---

## 📄 CONTRATOS (Vista Inquilino)

### Ubicación
- **Vista**: Integrada en `ver_propiedad.blade.php`
- **Descarga**: `GET /inquilino/contrato/{id_contrato}/descargar`

### 📋 Información del Contrato

**Mostrado**:
- Título contrato
- Fecha inicio - fin
- Monto alquiler
- Arrendador
- Gestor (si aplica)
- Firmas (arrendador, inquilino)
- Botón: Descargar PDF

**Estado del Contrato**:
- `Firmado por ambos` - Verde
- `Pendiente firma arrendador` - Amarillo
- `Pendiente firma inquilino` - Amarillo
- `Cancelado` - Rojo

### ✍️ Firma del Inquilino

**Trigger**: Si contrato está pendiente de firma inquilino

**Flujo**:
1. Click "Firmar contrato"
2. Modal/Canvas para dibujar firma
3. Botones: Limpiar, Cancelar, Firmar
4. Envío: `POST /inquilino/contrato/{id}/firmar`

**Actualización BD**:
- UPDATE tbl_contrato SET firmado_inquilino = 1
- UPDATE tbl_contrato SET fecha_firma_inquilino = NOW()

---

## 🔔 NOTIFICACIONES

### Ubicación (Generales)
- En header/navbar del layout
- Badge con contador
- Dropdown de últimas notificaciones

### Tipos de Notificaciones para Inquilino

| Evento | Mensaje | Trigger |
|--------|---------|---------|
| Pago recibido | "Tu pago de $X ha sido confirmado" | INSERT tbl_pago |
| Cuota atrasada | "Tienes una cuota vencida" | UPDATE cuota estado = 'atrasado' |
| Incidencia actualizada | "Tu incidencia [título] ha sido actualizado a [estado]" | UPDATE tbl_incidencia estado |
| Contrato enviado | "Nuevo contrato para firmar" | INSERT tbl_contrato |

**Almacenamiento**: `tbl_notificacion`
- Campos: id_usuario_fk, titulo_notificacion, mensaje_notificacion, etc.

---

## 📝 Actualización Automática de Cuotas Atrasadas

**Función**: `actualizarCuotasAtrasadas($userId)`

**Lógica** (en Controlador):
```php
private function actualizarCuotasAtrasadas($userId)
{
    $cuotasVencidas = DB::table('tbl_alquiler_cuota as c')
        ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
        ->where('a.id_inquilino_fk', $userId)
        ->where('c.estado', 'pendiente')
        ->where('c.mes_cuota', '<', now()->toDateString())
        ->get();
    
    foreach ($cuotasVencidas as $cuota) {
        DB::table('tbl_alquiler_cuota')
            ->where('id_cuota', $cuota->id_cuota)
            ->update(['estado' => 'atrasado']);
        
        // Crear notificación
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $userId,
            'titulo_notificacion' => 'Pago vencido',
            'mensaje_notificacion' => 'Tienes una cuota vencida...'
        ]);
    }
}
```

**Trigger**: Se ejecuta al cargar página de gestión de propiedades

---

## 🎨 Estructura CSS

| Archivo | Ubicación | Contenido |
|---------|-----------|----------|
| gestionar_propiedades.css | `public/css/inquilino/` | KPIs, filtros, grid de propiedades |
| ver_propiedad.css | `public/css/inquilino/` | Galería, detalles, sidebar |

### Ocultamiento Responsivo

```css
@media (max-width: 768px) {
    .columna-derecha {
        display: none;  /* Oculta sidebar en móvil */
    }
    
    .columna-izquierda {
        grid-column: 1 / -1;  /* Toma toda la página */
    }
}
```

---

## 🔐 Control de Acceso

**En Controlador**:
```php
$alquileresActivosInquilino = DB::table('tbl_alquiler')
    ->where('id_inquilino_fk', $userId)
    ->where('estado_alquiler', 'activo')
    ->exists();

if (!$alquileresActivosInquilino && !$alquileresActivosPropietario) {
    return redirect('/login')->with('error', 'Acceso restringido...');
}
```

**Solo usuarios con alquileres activos pueden acceder**

---

## 🔗 Rutas Completas

| Acción | Método | Ruta |
|--------|--------|------|
| Gestión de propiedades | GET | `/inquilino/gestionar-propiedades` |
| Ver propiedad | GET | `/inquilino/ver-propiedad/{id_alquiler}` |
| Pagos/Cuotas | GET | `/inquilino/pagos?id_alquiler={id}` |
| Realizar pago | POST | `/inquilino/pagar` |
| Incidencias | GET | `/inquilino/incidencias?id_alquiler={id}` |
| Reportar incidencia | POST | `/inquilino/incidencias/crear` |
| Descargar contrato | GET | `/inquilino/contrato/{id}/descargar` |
| Firmar contrato | POST | `/inquilino/contrato/{id}/firmar` |

---

## 📊 Resumen Técnico

| Aspecto | Detalles |
|--------|----------|
| **Vistas Principales** | 3-4 (gestionar_propiedades, ver_propiedad, pagos, incidencias) |
| **Acceso** | Solo usuarios autenticados con alquileres activos |
| **Transacciones BD** | Sí (pagos, incidencias, firmas) |
| **KPIs Dinámicos** | 2 principales (contratos activos, incidencias)  |
| **Filtros** | Client-side (búsqueda, ciudad) |
| **Notificaciones** | Sí, automáticas (pagos, cuotas vencidas, actualizaciones) |
| **Firmas Digitales** | Canvas HTML5 (contratos, si aplica) |

---

**Última actualización**: 3 de mayo de 2026
**Versión**: 1.0
