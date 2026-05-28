# SpotStay - Flujos operativos explicados

Este README reúne, de forma ordenada, los flujos más importantes que me pediste: gráficos del arrendador, pagos del inquilino, incidencias con correo al admin, subida de contratos con correo al arrendador y notificaciones automáticas o creadas por el admin.

---

## Explicacion de los graficos de arrendador

### Dónde se construyen
- Vista: [resources/views/arrendador/dashboard.blade.php](resources/views/arrendador/dashboard.blade.php)
- JS: [public/js/arrendador/dashboard-charts.js](public/js/arrendador/dashboard-charts.js)
- Controlador: [app/Http/Controllers/Arrendador/DashboardController.php](app/Http/Controllers/Arrendador/DashboardController.php)

### Cómo funciona
1. El controlador calcula los datos del arrendador autenticado.
2. La vista coloca esos valores en atributos `data-*` dentro de `.welcome-section`.
3. El JavaScript lee esos atributos cuando carga la página.
4. Chart.js dibuja dos gráficos tipo donut:
   - `#chartEstados`: propiedades por estado.
   - `#chartIngresos`: ocupación.

### Datos que usa
- `totalPropiedades`
- `propiedadesPorEstado`
- `propiedadesAlquiladas`
- `ingresosEsteMes`

### Qué calcula el controlador
- Propiedades activas.
- Inquilinos activos.
- Ingresos del mes.
- Ingresos totales.
- Propiedades por estado.
- Tasa de ocupación.
- Pagos pendientes.
- Incidencias pendientes.

### Idea clave
No es solo un gráfico visual. Primero se calculan métricas reales en backend y luego se transforman en datos para Chart.js.

---

## Explicacion de los pagos de inquilino

### Dónde se hace
- Controlador principal: [app/Http/Controllers/inquilino/InquilinoPagoController.php](app/Http/Controllers/inquilino/InquilinoPagoController.php)
- También hay resumen desde: [app/Http/Controllers/inquilino/InquilinoController.php](app/Http/Controllers/inquilino/InquilinoController.php)

### Cómo funciona el flujo
1. El inquilino entra a su zona de pagos.
2. El controlador busca sus cuotas y pagos asociados.
3. Filtra por estado, fechas, tipo de pago o referencia cuando aplica.
4. Si el inquilino paga una cuota, la cuota pasa a `pagado`.
5. Se inserta un registro en `tbl_pago`.
6. Si existe factura/documento, se relaciona con el pago.

### Tablas implicadas
- `tbl_alquiler_cuota`
- `tbl_pago`
- `tbl_alquiler`
- `tbl_documento`

### Qué guarda el sistema
- `estado_pago`
- `importe_pago`
- `fecha_confirmacion_pago`
- `referencia_pago`
- `tipo_pago`

### Idea clave
El pago no se queda solo en la vista: cambia el estado de la cuota y deja trazabilidad en `tbl_pago` para que luego el sistema pueda mostrar recibos, facturas o históricos.

---

## Explicacion de las incidencias con correo del admin

### Dónde se hace
- Controlador: [app/Http/Controllers/Admin/IncidenciaController.php](app/Http/Controllers/Admin/IncidenciaController.php)
- Mail usado: `App\Mail\ContactoIncidencia`

### Cómo funciona
1. El admin abre la incidencia.
2. Puede decidir a quién escribir: inquilino, gestor o arrendador.
3. El controlador calcula el email real según el destino.
4. Construye el correo con asunto y mensaje.
5. Envía el mail con `Mail::to($email)->send(...)`.

### Qué destino puede tener
- `inquilino`
- `gestor`
- `arrendador`

### Cómo se resuelve el correo
- Si el destino es inquilino, usa el usuario que reportó la incidencia.
- Si el destino es gestor, usa el usuario asignado.
- Si el destino es arrendador, busca el arrendador de la propiedad.

### Idea clave
La incidencia no solo se gestiona por estados. También puede disparar un correo directo desde admin para coordinar la solución con la persona correcta.

