# 📧 Análisis Completo del Sistema de Mails - SpotStay

## 1. CONFIGURACIÓN DE MAIL

### Archivo: `config/mail.php`
```php
'default' => env('MAIL_MAILER', 'smtp')  // SMTP configurado por defecto

'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'scheme' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'spotstayy@gmail.com',
        'password' => env('MAIL_PASSWORD'),
        'timeout' => null,
    ]
]
```

### Configuración en `.env`
```env
MAIL_MAILER=smtp                                  # Driver SMTP
MAIL_HOST=smtp.gmail.com                          # Gmail SMTP
MAIL_PORT=587                                     # TLS port
MAIL_USERNAME=spotstayy@gmail.com                 # Cuenta remitente
MAIL_PASSWORD=cgsblydszerozvsd                    # Contraseña (gmail app-specific)
MAIL_ENCRYPTION=tls                               # Encriptación TLS
MAIL_FROM_ADDRESS="spotstayy@gmail.com"           # Email del remitente
MAIL_FROM_NAME="${APP_NAME}"                      # Nombre del remitente (SpotStay)
```

**⚠️ NOTA CRÍTICA:** 
- Se utiliza Gmail SMTP (spotstayy@gmail.com)
- La contraseña es una contraseña de aplicación (App Password)
- Puerto 587 = TLS (seguro)
- Los mails se envían **síncronamente** (sin queue)

---

## 2. CLASES MAILABLE

### 2.1 `ContactoIncidencia.php`

**Propósito:** Enviar notificaciones sobre incidencias a usuarios (inquilinos, gestores, arrendadores)

**Ubicación:** `app/Mail/ContactoIncidencia.php`

**Propiedades públicas:**
```php
public $incidencia;              // Objeto de la incidencia
public $asunto;                  // Asunto personalizado del email
public $mensaje;                 // Cuerpo del mensaje personalizado
public $destinatarioNombre;      // Nombre del destinatario
public $urlLogin;                // URL de login (construida en build())
```

**Método `build()`:**
- Remitente: `spotstayy@gmail.com` (SpotStay)
- Asunto: Dinámico (pasado como parámetro)
- Vista: `emails.contacto_incidencia`
- Datos enviados a la vista:
  - `$incidencia` - Datos completos de la incidencia
  - `$mensaje` - Mensaje personalizado
  - `$destinatarioNombre` - Nombre del destinatario
  - `$urlLogin` - URL de login

**Usa Queueable:** ✓ Sí (pero NO se pone en queue, se envía síncrono)

---

### 2.2 `ContratoSubido.php`

**Propósito:** Notificar al inquilino cuando se sube un PDF de contrato

**Ubicación:** `app/Mail/ContratoSubido.php`

**Propiedades públicas:**
```php
public $idAlquiler;              // ID del alquiler
public $nombreInquilino;         // Nombre del inquilino
public $urlPdf;                  // URL para descargar el PDF
```

**Método `build()`:**
- Remitente: `spotstayy@gmail.com` (SpotStay)
- Asunto: Fijo = "Nuevo contrato disponible"
- Vista: `emails.contrato_subido`
- Datos enviados a la vista:
  - `$idAlquiler` - ID del alquiler
  - `$nombreInquilino` - Nombre del inquilino
  - `$urlPdf` - URL para descargar el contrato

**Usa Queueable:** ✓ Sí (pero NO se pone en queue, se envía síncrono)

---

## 3. DÓNDE SE ENVÍAN LOS MAILS

### 3.1 Envío de Incidencias

**Controlador:** `app/Http/Controllers/Admin/IncidenciaController.php`

**Método:** `responderIncidencia()` (línea ~352)

**Disparador:** Acción HTTP POST manual desde la interfaz de admin

**Flujo:**
1. Admin elige un destinatario (inquilino, gestor o arrendador)
2. Escribe asunto y mensaje
3. Envía formulario
4. El método determina el email según tipo de destinatario:
   - **Inquilino:** email_usuario del usuario que reportó la incidencia
   - **Gestor:** email_usuario del usuario asignado a la incidencia
   - **Arrendador:** email_usuario del arrendador de la propiedad

