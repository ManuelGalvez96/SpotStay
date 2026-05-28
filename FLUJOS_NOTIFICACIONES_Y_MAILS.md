# 📬 FLUJOS COMPLETOS: NOTIFICACIONES + MAILS EN SPOTSTAY

**Documento pedagógico** explicando cómo funcionan ambos sistemas desde cero.

---

## 📍 TABLA DE CONTENIDOS

1. [Sistema de Notificaciones](#1-sistema-de-notificaciones)
   - Flujo completo (creación → visualización → eliminación)
   - Arquitectura de base de datos
   - Disparadores
   - JavaScript involucrado
   - Tipos de notificaciones

2. [Sistema de Mails](#2-sistema-de-mails)
   - Configuración SMTP
   - Clases Mailable
   - Disparadores (incidencias, PDF, etc)
   - Personalización CSS/HTML
   - Flujo completo

3. [Casos de Uso Combinados](#3-casos-de-uso-combinados)
   - Notificación + Mail de incidencia
   - Notificación + Mail de contrato

---

# 1️⃣ SISTEMA DE NOTIFICACIONES

## 1.1 ¿Qué es una notificación?

Una **notificación** es un registro en `tbl_notificacion` que el sistema crea automáticamente cuando ocurre un evento.
Se muestra al usuario en un **dropdown campana** y puede ser:
- ✅ Leída (mostrada y descartada)
- ❌ Eliminada (borrada de BD)
- ⏳ Pendiente (sin leer)

### Estructura de tbl_notificacion

```sql
CREATE TABLE tbl_notificacion (
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario_fk INT NOT NULL,                    -- Quién la recibe
    tipo_notificacion VARCHAR(50),                 -- Tipo de evento
    titulo_notificacion VARCHAR(255),              -- "Nueva incidencia"
    mensaje_notificacion TEXT,                     -- "Agua goteando en Piso 3B"
    url_notificacion VARCHAR(255),                 -- Dónde ir al clickear
    icono_notificacion VARCHAR(50),                -- "exclamation-triangle"
    color_notificacion VARCHAR(20),                -- "#DC2626" (rojo)
    leida_notificacion TINYINT(1) DEFAULT 0,      -- ¿Se leyó?
    leida_en_notificacion TIMESTAMP NULL,         -- Cuándo se leyó
    tipo_entidad VARCHAR(50),                      -- "incidencia", "pago", etc
    id_entidad INT,                                -- ID de la incidencia, pago, etc
    creado_notificacion TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (id_usuario_fk) REFERENCES tbl_usuario(id_usuario) ON DELETE CASCADE
);
```

**Puntos clave:**
- Una notificación = UN usuario (no es broadcasts)
- Si quieres notificar a 3 personas, insertas 3 filas
- El `tipo_notificacion` determina icon, color y URL
- `leida_notificacion` es booleano (0/1)

---

## 1.2 ¿DÓNDE se crean las notificaciones?

**Punto central:** Clase `ActividadService`

```php
// app/Services/ActividadService.php
class ActividadService {
    public function incidenciaCreada($incidenciaId) {
        // 1. Busca quién debe recibir (gestor asignado)
        $idGestor = DB::table('tbl_incidencia')
            ->where('id_incidencia', $incidenciaId)
            ->value('id_asignado_fk');
        
        // 2. Crea la notificación
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $idGestor,
            'tipo_notificacion' => 'nueva_incidencia',
            'titulo_notificacion' => 'Nueva incidencia creada',
            'mensaje_notificacion' => 'Nueva incidencia reportada',
            'url_notificacion' => "/gestor/incidencias/$incidenciaId",
            'icono_notificacion' => 'exclamation-triangle',
            'color_notificacion' => '#DC2626',  // Rojo
            'tipo_entidad' => 'incidencia',
            'id_entidad' => $incidenciaId,
        ]);
    }
}
```

### **13 Tipos de Notificaciones Documentados:**

| Tipo | Quién lo recibe | Evento | Color | Ícono |
|------|------------------|--------|-------|-------|
| `nueva_incidencia` | Gestor | Incidencia reportada | 🔴 #DC2626 | ⚠️ exclamation-triangle |
| `incidencia_respondida` | Inquilino | Gestor responde | 🔵 #3B82F6 | 💬 chat |
| `pago_realizado` | Arrendador | Nuevo pago | 🟢 #16A34A | ✅ check-circle |
| `contrato_subido` | Inquilino | Contrato disponible | 🟠 #F97316 | 📄 file-text |
| `presupuesto_aprobado` | Inquilino | Admin aprueba gasto | 🟢 #10B981 | 👍 thumb-up |
| `solicitud_aprobada` | Usuario | Solicitud aceptada | 🟢 #059669 | ✅ check |
| `solicitud_rechazada` | Usuario | Solicitud rechazada | 🔴 #DC2626 | ❌ x-circle |
| `nuevo_mensaje` | Receptor | Mensaje privado | 💜 #7C3AED | 💌 mail |
| `incidencia_resuelta` | Inquilino | Incidencia cerrada | 🟢 #0F9F6E | ✅ check-double |
| `cambio_estado_alquiler` | Inquilino | Estado cambió | 🔵 #3B82F6 | 🔄 refresh |
| ... y 3 más | ... | ... | ... | ... |

### ¿Dónde se dispara?

```php
// Ejemplos en controladores:

// Admin\IncidenciaController.php (línea ~200)
public function crear(Request $request) {
    $incidencia = DB::table('tbl_incidencia')->insertGetId([...]);
    // ← Se activa evento que llama a ActividadService::incidenciaCreada()
    app(ActividadService::class)->incidenciaCreada($incidencia);
}

// Admin\SolicitudController.php (línea ~80)
public function aprobar(Request $request, $id) {
    // ... lógica de aprobación ...
    app(ActividadService::class)->solicitudAprobada($id);
}
```

---

## 1.3 ¿CÓMO se visualizan?

### **Flujo de carga en página (AppServiceProvider)**

```php
// app/Providers/AppServiceProvider.php
public function boot() {
    View::share('notificacionesGestor', function() {
        $usuarioId = auth()->id();
        
        // SELECT últimas 6 SIN LEER
        return DB::table('tbl_notificacion')
            ->where('id_usuario_fk', $usuarioId)
            ->where('leida_notificacion', 0)      // ← Solo sin leer
            ->orderBy('creado_notificacion', 'DESC')
            ->limit(6)
            ->get();
    });
}
```

**Variables inyectadas en TODAS las vistas:**
- `$notificacionesGestor` - Colección de notificaciones
- `$notificacionesGestorSinLeer` - Número (6, 0, etc)

### **Renderización en Blade (layout gestor)**

```blade
<!-- resources/views/layouts/gestor.blade.php -->
<div class="campana-container">
    <!-- Badge con número -->
    <button id="campana-btn" class="campana-btn">
        <i class="bi bi-bell"></i>
        @if($notificacionesGestorSinLeer > 0)
            <span class="badge">{{ $notificacionesGestorSinLeer }}</span>
        @endif
    </button>

    <!-- Dropdown (inicialmente oculto) -->
    <div id="campana-dropdown" class="campana-dropdown" style="display: none;">
        @forelse($notificacionesGestor as $notif)
            <div class="campana-item" data-id="{{ $notif->id_notificacion }}">
                <div class="icono" style="background: {{ $notif->color_notificacion }}">
                    <i class="bi bi-{{ $notif->icono_notificacion }}"></i>
                </div>
                
                <div class="contenido">
                    <h4>{{ $notif->titulo_notificacion }}</h4>
                    <p>{{ $notif->mensaje_notificacion }}</p>
                    <small>{{ $notif->creado_notificacion->diffForHumans() }}</small>
                </div>

                <button class="btn-eliminar" onclick="eliminarNotificacion({{ $notif->id_notificacion }})">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @empty
            <div class="sin-notificaciones">
                <p>No hay notificaciones nuevas</p>
            </div>
        @endforelse
    </div>
</div>
```

### **Apariencia Visual**

```
╔════════════════════════════════╗
║  🔔 (badge: 6)                 ║  ← Click abre dropdown
╚════════════════════════════════╝
              ↓ (se abre)
┌────────────────────────────────┐
│ ⚠️ 🔴 Nueva incidencia      [✕]│  ← Agua goteando — 3 min
├────────────────────────────────┤
│ 💬 🔵 Incidencia respondida [✕]│  ← Gestor respondió — 15 min
├────────────────────────────────┤
│ ✅ 🟢 Pago realizado        [✕]│  ← $500 pagados — 1 hora
├────────────────────────────────┤
│ 📄 🟠 Contrato subido       [✕]│  ← Documento disponible — 2 hs
├────────────────────────────────┤
│ 👍 🟢 Presupuesto aprobado  [✕]│  ← Gasto aceptado — 5 hs
├────────────────────────────────┤
│ 💌 💜 Nuevo mensaje         [✕]│  ← De Admin — 7 hs
└────────────────────────────────┘
```

---

## 1.4 ¿CÓMO se sincroniza? (La parte "automática")

**IMPORTANTE:** No hay polling ni WebSocket. Es **totalmente PASIVA**.

```
┌─────────────────┐
│ Usuario en BD   │
│ ID = 5 (gestor) │
└────────┬────────┘
         │
         │ Se crea evento
         ↓
    Admin crea incidencia
         │
         ↓
    ActividadService::incidenciaCreada(42)
         │
         ↓
    INSERT INTO tbl_notificacion (id_usuario_fk=5, ...)  ← En BD ahora
         │
         ↓
    Página del gestor está abierta
         │
    ❌ NO VE NADA (no hay polling)
         │
         ↓
    Gestor recarga F5
         │
         ↓
    AppServiceProvider::boot() ejecuta
         │
         ↓
    SELECT * FROM tbl_notificacion 
         WHERE id_usuario_fk = 5 
         AND leida_notificacion = 0
         │
         ✅ AHORA VE LA NOTIFICACIÓN
```

**Métodos actuales (AJAX):**
- Marcar como leída: `POST /notificaciones/123/marcar-leida`
- Eliminar: `POST /notificaciones/123/eliminar`
- Ambos son **on-demand**, no hay auto-sync

---

## 1.5 ¿CÓMO se eliminan? (Ciclo de vida)

### **Opción A: Usuario clickea botón ✕**

```javascript
// public/js/gestor/campana.js
function eliminarNotificacion(notifId) {
    fetch(`/notificaciones/${notifId}/eliminar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            // Remover del DOM
            document.querySelector(`[data-id="${notifId}"]`).remove();
            
            // Decrementar badge
            let badge = document.querySelector('.campana-btn .badge');
            if (badge) {
                let count = parseInt(badge.textContent);
                if (count > 1) {
                    badge.textContent = count - 1;
                } else {
                    badge.remove();
                }
            }
        }
    });
}
```

**Backend:**
```php
// app/Http/Controllers/NotificacionController.php
public function eliminar(Request $request, $id) {
    DB::table('tbl_notificacion')
        ->where('id_notificacion', $id)
        ->where('id_usuario_fk', auth()->id())  // ← Seguridad
        ->delete();
    
    return response()->json(['ok' => true]);
}
```

### **Opción B: Cascada automática (se elimina usuario)**

```sql
-- Si se borra el usuario, cascada elimina todas sus notificaciones
DELETE FROM tbl_usuario WHERE id_usuario = 5;
  ↓ Trigger CASCADE
