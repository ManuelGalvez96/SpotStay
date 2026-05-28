# SpotStay Admin — Seeders y Controladores

Documentación completa de los seeders, controladores Admin y vistas dinámicas implementadas para SpotStay en Laravel 13.4.0.

## 📋 Archivos generados

| Archivo | Ruta | Propósito |
|---------|------|----------|
| **RolSeeder.php** | `database/seeders/` | Inserta 5 roles base (Admin, Arrendador, Inquilino, Gestor, Miembro) |
| **UsuarioSeeder.php** | `database/seeders/` | Genera 30 usuarios con credenciales conocidas y roles asignados |
| **SuscripcionSeeder.php** | `database/seeders/` | Crea suscripciones para arrendadores (planes basico/pro) |
| **PropiedadSeeder.php** | `database/seeders/` | Inserta 15 propiedades distribuidas en varias ciudades españolas |
| **AlquilerSeeder.php** | `database/seeders/` | Genera 8 alquileres activos y pendientes |
| **ContratoSeeder.php** | `database/seeders/` | Crea contratos firmados para alquileres activos |
| **PagoSeeder.php** | `database/seeders/` | Inserta pagos de fianza y mensualidades |
| **IncidenciaSeeder.php** | `database/seeders/` | Genera 18 incidencias en diferentes estados |
| **HistorialIncidenciaSeeder.php** | `database/seeders/` | Registra el historial de cambios de incidencias |
| **ConversacionSeeder.php** | `database/seeders/` | Crea 8 conversaciones directas y de grupo |
| **MensajeSeeder.php** | `database/seeders/` | Inserta mensajes en las conversaciones |
| **NotificacionSeeder.php** | `database/seeders/` | Genera 20 notificaciones variadas |
| **SolicitudArrendadorSeeder.php** | `database/seeders/` | Crea 17 solicitudes de arrendador (pendientes, aprobadas, rechazadas) |
| **DatabaseSeeder.php** | `database/seeders/` | Orquesta la ejecución de todos los seeders |
| **DashboardController.php** | `app/Http/Controllers/Admin/` | Controlador del panel principal con KPIs y datos dinámicos |
| **UsuarioController.php** | `app/Http/Controllers/Admin/` | CRUD de usuarios con filtrado y exportación |
| **PropiedadController.php** | `app/Http/Controllers/Admin/` | CRUD de propiedades con búsqueda avanzada |
| **SolicitudController.php** | `app/Http/Controllers/Admin/` | Gestión de solicitudes de arrendador (aprobar/rechazar) |
| **IncidenciaController.php** | `app/Http/Controllers/Admin/` | Kanban de incidencias con asignación a gestores |
| **dashboard.blade.php** | `resources/views/admin/` | Vista principal con KPIs, gráficos y datos dinámicos |
| **usuarios.blade.php** | `resources/views/admin/` | Tabla paginada de usuarios con filtros y avatares |
| **propiedades.blade.php** | `resources/views/admin/` | Grid de propiedades con colores e información |
| **solicitudes.blade.php** | `resources/views/admin/` | Panel de solicitudes con lista de aprobadas/rechazadas |
| **incidencias.blade.php** | `resources/views/admin/` | Tablero Kanban con 4 columnas de estados |

---

## 🚀 Cómo ejecutar los seeders

### Primera vez (base de datos vacía)

Ejecuta estas comandos en el terminal bajo la raíz del proyecto:

```bash
php artisan migrate
php artisan db:seed
```

Esto ejecutará todas las migraciones y después todos los seeders en el orden correcto.

### Refrescar datos de prueba

Si ya tienes la base de datos creada y quieres resetearla con nuevos datos:

```bash
php artisan migrate:fresh --seed
```

⚠️ **Advertencia**: Este comando borrará todos los datos existentes y recreará las tablas.

### Ejecutar un seeder concreto

Para ejecutar solo un seeder específico:

```bash
php artisan db:seed --class=UsuarioSeeder
php artisan db:seed --class=PropiedadSeeder
php artisan db:seed --class=IncidenciaSeeder
```

