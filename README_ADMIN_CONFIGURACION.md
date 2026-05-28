# ⚙️ README: ADMIN CONFIGURACIÓN

**Vista:** `resources/views/admin/configuracion.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/ConfiguracionController.php`  
**Ruta:** `GET /admin/configuracion`

---

## 🎯 Propósito

Gestiona **notificaciones globales** del panel admin. Permite:
- Enviar notificaciones a todos los usuarios o a un rol concreto
- Enviar a un usuario específico
- Escribir título, mensaje y enlace opcional
- Validar el destino antes de crear la notificación
- Registrar la notificación desde el servicio de actividad

---

## 🔁 Flujo técnico

1. El usuario entra en `/admin/configuracion`.
2. `ConfiguracionController::index()` carga:
- `rolesDisponibles`
- `usuariosActivos`
3. La vista muestra el formulario de notificación.
4. `crearNotificacion(Request $request)` valida destino, alcance, usuario, título, mensaje y URL.
5. El controlador resuelve los usuarios destino y llama a `ActividadService::avisoImportante()`.
6. El servicio crea la notificación en la base de datos.

---

## 🎛️ Formulario de notificaciones

| Campo | ID / name | Tipo | Uso |
|-------|-----------|------|-----|
| Rol destino | `#destinoRolNotificacion` / `destino` | select | Selecciona todos o un rol concreto |
| Alcance | `#alcanceDestinoNotificacion` / `alcance_destino` | select | Todos del rol o usuario concreto |
| Usuario | `#usuarioDestinoNotificacion` / `usuario_destino` | select | Se muestra solo si el alcance es usuario |
| Título | `titulo_notificacion` | input | Obligatorio, máx. 200 caracteres |
| Enlace | `url_notificacion` | input | Opcional |
| Mensaje | `mensaje_notificacion` | textarea | Obligatorio, máx. 1000 caracteres |

**Reglas de validación reales:**
```php
'destino' => ['required', Rule::in(array_merge(['todos'], $rolesValidos))],
'alcance_destino' => ['required', Rule::in(['todos', 'usuario'])],
'usuario_destino' => ['nullable', 'integer', 'exists:tbl_usuario,id_usuario'],
'titulo_notificacion' => ['required', 'string', 'max:200'],
'mensaje_notificacion' => ['required', 'string', 'max:1000'],
'url_notificacion' => ['nullable', 'string', 'max:500'],
```

---

## 🧠 JavaScript de validación

```javascript
document.getElementById('destinoRolNotificacion').addEventListener('change', function(e) {
    const rol = e.target.value;
    if (rol && rol !== 'todos') {
        document.getElementById('bloqueAlcanceNotificacion').classList.remove('d-none');
    } else {
        document.getElementById('bloqueAlcanceNotificacion').classList.add('d-none');
    }
});

document.getElementById('alcanceDestinoNotificacion').addEventListener('change', function(e) {
    const alcance = e.target.value;
    if (alcance === 'usuario') {
        document.getElementById('bloqueUsuarioNotificacion').classList.remove('d-none');
    } else {
        document.getElementById('bloqueUsuarioNotificacion').classList.add('d-none');
    }
});

document.getElementById('form-notificacion-admin').addEventListener('submit', function(e) {
    const titulo = document.querySelector('input[name="titulo_notificacion"]').value.trim();
    const mensaje = document.querySelector('textarea[name="mensaje_notificacion"]').value.trim();

    if (!titulo || !mensaje) {
        e.preventDefault();
        alert('Título y mensaje son obligatorios');
    }
});
```

---

## 📊 Datos pasados a la vista

```php
compact('rolesDisponibles', 'usuariosActivos')
```

- `rolesDisponibles`: colección de roles con `slug_rol` y `nombre_rol`.
- `usuariosActivos`: colección de usuarios activos con `id_usuario`, `nombre_usuario`, `email_usuario` y `roles_usuario`.

---

## 📱 Responsive Design

### Desktop
- Formulario en rejilla Bootstrap.
- Campos de rol, alcance y usuario alineados.
- Mensaje a ancho completo.

### Mobile
- Campos apilados en una sola columna.
- Todo el formulario ocupa `100%`.

---

## 🔘 Botones y acciones

| Botón | Función |
|-------|---------|
| Enviar notificación | Ejecuta `POST /admin/configuracion/notificaciones/crear` |

---

## ⚠️ Puntos importantes

1. La validación del destino usa `Rule::in()` para limitar valores permitidos.
2. El envío real no lo hace la vista: lo resuelve `ActividadService`.
3. Si el alcance es `usuario`, el usuario destino es obligatorio.
4. El rol `todos` permite enviar a todos los usuarios activos.
5. El controlador usa consultas a `tbl_usuario` y `tbl_rol` para resolver destinatarios.