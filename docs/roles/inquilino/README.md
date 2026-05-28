# Inquilino — Documentación funcional

Esta documentación combina la guía funcional y técnica relacionada con pagos/facturas y edición de perfil del rol `inquilino`.

---

## Resumen (raíces)
- Historial de cuotas, filtros por mes/año/estado, pago de cuotas pendientes, KPIs.
- Edición de perfil: avatar, datos personales, cambio de contraseña.

---

## Pagos (Detalles técnicos)
(Extraído de `docs/Inquilino_Pagos.md`)

- Ruta: `GET /inquilino/pagos` → `App\Http\Controllers\Inquilino\PagoController@index`
- Flujo: `index()` obtiene cuotas filtradas por `mes`, `ano`, `estado` y devuelve `view('inquilino.pagos', compact('cuotas','meses','anos','filtros'))`.

Vista clave: `resources/views/inquilino/pagos.blade.php` — Estructura:
- Header, tarjetas KPI, formulario de filtros (mes, año, estado), tabla con columnas: # Cuota, Período, Concepto, Monto, Fecha Pago, Estado, Acciones.
- Botón `Pagar` con clase `.btn-pagar` y atributos `data-id` y `data-monto`.
- Paginación: `{{ $cuotas->appends(request()->query())->links() }}`.

JS relevante (resumen):
- Handler `.btn-pagar` que construye `FormData` y hace `fetch('{{ route("inquilino.pagos.pagar") }}', { method:'POST', body: formData })`, maneja respuesta JSON y recarga.
- `filterForm` con `select.onchange` para auto-enviar filtros.

Tablas de BD relevantes: `tbl_alquiler_cuota`, `tbl_pago`, `tbl_alquiler`.

---

## Perfil (Detalles técnicos)
(Extraído de `docs/Inquilino_Perfil.md`)

- Rutas: `GET /inquilino/perfil`, `POST /inquilino/perfil/actualizar`, `POST /inquilino/perfil/avatar`, `POST /inquilino/perfil/password`.
- Controlador: `App\Http\Controllers\Inquilino\PerfilController` — métodos: `index()`, `actualizar()`, `avatar()`, `password()`.

Vista: `resources/views/inquilino/perfil.blade.php` — Estructura:
- Card de avatar con `#avatarPreview`, input `#avatarInput`, formulario `#avatarForm`.
- Formulario `#profileForm` para campos `nombre`, `email`, `telefono`, `direccion`.
- Sección para cambio de contraseña (`#passwordForm`).

Validaciones destacadas:
- Email único: `unique:tbl_usuario,email,$userId,id_usuario`.
- Avatar: `mimes:jpg,jpeg,png,webp|max:2048`.
- Password: `min:8|confirmed|different:current_password` y verificación con `Hash::check()`.

JS: preview de avatar con `FileReader` y `fetch` para subir avatar; formularios usan `fetch` o submit tradicional.

---

## Responsive y UI
- Desktop: KPI + tabla completa y formulario en dos columnas.
- Móvil: tabla scrollable (`table-responsive`), formularios apilados y botones `btn-sm`.

---

## Ubicación de archivos relevantes
- Controladores: `app/Http/Controllers/Inquilino/PagoController.php`, `app/Http/Controllers/Inquilino/PerfilController.php`
- Vistas: `resources/views/inquilino/pagos.blade.php`, `resources/views/inquilino/perfil.blade.php`
- Rutas: definidas en `routes/web.php` con prefijo `inquilino` y middleware `auth`/`role:inquilino`.

---

(Se ha movido aquí el contenido original de `README_INQUILINO.md` y los documentos `Inquilino_Pagos.md`, `Inquilino_Perfil.md` para centralizar la referencia.)
