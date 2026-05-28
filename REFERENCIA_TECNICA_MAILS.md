# 🛠️ Referencia Técnica - Sistema de Mails SpotStay

## RESUMEN EJECUTIVO

| Característica | Valor |
|---|---|
| **Driver** | SMTP (Gmail) |
| **Clases Mailable** | 2 (ContactoIncidencia, ContratoSubido) |
| **Vistas de Email** | 2 (contacto_incidencia, contrato_subido) |
| **Disparadores** | 2 puntos de envío manual |
| **Queue** | NO IMPLEMENTADA (envío síncrono) |
| **Events** | NO IMPLEMENTADOS |
| **Estilo HTML** | CSS INLINE |
| **Reintentos** | NO (fallos = error al usuario) |
| **Log de mails** | Solo en `storage/logs/` en caso de error |

---

## 1. CÓMO ESTÁN ESTRUCTURADAS LAS CLASES MAILABLE

### Estructura Base

```php
<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MiMailable extends Mailable {
    use Queueable, SerializesModels;
    
    // 1. Propiedades públicas (datos del mail)
    public $dato1;
    public $dato2;
    
    // 2. Constructor (inicializar datos)
    public function __construct($dato1, $dato2) {
        $this->dato1 = $dato1;
        $this->dato2 = $dato2;
    }
    
    // 3. Método build() (configurar el email)
    public function build() {
        return $this->from('from@example.com', 'Nombre')
                    ->subject('Asunto')
                    ->view('emails.plantilla')
                    ->with([
                        'dato1' => $this->dato1,
                        'dato2' => $this->dato2,
                    ]);
    }
}
```

---

## 2. REFERENCIA DE CONTACTOINCIDENCIA

### Constructor
```php
public function __construct($incidencia, $asunto, $mensaje, $destinatarioNombre = null)
{
    $this->incidencia = $incidencia;              // Objeto incidencia (DB record)
    $this->asunto = $asunto;                      // String: "Incidencia respondida"
    $this->mensaje = $mensaje;                    // String: contenido del mensaje
    $this->destinatarioNombre = $destinatarioNombre; // String: "Juan García"
}
```

### Método build()
```php
public function build()
{
    // 1. Construye URL de login
    $this->urlLogin = route('login');
    
    // 2. Retorna configuración del mail
    return $this->from('spotstayy@gmail.com', 'SpotStay')   // Remitente
                ->subject($this->asunto)                     // Asunto dinámico
                ->view('emails.contacto_incidencia')         // Plantilla Blade
                ->with([                                      // Variables para Blade
                    'incidencia' => $this->incidencia,
                    'mensaje' => $this->mensaje,
                    'destinatarioNombre' => $this->destinatarioNombre,
                    'urlLogin' => $this->urlLogin,
                ]);
}
```

### Uso en Controlador
```php
Mail::to($email)->send(new ContactoIncidencia($incidencia, $asunto, $mensaje, $nombre));
```

---

## 3. REFERENCIA DE CONTRATOSUBIDO

### Constructor
```php
public function __construct(int $idAlquiler, ?string $nombreInquilino, string $urlPdf)
{
    $this->idAlquiler = $idAlquiler;              // int: 5
    $this->nombreInquilino = $nombreInquilino;    // string: "Carlos Ruiz" o null
    $this->urlPdf = $urlPdf;                      // string: URL completa
}
```

### Método build()
```php
public function build()
{
    // 1. Reconstruye la URL del PDF
    $this->urlPdf = route('contratos.descargar', ['id' => $this->idAlquiler]);
    
    // 2. Retorna configuración del mail
    return $this->from('spotstayy@gmail.com', 'SpotStay')
                ->subject('Nuevo contrato disponible')    // Asunto FIJO
                ->view('emails.contrato_subido')          // Plantilla Blade
                ->with([
                    'idAlquiler' => $this->idAlquiler,
                    'nombreInquilino' => $this->nombreInquilino,
                    'urlPdf' => $this->urlPdf,
                ]);
}
```

### Uso en Controlador
```php
Mail::to($infoAlquiler->email_usuario)->send(new ContratoSubido(
    $infoAlquiler->id_alquiler,
    $infoAlquiler->nombre_inquilino,
    $urlCompleta
));
```

---

## 4. MÉTODOS DE ENVÍO MAIL

### Síncrono (Actual)
```php
// Envía inmediatamente, espera respuesta
Mail::to($email)->send(new MiMailable(...));

// Con copia
Mail::to($email)
    ->cc('cc@example.com')
    ->send(new MiMailable(...));

// Con copia oculta
Mail::to($email)
    ->bcc('bcc@example.com')
    ->send(new MiMailable(...));

// Con responder a
Mail::to($email)
    ->replyTo('reply@example.com')
    ->send(new MiMailable(...));
```