---

## Explicacion de subir contratos y el correo al arrendador

### Dónde se hace
- Controlador: [app/Http/Controllers/Arrendador/ContratoController.php](app/Http/Controllers/Arrendador/ContratoController.php)
- Mail usado: `App\Mail\ContratoSubido`

### Cómo funciona
1. El arrendador sube un PDF desde el formulario.
2. El archivo se guarda en `public/contratos/`.
3. En base de datos se actualiza `url_pdf_contrato`.
4. Se genera una URL completa para abrir o descargar el documento.
5. El sistema intenta notificar por correo al inquilino asociado.

### Ruta real de guardado
- Fichero físico: `public/contratos/<nombre>.pdf`
- Ruta guardada en BD: `/contratos/<nombre>.pdf`

### Qué hace el correo
- Busca el inquilino asociado al contrato.
- Envía el mail `ContratoSubido` con el enlace de descarga.
- Si el PDF no existe localmente, el controlador lo detecta y devuelve error.

### Idea clave
El contrato se guarda en el servidor, se registra la ruta en la BD y después se notifica al usuario correcto para que pueda descargarlo.

---

## Explicacion de las notificaciones automaticas y las que crea el admin

### Notificaciones automaticas

#### Dónde se generan
- Servicio: `App\Services\ActividadService`
- Usado desde varios controladores del proyecto.

#### Ejemplos de disparadores
- Cambio de estado de una propiedad.
- Creación de una solicitud.
- Aprobación de un alquiler.
- Creación de una incidencia.
- Creación de un contrato.

#### Cómo funciona
1. Un controlador realiza una acción importante.
2. Llama a `ActividadService`.
3. El servicio crea una actividad o notificación en la base de datos.
4. El destinatario la ve en su panel o campana de notificaciones.

### Notificaciones creadas por el admin

#### Dónde se crean
- Controlador: [app/Http/Controllers/Admin/ConfiguracionController.php](app/Http/Controllers/Admin/ConfiguracionController.php)

#### Cómo funciona
1. El admin rellena el formulario de notificación.
2. El controlador valida título, mensaje, destino y URL opcional.
3. Llama a `ActividadService->avisoImportante(...)`.
4. Se guarda una notificación manual para el rol o usuario elegido.

#### Campos importantes
- `titulo_notificacion`
- `mensaje_notificacion`
- `url_notificacion`
- destino por rol o usuario

### Idea clave
Hay dos tipos de notificaciones: las automáticas, que se generan por acciones del sistema, y las manuales, que el admin crea explícitamente para comunicar algo importante.

---

## Resumen rapido
- Los gráficos del arrendador salen de datos agregados en backend y se dibujan con Chart.js.
- Los pagos del inquilino cambian el estado de la cuota y dejan registro en `tbl_pago`.
- Las incidencias pueden enviar correos desde admin al inquilino, gestor o arrendador.
- Los contratos se guardan como PDF en `public/contratos/` y luego se notifican por correo.
- Las notificaciones pueden ser automáticas o creadas manualmente por el admin.

---

## Archivos relacionados
- [app/Http/Controllers/Arrendador/DashboardController.php](app/Http/Controllers/Arrendador/DashboardController.php)
- [public/js/arrendador/dashboard-charts.js](public/js/arrendador/dashboard-charts.js)
- [app/Http/Controllers/inquilino/InquilinoPagoController.php](app/Http/Controllers/inquilino/InquilinoPagoController.php)
- [app/Http/Controllers/Admin/IncidenciaController.php](app/Http/Controllers/Admin/IncidenciaController.php)
- [app/Http/Controllers/Arrendador/ContratoController.php](app/Http/Controllers/Arrendador/ContratoController.php)
- [app/Http/Controllers/Admin/ConfiguracionController.php](app/Http/Controllers/Admin/ConfiguracionController.php)
- [app/Services/ActividadService.php](app/Services/ActividadService.php)