DELETE FROM tbl_notificacion WHERE id_usuario_fk = 5;
```

### **Opción C: Usuario nunca elimina (quedan en BD)**

⚠️ **Problema:** Notificaciones antiguas nunca se borran = tabla crece indefinidamente.

**Solución propuesta:** Job Cron que borre notificaciones > 30 días.

---

## 1.6 Resumen de JavaScript involucrado

| Archivo | Función | Qué hace |
|---------|---------|----------|
| `campana.js` | `eliminarNotificacion()` | POST a backend, remueve del DOM |
| `campana.js` | `marcarComoLeida()` | Marca como leída (puede no existir) |
| `campana.js` | `abrirDropdown()` | Toggle visibility |
| - | - | **No hay polling** ni auto-sync |

---

---

# 2️⃣ SISTEMA DE MAILS

## 2.1 ¿Qué es un Mail en Laravel?

Un **Mailable** es una clase que encapsula la creación y envío de emails.

```php
Mail::to($email)->send(new ContactoIncidencia($data));
```

SpotStay tiene **2 Mailable clases:**

| Clase | Cuándo | Destinatario | Vistas |
|-------|--------|--------------|--------|
| `ContactoIncidencia` | Respuesta a incidencia | Inquilino/Gestor/Arrendador | `emails/contacto_incidencia.blade.php` |
| `ContratoSubido` | Contrato subido | Inquilino | `emails/contrato_subido.blade.php` |

---

## 2.2 Configuración SMTP

```php
// config/mail.php
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),   // Gmail
        'port' => env('MAIL_PORT', 587),                   // TLS
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),     // TLS
        'username' => env('MAIL_USERNAME'),                // Email
        'password' => env('MAIL_PASSWORD'),                // App Password
    ],
],
```

```env
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=spotstayy@gmail.com
MAIL_ENCRYPTION=tls
MAIL_PASSWORD=abcd efgh ijkl mnop  # ← Google App Password (16 caracteres)
MAIL_FROM_ADDRESS=spotstayy@gmail.com
MAIL_FROM_NAME="SpotStay"
```

**Nota importante:** Google requiere **App Password** (no la contraseña normal).

---

## 2.3 Clase Mailable: ContactoIncidencia

```php
// app/Mail/ContactoIncidencia.php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactoIncidencia extends Mailable {
    
    public function __construct(
        public $incidenciaId,
        public $respuesta,
        public $nombreGestor,
        public $tipoDestinatario  // 'inquilino', 'gestor', 'arrendador'
    ) {}

    public function envelope() {
        return new Envelope(
            from: 'spotstayy@gmail.com',
            subject: "Respuesta a tu incidencia en SpotStay"
        );
    }

    public function content() {
        return new Content(
            view: 'emails.contacto_incidencia',
            with: [
                'respuesta' => $this->respuesta,
                'nombreGestor' => $this->nombreGestor,
                'tipoDestinatario' => $this->tipoDestinatario,
            ]
        );
    }
}
```

### **Cómo se dispara desde Admin\IncidenciaController:**

```php
// app/Http/Controllers/Admin/IncidenciaController.php
public function responder(Request $request, $id) {
    $incidencia = DB::table('tbl_incidencia')->find($id);
    
    // 1. Guardar respuesta en BD
    DB::table('tbl_respuesta_incidencia')->insert([
        'id_incidencia_fk' => $id,
        'respuesta' => $request->input('respuesta'),
        'respondido_por' => auth()->id(),
        'creado' => now()
    ]);
    
    // 2. Enviar mail al inquilino
    $inquilino = DB::table('tbl_usuario')
        ->where('id_usuario', $incidencia->id_reporta_fk)
        ->first();
    
    Mail::to($inquilino->email_usuario)
        ->send(new ContactoIncidencia(
            $id,
            $request->input('respuesta'),
            'Equipo SpotStay',
            'inquilino'
        ));
    
    // 3. Crear notificación
    app(ActividadService::class)
        ->incidenciaRespondida($id);
    
    return response()->json(['ok' => true]);
}
```

**Flujo:**
```
Admin clickea "Responder"
    ↓
