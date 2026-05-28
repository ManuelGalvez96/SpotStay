# 📚 INDICE CENTRAL: README VISTAS ADMIN

Este documento centraliza **todos los READMEs de las vistas del panel admin** de SpotStay. Cada vista tiene su documentación detallada con:
- Qué datos muestra
- Qué tablas consulta
- Qué filtros tiene
- Qué botones/acciones tiene
- Flujo técnico completo (controller → vista)

---

## 🗂️ VISTAS DISPONIBLES

### 📊 1. [Dashboard](README_ADMIN_DASHBOARD.md)
**Ruta:** `GET /admin/dashboard`  
**Controlador:** `DashboardController::index()`  
**Propósito:** Página inicial con KPIs y resumen

**Datos:** Total usuarios, propiedades activas, alquileres pendientes, últimos alquileres, usuarios por rol, actividad reciente

**Tablas:** `tbl_usuario`, `tbl_propiedad`, `tbl_alquiler`, `tbl_notificacion`, `tbl_rol`

**Botones:** Aprobar/Rechazar alquiler

---

### 👥 2. [Usuarios](README_ADMIN_USUARIOS.md)
**Ruta:** `GET /admin/usuarios`  
**Controlador:** `UsuarioController::index()`  
**Propósito:** Gestionar todos los usuarios del sistema

**Datos:** Listado de usuarios con roles, email, teléfono, ciudad, estado

**Tablas:** `tbl_usuario`, `tbl_rol_usuario`, `tbl_rol`

**Filtros:** Por rol, búsqueda (nombre/email), estado

**Botones:** Crear, Editar, Cambiar rol, Eliminar

**Paginación:** 15 usuarios por página

---

### 📋 3. [Solicitudes](README_ADMIN_SOLICITUDES.md)
**Ruta:** `GET /admin/solicitudes`  
**Controlador:** `SolicitudController::index()`  
**Propósito:** Revisar y aprobar/rechazar solicitudes de arrendadores y gestores

**Datos:** Solicitudes combinadas (arrendador + gestor), estado, usuario solicitante

**Tablas:** `tbl_solicitud_arrendador`, `tbl_solicitud_gestor`, `tbl_usuario`, `tbl_rol_usuario`

**Filtros:** Por estado (pendiente/aprobada/rechazada), tipo, rango de fecha, búsqueda usuario

**Botones:** Aprobar (crea rol + perfil + códigos), Rechazar (con motivo)

**Transacción en Aprobar:** Sí (5 operaciones conjuntas)

**Paginación:** 7 solicitudes por página

---

### 🚨 4. [Incidencias](README_ADMIN_INCIDENCIAS.md)
**Ruta:** `GET /admin/incidencias`  
**Controlador:** `IncidenciaController::index()`  
**Propósito:** Gestionar incidencias reportadas (Kanban por 5 estados)

**Datos:** Incidencias por estado (abierta → resuelta), propiedad, usuario reportante, gestor asignado

**Tablas:** `tbl_incidencia`, `tbl_propiedad`, `tbl_usuario` (3 roles), `tbl_categoria`, `tbl_historial_incidencia`

**Estados:** Abierta | Esperando Decisión | Esperando Pago | Solucionada | Resuelta

**Botones:** Responder (envía email), Cambiar estado, Crear presupuesto

**Vista:** Kanban (5 columnas por estado)

**Marca inactivas:** > 14 días sin cambios

---

### 🏠 5. [Propiedades (Listado)](README_ADMIN_PROPIEDADES.md) ✅
**Ruta:** `GET /admin/propiedades`  
**Controlador:** `PropiedadController::index()`  
**Propósito:** Ver todas las propiedades registradas

**Datos:** Listado propiedades, arrendador, ubicación, estado, precio, alquileres activos

**Tablas:** `tbl_propiedad`, `tbl_usuario`, `tbl_alquiler`, `tbl_foto`

**Filtros:** Por arrendador, ciudad, estado, rango de precio, búsqueda título

**Paginación:** 12 propiedades por página

---

### 🏆 6. [Alquileres (Listado)](README_ADMIN_ALQUILERES.md) ✅
**Ruta:** `GET /admin/alquileres`  
**Controlador:** `AlquilerController::index()`  
**Propósito:** Ver alquileres activos, pendientes, finalizados

**Datos:** Listado alquileres, propiedad, inquilino, arrendador, fechas, estado, cuotas pendientes

**Tablas:** `tbl_alquiler`, `tbl_propiedad`, `tbl_usuario` (inquilino + arrendador), `tbl_alquiler_cuota`

**Filtros:** Por estado, arrendador, búsqueda propiedad