---

## 🔐 Credenciales de usuarios de prueba

Todos los usuarios tienen un email único y una contraseña para pruebas:

| Nombre | Email | Contraseña | Rol | Propósito |
|--------|-------|------------|-----|----------|
| Administrador SpotStay | `admin@spotstay.com` | `admin1234` | Admin | Acceso total al panel |
| Carlos García | `carlos@spotstay.com` | `carlos1234` | Arrendador | Propietario principal (3 propiedades) |
| Elena Vargas | `elena@spotstay.com` | `elena1234` | Arrendador | Propietario (2 propiedades en Barcelona) |
| Roberto Mora | `roberto.mora@spotstay.com` | `roberto1234` | Arrendador | Propietario (3 propiedades premium) |
| Laura Martínez | `laura@spotstay.com` | `laura1234` | Inquilino | Inquilina activa (Calle Mayor) |
| Pedro Ruiz | `pedro@spotstay.com` | `pedro1234` | Inquilino | Inquilino activo (Calle Serrano) |
| Sofía López | `sofia@spotstay.com` | `sofia1234` | Inquilino | Inquilina activa (Av. Diagonal) |
| Miguel Fernández | `miguel@spotstay.com` | `miguel1234` | Gestor | Gestor de incidencias |
| Ana Torres | `ana@spotstay.com` | `ana1234` | Miembro | Miembro potencial arrendador |
| +21 usuarios adicionales | `nombre.apellido@email.com` | `password123` | Varios | Test adicionales |

**Nota sobre contraseñas**: Las contraseñas están hasheadas con `bcrypt()` en la base de datos. En development, puedes cambiarlas fácilmente editando el seeder.

---

## 📊 Qué genera cada seeder

### 1. RolSeeder
**Registros**: 5  
**Datos**: Crea los 5 roles del sistema
```
- Admin (slug: admin)
- Arrendador (slug: arrendador)
- Inquilino (slug: inquilino)
- Gestor (slug: gestor)
- Miembro (slug: miembro)
```

### 2. UsuarioSeeder
**Registros**: 30 (9 fijos + 21 aleatorios)  
**Datos generados**:
- 9 usuarios con credenciales conocidas
- 21 usuarios adicionales con emails automáticos
- Asignación automática de roles en `tbl_rol_usuario`
- 3 usuarios inactivos para probar filtros

### 3. SuscripcionSeeder
**Registros**: 1 por cada arrendador (~13)  
**Datos**:
- Plan "pro" para 3 arrendadores principales (máx 10 propiedades)
- Plan "básico" para el resto (máx 3 propiedades)
- Vigencia: -6 meses a +6 meses desde hoy

### 4. PropiedadSeeder
**Registros**: 15  
**Distribución por ciudad**:
- Madrid: 3 propiedades
- Barcelona: 2 propiedades
- Málaga: 1 propiedad
- Sevilla: 1 propiedad
- Valencia: 2 propiedades
- Bilbao: 1 propiedad
- Resto de ciudades: 5 propiedades
**Datos por propiedad**:
- Título, dirección, ciudad, código postal
- Coordenadas GPS (latitud/longitud)
- Descripción realista
- Precio mensual (500€ - 2200€)
- Gastos desagregados (JSON: agua, luz, comunidad, gas)
- Estado: publicada, alquilada, borrador o inactiva

### 5. AlquilerSeeder
**Registros**: 8 alquileres  
**Estados**:
- 5 activos (con aprobación registrada)
- 3 pendientes (sin admin asignado)
**Datos**:
- Relaciones con propiedad, inquilino y admin
- Fechas de inicio/fin (contratos anuales)
- Fecha de aprobación cuando es activo

### 6. ContratoSeeder
**Registros**: 5 (1 por cada alquiler activo)  
**Datos**:
- URL del contrato PDF
- Hash SHA256 del URL
- Fechas de firma (1 día antes del inicio del alquiler)
- IPs de firma (127.0.0.1 para testing)
- Firmado por arrendador e inquilino
- Estado: "firmado"

