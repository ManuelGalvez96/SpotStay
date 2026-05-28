# 🚨 README: ADMIN INCIDENCIAS

**Vista:** `resources/views/admin/incidencias.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/IncidenciaController.php`  
**Ruta:** `GET /admin/incidencias`

---

## 🎯 Propósito

Gestiona las incidencias reportadas por los inquilinos. Permite:
- Ver incidencias por estado
- Cambiar estado
- Asignar gestor
- Contactar por email
- Filtrar y buscar incidencias
- Ver incidencias inactivas

---

## 📊 Estados

- Abierta
- Esperando decisión
- Esperando pago
- Solucionada
- Resuelta

---

## 🎛️ Filtros y búsqueda

La vista trabaja con búsqueda y filtros sobre:
- Categoría
- Prioridad
- Propiedad
- Estado
- Texto libre

---

## 🔘 Botones

- Cambiar estado
- Asignar gestor
- Contactar
- Ver detalle

---

## 📊 Datos

```php
compact('abiertas', 'esperandoDecision', 'esperandoPago', 'solucionadas', 'resueltas', 'gestores', 'propiedades', 'inquilinos', 'categorias')
```

---

## ⚠️ Puntos importantes

1. Usa `ActividadService` al crear incidencias.
2. Marca como inactivas las incidencias sin cambios durante más de 14 días.
3. El controlador devuelve JSON para varias acciones AJAX.
4. Hay una lógica concreta para resolver el `encargadoPago`.