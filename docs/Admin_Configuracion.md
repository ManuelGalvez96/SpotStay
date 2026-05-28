# ⚙️ README: ADMIN CONFIGURACIÓN

**Vista:** `resources/views/admin/configuracion.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/ConfiguracionController.php`  
**Ruta:** `GET /admin/configuracion`

---

## 🎯 Propósito

Gestiona **notificaciones globales** que envían los administradores. Permite:
- Enviar notificaciones a usuarios por rol o individuales
- Elegir destino: todos, rol específico, o usuario concreto
- Escribir títulos y mensajes personalizados
- Agregar enlaces opcionales
- Validar y enviar notificaciones masivas

---

## 🎛️ Formulario de Notificaciones

| Campo | ID HTML | Tipo | Opciones |
|-------|---------|------|----------|
| **Rol Destino** | `#destinoRolNotificacion` | select | Todos, Admin, Arrendador, Inquilino, Gestor |
| **Alcance** | `#alcanceDestinoNotificacion` | select | Todos del rol / Usuario concreto |
| **Usuario** | `#usuarioDestinoNotificacion` | select | Listar usuarios activos |
| **Título** | `nombre="titulo_notificacion"` | input text | Máx 200 caracteres |
| **Enlace** | `nombre="url_notificacion"` | input text | `/admin/...` o URL completa |
| **Mensaje** | `nombre="mensaje_notificacion"` | textarea | Máx 1000 caracteres |

**JavaScript de validación:**
```javascript
// Mostrar/ocultar campos según rol seleccionado
document.getElementById('destinoRolNotificacion').addEventListener('change', function(e) {
    const rol = e.target.value;
    
    // Mostrar alcance solo si seleccionó un rol específico
    if (rol && rol !== 'todos') {
        document.getElementById('bloqueAlcanceNotificacion').classList.remove('d-none');
    } else {
        document.getElementById('bloqueAlcanceNotificacion').classList.add('d-none');
    }
});

// Mostrar/ocultar selector de usuario
document.getElementById('alcanceDestinoNotificacion').addEventListener('change', function(e) {
    const alcance = e.target.value;
    
    if (alcance === 'usuario') {
        document.getElementById('bloqueUsuarioNotificacion').classList.remove('d-none');
    } else {
        document.getElementById('bloqueUsuarioNotificacion').classList.add('d-none');
    }
});

// Validación de formulario
document.getElementById('form-notificacion-admin').addEventListener('submit', function(e) {
    const titulo = document.querySelector('input[name="titulo_notificacion"]').value.trim();
    const mensaje = document.querySelector('textarea[name="mensaje_notificacion"]').value.trim();
    
    if (!titulo || !mensaje) {
        e.preventDefault();
        alert('Título y mensaje son obligatorios');
        return false;
    }
});
```

---

## 📱 Responsive Design

### 🖥️ **Desktop (1200px+)**
- ✅ Formulario: Grid de 12 columnas
- ✅ Campos lado a lado (col-md-4, col-md-6)
- ✅ Textarea: Ancho completo
- ✅ Botón submit visible

**Layout:**
```
[Rol Destino]    [Alcance]    [Usuario]
[Título 50%] [Enlace 50%]
[Mensaje 100%]
[Botón]
```

### 📱 **Mobile (< 768px)**
- ✅ Formulario: Stack vertical (col-12)
- ✅ Todos los campos 100% ancho
- ✅ Campos se muestran uno debajo de otro
- ✅ Botón responsive

**CSS:**
```css
@media (max-width: 768px) {
    .col-md-4,
    .col-md-6 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
}
```

---

## 📊 Datos Pasados a la Vista

```php
compact(
    'rolesDisponibles',  // Collection de roles
    'usuariosActivos'    // Collection de usuarios activos
)
```

**Roles disponibles:**
```php
[
    {
        'id_rol' => 1,
        'nombre_rol' => 'Admin',
        'slug_rol' => 'admin'
    },
    {
        'id_rol' => 2,
        'nombre_rol' => 'Arrendador',
        'slug_rol' => 'arrendador'
    }
    // ... etc
]
```