### Asíncrono (NO IMPLEMENTADO)
```php
// Pone en queue para procesamiento posterior
Mail::to($email)->queue(new MiMailable(...));

// Con delay
Mail::to($email)->delay(now()->addMinutes(10))
    ->queue(new MiMailable(...));
```

---

## 5. ESTRUCTURA DE VISTAS BLADE

### Características comunes
```blade
<!-- DOCTYPE y estructura HTML -->
<!DOCTYPE html>
<html lang="es">

<!-- Estilos INLINE (no hay <link>) -->
<body style="font-family: Arial; background-color: #f9fafb;">
    <div style="max-width: 600px; ...">
        <!-- Contenido -->
    </div>
</body>
```

### Variables disponibles
```blade
<!-- En contacto_incidencia.blade.php -->
{{ $destinatarioNombre }}              <!-- Blade, interpolación -->
{{ $mensaje }}                          <!-- HTML escapado -->
{{ $incidencia->id_incidencia }}        <!-- Propiedades del objeto -->
{{ $urlLogin }}                         <!-- URL route()

<!-- En contrato_subido.blade.php -->
{{ $nombreInquilino }}
{{ $idAlquiler }}
{{ $urlPdf }}
```

### Variables de colores
```blade
@php
    $colorPrioridad = match($incidencia->prioridad_incidencia ?? 'media') {
        'urgente' => '#EF4444',    // Rojo
        'alta' => '#D97706',       // Naranja  
        'media' => '#6B7280',      // Gris
        'baja' => '#1AA068',       // Verde
        default => '#6B7280'
    };
@endphp

<!-- Usar variable en estilos -->
<span style="background-color: {{ $colorPrioridad }};">...</span>
```

---

## 6. FLUJO EN CONTROLADORES

### IncidenciaController::responderIncidencia()

**Paso 1: Validar entrada**
```php
$asunto = $request->input('asunto');
$mensaje = $request->input('mensaje');
$destino = $request->input('destino'); // 'inquilino', 'gestor', 'arrendador'
$incId = $request->input('inc_id');
```

**Paso 2: Obtener incidencia**
```php
$inc = DB::table('tbl_incidencia')->find($incId);
if (!$inc) {
    return response()->json(['success' => false, 'error' => 'Incidencia no encontrada']);
}
```

**Paso 3: Determinar email según destino**
```php
$email = null;
$nombre = null;

if ($destino === 'inquilino') {
    $emailData = DB::table('tbl_usuario')
        ->where('id_usuario', $inc->id_reporta_fk)
        ->select('email_usuario', 'nombre_usuario')
        ->first();
    
} elseif ($destino === 'gestor') {
    $emailData = DB::table('tbl_usuario')
        ->where('id_usuario', $inc->id_asignado_fk)
        ->select('email_usuario', 'nombre_usuario')
        ->first();
        
} elseif ($destino === 'arrendador') {
    $idArrendador = DB::table('tbl_propiedad')
        ->where('id_propiedad', $inc->id_propiedad_fk)
        ->value('id_arrendador_fk');
    
    $emailData = DB::table('tbl_usuario')
        ->where('id_usuario', $idArrendador)
        ->select('email_usuario', 'nombre_usuario')
        ->first();
}

$email = $emailData->email_usuario ?? null;
$nombre = $emailData->nombre_usuario ?? null;
```

**Paso 4: Validar email**
```php
if (!$email) {
    return response()->json(['success' => false, 'error' => 'Email no encontrado']);
}
```

**Paso 5: Enviar mail**
```php
try {
    Mail::to($email)->send(new ContactoIncidencia($inc, $asunto, $mensaje, $nombre));
    return response()->json(['success' => true]);
} catch (\Exception $e) {
    return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
}
```

---

### ContratoController (upload PDF)

**Paso 1: Guardar archivo**
```php
$rutaRelativa = $request->file('pdf')->store('contratos', 'public');
```

**Paso 2: Construir URL**
```php
$urlCompleta = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/storage/' . $rutaRelativa;
```

**Paso 3: Buscar inquilino**
```php
$infoAlquiler = DB::table('tbl_contrato as c')
    ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
    ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
    ->where('c.id_contrato', $id)
    ->select('a.id_alquiler', 'inquilino.email_usuario', 'inquilino.nombre_usuario as nombre_inquilino')
    ->first();
```

**Paso 4: Enviar mail (con error handling silencioso)**
```php
if ($infoAlquiler && !empty($infoAlquiler->email_usuario)) {
    try {
        Mail::to($infoAlquiler->email_usuario)->send(new ContratoSubido(
            $infoAlquiler->id_alquiler,
            $infoAlquiler->nombre_inquilino,
            $urlCompleta
        ));
    } catch (\Exception $e) {
        Log::error('Error enviando notificación de contrato: ' . $e->getMessage(), [
            'contrato_id' => $id,
        ]);
        // No afecta a la respuesta - el PDF se guarda igual
    }
}
```