### 7. PagoSeeder
**Registros**: ~20 (4 por alquiler activo)  
**Por alquiler**:
- 1 pago de fianza (x2 precio mensual)
- 3 pagos mensuales confirmados (meses 1-3)
- 1 pago pendiente (mes actual)
**Estados**:
- Fianza y 3 primeros meses: "confirmado"
- Mes actual: "pendiente" (sin referencia ni fecha)

### 8. IncidenciaSeeder
**Registros**: 18 incidencias  
**Por estado**:
- 6 ABIERTAS (prioridades urgente/alta/media/baja)
- 5 EN PROCESO (asignadas a Miguel Fernández)
- 4 RESUELTAS (con historial completo)
- 3 CERRADAS (archivadas)
**Categorías**:
- fontanería, calefacción, electricidad, otro
**Datos realistas** de problemas comunes en inmuebles

### 9. HistorialIncidenciaSeeder
**Registros**: ~45 (historial de 18 incidencias)  
**Eventos por incidencia**:
- Registro 1: Incidencia reportada
- Registro 2: Revisada por admin
- Registro 3: Asignada a gestor (si en_proceso+)
- Registro 4: Resuelta (si resuelta+)
- Registro 5: Cerrada (si cerrada)

### 10. ConversacionSeeder
**Registros**: 8 conversaciones  
**Tipos**:
- 4 directas arrendador-inquilino (con propiedad)
- 2 grupos (arrendador + inquilino + gestor)
- 2 directas miembro-arrendador (sin propiedad)

### 11. MensajeSeeder
**Registros**: 25-30 mensajes  
**Distribución**:
- Conversación Calle Mayor: 5 mensajes específicos con contenido real
- Resto: 3-4 mensajes genéricos alternando remitentes
- **Último mensaje de cada conversación**: sin leer (`leido_mensaje: false`)

### 12. NotificacionSeeder
**Registros**: 20 notificaciones  
**Para admin**:
- 5 de tipo "nueva_solicitud" (3 sin leer)
- 5 de tipo "alquiler_pendiente" (2 sin leer)
**Para Carlos García**:
- 1 "nueva_incidencia" (sin leer)
- 1 "mensaje_nuevo" (sin leer)
**Para Laura Martínez**:
- 1 "alquiler_aprobado" (leída)
- 1 "mensaje_nuevo" (sin leer)
- 1 "pago_confirmado" (leída)
- 1 "incidencia_actualizada" (sin leer)
**Para otros usuarios**: 3 notificaciones variadas

### 13. SolicitudArrendadorSeeder
**Registros**: 17 solicitudes  
**Distribuidas**:
- 9 PENDIENTES (con detalles de propiedad)
- 5 APROBADAS (con id_admin_revisa_fk asignado)
- 3 RECHAZADAS (con notas específicas del motivo)
**Datos de propiedad**:
- Dirección, ciudad, tipo (Piso/Estudio/Ático)
- Precio estimado, habitaciones, baños, tamaño
- Descripción realista

---

## 🔗 Cómo están conectados los controladores con las vistas

### DashboardController → dashboard.blade.php

El controlador devuelve estas 8 variables:

```php
compact(
    'totalUsuarios',           // int: total de usuarios en el sistema
    'propiedadesActivas',      // int: propiedades publicadas o alquiladas
    'alquileresPendientes',    // int: alquileres sin aprobar
    'solicitudesNuevas',       // int: solicitudes pendientes de revisar
    'ultimosAlquileres',       // Collection: últimos 5 alquileres con detalles
    'ultimasSolicitudes',      // Collection: últimas 3 solicitudes pendientes
    'usuariosPorRol',          // Collection: distribución de usuarios por rol
    'actividadReciente'        // Collection: últimas 5 notificaciones del admin
)
```

**Flujo de ejecución**:
1. Usuario accede a `/admin/dashboard`
2. `DashboardController@index()` ejecuta queries con DB::table()
3. Compacta las 8 variables
4. Pasa a `admin.dashboard` view
5. Vista renderiza KPIs, tabla dinám, gráficos y timeline