**Usuarios activos:**
```php
[
    {
        'id_usuario' => 5,
        'nombre_usuario' => 'Juan García',
        'email_usuario' => 'juan@mail.com',
        'roles_usuario' => 'arrendador,gestor'  // JSON string
    }
]
```

---

## 🔘 Botones y Acciones

| Botón | Función | Endpoint | Acción |
|-------|---------|----------|--------|
| **Enviar Notificación** | Envía notificación | POST `/admin/configuracion/notificaciones/crear` | INSERT + SEND |

**Backend:**
```php
public function crearNotificacion(Request $request) {
    $destino = $request->input('destino');      // 'todos' o slug_rol
    $alcance = $request->input('alcance_destino');
    $usuarioId = $request->input('usuario_destino');
    $titulo = $request->input('titulo_notificacion');
    $mensaje = $request->input('mensaje_notificacion');
    $url = $request->input('url_notificacion');
    
    // Determinar usuarios destino
    $usuariosDestino = [];
    
    if ($alcance === 'usuario' && $usuarioId) {
        $usuariosDestino = [$usuarioId];
    } else if ($destino === 'todos') {
        $usuariosDestino = DB::table('tbl_usuario')
            ->pluck('id_usuario')
            ->toArray();
    } else {
        // Por rol específico
        $usuariosDestino = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol', $destino)
            ->pluck('tbl_usuario.id_usuario')
            ->toArray();
    }
    
    // Crear notificaciones
    foreach ($usuariosDestino as $userId) {
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $userId,
            'titulo_notificacion' => $titulo,
            'mensaje_notificacion' => $mensaje,
            'url_notificacion' => $url,
            'leida_notificacion' => false,
            'creado_notificacion' => now()
        ]);
    }
    
    return redirect()->back()->with('mensaje_exito_plan', 
        'Notificación enviada a ' . count($usuariosDestino) . ' usuarios');
}
```

---

## 📊 Datos que muestra

| Dato | Fuente | Qué es |
|------|--------|--------|
| **Notificaciones Email** | .env o tabla config | Activadas/desactivadas |
| **SMTP** | config/mail.php | Servidor, puerto, usuario |
| **Idioma** | config/app.php o tabla | es, en, etc |
| **Timezone** | config/app.php | UTC, Europe/Madrid, etc |
| **Modo Mantenimiento** | .env o archivo | Sitio en mantenimiento |
| **Logs** | storage/logs | Errores recientes |

---

## 🔌 Configuraciones Disponibles

```
NOTIFICACIONES
├─ notificaciones_email_activadas (bool)
├─ notificaciones_push_activadas (bool)
├─ notificaciones_sms_activadas (bool)
└─ ...

EMAIL (SMTP)
├─ MAIL_HOST (smtp.mailtrap.io, etc)
├─ MAIL_PORT (587, 465, etc)
├─ MAIL_USERNAME
├─ MAIL_PASSWORD
├─ MAIL_FROM_ADDRESS
├─ MAIL_FROM_NAME
└─ MAIL_ENCRYPTION (tls, ssl)

APLICACIÓN
├─ APP_NAME (SpotStay)
├─ APP_ENV (production, local, testing)
├─ APP_DEBUG (true|false)
├─ APP_URL
├─ APP_TIMEZONE
├─ APP_LOCALE (es, en)
└─ ...

BASE DE DATOS
├─ DB_CONNECTION
├─ DB_HOST
├─ DB_PORT
├─ DB_DATABASE
├─ DB_USERNAME
└─ ...
```

---

## 🔍 Flujo Técnico Detallado

### 1️⃣ Usuario accede a `/admin/configuracion`

```
GET /admin/configuracion
  ↓
Route::get('/configuracion', [ConfiguracionController::class, 'index'])
  ↓
ConfiguracionController::index()
```

### 2️⃣ Controlador obtiene configuraciones