**Código:**
```php
if ($destino === 'inquilino') {
    $email = DB::table('tbl_usuario')
        ->where('id_usuario', $inc->id_reporta_fk)
        ->value('email_usuario');
} elseif ($destino === 'gestor') {
    $email = DB::table('tbl_usuario')
        ->where('id_usuario', $inc->id_asignado_fk)
        ->value('email_usuario');
} elseif ($destino === 'arrendador') {
    $idArr = DB::table('tbl_propiedad')
        ->where('id_propiedad', $inc->id_propiedad_fk)
        ->value('id_arrendador_fk');
    $email = DB::table('tbl_usuario')
        ->where('id_usuario', $idArr)
        ->value('email_usuario');
}

try {
    Mail::to($email)->send(new ContactoIncidencia($inc, $asunto, $mensaje, $nombre));
    return response()->json(['success' => true]);
} catch (\Exception $e) {
    return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
}
```

**Validaciones:**
- Verifica que existe email_usuario
- Manejo de excepciones con try/catch
- Retorna JSON con estado

---

### 3.2 Envío de Contratos

**Controlador:** `app/Http/Controllers/Arrendador/ContratoController.php`

**Método:** ~línea 234 (dentro del método que guarda PDFs)

**Disparador:** Subida de archivo PDF en formulario de contrato

**Flujo:**
1. Arrendador sube un PDF de contrato
2. Se guarda el archivo en `public/contratos/`
3. Se genera URL completa para descargar
4. Se busca el inquilino asociado al alquiler
5. Se envía mail al inquilino notificando disponibilidad del contrato

**Código:**
```php
// Se busca el inquilino asociado
$infoAlquiler = DB::table('tbl_contrato as c')
    ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
    ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
    ->where('c.id_contrato', $id)
    ->select('a.id_alquiler', 'inquilino.email_usuario', 'inquilino.nombre_usuario as nombre_inquilino')
    ->first();

// Se envía el mail al inquilino
if ($infoAlquiler && !empty($infoAlquiler->email_usuario)) {
    try {
        Mail::to($infoAlquiler->email_usuario)->send(new ContratoSubido(
            $infoAlquiler->id_alquiler,
            $infoAlquiler->nombre_inquilino,
            $urlCompleta
        ));
    } catch (\Exception $e) {
        Log::error('Error enviando notificación de contrato: ' . $e->getMessage());
    }
}
```

**Validaciones:**
- Verifica que existe email
- Manejo de excepciones silencioso (log de error)
- No afecta a la respuesta (el PDF se guarda igual)

---

## 4. VISTAS DE EMAIL

### 4.1 `resources/views/emails/contacto_incidencia.blade.php`

