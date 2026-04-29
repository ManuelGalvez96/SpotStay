# README_CAMBIOS - SpotStay

## Estado final
- Migraciones: completadas.
- Seeders: completados.
- Modelos: actualizados.
- Controllers, vistas Blade y JS: adaptados a normalizacion sin JSON en BD.
- Validacion final: `php artisan migrate:fresh --seed` ejecutado correctamente (Exit Code 0).

## Objetivo del cambio
Se completo la normalizacion del esquema para eliminar campos JSON y mover la informacion a columnas estructuradas, manteniendo compatibilidad funcional de paneles Admin/Arrendador/Gestor/Inquilino y seeders.

## Cambios principales realizados

### 1) Base de datos (migraciones)
- Se actualizaron migraciones base `2024_04_14_*` para incorporar nuevos campos y eliminar JSON.
- Se crearon nuevas tablas estructurales:
  - `tbl_plan`
  - `tbl_historial_propiedad`
  - `tbl_valoracion`
  - `tbl_favorito`
  - `tbl_visita`
- Se corrigieron dependencias de FKs para evitar fallos por orden de ejecucion en `migrate:fresh`:
  - `tbl_pago` ya no crea FKs a tablas de gasto antes de tiempo.
  - `tbl_suscripcion` no crea FK a `tbl_plan` en la migracion base; se agrega cuando `tbl_plan` existe.

### 2) Modelos Eloquent
Se actualizaron `fillable/casts/relaciones` para reflejar el nuevo esquema:
- [app/Models/Usuario.php](app/Models/Usuario.php)
- [app/Models/Propiedad.php](app/Models/Propiedad.php)
- [app/Models/Notificacion.php](app/Models/Notificacion.php)
- [app/Models/SolicitudArrendador.php](app/Models/SolicitudArrendador.php)
- [app/Models/Suscripcion.php](app/Models/Suscripcion.php)
- [app/Models/Plan.php](app/Models/Plan.php)
- [app/Models/HistorialPropiedad.php](app/Models/HistorialPropiedad.php)

Puntos clave en suscripcion:
- `plan_suscripcion` sigue presente y obligatorio por compatibilidad funcional.
- Se anadio soporte completo para `id_plan_fk` y `precio_pagado_suscripcion`.
- Relacion `plan()` operativa.

### 3) Seeders
Se actualizaron para poblar datos compatibles con el nuevo esquema:
- [database/seeders/UsuarioSeeder.php](database/seeders/UsuarioSeeder.php)
- [database/seeders/SuscripcionSeeder.php](database/seeders/SuscripcionSeeder.php)
- [database/seeders/NotificacionSeeder.php](database/seeders/NotificacionSeeder.php)
- [database/seeders/SolicitudArrendadorSeeder.php](database/seeders/SolicitudArrendadorSeeder.php)
- [database/seeders/PropiedadSeeder.php](database/seeders/PropiedadSeeder.php)
- [database/seeders/ArrendadorDemoSeeder.php](database/seeders/ArrendadorDemoSeeder.php)
- [database/seeders/PlanSeeder.php](database/seeders/PlanSeeder.php)
- [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)

Correcciones adicionales relevantes:
- Se elimino cualquier insercion de `gastos_propiedad`.
- Se corrigio inconsistencia de ciudad en `PropiedadSeeder` (`Malaga`) que provocaba `Undefined array key`.
- Se corrigio mapeo de planes en `SuscripcionSeeder` para evitar inserts invalidos.

### 4) Controllers (normalizacion JSON)
Se eliminaron referencias a campos JSON antiguos y se adaptaron respuestas/filtros:
- [app/Http/Controllers/Admin/SolicitudController.php](app/Http/Controllers/Admin/SolicitudController.php)
- [app/Http/Controllers/Admin/DashboardController.php](app/Http/Controllers/Admin/DashboardController.php)
- [app/Http/Controllers/Arrendador/PropiedadController.php](app/Http/Controllers/Arrendador/PropiedadController.php)
- [app/Http/Controllers/Arrendador/PrecioGastoController.php](app/Http/Controllers/Arrendador/PrecioGastoController.php)
- [app/Http/Controllers/Gestor/PropiedadController.php](app/Http/Controllers/Gestor/PropiedadController.php)

### 5) Vistas Blade
Se eliminaron `json_decode(...)` y se pasaron a campos normalizados:
- [resources/views/admin/solicitudes.blade.php](resources/views/admin/solicitudes.blade.php)
- [resources/views/admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php)
- [resources/views/gestor/dashboard.blade.php](resources/views/gestor/dashboard.blade.php)

### 6) JavaScript
Se eliminaron parseos de JSON antiguo y se ajustaron mapeos en modal/listados:
- [public/js/admin/solicitudes.js](public/js/admin/solicitudes.js)
- [public/js/admin/dashboard.js](public/js/admin/dashboard.js)

## Errores relevantes corregidos durante la ejecucion

### Error 1
`Failed to open the referenced table 'tbl_gasto_cuota_detalle'` al crear FK en `tbl_pago`.
- Causa: FK creada antes de existir tabla referenciada.
- Solucion: mover/posponer FKs a migraciones posteriores con tablas ya creadas.

### Error 2
`Field 'plan_suscripcion' doesn't have a default value` en seeding de suscripciones.
- Causa: insercion sin `plan_suscripcion` y modelo sin permitir ese atributo.
- Solucion: incluir `plan_suscripcion` en seeder y en `fillable` de `Suscripcion`.

### Error 3
`Undefined array key "M├ílaga"` en `PropiedadSeeder`.
- Causa: claves de ciudad inconsistentes por codificacion.
- Solucion: unificar clave a `Malaga` en arrays de ciudad/lat/lng/cp.

## Verificacion final
Comando validado:
- `php artisan migrate:fresh --seed`

Resultado:
- Todas las migraciones en estado `DONE`.
- Todos los seeders en estado `DONE`.
- Sin excepciones SQL ni errores de ejecucion.

## Nota de compatibilidad
Se mantuvo `plan_suscripcion` por compatibilidad con consultas y panel de suscripciones existentes en Admin, a la vez que se habilito el enlace estructurado por `id_plan_fk`.

## Archivos de referencia historica
- [RESUMEN_CAMBIOS_FASE_1_3.md](RESUMEN_CAMBIOS_FASE_1_3.md)
- [RESUMEN_CAMBIOS_SOLICITUDES.md](RESUMEN_CAMBIOS_SOLICITUDES.md)