---

### UsuarioController → usuarios.blade.php

Variables devueltas:

```php
compact(
    'usuarios',              // Paginated: 10 usuarios por página con rol
    'totalUsuarios',         // int: total
    'activos',              // int: activos_usuario = true
    'inactivos',            // int: activos_usuario = false
    'esteMes'               // int: usuario creados este mes
)
```

**Métodos AJAX**:
- `filtrar()`: POST a `/admin/usuarios/filtrar?rol=X&estado=Y&q=Z`
- `toggleEstado()`: POST a `/admin/usuarios/{id}/toggle-estado`

---

### PropiedadController → propiedades.blade.php

Variables:

```php
compact(
    'propiedades',       // Paginated: 10 propiedades con arrendador
    'totalPropiedades',  // int
    'alquiladas',       // int
    'publicadas',       // int
    'inactivas'         // int
)
```

**Métodos**:
- `filtrar()`: GET a `/admin/propiedades/filtrar?estado=X&ciudad=Y&precioMax=Z`
- `desactivar()`: POST a `/admin/propiedades/{id}/desactivar`

---

### SolicitudController → solicitudes.blade.php

Variables:

```php
compact(
    'solicitudesPendientes',  // Paginated: 10 por página
    'aprobadas',             // int (este mes)
    'rechazadas',            // int (este mes)
    'totalSolicitudes',      // int (todas las épocas)
    'ultimasAprobadas',      // Collection: últimas 5
    'ultimasRechazadas'      // Collection: últimas 3
)
```

**Métodos AJAX**:
- `aprobar()`: POST a `/admin/solicitudes/{id}/aprobar`
  - Cambia estado a "aprobada"
  - Asigna rol "arrendador" al usuario
- `rechazar()`: POST a `/admin/solicitudes/{id}/rechazar`
  - Body JSON: `{ "motivo": "Texto del rechazo" }`

---

### IncidenciaController → incidencias.blade.php

Variables:

```php
compact(
    'abiertas',       // Collection: incidencias con estado="abierta"
    'enProceso',      // Collection: estado="en_proceso"
    'resueltas',      // Collection: estado="resuelta"
    'cerradas',       // Collection: estado="cerrada"
    'totalAbiertas',  // int
    'totalEnProceso', // int
    'totalResueltas', // int
    'urgentes'        // int: prioridad="urgente" y está abierta o en proceso
)
```

---

## 📱 Variables disponibles en cada vista

### dashboard.blade.php

| Variable | Tipo | Ejemplo |
|----------|------|---------|
| `$totalUsuarios` | int | 30 |
| `$propiedadesActivas` | int | 12 |
| `$alquileresPendientes` | int | 3 |
| `$solicitudesNuevas` | int | 9 |
| `$ultimosAlquileres` | Collection | Alquileres con: id_alquiler, titulo_propiedad, ciudad_propiedad, nombre_inquilino, nombre_arrendador, estado_alquiler, creado_alquiler |
| `$ultimasSolicitudes` | Collection | Solicitudes con: id_solicitud_arrendador, nombre_usuario, datos_solicitud_arrendador (JSON con ciudad), creado_solicitud_arrendador |
| `$usuariosPorRol` | Collection | Roles con: nombre_rol, total |
| `$actividadReciente` | Collection | Notificaciones con: tipo_notificacion, datos_notificacion (JSON con titulo), creado_notificacion |

### usuarios.blade.php

| Variable | Tipo |
|----------|------|
| `$usuarios` | LengthAwarePaginator | Usuarios con: id_usuario, nombre_usuario, email_usuario, slug_rol, nombre_rol, total_propiedades, activo_usuario, creado_usuario |
| `$totalUsuarios` | int |
| `$activos` | int |
| `$inactivos` | int |
| `$esteMes` | int |

### propiedades.blade.php

| Variable | Tipo |
|----------|------|
| `$propiedades` | LengthAwarePaginator | Con: id_propiedad, titulo_propiedad, ciudad_propiedad, descripcion_propiedad, precio_propiedad, estado_propiedad, nombre_arrendador, total_inquilinos |
| `$totalPropiedades` | int |
| `$alquiladas` | int |
| `$publicadas` | int |
| `$inactivas` | int |

