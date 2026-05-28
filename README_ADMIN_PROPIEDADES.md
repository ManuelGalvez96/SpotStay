# 🏠 README: ADMIN PROPIEDADES

**Vista:** `resources/views/admin/propiedades.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/PropiedadController.php`  
**Ruta:** `GET /admin/propiedades`

---

## 🎯 Propósito

Gestiona todas las propiedades registradas. Permite:
- Ver listado paginado
- Buscar por título
- Filtrar por estado, ciudad, arrendador y precio
- Ver detalle
- Editar
- Eliminar
- Ver alquileres vinculados

---

## 🎛️ Filtros

| Filtro | ID HTML | Tipo |
|--------|---------|------|
| Buscar | `#buscadorPropiedades` | input |
| Estado | `#selectEstado` | select |
| Ciudad | `#selectCiudad` | select |
| Precio | `#selectPrecio` | select |

---

## 📊 Datos

```php
compact('propiedades', 'totales', 'filtros')
```

La lista suele incluir:
- Título
- Dirección
- Ciudad
- Precio
- Estado
- Arrendador
- Acciones

---

## 🔘 Botones

- Ver detalle
- Editar
- Eliminar
- Abrir alquileres relacionados

---

## ⚠️ Puntos importantes

1. La vista usa filtros con JavaScript y/o GET según el caso.
2. El precio se trata por rangos.
3. La paginación suele ser de 12 elementos por página.