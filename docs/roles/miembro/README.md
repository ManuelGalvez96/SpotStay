# Miembro — Documentación funcional

Combina la guía funcional y técnica sobre mapa, chat y anuncios para `miembro`.

---

## Resumen
- Mapa interactivo con Leaflet, marcadores y clustering.
- Chat: conversaciones, envío de mensajes, carga por AJAX.
- Anuncios: listado/ tarjetas, navegación y acciones.

---

## Mapa (técnico)
(Extraído de `docs/Miembro_Mapa.md`)

- Rutas: `GET /miembro/mapa` y `GET /miembro/mapa/propiedades` → `Miembro\MapaController`.
- La vista `resources/views/miembro/mapa.blade.php` contiene `#map`, `#filterForm`, `#propertyList`.
- JS: Leaflet + MarkerCluster; `loadProperties()` hace `fetch('{{ route("miembro.mapa.propiedades") }}?'+params)` y pinta marcadores, popups y lista lateral.
- Filtros: ciudad, precio_min, precio_max, habitaciones, tipo.

---

## Chat (técnico)
(Extraído de `docs/Miembro_Chat.md`)

- Listado de conversaciones y vista de conversación activa.
- Endpoints AJAX para enviar/recibir mensajes, marcar leídos y actualizar actividad.
- Vistas parciales en `resources/views/miembro/chat/*` y JS que maneja sockets o polling según implementación.

---

## Anuncios (técnico)
(Extraído de `docs/Miembro_Anuncios.md`)

- Listado de anuncios o contenidos del miembro, tarjetas con imagen, título, extracto, acciones.
- Filtrado y paginación en backend; vista prioriza lectura y navegación.

---

## Responsive
- Sidebar y mapa en desktop; filtros colapsables en móvil; chat adaptable y tarjetas verticales.

---

## Ubicación de archivos
- Controladores: `app/Http/Controllers/Miembro/MapaController.php`, `.../ChatController.php`.
- Vistas: `resources/views/miembro/mapa.blade.php`, `resources/views/miembro/chat/`, `resources/views/miembro/anuncios.blade.php`.

(Se ha movido aquí el contenido original de `README_MIEMBRO.md` y los documentos `Miembro_Mapa.md`, `Miembro_Chat.md`, `Miembro_Anuncios.md`.)