### solicitudes.blade.php

| Variable | Tipo |
|----------|------|
| `$solicitudesPendientes` | LengthAwarePaginator | Con: id_solicitud_arrendador, nombre_usuario, email_usuario, telefono_usuario, datos_solicitud_arrendador (JSON), creado_solicitud_arrendador |
| `$aprobadas`, `$rechazadas`, `$totalSolicitudes` | int |
| `$ultimasAprobadas`, `$ultimasRechazadas` | Collection |

### incidencias.blade.php

| Variable | Tipo |
|----------|------|
| `$abiertas`, `$enProceso`, `$resueltas`, `$cerradas` | Collection | Con: id_incidencia, titulo_incidencia, descripcion_incidencia, categoria_incidencia, prioridad_incidencia, estado_incidencia, titulo_propiedad, direccion_propiedad, nombre_inquilino, nombre_gestor, creado_incidencia |
| `$totalAbiertas`, `$totalEnProceso`, `$totalResueltas`, `$urgentes` | int |

---

## 🛣️ Rutas disponibles

Todas las rutas son **accesibles sin login** (SIN middleware). Esto es temporal para desarrollo.

### Dashboard
| Método | URL | Controlador | Devuelve |
|--------|-----|-------------|----------|
| GET | `/admin/dashboard` | DashboardController@index | HTML con KPIs y datos |
| POST | `/admin/alquiler/{id}/aprobar` | DashboardController@aprobarAlquiler | JSON: `{ success: true }` |
| POST | `/admin/alquiler/{id}/rechazar` | DashboardController@rechazarAlquiler | JSON: `{ success: true }` |

### Usuarios
| Método | URL | Controlador | Devuelve |
|--------|-----|-------------|----------|
| GET | `/admin/usuarios` | UsuarioController@index | HTML paginado |
| GET | `/admin/usuarios/filtrar?rol=X&estado=Y&q=Z` | UsuarioController@filtrar | JSON con usuarios filtrados |
| GET | `/admin/usuarios/{id}` | UsuarioController@show | JSON del usuario |
| POST | `/admin/usuarios/{id}/toggle-estado` | UsuarioController@toggleEstado | JSON: `{ success: true, activo: bool }` |
| GET | `/admin/usuarios/exportar` | UsuarioController@exportar | JSON array de usuarios |

### Propiedades
| Método | URL | Controlador | Devuelve |
|--------|-----|-------------|----------|
| GET | `/admin/propiedades` | PropiedadController@index | HTML paginado |
| GET | `/admin/propiedades/filtrar?estado=X&ciudad=Y` | PropiedadController@filtrar | JSON con propiedades |
| GET | `/admin/propiedades/{id}` | PropiedadController@show | JSON de propiedad + alquileres |
| POST | `/admin/propiedades/{id}/desactivar` | PropiedadController@desactivar | JSON: `{ success: true }` |
| GET | `/admin/propiedades/exportar` | PropiedadController@exportar | JSON array de propiedades |

### Solicitudes
| Método | URL | Controlador | Devuelve |
|--------|-----|-------------|----------|
| GET | `/admin/solicitudes` | SolicitudController@index | HTML paginado |
| GET | `/admin/solicitudes/{id}` | SolicitudController@show | JSON de solicitud |
| POST | `/admin/solicitudes/{id}/aprobar` | SolicitudController@aprobar | JSON: `{ success: true }` |
| POST | `/admin/solicitudes/{id}/rechazar` | SolicitudController@rechazar | JSON: `{ success: true }` |

**Body para rechazar** (JSON):
```json
{ "motivo": "Documentación incompleta" }
```

