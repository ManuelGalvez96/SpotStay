# Base de datos de SpotStay

Este documento resume cómo está organizada la base de datos del proyecto y cómo se relacionan las tablas principales.

## Convenciones generales

- Las tablas usan el prefijo `tbl_` y nombre en singular.
- La clave primaria suele ser `id_<entidad>`.
- Las claves foráneas usan el sufijo `_fk`.
- No se usan columnas `JSON`; la información se normaliza en tablas relacionadas.
- Los campos de auditoría suelen seguir el patrón `creado_*` y `actualizado_*`.

## Tablas principales

### `tbl_usuario`

Guarda la identidad y credenciales de todos los usuarios.

Campos destacados:

- `id_usuario`: identificador principal.
- `nombre_usuario`, `email_usuario`, `contrasena_usuario`.
- `telefono_usuario`, `dni_usuario`, `fecha_nacimiento_usuario`.
- `avatar_usuario`: ruta del avatar del usuario.
- `stripe_status`, `stripe_account_id`: estado de suscripción y cobros.

Relaciones relevantes:

- Un usuario puede tener varios roles mediante `tbl_rol_usuario`.
- Un usuario puede tener varias suscripciones en `tbl_suscripcion`.
- Un usuario puede tener propiedades, pagos, mensajes, documentos y notificaciones asociadas.

### `tbl_rol`

Catálogo de roles del sistema.

Valores habituales:

- `admin`
- `arrendador`
- `inquilino`
- `gestor`
- `miembro`

### `tbl_rol_usuario`

Tabla pivote entre usuarios y roles.

Campos:

- `id_usuario_fk`
- `id_rol_fk`
- `asignado_rol_usuario`

### `tbl_plan`

Catálogo de planes de suscripción.

Campos destacados:

- `id_plan`
- `nombre_plan`
- `slug_plan`
- `rol_destino`: indica si el plan es para `miembro`, `inquilino` o `arrendador`.
- `precio_plan`
- `max_propiedades_plan`
- `descripcion_plan`
- `activo_plan`

Ejemplos de uso:

- Los planes gratuitos suelen tener precio `0`.
- Los arrendadores solo deben ver planes con `rol_destino = arrendador`.
- Los miembros e inquilinos deben ver planes con `rol_destino = miembro`.

### `tbl_suscripcion`

Registra la suscripción actual o histórica de cada usuario.

Campos destacados:

- `id_usuario_fk`
- `id_plan_fk`
- `plan_suscripcion`
- `max_propiedades_suscripcion`
- `precio_pagado_suscripcion`
- `inicio_suscripcion`
- `fin_suscripcion`
- `estado_suscripcion`

Estados habituales:

- `activa`
- `pendiente_pago`
- `cancelada`
- `expirada`

Flujo de negocio:

- Si un usuario elige un plan de pago, la suscripción puede quedar `pendiente_pago`.
- Si se programa una cancelación, la suscripción queda `cancelada` hasta fin de mes.
- Cuando vence una cancelación programada, el sistema la rebaja al plan gratuito de miembro.

### `tbl_pago`

Registra pagos del sistema.

Usos frecuentes:

- Pagos de suscripción.
- Pagos de alquiler.
- Pagos asociados a incidencias o gastos, según el módulo.

### `tbl_documento`

Guarda documentos generados por el sistema.

Uso habitual:

- Facturas de suscripción.
- Facturas o documentos relacionados con pagos.

### `tbl_propiedad`

Catálogo de propiedades.

Relaciones típicas:

- Un arrendador puede tener varias propiedades.
- Un gestor puede estar asignado a propiedades concretas.

### `tbl_alquiler`

Registra contratos y estados de alquiler.

Relaciones típicas:

- Se vincula con el arrendador, inquilino y, en algunos flujos, con un administrador aprobador.

### `tbl_gasto`, `tbl_gasto_cuota`, `tbl_gasto_cuota_detalle`

Modelo normalizado para gastos mensuales y su reparto.

Regla importante:

- Un gasto no se almacena en JSON.
- El gasto base vive en `tbl_gasto`.
- Las cuotas mensuales viven en `tbl_gasto_cuota`.
- El reparto por usuario o concepto vive en `tbl_gasto_cuota_detalle`.

## Flujos importantes

### Alta de suscripción

1. El usuario elige un plan.
2. Se crea o actualiza `tbl_suscripcion`.
3. Si el plan es de pago, el estado pasa a `pendiente_pago`.
4. Si el plan es gratuito, el estado pasa a `activa`.

### Cancelación programada

1. El usuario pulsa cancelar.
2. La suscripción se marca como `cancelada`.
3. Se fija `fin_suscripcion` al final del mes.
4. Cuando la fecha vence, el sistema la devuelve al plan gratuito.

### Avatares

- El avatar del usuario se guarda como ruta en `tbl_usuario.avatar_usuario`.
- La ruta física actual es `public/img/avatares/{id_usuario}`.

## Archivos relacionados

- [app/Http/Controllers/Miembro/PerfilController.php](app/Http/Controllers/Miembro/PerfilController.php)
- [app/Http/Controllers/Miembro/MiembroSuscripcionController.php](app/Http/Controllers/Miembro/MiembroSuscripcionController.php)
- [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)
- [database/migrations/2026_04_24_100001_create_tbl_plan.php](database/migrations/2026_04_24_100001_create_tbl_plan.php)
- [database/seeders/PlanSeeder.php](database/seeders/PlanSeeder.php)
