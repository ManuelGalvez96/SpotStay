# 📊 README: ADMIN PLANES

**Vista:** `resources/views/admin/planes.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/PlanController.php`  
**Ruta:** `GET /admin/planes`

---

## 🎯 Propósito

Gestiona los planes de suscripción. Permite:
- Ver los planes existentes
- Crear nuevos planes
- Editar planes
- Activar o desactivar planes
- Eliminar solo si no tienen suscriptores activos

---

## 🔘 Botones

- Crear plan
- Guardar cambios
- Eliminar
- Activar / desactivar

---

## 📊 Datos

```php
compact('planes', 'totales')
```

Cada tarjeta/plan suele mostrar:
- Nombre
- Slug
- Precio
- Rol destino
- Descripción
- Estado
- Suscriptores vinculados

---

## ⚠️ Puntos importantes

1. No se puede borrar un plan con suscripciones activas.
2. La interfaz se organiza en tarjetas, no en una tabla.
3. Los formularios suelen estar embebidos en cada card.