# SpotStay - Análisis Completo de Vistas y Lógica Cliente

## Índice
1. [Panel de Arrendador](#1-panel-de-arrendador)
2. [Panel de Administrador](#2-panel-de-administrador)
3. [Panel de Gestor](#3-panel-de-gestor)
4. [Panel de Inquilino](#4-panel-de-inquilino)
5. [Panel de Miembro](#5-panel-de-miembro)
6. [Layouts y Estructura General](#6-layouts-y-estructura-general)
7. [Autenticación y Registro](#7-autenticación-y-registro)
8. [Scripts Compartidos y Mascotas](#8-scripts-compartidos-y-mascotas)

---

## 1. Panel de Arrendador

### Vistas
#### `resources/views/arrendador/dashboard.blade.php`
- **Variables de Controlador**: `$totalPropiedades`, `$alquileresActivos`, `$mediaOcupacion`, `$ingresosMes`, `$ultimosAlquileres`.
- **Lógica Inline**: Calcula el porcentaje de ocupación visualmente (`width: {{ $mediaOcupacion }}%`).

#### `resources/views/arrendador/propiedades.blade.php`
- **Variables de Controlador**: `$propiedades` (colección de propiedades del arrendador).
- **Formularios**: 
  - Crear/Editar propiedad (`id="form-propiedad"`, `id="form-editar-propiedad"`).
  - Inputs: `nombre`, `descripcion`, `direccion`, `ciudad`, `provincia`, `codigo_postal`, `precio_noche`, `tipo_propiedad`, `imagen` (file), `checkboxes` de equipamiento.
- **Scripts Inline**: Inicializa `nuevoEquipamiento` array desde `@json($equipamientos)`. Llama a `window.propiedades.init()`.
- **JS Asociado**: `public/js/arrendador/propiedades.js`.

#### `resources/views/arrendador/alquileres.blade.php`
- **Variables de Controlador**: `$alquileres`, `$totales` (`['pendientes' => N, 'activos' => N, 'completados' => N]`).
- **Formularios**: 
  - Modal edición (`id="form-editar-alquiler"`).
  - Inputs: `estado`, `comentarios`, `fecha_fin`.
- **JS Asociado**: `public/js/arrendador/alquileres.js`.

#### `resources/views/arrendador/incidencias.blade.php`
- **Variables de Controlador**: `$arrendador`, `$incidencias`.
- **Formularios**: 
  - Filtros: `id="form-filtros"`, `id="filtro-estado"`.
- **Lógica Inline**: `onclick="document.getElementById('form-filtros').submit()"` para cambio de estado.
- **JS Asociado**: `public/js/arrendador/incidencias.js`.

#### `resources/views/arrendador/facturacion.blade.php`
- **Variables de Controlador**: `$facturas`, `$totales` (`['total_ingresos' => N, 'pendiente_cobro' => N]`).
- **Formularios**: Ninguno, solo visualización y botón de impresión.

#### `resources/views/arrendador/perfil.blade.php`
- **Variables de Controlador**: `$usuario`, `$rolesDisponibles`.
- **Formularios**:
  - Datos básicos (`action="{{ route('arrendador.perfil.actualizar') }}"`).
  - Inputs: `nombre`, `email`, `password`, `password_confirmation`, `telefono`, `biografia`, `avatar` (file).
  - Cambio de rol (`id="form-cambiar-rol"`).
  - Eliminación de cuenta (`id="form-eliminar-cuenta"`).
- **Validación Cliente**: JS en `public/js/arrendador/perfil.js` con `oninput` para `telefono` (regex `/^[0-9]{0,9}$/`) y coincidencia de contraseñas.
- **Validación Servidor**: `$request->validate()` en `ArrendadorController::perfilActualizar()`.

---

## 2. Panel de Administrador

### Vistas
#### `resources/views/admin/dashboard.blade.php`
- **Variables de Controlador**: `$totalUsuarios`, `$totalPropiedades`, `$totalAlquileres`, `$totalIngresos`, `$usuariosActivos`, `$chartData` (`['labels', 'data']` para Chart.js).
- **JS Asociado**: `public/js/admin/dashboard.js` (renderiza gráfico de ingresos).

#### `resources/views/admin/usuarios.blade.php`
- **Variables de Controlador**: `$usuarios`, `$totales`.
- **Formularios**: 
  - Modal creación (`id="form-crear-usuario"`).
  - Modal edición (`id="form-editar-usuario"`).
  - Inputs: `nombre`, `email`, `password`, `rol` (select dinámico).
- **JS Asociado**: `public/js/admin/usuarios.js` (manejo de modales, envíos `fetch()`, borrado con `swal-oso`).

#### `resources/views/admin/propiedades.blade.php`
- **Variables de Controlador**: `$propiedades`, `$totales`.
- **Formularios**: Filtros y modales de edición/borrado.
- **JS Asociado**: `public/js/admin/propiedades.js`.

#### `resources/views/admin/incidencias.blade.php`
- **Variables de Controlador**: `$incidencias`, `$totales`.
- **JS Asociado**: `public/js/admin/incidencias.js` (Kanban drag/drop para cambio de estado: `ondragstart`, `ondragover`, `ondrop`).

#### `resources/views/admin/alquileres.blade.php`
- **Variables de Controlador**: `$alquileres`, `$totales`.
- **JS Asociado**: `public/js/admin/alquileres.js`.

#### `resources/views/admin/solicitudes.blade.php`
- **Variables de Controlador**: `$solicitudes`, `$totales`, `$tipos`, `$estados`, `$estadisticas`.
- **Formularios**: 
  - Filtros por estado/tipo (`id="form-filtros"`).
  - Modal de resolución (`id="form-resolver-solicitud"`).
- **Validación Inline**: `@if($solicitud->estado === 'pendiente')` para habilitar botones.
- **JS Asociado**: `public/js/admin/solicitudes.js`.

#### `resources/views/admin/suscripciones.blade.php`
- **Variables de Controlador**: `$planes`, `$suscripciones`, `$totales`.
- **Formularios**: Modal de creación/edición de planes (`id="form-plan"`).
- **JS Asociado**: `public/js/admin/suscripciones.js`.

#### `resources/views/admin/facturacion.blade.php`
- **Variables de Controlador**: `$facturas`, `$totales`.

#### `resources/views/admin/perfil.blade.php`
- **Variables de Controlador**: `$usuario`.
- **Formularios**: Igual que perfil de arrendador, con validación específica en `public/js/admin/perfil.js`.

#### `resources/views/admin/estadisticas.blade.php`
- **Variables de Controlador**: `$ingresosMensuales`, `$usuariosPorRol`, `$propiedadesPorTipo`.
- **Lógica Inline**: Cálculo de porcentajes para barras de progreso.

---

## 3. Panel de Gestor

### Vistas
#### `resources/views/gestor/dashboard.blade.php`
- **Variables de Controlador**: `$totalIncidencias`, `$incidenciasAbiertas`, `$tasaResolucion`, `$tiempoMedio`.
- **Formularios**: Filtros rápidos en el dashboard.

#### `resources/views/gestor/incidencias.blade.php`
- **Variables de Controlador**: `$incidencias`, `$totales`, `$estadisticas`.
- **Formularios**: 
  - Filtros (`id="form-filtros"`).
  - Modal de asignación (`id="form-asignar"`).
- **JS Asociado**: `public/js/gestor/incidencias.js`.

#### `resources/views/gestor/perfil.blade.php`
- **Variables de Controlador**: `$usuario`.
- **JS Asociado**: `public/js/gestor/perfil.js`.

#### `resources/views/gestor/equipos.blade.php`
- **Variables de Controlador**: `$equipos`, `$miembros`.
- **Formularios**: Modal de creación de equipo (`id="form-crear-equipo"`).
- **JS Asociado**: `public/js/gestor/equipos.js` (lógica para añadir/eliminar miembros del equipo dinámicamente).

---

## 4. Panel de Inquilino

### Vistas
#### `resources/views/inquilino/dashboard.blade.php`
- **Variables de Controlador**: `$alquileresActivos`, `$proximosCheckouts`, `$mensajesNoLeidos`.

#### `resources/views/inquilino/alquileres.blade.php`
- **Variables de Controlador**: `$alquileres`, `$totales`.
- **Formularios**: Modal de comentarios.
- **JS Asociado**: `public/js/inquilino/alquileres.js`.

#### `resources/views/inquilino/incidencias.blade.php`
- **Variables de Controlador**: `$incidencias`, `$totales`.
- **Formularios**: Modal de creación de incidencia (`id="form-crear-incidencia"`).
- **Inputs**: `tipo`, `descripcion`, `prioridad`, `ubicacion`.
- **JS Asociado**: `public/js/inquilino/incidencias.js`.

#### `resources/views/inquilino/perfil.blade.php`
- **Variables de Controlador**: `$usuario`.
- **JS Asociado**: `public/js/inquilino/perfil.js`.

#### `resources/views/inquilino/favoritos.blade.php`
- **Variables de Controlador**: `$favoritos`.
- **Lógica Inline**: Botones de "Quitar de favoritos" con `POST` directo.

---

## 5. Panel de Miembro

### Vistas
#### `resources/views/miembro/dashboard.blade.php`
- **Variables de Controlador**: `$tareasPendientes`, `$proximasRevisiones`.

#### `resources/views/miembro/mapa.blade.php`
- **Variables de Controlador**: Ninguna significativa (se carga vía AJAX).
- **Lógica Inline**: Definición de `div#map` con estilos de altura `calc(100vh - 200px)`.
- **JS Asociado**: `public/js/miembro/mapa.js` (Leaflet, fetch a `/miembro/mapa/propiedades`, marcadores con popups de estado).

#### `resources/views/miembro/mensajes.blade.php`
- **Variables de Controlador**: `$conversaciones`, `$destinatarios`, `$mensajes`.
- **Lógica Inline**: `id="chat-container"`, `id="mensajes-list"`.
- **JS Asociado**: `public/js/miembro/mensajes.js` (Polling con `setInterval(fetch('/miembro/mensajes/${id}/nuevos'), 3000)` para nuevos mensajes).

#### `resources/views/miembro/perfil.blade.php`
- **Variables de Controlador**: `$usuario`.
- **JS Asociado**: `public/js/miembro/perfil.js`.

#### `resources/views/miembro/tareas.blade.php`
- **Variables de Controlador**: `$tareas`, `$estadisticas`.
- **Formularios**: Filtros por estado.

---

## 6. Layouts y Estructura General

### `resources/views/layouts/app.blade.php`
- **Variables de Controlador**: `$usuario` (auth), `$unreadNotifications` (count), `$menuItems` (basado en rol).
- **Lógica Inline**: 
  - `@auth` / `@guest` checks.
  - `@yield('content')`.
  - Scripts globales de Bootstrap y SweetAlert2.
- **Navegación**: Menús dinámicos por rol (`Admin`, `Arrendador`, `Gestor`, `Inquilino`, `Miembro`).

### `resources/views/layouts/guest.blade.php`
- Uso para login y registro. Incluye fondo y diseño centrado.

---

## 7. Autenticación y Registro

### `resources/views/auth/login.blade.php`
- **Formularios**: `action="{{ route('login') }}"`, `id="form-login"`.
- **Inputs**: `email`, `password`, `remember`.
- **Validación Cliente**: `public/js/login.js`:
  - `oninput` en email (regex email).
  - `onclick` en toggle password.
  - `onsubmit` chequeo de campos vacíos antes de envío.
- **Validación Servidor**: `$request->validate(['email' => 'required|email', 'password' => 'required'])` en `AuthenticatedSessionController`.

### `resources/views/auth/registro.blade.php`
- **Formularios**: `action="{{ route('register') }}"`, `id="form-registro"`.
- **Inputs**: `nombre`, `email`, `password`, `password_confirmation`, `rol` (select).
- **Validación Cliente**: `public/js/registro.js`:
  - `oninput` en nombre (solo letras/espacios).
  - `oninput` en email (regex email).
  - `oninput` en password (fuerza: regex para mayúscula, número, 8+ caracteres).
  - `oninput` en password_confirmation (match check).
  - `fetch()` para verificar disponibilidad de email en tiempo real (`GET /registro/verificar-email`).
- **Validación Servidor**: `$request->validate()` en `RegisteredUserController`.

---

## 8. Scripts Compartidos y Mascotas

### `public/js/shared/swal-oso.js`
- **Lógica**: Define `window.SwalOso` con funciones `success`, `error`, `info`.
- **Customización**: Genera SVG de "Oso" (cabeza redonda, orejas, ojos) dinámicamente usando `createElementNS('http://www.w3.org/2000/svg', 'svg')`.

### `public/js/arrendador/layout.js`, `public/js/admin/layout.js`, etc.
- **Lógica**: Manejo del sidebar (toggle, colapsar), inicialización de tooltips de Bootstrap, y manejo de notificaciones dropdown.
- **Event Binding**: Usa `document.getElementById('sidebar-toggle').onclick = ...`.

### `resources/js/bootstrap.js`
- **Lógica**: Configuración global de `axios` para incluir `X-CSRF-TOKEN` desde `<meta name="csrf-token">`.
- **Imports**: Importa `lodash` y `axios`.