```php
// app/Http/Controllers/Admin/ConfiguracionController.php

public function index() {
    // PASO 1: Obtener vars de entorno (.env)
    $configuracion = [
        'app_name' => env('APP_NAME', 'SpotStay'),
        'app_env' => env('APP_ENV', 'production'),
        'app_debug' => env('APP_DEBUG', false),
        'app_url' => env('APP_URL', 'http://localhost'),
        'app_timezone' => env('APP_TIMEZONE', 'UTC'),
        'app_locale' => env('APP_LOCALE', 'es'),
        
        'mail_host' => env('MAIL_HOST'),
        'mail_port' => env('MAIL_PORT'),
        'mail_username' => env('MAIL_USERNAME'),
        'mail_from_address' => env('MAIL_FROM_ADDRESS'),
        'mail_from_name' => env('MAIL_FROM_NAME'),
        'mail_encryption' => env('MAIL_ENCRYPTION'),
        
        'notifications_email' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
        'notifications_push' => env('NOTIFICATIONS_PUSH_ENABLED', false),
        'notifications_sms' => env('NOTIFICATIONS_SMS_ENABLED', false),
        
        'db_host' => env('DB_HOST'),
        'db_port' => env('DB_PORT'),
        'db_database' => env('DB_DATABASE'),
        'db_username' => env('DB_USERNAME'),
    ];
    
    // PASO 2: Obtener logs recientes
    $logFile = storage_path('logs/laravel.log');
    $ultimosLogs = [];
    
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $ultimosLogs = array_slice(explode("\n", $logs), -20);  // últimas 20 líneas
    }
    
    // PASO 3: Información del servidor
    $serverInfo = [
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
        'mysql_version' => DB::connection()->getConnection()->getServerVersion(),
        'disk_free' => disk_free_space('/'),
        'disk_total' => disk_total_space('/'),
    ];
    
    return view('admin.configuracion', compact(
        'configuracion',
        'ultimosLogs',
        'serverInfo'
    ));
}
```

### 3️⃣ Vista renderiza form

