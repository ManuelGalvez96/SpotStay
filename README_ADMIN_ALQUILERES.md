# 🏆 README: ADMIN ALQUILERES

**Vista:** `resources/views/admin/alquileres.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AlquilerController.php`  
**Ruta:** `GET /admin/alquileres`

---

## 🎯 Propósito

Gestiona el listado de alquileres. Permite:
- Ver alquileres activos, pendientes y finalizados
- Filtrar por estado, arrendador y propiedad
- Ver cuotas pendientes y atrasadas
- Crear alquileres
- Consultar información de cada contrato

---

## 🎛️ Filtros

| Filtro | ID HTML | Tipo |
|--------|---------|------|
| Buscar | `#buscadorAlq` | input |
| Estado | `#selectEstadoAlq` | select |
| Propiedad | `#selectPropiedadAlq` | select |
| Mes | `#selectMesAlq` | select |

---

## 📊 Datos

```php
compact('alquileres', 'totales', 'filtros')
```

La tabla suele mostrar:
- Propiedad
- Inquilino
- Arrendador
- Fecha inicio/fin
- Estado
- Cuotas pendientes
- Acciones

---

## 🔘 Botones

- Ver detalle
- Crear alquiler
- Editar alquiler
- Finalizar alquiler

---

## ⚠️ Puntos importantes

1. La vista calcula cuotas pendientes y atrasadas.
2. La paginación suele ser de 10 o 12 registros.
3. Algunas columnas se ocultan en móvil para simplificar la tabla.