POST /admin/incidencias/42/responder
    ↓
Backend guarda respuesta en BD
    ↓
Backend crea notificación en tbl_notificacion
    ↓
Backend crea Mail(ContactoIncidencia)
    ↓
Laravel conecta a SMTP (Gmail)
    ↓
SMTP envía email al inquilino@gmail.com
    ↓
✅ Email llega en 1-2 segundos
```

---

## 2.4 Vistas de Email con CSS Inline

```blade
<!-- resources/views/emails/contacto_incidencia.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto;
            margin: 0;
            padding: 0;
            background: #f5f7fa;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #035498 0%, #123b7a 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px;
        }
        .content p {
            color: #333;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .respuesta-box {
            background: #f9f9f9;
            border-left: 4px solid #035498;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
            color: #555;
        }
        .footer {
            background: #f5f7fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            background: #035498;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📌 Respuesta a tu Incidencia</h1>
        </div>

        <div class="content">
            <p>Hola {{ $nombreUsuario ?? 'Usuario' }},</p>

            <p>El gestor ha respondido a tu incidencia:</p>

            <div class="respuesta-box">
                {{ $respuesta }}
            </div>

            @if($tipoDestinatario === 'inquilino')
                <p>Puedes ver el estado completo de tu incidencia en tu panel:</p>
                <a href="https://spotstay.com/inquilino/incidencias" class="btn">
                    Ver mis incidencias
                </a>
            @endif

            <p>Si tienes más preguntas, contáctanos.</p>

            <p>Saludos,<br><strong>Equipo SpotStay</strong></p>
        </div>

        <div class="footer">
            <p>Este email fue enviado automáticamente. No respondas a este correo.</p>
            <p>© 2024 SpotStay. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
```

**Puntos importantes:**
- ✅ **CSS Inline** (todos los estilos en `<style>`)
- ✅ Compatible con Gmail, Outlook, Apple Mail
- ✅ Variables Blade (`{{ $respuesta }}`)
- ✅ Links con URLs completas
- ✅ Sin imágenes externas (para privacidad)

---

## 2.5 Clase Mailable: ContratoSubido

```php
// app/Mail/ContratoSubido.php
class ContratoSubido extends Mailable {
    
    public function __construct(
        public $alquilerId,
        public $nombreArrendador,
        public $urlContrato
    ) {}

    public function envelope() {
        return new Envelope(
            from: 'spotstayy@gmail.com',
            subject: "Tu contrato de alquiler está disponible"
        );
    }

    public function content() {
        return new Content(
            view: 'emails.contrato_subido',
            with: [
                'nombreArrendador' => $this->nombreArrendador,
                'urlDescarga' => $this->urlContrato,
            ]
        );
    }
}
```

### **Cómo se dispara desde Arrendador\ContratoController:**

```php
// app/Http/Controllers/Arrendador/ContratoController.php
public function subirPdf(Request $request, $alquilerId) {
    $alquiler = DB::table('tbl_alquiler')->find($alquilerId);
    $inquilino = DB::table('tbl_usuario')
        ->where('id_usuario', $alquiler->id_inquilino_fk)
        ->first();
    
    // 1. Guardar PDF en storage
    $rutaPdf = $request->file('contrato')->store('contratos', 's3');
    
    // 2. Actualizar BD
    DB::table('tbl_alquiler')
        ->where('id_alquiler', $alquilerId)
        ->update([
            'pdf_contrato' => $rutaPdf,
            'actualizado_alquiler' => now()
        ]);
    
    // 3. Enviar mail
    Mail::to($inquilino->email_usuario)
        ->send(new ContratoSubido(
            $alquilerId,
            auth()->user()->nombre_usuario,
            Storage::disk('s3')->url($rutaPdf)
        ));
    
    // 4. Crear notificación
    DB::table('tbl_notificacion')->insert([
        'id_usuario_fk' => $inquilino->id_usuario,
        'tipo_notificacion' => 'contrato_subido',
        'titulo_notificacion' => 'Tu contrato está disponible',
        'mensaje_notificacion' => 'El arrendador subió tu contrato',
        'url_notificacion' => "/inquilino/alquileres/$alquilerId",
        'icono_notificacion' => 'file-text',
        'color_notificacion' => '#F97316',
    ]);
    
    return response()->json(['ok' => true, 'message' => 'PDF subido y notificado']);
}
```

---

## 2.6 Tabla Comparativa: Incidencias vs Contratos

| Aspecto | Respuesta a Incidencia | Contrato Subido |
|--------|----------------------|-----------------|
| **Evento** | Admin clickea botón "Responder" | Arrendador sube PDF |
| **Mailable** | `ContactoIncidencia` | `ContratoSubido` |
| **Destinatario** | Inquilino/Gestor/Arrendador | Inquilino |
| **Tipo Notificación** | `incidencia_respondida` | `contrato_subido` |
| **Color** | 🔵 Azul | 🟠 Naranja |
| **URL en Notif** | `/incidencias/42` | `/alquileres/5` |
| **JS involucrado** | Click en botón "Responder" form | Click en input file + upload |

---

## 2.7 Flujo Completo: De evento a email en usuario

```
ESCENARIO: Admin responde incidencia de agua en Piso 3B

1️⃣ DISPARO DEL EVENTO
   ┌─────────────────────────────────────────────────┐
   │ Admin abre incidencia #42 en /admin/incidencias │
   │                                                   │
   │ [Panel lateral derecho]                          │
   │ ┌──────────────────────────────────────────────┐│
   │ │ Responder a incidencia                       ││
   │ │ ┌──────────────────────────────────────────┐││
   │ │ │ <textarea placeholder="Tu respuesta">   │││
   │ │ │ Parece que hay filtración. Enviaremos   │││
   │ │ │ al plomero urgente.                     │││
   │ │ └──────────────────────────────────────────┘││
   │ │ [Enviar Mail + Guardar] button               ││
   │ └──────────────────────────────────────────────┘│
   └─────────────────────────────────────────────────┘

2️⃣ BACKEND RECIBE POST
   POST /admin/incidencias/42/responder
   {
       "respuesta": "Parece que hay filtración. Enviaremos al plomero."
   }

3️⃣ GUARDAR EN BD
   INSERT INTO tbl_respuesta_incidencia (
       id_incidencia_fk,
       respuesta,
       respondido_por,
       creado
   ) VALUES (
       42,
       "Parece que...",
       3,  ← Admin ID
       2024-05-27 14:30:00
   );

4️⃣ CREAR NOTIFICACIÓN EN BD
   INSERT INTO tbl_notificacion (
       id_usuario_fk,              ← 7 (inquilino Juan)
       tipo_notificacion,          ← "incidencia_respondida"
       titulo_notificacion,        ← "Tu incidencia fue respondida"
       mensaje_notificacion,       ← "El gestor respondió a tu reporte"
       url_notificacion,           ← "/inquilino/incidencias/42"
       icono_notificacion,         ← "chat"
       color_notificacion,         ← "#3B82F6"
       creado_notificacion         ← NOW()
   );

5️⃣ CREAR INSTANCIA MAILABLE
   $mail = new ContactoIncidencia(
       incidenciaId: 42,
       respuesta: "Parece que hay filtración...",
       nombreGestor: "Carlos García",
       tipoDestinatario: "inquilino"
   );

6️⃣ ENVIAR MAIL
   Mail::to("juan@gmail.com")
       ->send($mail);
   
   ↓ Laravel hace:
   ├─ Instancia Mail\ContactoIncidencia
   ├─ Renderiza view emails/contacto_incidencia.blade.php
   ├─ Aplica CSS inline
   ├─ Conecta a SMTP Gmail
   ├─ Autentica con App Password
   └─ Envía email

7️⃣ GMAIL RECIBE
   ┌──────────────────────────────┐
   │ From: spotstayy@gmail.com     │
   │ To: juan@gmail.com           │
   │ Subject: Respuesta a tu      │
   │          incidencia          │
   │                              │
   │ [Email HTML renderizado]     │
   │ Tu incidencia fue respondida │
   │ Respuesta: Parece que hay... │
   │ [Botón: Ver incidencias]     │
   │ [Footer SpotStay]            │
   └──────────────────────────────┘

8️⃣ USUARIO VE EN BANDEJA
   Juan abre Gmail
       ↓
   Ve email de "SpotStay" con asunto "Respuesta a tu..."
       ↓
   Lee el email (renderizado hermosamente)
       ↓
   Clickea botón "Ver incidencias"
       ↓
   Va a https://spotstay.com/inquilino/incidencias/42

9️⃣ SINCRONIZACIÓN CON NOTIFICACIÓN
   Juan recarga /inquilino/dashboard
       ↓
   AppServiceProvider::boot() ejecuta
       ↓
   SELECT * FROM tbl_notificacion WHERE id_usuario_fk = 7 AND leida = 0
       ↓
   Obtiene la notificación de paso 4
       ↓
   Se la muestra en dropdown campana
       ↓
   Juan ve: [💬 Tu incidencia fue respondida] en la campana
```

---

## 2.8 Configuración de Estilos (CSS)

Los emails usan **CSS Inline** para máxima compatibilidad:

```html
<style>
    /* Estos estilos se aplican a TODOS los clientes de email */
    body { font-family: Arial, sans-serif; }
    .header { background: #035498; color: white; }
    .content { padding: 20px; }
    .respuesta-box { 
        background: #f0f0f0; 
        border-left: 4px solid #035498; 
    }
</style>
```

**No se usan:**
- ❌ `<link>` a CSS externo (no soportado)
- ❌ Media queries complejas (limitado soporte)
- ❌ Imágenes de fondo (no soportado)
- ❌ Flexbox/Grid (no soportado)

**Se usan:**
- ✅ `<table>` para layouts (viejo pero funciona)
- ✅ Estilos inline en etiquetas
- ✅ Colores HEX
- ✅ Fuentes web-safe

---

## 2.9 Problemas y Limitaciones Actuales

| Problema | Impacto | Solución |
|----------|---------|----------|
| **Envío síncrono** | Si SMTP falla, el usuario ve error | Usar Queue (async) |
| **Sin reintentos** | Email se pierde si falla | Queue auto-reintenta |
| **Sin logging** | No sabemos si se envió | Agregar log `Mail::events()` |
| **No personalizado por rol** | Mismo HTML para todos | Usar variables Blade |
| **Attachment manual** | No se pueden adjuntar PDFs | Usar `$mail->attach()` |

---

---

# 3️⃣ CASOS DE USO COMBINADOS

## 3.1 Caso: Incidencia Reportada

```
Usuario (Inquilino)     →    Reporta incidencia
                                    ↓
                        CREATE tbl_incidencia
                                    ↓
                    ActividadService::incidenciaCreada()
                                    ↓
                        ┌───────────┴────────────┐
                        ↓                        ↓
                   INSERT tbl_notificacion   (gestor)
                   
Gestor (a los 10 seg)  →    Recarga dashboard
                                    ↓
                    SELECT ... notificaciones sin leer
                                    ↓
                        ┌─────────────────────────┐
                        │ Badge: 1 (nueva notif)  │
                        │ Dropdown:               │
                        │ [⚠️ Nueva incidencia]   │
                        └─────────────────────────┘
                                    ↓
                        Gestor clickea notificación
                                    ↓
                        Navega a /gestor/incidencias/42
```

---

## 3.2 Caso: Admin Responde + Mail + Notificación

```
┌──────────────────────────────────────────────────────────────┐
│ EVENTO: Admin responde incidencia #42 (agua en Piso 3B)      │
└──────────────────────────────────────────────────────────────┘

                    ┌─────────────────────┐
                    │  PASO 1: BD         │
                    ├─────────────────────┤
                    │ Guardar respuesta:  │
                    │                     │
                    │ INSERT tbl_respuesta│
                    │ VALUES (42, text,   │
                    │ admin_id, NOW())    │
                    └────────────┬────────┘
                                 │
                    ┌────────────↓────────────┐
                    │  PASO 2: NOTIFICACIÓN   │
                    ├────────────────────────┤
                    │ INSERT tbl_notificacion│
                    │ VALUES (                │
                    │  id_usuario = 7,       │
                    │  tipo = "respondida",  │
                    │  titulo = "Tu...",     │
                    │  msg = "Gestor...",    │
                    │  url = "/inc/42",      │
                    │  icono = "chat",       │
                    │  color = "#3B82F6"     │
                    │ )                      │
                    └────────────┬───────────┘
                                 │
                    ┌────────────↓────────────┐
                    │  PASO 3: EMAIL MAILABLE │
                    ├────────────────────────┤
                    │ $mail = new            │
                    │ ContactoIncidencia(    │
                    │  42,                   │
                    │  "texto respuesta",    │
                    │  "Carlos",             │
                    │  "inquilino"           │
                    │ )                      │
                    └────────────┬───────────┘
                                 │
                    ┌────────────↓────────────┐
                    │  PASO 4: ENVÍO SMTP    │
                    ├────────────────────────┤
                    │ Mail::to(juan@gmail)   │
                    │     ->send($mail)      │
                    │                        │
                    │ ↓ Conecta a SMTP       │
                    │ ↓ Autentica            │
                    │ ↓ Envía email HTML     │
                    │ ✅ Llega en 2 seg      │
                    └────────────┬───────────┘
                                 │
                    ┌────────────↓────────────┐
                    │  RESULTADO FINAL       │
                    ├────────────────────────┤
                    │ Juan tiene:            │
                    │ • Email en bandeja     │
                    │ • Notificación en BD   │
                    │ • Visible en campana   │
                    │   (cuando recargue)    │
                    └────────────────────────┘
```

---

## 3.3 Caso: Contrato Subido + Mail + Notificación

```
Arrendador sube PDF de contrato
        ↓
POST /arrendador/contratos/5/subir-pdf
        ↓
PASO 1: Guardar PDF en S3
    Storage::disk('s3')->put('contratos/alquiler_5.pdf', $file)
        ↓
PASO 2: Actualizar tbl_alquiler
    UPDATE tbl_alquiler
    SET pdf_contrato = 'contratos/alquiler_5.pdf',
        actualizado_alquiler = NOW()
    WHERE id_alquiler = 5
        ↓
PASO 3: Crear notificación
    INSERT tbl_notificacion (
        id_usuario_fk = 8,  ← Inquilino
        tipo = 'contrato_subido',
        titulo = 'Tu contrato está disponible',
        icono = 'file-text',
        color = '#F97316'
    )
        ↓
PASO 4: Enviar mail
    Mail::to(inquilino@gmail.com)
        ->send(new ContratoSubido(...))
        ↓ Renderiza HTML con link descarga
        ↓ Envía por SMTP
        ↓ ✅ Llega en 2 seg
        ↓
RESULTADO: Inquilino ve
    • Email: "Tu contrato está disponible [Descargar]"
    • Notificación en campana
    • Link en BD para descargar PDF
```

---

## 3.4 Resumen Visual: Flujo Arquitectónico Completo

```
USUARIO ACTION
    │
    ├─ Reporta incidencia
    ├─ Responde incidencia ← (ADMIN)
    ├─ Sube contrato ← (ARRENDADOR)
    └─ ...
    │
    ↓
CONTROLADOR (HTTP Request)
    │
    ├─ Admin\IncidenciaController::responder()
    ├─ Arrendador\ContratoController::subirPdf()
    └─ ...
    │
    ↓ (Múltiples streams en paralelo)
    │
    ┌───────────────┬──────────────┬──────────────┐
    ↓               ↓               ↓              │
 DATABASE       NOTIFICACIONES    EMAILS         │
    │               │               │              │
    ├─ INSERT      ├─ INSERT       ├─ Mailable   │
    │ respuesta    │ notify        │   class     │
    │              │               │              │
    │              │         ├─ Template   ├─ HTML
    │              │         ├─ Variables  │ + CSS
    │              │         └─ Send      └─ SMTP
    │              │               │
    │              │        ✅ Email llega
    │              │
    │         ✅ NotifiView  ├─ Visible en BD
    │            (lazy load  ├─ Visible en Blade
    │             al reload) └─ Visible en campana
    │
    └──────────────────────────────────────────→
                   Sync: TODO en backend

VISTA / USUARIO
    │
    ├─ Recarga página
    ├─ AppServiceProvider ejecuta
    ├─ SELECT notificaciones sin leer
    ├─ Renderiza en Blade
    └─ ✅ Usuario ve en campana
    │
    └─ Abre Gmail
       └─ ✅ Lee email hermoso
```

---

---

# 📚 REFERENCIAS Y CHEAT SHEET

## Endpoints de Notificaciones

```
POST /notificaciones/{id}/eliminar
POST /notificaciones/{id}/marcar-leida
GET  /admin/dashboard  (ve timeline histórico)
```

## Métodos de ActividadService

```php
app(ActividadService::class)->incidenciaCreada($id);
app(ActividadService::class)->incidenciaRespondida($id);
app(ActividadService::class)->solicitudAprobada($id);
app(ActividadService::class)->pagoRealizado($id);
// ... 13 métodos totales
```

## Enviar Mail manualmente

```php
use App\Mail\ContactoIncidencia;
use Illuminate\Support\Facades\Mail;

Mail::to('usuario@example.com')
    ->send(new ContactoIncidencia(
        incidenciaId: 42,
        respuesta: 'Tu texto',
        nombreGestor: 'Carlos',
        tipoDestinatario: 'inquilino'
    ));
```

## Variables inyectadas en Blade

```blade
<!-- Disponible en TODAS las vistas -->
@php
    $notificacionesGestor = [...];  // Colección
    $notificacionesGestorSinLeer = 6;  // Int
@endphp

<!-- Usar en dropdown -->
@forelse($notificacionesGestor as $notif)
    <div>{{ $notif->titulo_notificacion }}</div>
@empty
    <p>Sin notificaciones</p>
@endforelse
```

---

## 🎓 Conclusión Pedagógica

**Notificaciones:**
- ✅ Simple pero efectiva (no hay polling)
- ✅ Centralizada en `ActividadService`
- ✅ 13 tipos documentados
- ⚠️ Pasiva (solo al recargar)
- 💡 Mejora: Agregar polling cada 30s

**Mails:**
- ✅ Uso de Laravel Mailables
- ✅ CSS inline para compatibilidad
- ✅ Eventos disparados desde backend
- ⚠️ Síncrono (puede bloquear)
- 💡 Mejora: Mover a Queue para async

Ambos sistemas trabajan juntos para notificar al usuario: primero por email (inmediato) y luego en la app (al recargar).

