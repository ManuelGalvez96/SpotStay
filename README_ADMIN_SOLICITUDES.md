# 📋 README: ADMIN SOLICITUDES

**Vista:** `resources/views/admin/solicitudes.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/SolicitudController.php`  
**Ruta:** `GET /admin/solicitudes`

---

## 🎯 Propósito

Gestiona solicitudes de usuarios que quieren ser arrendadores o gestores. Permite:
- Ver solicitudes pendientes, aprobadas y rechazadas
- Filtrar por estado, tipo, fecha y búsqueda
- Aprobar solicitudes
- Rechazar solicitudes con motivo
- Ver el detalle de cada solicitud

---

## 🎛️ Filtros

| Filtro | ID HTML | Tipo |
|--------|---------|------|
| Buscar | `#buscadorSolicitudes` | input |
| Rango fecha | `#selectRangoSol` | select |
| Tipo | `#selectTipoSol` | select |
| Estado | `#selectEstadoSol` | select |
| Ciudad | `#selectCiudadSol` | select |

Los filtros se envían por GET al controlador.

---

## 🔘 Botones y acciones

| Botón | Acción |
|-------|--------|
| Aprobar | Crea rol, perfil y códigos |
| Rechazar | Marca la solicitud y guarda el motivo |
| Ver detalle | Abre la solicitud |

---

## 📊 Datos

```php
compact('solicitudes', 'totales', 'filtros')
```

---

## ⚠️ Puntos importantes

1. Aprobar una solicitud suele implicar varias operaciones coordinadas.
2. La vista combina solicitudes de arrendador y gestor.
3. La paginación suele ser de 7 registros por página.