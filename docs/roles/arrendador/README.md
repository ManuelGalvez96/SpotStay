# Arrendador — Documentación funcional

Combina la guía sobre gráficos del dashboard, generación/almacenamiento de PDFs y el modal de selección de gestor para `arrendador`.

---

## Resumen
- KPIs: propiedades, ingresos mensuales, ocupación, incidencias.
- Gráficos con Chart.js (datos pre-agrupados por controlador).
- PDFs generados en backend y almacenados localmente (ruta `storage` o `public`).
- Modal de propiedades para asignar/validar gestor y permisos.

---

## Gráficos (técnico)
(Extraído de `docs/Arrendador_Graficos.md`)

- Ruta: `GET /arrendador/graficos` → `App\Http\Controllers\Arrendador\GraficoController@index`.
- Endpoints AJAX: `getIncomeChartData`, `getOccupancyChartData`, `getIncidentsChartData`.
- Agrupaciones: `DB::raw('MONTH(fecha_pago) as mes')`, `YEAR(fecha_pago) as ano'` y sumas de `monto_pagado`.
- Vistas: `resources/views/arrendador/graficos.blade.php` con `canvas` para `incomeChart`, `occupancyChart`, `incidentsChart`, `propertyIncomeChart`.
- Inclusion: `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>` y `json_encode` de etiquetas/valores desde el controlador.

---

## PDFs (técnico)
(Extraído de `docs/Arrendador_PDFs.md`)

- PDFs generados por backend (ej. contratos, facturas) y guardados en `storage/app/public/...` o `public/facturas/` según configuración.
- Rutas de descarga retornan `Storage::disk('public')->download($path)` o `response()->file()` dependiendo del flujo.
- Mostrar feedback en UI si el archivo todavía no existe; permitir re-generar desde backend.

---

## Modal de gestor (técnico)
(Extraído de `docs/Arrendador_Gestor_Modal.md`)

- Modal con selector de gestor y validación de código de gestor.
- Validación server-side en `Admin\PropiedadController@validarCodigoGestor` o flujo análogo.
- Endpoints: `POST /arrendador/propiedades/{id}/gestor/permisos` para asignar permisos; `GET /arrendador/propiedades/{id}/gestor/permisos` para obtener estado.

---

## Responsive
- Dashboard apila gráficos en móvil; modal ocupa ancho casi completo.

---

## Ubicación de archivos
- Controladores: `app/Http/Controllers/Arrendador/GraficoController.php`
- Vistas: `resources/views/arrendador/graficos.blade.php`
- Rutas: prefijadas con `arrendador`, middleware `auth`/`role:arrendador`.

---

(Se ha movido aquí el contenido original de `README_ARRENDADOR.md` y los documentos `Arrendador_Graficos.md`, `Arrendador_PDFs.md`, `Arrendador_Gestor_Modal.md`.)