**Características:**
- HTML responsivo con estilos INLINE (CSS embebido)
- Tema de colores verde SpotStay (#1AA068)
- Sección de incidencia con badge de prioridad coloreada

**Variables disponibles:**
- `$destinatarioNombre` - Saludo personalizado
- `$mensaje` - Cuerpo del email
- `$incidencia` - Objeto completo con ID, estado, prioridad
- `$urlLogin` - URL del login para verificar la incidencia

**Estilos:**
```html
<!-- Encabezado degradado verde -->
background: linear-gradient(135deg, #1AA068 0%, #15824a 100%)

<!-- Color de prioridad dinámico -->
$colorPrioridad = match($incidencia->prioridad_incidencia) {
    'urgente' => '#EF4444',    // Rojo
    'alta' => '#D97706',       // Naranja
    'media' => '#6B7280',      // Gris
    'baja' => '#1AA068',       // Verde
}
```

---

### 4.2 `resources/views/emails/contrato_subido.blade.php`

**Características:**
- HTML responsivo con estilos INLINE
- Tema de colores verde SpotStay
- Botón para descargar el contrato
- Información de seguridad sobre el documento

**Variables disponibles:**
- `$nombreInquilino` - Saludo personalizado
- `$idAlquiler` - ID del alquiler
- `$urlPdf` - URL para descargar el PDF

**Estilos:**
```html
<!-- Encabezado igual que incidencias -->
background: linear-gradient(135deg, #1AA068 0%, #15824a 100%)
```

---

## 5. EVENTOS Y LISTENERS

**Estado:** ❌ **NO IMPLEMENTADOS**

El sistema actual:
- ✗ No usa Laravel Events
- ✗ No usa Listeners
- ✓ Envía mails directamente en los controladores

**Implicaciones:**
- Lógica de mails acoplada a controladores
- Difícil testear separadamente
- No hay separación de responsabilidades
- Envios síncronos (bloquean la ejecución)

---

## 6. QUEUE CONFIGURATION

**Estado:** ❌ **NO IMPLEMENTADO**

**Configuración actual:**
```php
// Ambas clases usan Queueable:
use Queueable, SerializesModels;

// PERO se envían síncronamente:
Mail::to($email)->send(new ContactoIncidencia(...));  // ✗ send() = síncrono
// Deberían ser:
Mail::to($email)->queue(new ContactoIncidencia(...)); // ✓ queue() = async
```

**Implicaciones:**
- ⚠️ Los mails se envían EN TIEMPO REAL
- ⚠️ Si falla Gmail, se bloquea la solicitud HTTP
- ⚠️ No hay reintentos automáticos
- ⚠️ No hay register de mails enviados

**Configuración en `.env`:**
```env
QUEUE_CONNECTION=sync  # Por defecto sincrónico
# Otros drivers disponibles: database, redis, beanstalkd, etc.
```

---

## 7. JAVASCRIPT

**Estado:** ❌ **NO ENCONTRADO**

**Búsqueda realizada:**
- Grep search en `app/Http/Controllers/`
- No hay JavaScript que dispare envíos de mails
- Los mails se envían 100% desde el servidor

**Formularios que activan mails:**
- Respuesta a incidencias en `/admin/incidencias`
- Subida de PDFs de contrato en `/contratos/upload`

---

## RESUMEN TÉCNICO

| Aspecto | Estado | Detalles |
|---------|--------|----------|
| **Configuración** | ✅ Configurado | SMTP Gmail con TLS |
| **Clases Mailable** | ✅ 2 clases | Incidencias + Contratos |
| **Vistas** | ✅ 2 vistas | Estilos inline HTML/CSS |
| **Disparadores** | ✅ Manual | Desde controladores (no automático) |
| **Events/Listeners** | ❌ No usado | Acoplado a controladores |
| **Queue** | ❌ No usado | Se envía síncrono (->send()) |
| **JavaScript** | ❌ No existe | Envío 100% servidor |

---

## PUNTOS CRÍTICOS A CONSIDERAR

1. **SEGURIDAD:**
   - ✓ La contraseña está en `.env` (bien)
   - ✓ Usa TLS (encriptado)
   - ⚠️ Pero está expuesta en `.env` (no committed en .gitignore?)

2. **RENDIMIENTO:**
   - ⚠️ Envío síncrono = bloquea la solicitud
   - ❌ Si falla Gmail, el usuario espera error
   - 💡 Solución: Usar queue con database/redis

3. **CONFIABILIDAD:**
   - ⚠️ Sin cola, sin reintentos
   - ⚠️ Sin logs de envío
   - 💡 Solución: Usar tabla de `mails_enviados`

4. **ESCALABILIDAD:**
   - ❌ No es escalable (síncrono)
   - ❌ Si hay muchas incidencias, sistema lento
   - 💡 Solución: Queue + job workers

5. **PERSONALIZACIÓ:**
   - ✅ Mensajes dinámicos
   - ✅ Estilos personalizados por tipo
   - ⚠️ Pero hardcodeado en controladores