### Incidencias
| Método | URL | Controlador | Devuelve |
|--------|-----|-------------|----------|
| GET | `/admin/incidencias` | IncidenciaController@index | HTML con 4 columnas Kanban |
| GET | `/admin/incidencias/{id}` | IncidenciaController@show | JSON: incidencia + historial |
| POST | `/admin/incidencias/{id}/estado` | IncidenciaController@cambiarEstado | JSON: `{ success: true }` |
| POST | `/admin/incidencias/{id}/asignar` | IncidenciaController@asignar | JSON: `{ success: true }` |

**Body para cambiar estado** (JSON):
```json
{
  "estado": "en_proceso|resuelta|cerrada",
  "comentario": "Texto del cambio"
}
```

**Body para asignar** (JSON):
```json
{ "id_gestor": 8 }
```

---

## 🔄 Cómo funcionan los filtros AJAX

Todas las vistas usan `fetch` con `.then()` para comunicarse con el servidor sin recargar.

### Ejemplo: Filtrado de Usuarios

```html
<select id="filtroRol">
  <option value="">Todos</option>
  <option value="admin">Admin</option>
  <option value="arrendador">Arrendador</option>
</select>

<script>
document.getElementById('filtroRol').addEventListener('change', function() {
    const rol = this.value;
    
    fetch('/admin/usuarios/filtrar?rol=' + rol)
        .then(response => response.json())
        .then(data => {
            console.log('Usuarios:', data.usuarios);
            console.log('Total:', data.total);
            // Aquí se actualizaría el DOM
        });
});
</script>
```

### Ejemplo: Aprobar Alquiler

```javascript
document.querySelector('.btn-aprobar').addEventListener('click', function() {
    const id = this.dataset.id;
    
    fetch('/admin/alquiler/' + id + '/aprobar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();  // Recarga la página
        }
    });
});
```

### Ejemplo: Rechazar Solicitud

```javascript
const motivo = prompt('¿Motivo del rechazo?');

fetch('/admin/solicitudes/' + id + '/rechazar', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ motivo: motivo })
})
.then(r => r.json())
.then(d => {
    if (d.success) location.reload();
});
```

**Patrón común**:
1. Haz click en un botón
2. Se envía `fetch()` a la ruta
3. El controlador procesa la solicitud
4. Devuelve JSON con `{ success: true }`
5. JavaScript recarga o actualiza el DOM

---

## ➕ Cómo añadir más datos de prueba a los seeders

### Opción 1: Editando el seeder y ejecutándolo

Edita `database/seeders/UsuarioSeeder.php`:

```php
// Añade más usuarios en el array $nombresAdicionales
$nombresAdicionales = [
    // ... existentes ...
    ['nombre' => 'Tu Nombre', 'email' => 'tunombre@email.com', 'rol' => 'arrendador'],
];
```

Luego ejecuta:
```bash
php artisan migrate:fresh --seed
```

### Opción 2: Crear un nuevo seeder

```bash
php artisan make:seeder PruebasExtrasSeeder
```

Edita `database/seeders/PruebasExtrasSeeder.php`:

```php
public function run(): void
{
    DB::table('tbl_usuario')->insert([
        'nombre_usuario' => 'Prueba',
        'email_usuario' => 'prueba@test.com',
        'contrasena_usuario' => bcrypt('test123'),
        // ...
    ]);
}
```

Añádelo a `DatabaseSeeder.php`:

```php
$this->call([
    // ... existentes ...
    PruebasExtrasSeeder::class,
]);
```

### Opción 3: Inserción manual en base de datos

```php
php artisan tinker

>>> DB::table('tbl_usuario')->insert([
    'nombre_usuario' => 'Test',
    'email_usuario' => 'test@test.com',
    'contrasena_usuario' => bcrypt('test123'),
    'activo_usuario' => true,
    'creado_usuario' => now(),
]);
```

---

## ⚠️ Problemas conocidos y soluciones

### Error: "SQLSTATE[23000]: Integrity constraint violation"

**Causa**: Intenta insertar un email duplicado  
**Solución**:
```bash
php artisan migrate:fresh
php artisan db:seed
```

O edita los seeders para que generen emails únicos.

---

### Error: "Class 'App\\Http\\Controllers\\Admin\\DashboardController' not found"