**Paginación:** 10 alquileres por página

---

### 🎫 7. [Suscripciones](README_ADMIN_SUSCRIPCIONES.md) ✅
**Ruta:** `GET /admin/suscripciones`  
**Controlador:** `SuscripcionController::index()`  
**Propósito:** Ver suscripciones de usuarios a planes

**Datos:** Listado suscripciones con KPIs (ingresos, próximas a expirar), estado

**Tablas:** `tbl_suscripcion`, `tbl_plan`, `tbl_usuario`, `tbl_pago_suscripcion`

**Filtros:** Por estado, plan, búsqueda usuario

**KPIs:** Total activas, próximas expirar, ingresos mes, ingresos históricos

---

### 📊 8. [Planes](README_ADMIN_PLANES.md) ✅
**Ruta:** `GET /admin/planes`  
**Controlador:** `PlanController::index()`  
**Propósito:** CRUD de planes de suscripción

**Datos:** Listado planes con precio, duración, características, suscriptores, ingresos

**Tablas:** `tbl_plan`, `tbl_caracteristica_plan`, `tbl_suscripcion`, `tbl_pago_suscripcion`

**Botones:** Crear, Editar, Activar/Desactivar, Eliminar (solo sin suscriptores)

---

### ⚙️ 9. [Configuración](README_ADMIN_CONFIGURACION.md) ✅
**Ruta:** `GET /admin/configuracion`  
**Controlador:** `ConfiguracionController::index()`  
**Propósito:** Configuraciones del sistema (notificaciones, mails, app, servidor)

**Datos:** Variables de entorno (.env), info servidor, logs recientes

**Secciones:** App, Email (SMTP), Notificaciones, Servidor, Logs

**Editable:** APP_NAME, APP_URL, APP_TIMEZONE, MAIL_*, NOTIFICATIONS_*

---

### 📚 10. [Asesoría (Categorías)](README_ADMIN_ASESORIA.md) ✅
**Ruta:** `GET /admin/asesoria`  
**Controlador:** `AsesoriaController::index()`  
**Propósito:** Gestionar categorías de artículos de asesoría

**Datos:** Listado categorías (Fiscalidad, Mantenimiento, etc) con contador de artículos

**Tablas:** `tbl_categoria_asesoria`, `tbl_articulo_asesoria`

**Botones:** Crear, Editar, Eliminar (solo sin artículos)

---

### ✍️ 11. [Asesoría (Artículos)](README_ADMIN_ASESORIA_ARTICULOS.md) ✅
**Ruta:** `GET /admin/asesoria/articulos`  
**Controlador:** `AsesoriaController::articulos()`  
**Propósito:** CRUD de artículos de blog/asesoría con editor TinyMCE

**Datos:** Artículos con contenido HTML enriquecido, categoría, autor, vistas, estado

**Tablas:** `tbl_articulo_asesoria`, `tbl_categoria_asesoria`, `tbl_usuario`

**Editor:** TinyMCE 6 Cloud (WYSIWYG con tablas, imágenes, videos, código HTML)

**Filtros:** Por categoría, estado (borrador/publicado), búsqueda título

**Estados:** Borrador (no visible) | Publicado (visible al público)

---

---

---

## 📌 ESTADO DE DOCUMENTACIÓN

✅ **11 READMEs completados** de las 13 vistas principais

**Completados:**
1. ✅ Dashboard (pre-existente)
2. ✅ Usuarios 
3. ✅ Solicitudes
4. ✅ Incidencias
5. ✅ Propiedades
6. ✅ Alquileres
7. ✅ Suscripciones
8. ✅ Planes
9. ✅ Configuración
10. ✅ Asesoría (Categorías)
11. ✅ Asesoría (Artículos)

**No documentados (vistas de formularios adicionales):**
- Propiedades (Crear/Editar) - Podría crearse con el patrón
- Alquileres (Crear/Editar) - Podría crearse con el patrón

---

## 📌 RESUMEN RÁPIDO POR TIPO

### 🔍 **Vistas Principales (Listados)** - ✅ 11 COMPLETADAS
1. Dashboard ✅
2. Usuarios ✅
3. Solicitudes ✅
4. Incidencias ✅
5. Propiedades ✅
6. Alquileres ✅
7. Suscripciones ✅
8. Planes ✅
9. Asesoría (Categorías) ✅
10. Asesoría (Artículos) ✅
11. Configuración ✅

### ✏️ **Vistas de Formularios (Crear/Editar)** - PUEDEN AGREGARSE
- Propiedades (crear)
- Alquileres (crear)

