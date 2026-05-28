# 🔍 ANÁLISIS TÉCNICO PROFUNDO: NOTIFICACIONES Y MAILS

**Documento de referencia técnica** con código exacto, diagramas detallados y casos reales del proyecto.

---

## 📍 INDICE

1. [Estructura de BD: Notificaciones](#1-estructura-de-bd-notificaciones)
2. [Clase ActividadService](#2-clase-actividadservice)
3. [Controladores que crean Notificaciones](#3-controladores-que-crean-notificaciones)
4. [JavaScript de Notificaciones](#4-javascript-de-notificaciones)
5. [Configuración de Mails](#5-configuración-de-mails)
6. [Clases Mailable](#6-clases-mailable)
7. [Controladores que envían Mails](#7-controladores-que-envían-mails)
8. [Vistas de Email](#8-vistas-de-email)
9. [Diagrama de Secuencia Completo](#9-diagrama-de-secuencia-completo)
10. [Testing y Debugging](#10-testing-y-debugging)

---

# 1️⃣ ESTRUCTURA DE BD: NOTIFICACIONES

## 1.1 Tabla: tbl_notificacion

```sql
CREATE TABLE tbl_notificacion (
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario_fk INT NOT NULL,
    tipo_notificacion VARCHAR(50) NOT NULL,
    titulo_notificacion VARCHAR(255) NOT NULL,
    mensaje_notificacion TEXT NOT NULL,
    url_notificacion VARCHAR(255),
    icono_notificacion VARCHAR(50),
    color_notificacion VARCHAR(20),
    leida_notificacion TINYINT(1) DEFAULT 0,
    leida_en_notificacion TIMESTAMP NULL,
    tipo_entidad VARCHAR(50),
    id_entidad INT,
    creado_notificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_notificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario_fk) REFERENCES tbl_usuario(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario_leida (id_usuario_fk, leida_notificacion),
    INDEX idx_creado (creado_notificacion)
);
```

## 1.2 Campos Explicados

| Campo | Tipo | Ejemplo | Nota |
|-------|------|---------|------|
| `id_notificacion` | INT | 1234 | PK, auto-increment |
| `id_usuario_fk` | INT | 5 | Quién la recibe (FK a tbl_usuario) |
| `tipo_notificacion` | VARCHAR | "nueva_incidencia" | Determina icon/color |
| `titulo_notificacion` | VARCHAR | "Nueva incidencia" | Título visible |
| `mensaje_notificacion` | TEXT | "Agua goteando..." | Descripción |
| `url_notificacion` | VARCHAR | "/gestor/incidencias/42" | Dónde ir al clickear |
| `icono_notificacion` | VARCHAR | "exclamation-triangle" | Ícono Bootstrap (bi-*) |
| `color_notificacion` | VARCHAR | "#DC2626" | Color hex del ícono |
| `leida_notificacion` | TINYINT | 0 ó 1 | 0=sin leer, 1=leída |
| `leida_en_notificacion` | TIMESTAMP | 2024-05-27 10:30:00 | Cuándo se leyó |
| `tipo_entidad` | VARCHAR | "incidencia" | Tipo de recurso asociado |
| `id_entidad` | INT | 42 | ID del recurso |
| `creado_notificacion` | TIMESTAMP | 2024-05-27 09:15:00 | Cuándo se creó |

## 1.3 Indices para Performance

```sql
-- Índice 1: Busca rápida de notificaciones sin leer de un usuario
INDEX idx_usuario_leida (id_usuario_fk, leida_notificacion)

-- Índice 2: Ordenamiento por fecha
INDEX idx_creado (creado_notificacion)
```

**Queries optimizadas:**
```php
// Query 1: Obtener últimas 6 sin leer (RÁPIDO con índice)
SELECT * FROM tbl_notificacion
WHERE id_usuario_fk = 5
  AND leida_notificacion = 0
ORDER BY creado_notificacion DESC
LIMIT 6;
```

---

# 2️⃣ CLASE ActividadService

## 2.1 Ubicación y Estructura

```
app/Services/ActividadService.php
```

### Código (simplificado):

```php
<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActividadService {
    
    /**
     * Crear notificación cuando se reporta una incidencia
     */
    public function incidenciaCreada($idIncidencia) {
        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $idIncidencia)
            ->first();
        
        if (!$incidencia || !$incidencia->id_asignado_fk) {
            return;
        }
        
        $this->crearNotificacion(
            usuarioId: (int) $incidencia->id_asignado_fk,
            tipo: 'nueva_incidencia',
            titulo: 'Nueva incidencia',
            mensaje: 'Se ha reportado una nueva incidencia',
            url: "/gestor/incidencias/{$idIncidencia}",
            icono: 'exclamation-triangle',
            color: '#DC2626',
            tipoEntidad: 'incidencia',
            idEntidad: (int) $idIncidencia
        );
    }

    /**
     * Crear notificación cuando se responde una incidencia
     */
    public function incidenciaRespondida($idIncidencia) {
        $incidencia = DB::table('tbl_incidencia')
            ->where('id_incidencia', $idIncidencia)
            ->first();
        
        $this->crearNotificacion(
            usuarioId: (int) $incidencia->id_reporta_fk,
            tipo: 'incidencia_respondida',
            titulo: 'Tu incidencia fue respondida',
            mensaje: 'El gestor respondió a tu reporte',
            url: "/inquilino/incidencias/{$idIncidencia}",
            icono: 'chat',
            color: '#3B82F6',
            tipoEntidad: 'incidencia',
            idEntidad: (int) $idIncidencia
        );
    }

    /**
     * Crear notificación cuando se aprueba una solicitud
     */
    public function solicitudAprobada($idSolicitud, $tipo = 'arrendador') {
        $solicitud = $tipo === 'gestor' 
            ? DB::table('tbl_solicitud_gestor')->find($idSolicitud)
            : DB::table('tbl_solicitud_arrendador')->find($idSolicitud);
        
        $this->crearNotificacion(
            usuarioId: (int) $solicitud->id_usuario_fk,
            tipo: 'solicitud_aprobada',
            titulo: 'Tu solicitud fue aprobada',
            mensaje: 'ඡ Felicidades! Tu solicitud fue aceptada.',
            url: "/usuario/solicitudes/{$idSolicitud}",
            icono: 'check-circle',
            color: '#059669',
            tipoEntidad: 'solicitud',
            idEntidad: (int) $idSolicitud
        );
    }

    /**
     * Crear notificación cuando se rechaza una solicitud
     */
    public function solicitudRechazada($idSolicitud, $tipo = 'arrendador') {
        // Similar a solicitudAprobada pero con color rojo
        $this->crearNotificacion(
            usuarioId: ...,
            tipo: 'solicitud_rechazada',
            titulo: 'Tu solicitud fue rechazada',
            color: '#DC2626',  // Rojo
            // ...
        );
    }

    /**
     * Crear notificación cuando se realiza un pago
     */
    public function pagoRealizado($idPago) {
        $pago = DB::table('tbl_pago')->find($idPago);
        $alquiler = DB::table('tbl_alquiler')->find($pago->id_alquiler_fk);
        
        // El arrendador recibe la notificación
        $this->crearNotificacion(
            usuarioId: DB::table('tbl_propiedad')
                ->where('id_propiedad', $alquiler->id_propiedad_fk)
                ->value('id_arrendador_fk'),
            tipo: 'pago_realizado',
            titulo: 'Pago recibido',
            mensaje: "Se ha recibido un pago de \${$pago->monto_pago}",
            url: "/arrendador/pagos/{$idPago}",
            icono: 'check-circle',
            color: '#16A34A',  // Verde
            tipoEntidad: 'pago',
            idEntidad: (int) $idPago
        );
    }

    /**
     * Método central para crear notificaciones
     */
    private function crearNotificacion(
        int $usuarioId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $url,
        string $icono,
        string $color,
        string $tipoEntidad,
        int $idEntidad
    ) {
        // Validar que el usuario existe
        $usuario = DB::table('tbl_usuario')
            ->where('id_usuario', $usuarioId)
            ->first();
        
        if (!$usuario) {
            \Log::warning("Usuario $usuarioId no existe para notificación");
            return;
        }
        
        // Insertar notificación
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $usuarioId,
            'tipo_notificacion' => $tipo,
            'titulo_notificacion' => $titulo,
            'mensaje_notificacion' => $mensaje,
            'url_notificacion' => $url,
            'icono_notificacion' => $icono,
            'color_notificacion' => $color,
            'leida_notificacion' => 0,
            'tipo_entidad' => $tipoEntidad,
            'id_entidad' => $idEntidad,
            'creado_notificacion' => Carbon::now()
        ]);
    }

    // ... 13 métodos en total
}
```

---

# 3️⃣ CONTROLADORES QUE CREAN NOTIFICACIONES

## 3.1 Admin\IncidenciaController

```php
// app/Http/Controllers/Admin/IncidenciaController.php

public function crear(Request $request) {
    // ... validación ...
    
    $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
        'id_reporta_fk' => $request->input('id_inquilino'),
        'id_asignado_fk' => $request->input('id_gestor'),
        'id_propiedad_fk' => $request->input('id_propiedad'),
        'descripcion_incidencia' => $request->input('descripcion'),
        'estado_incidencia' => 'abierta',
        'creado_incidencia' => now()
    ]);
    
    // ← DISPARA NOTIFICACIÓN AL GESTOR
    app(ActividadService::class)->incidenciaCreada($idIncidencia);
    
    return response()->json([
        'ok' => true,
        'message' => 'Incidencia creada exitosamente'
    ]);
}

public function responder(Request $request, $id) {
    // ... validación ...
    
    DB::beginTransaction();
    try {
        // 1. Guardar respuesta
        DB::table('tbl_respuesta_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'respuesta' => $request->input('respuesta'),
            'respondido_por' => auth()->id(),
            'creado' => now()
        ]);
        
        // 2. ← DISPARA NOTIFICACIÓN AL INQUILINO
        app(ActividadService::class)->incidenciaRespondida($id);
        
        // 3. ← ENVÍA EMAIL AL INQUILINO
        $incidencia = DB::table('tbl_incidencia')->find($id);
        $inquilino = DB::table('tbl_usuario')
            ->find($incidencia->id_reporta_fk);
        
        Mail::to($inquilino->email_usuario)
            ->send(new ContactoIncidencia(
                $id,
                $request->input('respuesta'),
                auth()->user()->nombre_usuario,
                'inquilino'
            ));
        
        DB::commit();
        
        return response()->json([
            'ok' => true,
            'message' => 'Respuesta enviada'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error al responder incidencia: " . $e->getMessage());
        return response()->json([
            'ok' => false,
            'message' => 'Error al enviar respuesta'
        ], 500);
    }
}
```

## 3.2 Admin\SolicitudController

```php
// app/Http/Controllers/Admin/SolicitudController.php

public function aprobar(Request $request, $id) {
    $tipoSolicitud = $request->query('tipo', 'arrendador');
    
    DB::beginTransaction();
    try {
        if ($tipoSolicitud === 'gestor') {
            $solicitud = DB::table('tbl_solicitud_gestor')
                ->where('id_solicitud_gestor', $id)
                ->first();
            
            // Actualizar solicitud
            DB::table('tbl_solicitud_gestor')
                ->where('id_solicitud_gestor', $id)
                ->update([
                    'estado_solicitud_gestor' => 'aprobada',
                    'actualizado_solicitud_gestor' => now()
                ]);
            
            // ← DISPARA NOTIFICACIÓN
            app(ActividadService::class)
                ->solicitudAprobada($id, 'gestor');
        }
        
        DB::commit();
        return response()->json(['ok' => true]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['ok' => false], 500);
    }
}
```

---

# 4️⃣ JAVASCRIPT DE NOTIFICACIONES

## 4.1 Dropdown Toggle y Eliminación

```javascript
// public/js/gestor/campana.js

document.addEventListener('DOMContentLoaded', function() {
    const campanaBtn = document.getElementById('campana-btn');
    const campanaDropdown = document.getElementById('campana-dropdown');
    const eliminarBtns = document.querySelectorAll('.btn-eliminar');

    // Toggle dropdown
    if (campanaBtn) {
        campanaBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            campanaDropdown.style.display = 
                campanaDropdown.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Cerrar dropdown al clickear fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.campana-container')) {
            campanaDropdown.style.display = 'none';
        }
    });

    // Eliminar notificación
    eliminarBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const notifId = this.closest('.campana-item').dataset.id;
            eliminarNotificacion(notifId);
        });
    });
});

function eliminarNotificacion(notifId) {
    const csrfToken = document.querySelector('[name=csrf-token]').content;
    
    fetch(`/notificaciones/${notifId}/eliminar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Remover del DOM
            const item = document.querySelector(`[data-id="${notifId}"]`);
            item.remove();
            
            // Actualizar badge
            const badge = document.querySelector('.campana-btn .badge');
            if (badge) {
                const count = parseInt(badge.textContent);
                if (count > 1) {
                    badge.textContent = count - 1;
                } else {
                    badge.remove();
                }
            }
            
            mostrarMensaje('Notificación eliminada', false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al eliminar notificación', true);
    });
}
```

## 4.2 Controller para eliminar notificaciones

```php
// app/Http/Controllers/NotificacionController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller {
    
    public function eliminar($id) {
        $eliminadas = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', auth()->id())  // ← Seguridad
            ->delete();
        
        return response()->json([
            'ok' => $eliminadas > 0
        ]);
    }
    
    public function marcarLeida($id) {
        $actualizado = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_fk', auth()->id())
            ->update([
                'leida_notificacion' => 1,
                'leida_en_notificacion' => now()
            ]);
        
        return response()->json([
            'ok' => $actualizado > 0
        ]);
    }
}
```

---

# 5️⃣ CONFIGURACIÓN DE MAILS

## 5.1 Archivos de Configuración

### config/mail.php
```php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],
    ],
];
```

### .env
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=spotstayy@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=spotstayy@gmail.com
MAIL_FROM_NAME=SpotStay
```

**⚠️ Nota sobre Google App Password:**
1. Habilitar 2FA en Google Account
2. Ir a https://myaccount.google.com/apppasswords
3. Crear "App Password" para Mail
4. Copiar la contraseña (16 caracteres con espacios)
5. Pegar en .env (sin copiar los espacios)

---

# 6️⃣ CLASES MAILABLE

## 6.1 ContactoIncidencia

```php
// app/Mail/ContactoIncidencia.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactoIncidencia extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(
        public $idIncidencia,
        public $respuesta,
        public $nombreGestor,
        public $tipoDestinatario  // 'inquilino', 'gestor', 'arrendador'
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS'),
            subject: "Respuesta a tu incidencia en SpotStay"
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.contacto_incidencia',
            with: [
                'idIncidencia' => $this->idIncidencia,
                'respuesta' => $this->respuesta,
                'nombreGestor' => $this->nombreGestor,
                'tipoDestinatario' => $this->tipoDestinatario,
            ]
        );
    }
}
```

## 6.2 ContratoSubido

```php
// app/Mail/ContratoSubido.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContratoSubido extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(
        public $alquilerId,
        public $nombreArrendador,
        public $urlContrato
    ) {}

    public function envelope(): Envelope {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS'),
            subject: "Tu contrato de alquiler está disponible"
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.contrato_subido',
            with: [
                'alquilerId' => $this->alquilerId,
                'nombreArrendador' => $this->nombreArrendador,
                'urlContrato' => $this->urlContrato,
            ]
        );
    }
}
```

---

# 7️⃣ CONTROLADORES QUE ENVÍAN MAILS

## 7.1 Admin\IncidenciaController (Responder)

```php
public function responder(Request $request, $id) {
    $incidencia = DB::table('tbl_incidencia')->find($id);
    
    DB::beginTransaction();
    try {
        // 1. Guardar respuesta
        DB::table('tbl_respuesta_incidencia')->insert([
            'id_incidencia_fk' => $id,
            'respuesta' => $request->input('respuesta'),
            'respondido_por' => auth()->id(),
            'creado' => now()
        ]);
        
        // 2. Obtener datos para email
        $inquilino = DB::table('tbl_usuario')
            ->find($incidencia->id_reporta_fk);
        
        // 3. ENVIAR EMAIL ← AQUÍ
        Mail::to($inquilino->email_usuario)
            ->send(new ContactoIncidencia(
                idIncidencia: $id,
                respuesta: $request->input('respuesta'),
                nombreGestor: auth()->user()->nombre_usuario,
                tipoDestinatario: 'inquilino'
            ));
        
        // 4. Crear notificación
        app(ActividadService::class)->incidenciaRespondida($id);
        
        DB::commit();
        
        return response()->json([
            'ok' => true,
            'message' => 'Respuesta enviada y email notificado'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error responder incidencia: " . $e->getMessage());
        return response()->json([
            'ok' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
```

## 7.2 Arrendador\ContratoController (Subir PDF)

```php
// app/Http/Controllers/Arrendador/ContratoController.php

public function subirPdf(Request $request, $alquilerId) {
    $request->validate([
        'contrato' => 'required|file|mimes:pdf|max:5000'
    ]);
    
    $alquiler = DB::table('tbl_alquiler')->find($alquilerId);
    
    DB::beginTransaction();
    try {
        // 1. Guardar PDF en S3
        $rutaPdf = $request->file('contrato')
            ->store('contratos', 's3');
        
        // 2. Actualizar BD
        DB::table('tbl_alquiler')
            ->where('id_alquiler', $alquilerId)
            ->update([
                'pdf_contrato' => $rutaPdf,
                'actualizado_alquiler' => now()
            ]);
        
        // 3. Obtener inquilino
        $inquilino = DB::table('tbl_usuario')
            ->find($alquiler->id_inquilino_fk);
        
        // 4. URL de descarga
        $urlDescarga = \Storage::disk('s3')
            ->url($rutaPdf);
        
        // 5. ENVIAR EMAIL ← AQUÍ
        Mail::to($inquilino->email_usuario)
            ->send(new ContratoSubido(
                alquilerId: $alquilerId,
                nombreArrendador: auth()->user()->nombre_usuario,
                urlContrato: $urlDescarga
            ));
        
        // 6. Crear notificación
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $inquilino->id_usuario,
            'tipo_notificacion' => 'contrato_subido',
            'titulo_notificacion' => 'Tu contrato está disponible',
            'mensaje_notificacion' => 'El arrendador subió tu contrato',
            'url_notificacion' => "/inquilino/alquileres/{$alquilerId}",
            'icono_notificacion' => 'file-text',
            'color_notificacion' => '#F97316'
        ]);
        
        DB::commit();
        
        return response()->json([
            'ok' => true,
            'message' => 'Contrato subido y enviado por email'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error subir contrato: " . $e->getMessage());
        return response()->json([
            'ok' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
```

---

# 8️⃣ VISTAS DE EMAIL

## 8.1 emails/contacto_incidencia.blade.php

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta a tu incidencia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #035498 0%, #123b7a 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .content h2 {
            font-size: 20px;
            color: #035498;
            margin-bottom: 20px;
        }
        
        .content p {
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .respuesta-box {
            background-color: #f0f4f8;
            border-left: 4px solid #035498;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            font-style: italic;
            color: #333;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #035498;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        
        .cta-button:hover {
            background-color: #123b7a;
        }
        
        .footer {
            background-color: #f5f7fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📌 Respuesta a tu Incidencia</h1>
            <p>En SpotStay, tu comodidad es nuestra prioridad</p>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h2>Hola,</h2>
            
            <p>
                El gestor ha respondido a tu incidencia reportada. 
                A continuación, te mostramos la respuesta:
            </p>

            <!-- La respuesta del gestor -->
            <div class="respuesta-box">
                {!! nl2br(e($respuesta)) !!}
            </div>

            <p>
                <strong>Respondido por:</strong> {{ $nombreGestor }}
            </p>

            <!-- CTA según tipo -->
            @if($tipoDestinatario === 'inquilino')
                <p>Puedes ver el estado completo de tu incidencia en tu panel de control:</p>
                <a href="https://spotstay.com/inquilino/incidencias/{{ $idIncidencia }}" class="cta-button">
                    Ver Incidencia Completa
                </a>
            @elseif($tipoDestinatario === 'gestor')
                <p>Accede a tu panel para ver todos los detalles:</p>
                <a href="https://spotstay.com/gestor/incidencias" class="cta-button">
                    Ir al Panel
                </a>
            @elseif($tipoDestinatario === 'arrendador')
                <p>Revisa el progreso en tu panel de propiedades:</p>
                <a href="https://spotstay.com/arrendador/propiedades" class="cta-button">
                    Mis Propiedades
                </a>
            @endif

            <p style="margin-top: 30px; color: #999; font-size: 12px;">
                Si tienes preguntas adicionales, no dudes en contactar con nuestro equipo de soporte.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© 2024 SpotStay. Todos los derechos reservados.</p>
            <p>Este email fue enviado automáticamente. Por favor, no respondas a este correo.</p>
            <p>Para más información, visita <a href="https://spotstay.com" style="color: #035498; text-decoration: none;">spotstay.com</a></p>
        </div>
    </div>
</body>
</html>
```

## 8.2 emails/contrato_subido.blade.php

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu contrato está disponible</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #035498 0%, #123b7a 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .content { padding: 40px; }
        .content p { margin-bottom: 15px; color: #666; }
        .document-box {
            background: #f0f4f8;
            border: 2px dashed #035498;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            border-radius: 6px;
        }
        .document-box i { font-size: 40px; color: #035498; }
        .document-box h3 { color: #035498; margin: 10px 0; }
        .download-button {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            background: #f5f7fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Tu Contrato de Alquiler</h1>
            <p>El documento está listo para descargar</p>
        </div>

        <div class="content">
            <p>Hola,</p>
            
            <p>
                El arrendador, <strong>{{ $nombreArrendador }}</strong>, 
                ha subido tu contrato de alquiler. 
                Puedes descargarlo ahora mismo.
            </p>

            <div class="document-box">
                <p style="font-size: 40px;">📑</p>
                <h3>Contrato de Alquiler</h3>
                <p style="color: #666; margin-bottom: 15px;">Alquiler ID: {{ $alquilerId }}</p>
                <a href="{{ $urlContrato }}" class="download-button">
                    Descargar PDF
                </a>
            </div>

            <p>
                El documento contiene todos los términos y condiciones del alquiler. 
                Te recomendamos revisar cuidadosamente toda la información.
            </p>

            <p>
                Si tienes dudas, puedes contactar al arrendador desde tu panel de SpotStay.
            </p>
        </div>

        <div class="footer">
            <p>© 2024 SpotStay. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
```

---

# 9️⃣ DIAGRAMA DE SECUENCIA COMPLETO

```
ESCENARIO: Admin responde incidencia → Inquilino recibe email + notificación

TIMELINE:
═════════════════════════════════════════════════════════════════════

T=0:00  👨‍💼 Admin abre /admin/incidencias/42
        ├─ Panel lateral: "Responder a incidencia"
        ├─ Textarea: escribe "Enviaremos plomero mañana"
        └─ Clickea botón [Enviar]

T=0:00  🌐 Browser
        POST /admin/incidencias/42/responder
        {
            "respuesta": "Enviaremos plomero mañana",
            "id_incidencia": 42
        }

T=0:01  🖥️ Backend (IncidenciaController)
        ├─ DB::beginTransaction()
        │
        ├─ INSERT tbl_respuesta_incidencia
        │  ├─ id_incidencia_fk = 42
        │  ├─ respuesta = "Enviaremos..."
        │  ├─ respondido_por = 3 (admin ID)
        │  └─ creado = NOW()
        │
        ├─ app(ActividadService::class)->incidenciaRespondida(42)
        │  └─ INSERT tbl_notificacion
        │     ├─ id_usuario_fk = 7 (inquilino Juan)
        │     ├─ tipo_notificacion = "incidencia_respondida"
        │     ├─ titulo = "Tu incidencia fue respondida"
        │     ├─ mensaje = "El gestor respondió"
        │     ├─ url = "/inquilino/incidencias/42"
        │     ├─ icono = "chat"
        │     ├─ color = "#3B82F6"
        │     ├─ leida = 0
        │     └─ creado = NOW()
        │
        ├─ Mail::to(juan@gmail.com)->send(new ContactoIncidencia(...))
        │  ├─ Instancia ContactoIncidencia
        │  ├─ Renderiza emails/contacto_incidencia.blade.php
        │  │  └─ Aplica estilos CSS inline
        │  │  └─ Sustituye {{ $respuesta }} → "Enviaremos..."
        │  │  └─ Sustituye {{ $nombreGestor }} → "Admin Sist"
        │  ├─ Conecta a SMTP Gmail
        │  │  ├─ Host: smtp.gmail.com:587
        │  │  ├─ Username: spotstayy@gmail.com
        │  │  ├─ Password: App Password
        │  │  └─ TLS encryption
        │  ├─ Autentica
        │  └─ Envía email (sincrónico ⚠️)
        │
        └─ DB::commit()
           └─ RETURN { "ok": true }

T=0:02  📧 Gmail SMTP recibe
        ├─ From: spotstayy@gmail.com
        ├─ To: juan@gmail.com
        ├─ Subject: "Respuesta a tu incidencia en SpotStay"
        └─ Body: HTML renderizado

T=0:03  ✉️ Gmail entrega
        Juan (inquilino) ve email en bandeja
        ├─ Asunto: "Respuesta a tu incidencia en SpotStay"
        ├─ Preview: "El gestor respondió a tu reporte"
        └─ [Abre email]

T=0:05  👁️ Juan lee email
        ├─ Encabezado: "📌 Respuesta a tu Incidencia"
        ├─ Texto: "El gestor ha respondido..."
        ├─ Respuesta: "Enviaremos plomero mañana"
        ├─ Botón: [Ver Incidencia Completa]
        └─ Clickea botón → /inquilino/incidencias/42

T=0:06  🔄 Juan navega a /inquilino/incidencias/42
        ├─ AppServiceProvider::boot()
        │  └─ SELECT * FROM tbl_notificacion
        │     WHERE id_usuario_fk = 7
        │     AND leida_notificacion = 0
        │     ORDER BY creado DESC
        │     LIMIT 6
        │  └─ Obtiene notificación creada en T=0:01
        │
        ├─ Blade renderiza
        │  ├─ Dropdown campana
        │  │  ├─ Badge: "1"
        │  │  └─ Item: 
        │  │     [💬] Tu incidencia fue respondida [✕]
        │  └─ Contenido de incidencia
        │     └─ Muestra respuesta: "Enviaremos plomero mañana"
        │
        └─ Juan ve TODO sincronizado

═════════════════════════════════════════════════════════════════════
```

---

# 🔟 TESTING Y DEBUGGING

## 10.1 Probar Mails Localmente

```php
// artisan tinker

// 1. Crear mail en memoria (sin enviar)
$mail = new \App\Mail\ContactoIncidencia(42, "Respuesta test", "Admin", "inquilino");

// 2. Ver contenido renderizado
echo $mail->render();

// 3. Enviar a mailtrap (si está configurado)
Mail::to('test@example.com')->send($mail);

// 4. Enviar con log
Mail::to('juan@gmail.com')->send($mail);
Log::info("Email enviado");
```

## 10.2 Debugging de Notificaciones

```php
// Ver notificaciones de un usuario
DB::table('tbl_notificacion')
   ->where('id_usuario_fk', 7)
   ->orderBy('creado_notificacion', 'desc')
   ->take(10)
   ->get();

// Eliminar notificaciones antigas
DB::table('tbl_notificacion')
   ->where('creado_notificacion', '<', now()->subDays(30))
   ->delete();
```

## 10.3 Logs para troubleshoot

```php
// Ver logs de mails enviados
tail -f storage/logs/laravel.log | grep "Mail"

// En controlador, agregar logs
\Log::info("Enviando mail a: " . $inquilino->email_usuario);
try {
    Mail::to($inquilino->email_usuario)
        ->send(new ContactoIncidencia(...));
    \Log::info("Mail enviado correctamente");
} catch (\Exception $e) {
    \Log::error("Error mail: " . $e->getMessage());
}
```

---

## 📌 CHECKLIST PRE-PRODUCCIÓN

- [ ] Configurar `.env` con credenciales Google
- [ ] Habilitar "Less secure apps" o usar App Password
- [ ] Testear `Mail::send()` con tinker
- [ ] Testear que notificaciones se crean en BD
- [ ] Verificar que dropdown campana muestra notificaciones
- [ ] Probar eliminación de notificaciones
- [ ] Agregar logs en controllers
- [ ] Setup para limpiar notificaciones antigas (Cron job)
- [ ] Cambiar a Queue (mejora futura)

