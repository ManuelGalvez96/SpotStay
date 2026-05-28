# Gestor — Documentación funcional

Combina la guía sobre permisos, flujo de incidentes y vistas relevantes para el rol `gestor`.

---

## Resumen
- Gestión granular de permisos por propiedad (`tbl_propiedad_permisos`).
- Vistas: dashboard, listado de propiedades, detalle propiedad, incidencias, gastos.
- Los controladores aplican filtros por permisos y legacy `id_gestor_fk`.

---

## Permisos (técnico)
(Extraído de `docs/Gestor_Permisos.md`)

- Tabla `tbl_propiedad_permisos` con banderas: `incidencias`, `gastos`, `chat`, `editar_propiedad`.
- Patrón de comprobación: método `getPermisosPropiedad($gestorId,$propiedadId)` y `WHERE EXISTS` para consultas.
- Endpoints para asignar/desasignar y actualizar permisos desde `arrendador` o `admin`.

---

## Vistas y controladores clave
- `app/Http/Controllers/Gestor/PropiedadController.php` — lista, show, gastos CRUD.
- `app/Http/Controllers/Gestor/IncidenciaController.php` — index/show y acciones sobre incidencias permitidas.
- `resources/views/gestor/*` — vistas con gating de acciones según `$permisos`.

---

## Security y Scoping
- Todas las queries filtran por permisos (o `id_gestor_fk`) para evitar exfiltración de datos.
- Mensajes de error estandarizados en `redirigirSinPermiso()`.

---

## Ubicación de archivos
- Controllers: `app/Http/Controllers/Gestor/`.
- Views: `resources/views/gestor/`.

(Se ha movido aquí el contenido original de `README_GESTOR.md` y el documento `Gestor_Permisos.md`.)