**Causa**: Controller no existe o namespace incorrecto  
**Solución**:
```bash
# Verifica que el archivo existe
ls app/Http/Controllers/Admin/DashboardController.php

# Si no existe, recrealo
php artisan make:controller Admin/DashboardController
```

---

### Error: "Trying to get property 'total' of non-object"

**Causa**: La query devuelve null o colección vacía  
**Solución**: Añade `@forelse` en la vista:

```blade
@forelse($usuarios as $user)
    <tr>{{ $user->nombre }}</tr>
@empty
    <tr><td colspan="3">No hay datos</td></tr>
@endforelse
```

---

### Las rutas devuelven 404

**Causa**: Laravel no encuentra las rutas en `web.php`  
**Solución**:
```bash
# Borra caché de rutas
php artisan route:clear

# Registra las rutas nuevamente
php artisan route:cache
```

---

### Datos no se reflejan en la vista

**Causa**: El controlador no pasó las variables correctamente  
**Solución**:
```php
// Asegúrate de usar compact() correctamente
return view('admin.usuarios', compact('usuarios', 'totalUsuarios'));

// O pasa como array
return view('admin.usuarios', [
    'usuarios' => $usuarios,
    'totalUsuarios' => $totalUsuarios
]);
```

---

## 🔐 Seguridad en desarrollo

⚠️ **IMPORTANTE**: Las rutas actuales **NO tienen autenticación**. Esto es solo para desarrollo.

Cuando estés listo para producción, añade middleware:

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    // ... resto de rutas
});
```

O protege cada ruta individualmente:

```php
Route::get('/admin/dashboard', [DashboardController::class, 'index'])->middleware('auth:admin');
```

---

## 📚 Estructura de carpetas

```
SpotStay/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Admin/                        ← Controllers nuevos
│               ├── DashboardController.php
│               ├── UsuarioController.php
│               ├── PropiedadController.php
│               ├── SolicitudController.php
│               └── IncidenciaController.php
├── database/
│   └── seeders/                              ← Todos los seeders
│       ├── DatabaseSeeder.php
│       ├── RolSeeder.php
│       ├── UsuarioSeeder.php
│       ├── SuscripcionSeeder.php
│       ├── PropiedadSeeder.php
│       ├── AlquilerSeeder.php
│       ├── ContratoSeeder.php
│       ├── PagoSeeder.php
│       ├── IncidenciaSeeder.php
│       ├── HistorialIncidenciaSeeder.php
│       ├── ConversacionSeeder.php
│       ├── MensajeSeeder.php
│       ├── NotificacionSeeder.php
│       └── SolicitudArrendadorSeeder.php
└── resources/
    └── views/
        └── admin/                            ← Vistas actualizadas
            ├── dashboard.blade.php
            ├── usuarios.blade.php
            ├── propiedades.blade.php
            ├── solicitudes.blade.php
            └── incidencias.blade.php
```

---

## ✅ Checklist de verificación

Después de ejecutar los seeders, verifica:

- [ ] `php artisan db:seed` ejecuta sin errores
- [ ] Base de datos tiene 30 usuarios
- [ ] 15 propiedades creadas
- [ ] `/admin/dashboard` muestra números correctos
- [ ] `/admin/usuarios` pagina usuarios
- [ ] `/admin/propiedades` cargar grid de propiedades
- [ ] `/admin/solicitudes` muestra lista
- [ ] `/admin/incidencias` muestra Kanban
- [ ] Puedes hacer login con `admin@spotstay.com` / `admin1234`
- [ ] Filtros AJAX funcionan sin recargar página
- [ ] Botones AJAX Aprobar/Rechazar funcionan

---

## 📞 Contacto y soporte

Para problemas o mejoras:
1. Revisa los logs: `storage/logs/laravel.log`
2. Ejecuta `php artisan tinker` para debuguear
3. Verifica que las migraciones se ejecutaron: `php artisan migrate:status`

---

**Última actualización**: Abril 2025  
**Laravel**: 13.4.0  
**PHP**: 8.2+  
**Base de datos**: MySQL 8.0+