**Paso 5: Retornar respuesta**
```php
return response()->json([
    'success' => true,
    'message' => 'PDF subido correctamente.',
    'url_pdf' => $urlCompleta,
]);
```

---

## 7. CONFIGURACIÓN DE GMAIL

### Requisitos
1. Cuenta Gmail: `spotstayy@gmail.com`
2. **Contraseña de aplicación** (no contraseña normal)
3. **2FA habilitado** en la cuenta
4. **App específica** creada en Google Cloud Console

### Paso a paso para crear App Password
1. Ir a [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Seleccionar: App = Mail, Device = Windows Computer
3. Google genera contraseña de 16 caracteres
4. Copiar a `.env` en `MAIL_PASSWORD`

### Verificar conexión SMTP
```bash
telnet smtp.gmail.com 587
```

---

## 8. MEJORAS RECOMENDADAS

### MEJORA 1: Implementar Queue
```php
// Cambiar en .env
QUEUE_CONNECTION=database  // O redis

// Crear tabla jobs
php artisan queue:table
php artisan migrate

// En controladores, cambiar send() por queue()
Mail::to($email)->queue(new ContactoIncidencia(...));

// Ejecutar worker en background
php artisan queue:work
```

### MEJORA 2: Implementar Events
```php
// Crear evento
php artisan make:event IncidenciaRespondida

// Crear listener
php artisan make:listener EnviarMailIncidencia --event=IncidenciaRespondida

// En controlador
event(new IncidenciaRespondida($incidencia, $asunto, $mensaje));

// En listener
public function handle(IncidenciaRespondida $event) {
    Mail::to($email)->queue(new ContactoIncidencia(...));
}
```

### MEJORA 3: Log de Mails Enviados
```php
// Crear tabla
Schema::create('mails_enviados', function (Blueprint $table) {
    $table->id();
    $table->string('para');
    $table->string('asunto');
    $table->string('tipo'); // 'incidencia', 'contrato'
    $table->string('estado'); // 'enviado', 'pendiente', 'error'
    $table->text('error_mensaje')->nullable();
    $table->timestamps();
});

// En mail build()
MailEnviado::create([
    'para' => $this->email,
    'asunto' => $subject,
    'tipo' => 'incidencia',
    'estado' => 'pendiente',
]);
```

### MEJORA 4: Plantillas Dinámicas
```php
// En lugar de hardcode, usar tabla
$plantilla = DB::table('tbl_plantilla_email')
    ->where('tipo', 'incidencia')
    ->first();

$html = str_replace(
    ['{{nombre}}', '{{mensaje}}'],
    [$nombre, $mensaje],
    $plantilla->html
);

return $this->view('emails.plantilla_dinamica')
            ->with('html', $html);
```

### MEJORA 5: SMTP Testing
```php
// Usar Mailtrap en development
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_ENCRYPTION=tls

// En testing:
Mail::fake();
Mail::assertSent(ContactoIncidencia::class);
```

---

## 9. DEBUGGING

### Ver logs de mail
```bash
# Archivo de log
tail -f storage/logs/laravel.log

# Buscar errores de mail
grep -i "mail\|smtp" storage/logs/laravel.log
```

### Usar Log::info en desarrollo
```php
Log::info('Mail enviado a: ' . $email);
Log::info('Mailable: ' . get_class($mailable));
```

### Test en CLI
```php
// Tinker
php artisan tinker

>>> $inc = \App\Models\Incidencia::find(1);
>>> Mail::to('test@example.com')->send(new \App\Mail\ContactoIncidencia($inc, 'Test', 'Mensaje'));
```

---

## 10. CHECKLIST ANTES DE PRODUCCIÓN

- [ ] Gmail App Password configurada en `.env`
- [ ] `.env` NO está en git (`.gitignore` lo excluye)
- [ ] Implementar Queue (no envío síncrono)
- [ ] Implementar reintentos en queue
- [ ] Log de mails enviados en BD
- [ ] Plantillas de email responsive
- [ ] Testing de mails en diferentes clientes
- [ ] Bounce handler implementado
- [ ] Unsubscribe link en footer
- [ ] SPF/DKIM/DMARC configurados en Gmail

---

## RESUMEN DE RUTAS DE CÓDIGO

| Archivo | Línea | Función |
|---------|-------|---------|
| `config/mail.php` | 1-100 | Configuración SMTP |
| `.env` | 47-54 | Credenciales Gmail |
| `app/Mail/ContactoIncidencia.php` | 1-45 | Mailable de incidencias |
| `app/Mail/ContratoSubido.php` | 1-40 | Mailable de contratos |
| `resources/views/emails/contacto_incidencia.blade.php` | 1-... | Plantilla incidencias |
| `resources/views/emails/contrato_subido.blade.php` | 1-... | Plantilla contratos |
| `app/Http/Controllers/Admin/IncidenciaController.php` | 335-365 | Envío incidencias |
| `app/Http/Controllers/Arrendador/ContratoController.php` | 220-250 | Envío contratos |