```blade
<!-- resources/views/admin/configuracion.blade.php -->

<!-- Configuración de Aplicación -->
<div class="config-section">
    <h3>Configuración de Aplicación</h3>
    
    <form method="POST" action="/admin/configuracion/guardar">
        @csrf
        
        <div class="form-group">
            <label>Nombre Aplicación</label>
            <input type="text" name="app_name" value="{{ $configuracion['app_name'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>URL Aplicación</label>
            <input type="url" name="app_url" value="{{ $configuracion['app_url'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Ambiente</label>
            <select name="app_env" class="form-control">
                <option value="production" @selected($configuracion['app_env'] === 'production')>Producción</option>
                <option value="local" @selected($configuracion['app_env'] === 'local')>Local</option>
                <option value="testing" @selected($configuracion['app_env'] === 'testing')>Testing</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Debug Activado</label>
            <input type="checkbox" name="app_debug" value="true" @checked($configuracion['app_debug'])>
            <small class="text-danger">⚠️ No activar en producción</small>
        </div>
        
        <div class="form-group">
            <label>Timezone</label>
            <select name="app_timezone" class="form-control">
                <option value="UTC" @selected($configuracion['app_timezone'] === 'UTC')>UTC</option>
                <option value="Europe/Madrid" @selected($configuracion['app_timezone'] === 'Europe/Madrid')>Europe/Madrid</option>
                <option value="America/New_York" @selected($configuracion['app_timezone'] === 'America/New_York')>America/New_York</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Idioma</label>
            <select name="app_locale" class="form-control">
                <option value="es" @selected($configuracion['app_locale'] === 'es')>Español</option>
                <option value="en" @selected($configuracion['app_locale'] === 'en')>English</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-success">Guardar Cambios</button>
    </form>
</div>

<!-- Configuración de Email (SMTP) -->
<div class="config-section mt-5">
    <h3>Configuración de Email</h3>
    
    <form method="POST" action="/admin/configuracion/guardar-email">
        @csrf
        
        <div class="form-group">
            <label>Host SMTP</label>
            <input type="text" name="mail_host" value="{{ $configuracion['mail_host'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Puerto</label>
            <input type="number" name="mail_port" value="{{ $configuracion['mail_port'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="mail_username" value="{{ $configuracion['mail_username'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Email Remitente</label>
            <input type="email" name="mail_from_address" value="{{ $configuracion['mail_from_address'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Nombre Remitente</label>
            <input type="text" name="mail_from_name" value="{{ $configuracion['mail_from_name'] }}" class="form-control">
        </div>
        
        <div class="form-group">
            <label>Encriptación</label>
            <select name="mail_encryption" class="form-control">
                <option value="tls" @selected($configuracion['mail_encryption'] === 'tls')>TLS</option>
                <option value="ssl" @selected($configuracion['mail_encryption'] === 'ssl')>SSL</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-success">Guardar Email</button>
        <button type="button" class="btn btn-info" onclick="testearEmail()">Enviar Email Prueba</button>
    </form>
</div>

<!-- Configuración de Notificaciones -->
<div class="config-section mt-5">
    <h3>Canales de Notificación</h3>
    
    <form method="POST" action="/admin/configuracion/guardar-notificaciones">
        @csrf
        
        <div class="form-check">
            <input type="checkbox" name="notifications_email" value="true" 
                   @checked($configuracion['notifications_email']) class="form-check-input">
            <label class="form-check-label">Email Activado</label>
        </div>
        
        <div class="form-check">
            <input type="checkbox" name="notifications_push" value="true" 
                   @checked($configuracion['notifications_push']) class="form-check-input">
            <label class="form-check-label">Push Activado</label>
        </div>
        
        <div class="form-check">
            <input type="checkbox" name="notifications_sms" value="true" 
                   @checked($configuracion['notifications_sms']) class="form-check-input">
            <label class="form-check-label">SMS Activado</label>
        </div>
        
        <button type="submit" class="btn btn-success mt-3">Guardar Notificaciones</button>
    </form>
</div>

<!-- Información del Servidor -->
<div class="config-section mt-5">
    <h3>Información del Servidor</h3>
    
    <table class="table">
        <tr>
            <td>PHP Version</td>
            <td><code>{{ $serverInfo['php_version'] }}</code></td>
        </tr>
        <tr>
            <td>Laravel Version</td>
            <td><code>{{ $serverInfo['laravel_version'] }}</code></td>
        </tr>
        <tr>
            <td>MySQL Version</td>
            <td><code>{{ $serverInfo['mysql_version'] }}</code></td>
        </tr>
        <tr>
            <td>Disco Libre</td>
            <td>{{ number_format($serverInfo['disk_free'] / 1024 / 1024 / 1024, 2) }} GB</td>
        </tr>
        <tr>
            <td>Disco Total</td>
            <td>{{ number_format($serverInfo['disk_total'] / 1024 / 1024 / 1024, 2) }} GB</td>
        </tr>
    </table>
</div>

<!-- Logs Recientes -->
<div class="config-section mt-5">
    <h3>Logs Recientes</h3>
    
    <div class="logs-viewer" style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-family: monospace;">
        @forelse($ultimosLogs as $log)
            <div>{{ $log }}</div>
        @empty
            <div class="text-muted">Sin logs</div>
        @endforelse
    </div>
    
    <button type="button" class="btn btn-warning mt-3" onclick="limpiarLogs()">Limpiar Logs</button>
</div>
```

---

## ⚠️ Puntos Importantes

1. **Variables de entorno:** Se guardan en archivo `.env` (no en DB)
2. **No editar directamente:** Mejor usar `.env` + `php artisan config:cache`
3. **Debug en producción:** NUNCA activar en servidor público
4. **SMTP:** Datos sensibles, no mostrar en logs
5. **Timezone:** Afecta todas las fechas de la app

---

## 🔐 Seguridad

- Solo ADMIN puede acceder
- Datos sensibles (MAIL_PASSWORD) deben ocultarse parcialmente
- Cambios de configuración deben registrarse en logs