### 🎛️ **Vistas de Configuración** - ✅ 1 COMPLETADA
- Configuración ✅

---

## 🔧 TABLAS PRINCIPALES CONSULTADAS

| Tabla | Usada en vistas | Operaciones |
|-------|---|---|
| `tbl_usuario` | Dashboard, Usuarios, Solicitudes, Incidencias, Alquileres | SELECT, INSERT, UPDATE, DELETE |
| `tbl_propiedad` | Propiedades, Incidencias, Alquileres | SELECT, INSERT, UPDATE, DELETE |
| `tbl_alquiler` | Dashboard, Alquileres | SELECT, INSERT, UPDATE |
| `tbl_solicitud_arrendador` | Solicitudes | SELECT, UPDATE (aprobar/rechazar) |
| `tbl_solicitud_gestor` | Solicitudes | SELECT, UPDATE (aprobar/rechazar) |
| `tbl_incidencia` | Incidencias | SELECT, UPDATE (cambiar estado) |
| `tbl_rol` | Usuarios | SELECT |
| `tbl_rol_usuario` | Usuarios | SELECT, INSERT, DELETE (cambiar rol) |
| `tbl_notificacion` | Dashboard | SELECT |
| `tbl_suscripcion` | Suscripciones | SELECT |
| `tbl_plan` | Planes, Suscripciones | SELECT, INSERT, UPDATE, DELETE |

---

## 🔄 FLUJO GENERAL DE CUALQUIER VISTA

```
1. Usuario accede GET /admin/{vista}
                ↓
2. Route → Controller::index()
                ↓
3. Controller ejecuta queries
                ↓
4. Aplica filtros (si hay)
                ↓
5. Ordena y pagina (si aplica)
                ↓
6. Pasa datos a view con compact()
                ↓
7. View renderiza con @forelse/@foreach
                ↓
8. Usuario ve tabla/kanban/cards
                ↓
9. Si clickea botón (ej: Aprobar)
                ↓
10. AJAX POST a /admin/{vista}/{id}/{accion}
                ↓
11. Controller ejecuta lógica (UPDATE/INSERT)
                ↓
12. Si transacción, BEGIN/COMMIT/ROLLBACK
                ↓
13. Retorna JSON response
                ↓
14. JavaScript recarga página o actualiza DOM
```

---

## 📊 ESTADÍSTICAS

| Métrica | Cantidad | Estado |
|---------|----------|--------|
| **READMEs completados** | 11 / 13 | ✅ 85% |
| **Vistas principales** | 11 | ✅ Documentadas |
| **Tablas documentadas** | 20+ | ✅ Documentadas |
| **Controladores cubiertos** | 11 | ✅ Cubiertos |
| **Filtros documentados** | 30+ | ✅ Documentados |
| **Acciones documentadas** | 50+ | ✅ Documentadas |
| **Ejemplos de código** | 20+ | ✅ Incluidos |
| **Flujos técnicos** | 11 | ✅ Detallados |

**Cobertura por tipo:**
- Listados: 11/11 ✅
- Formularios: 0/2 (pueden crearse fácilmente)
- Configuración: 1/1 ✅

---

## 🎓 CÓMO USAR ESTA DOCUMENTACIÓN

1. **Para entender una vista:**
   - Haz clic en el nombre de la vista (link)
   - Lee el README específico con código

2. **Para saber qué tablas consulta:**
   - Ve a "TABLAS PRINCIPALES CONSULTADAS"
   - O abre el README de la vista

3. **Para ver el flujo técnico:**
   - Abre README de la vista
   - Sección "Flujo Técnico Detallado"
   - Ahí está el código del controller

4. **Para saber qué filtros tiene:**
   - Sección "Filtros" en cada README
   - Con valores y efectos

5. **Para entender qué hace cada botón:**
   - Sección "Botones y Acciones"
   - Con endpoint y código backend

---

## 🔗 VÍNCULOS ÚTILES

- [Análisis Completo Notificaciones y Mails](FLUJOS_NOTIFICACIONES_Y_MAILS.md)
- [Análisis Técnico Profundo Notificaciones y Mails](ANALISIS_TECNICO_NOTIFICACIONES_Y_MAILS.md)
- [DB::raw Explicado](#) (preguntar en chat)

---

## 📝 NOTAS

- Los READMEs marcados con *[Pendiente]* aún no se han creado (pero puedo hacerlo al instante)
- Los códigos mostrados son simplificados pero técnicamente correctos
- Las transacciones se muestran explícitamente donde son críticas
- Los filtros están documentados con sus parámetros URL exactos

**¿Necesitas que cree los READMEs faltantes o que profundice en algún aspecto?**

