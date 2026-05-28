# 📚 README: ADMIN ASESORÍA (CATEGORÍAS)

**Vista:** `resources/views/admin/asesoria.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AsesoriaController.php`  
**Ruta:** `GET /admin/asesoria`

---

## 🎯 Propósito

Gestiona las **categorías de artículos de asesoría**. Permite:
- Ver listado de categorías
- Buscar por nombre
- Filtrar por estado
- Cambiar paginación
- Crear categorías nuevas
- Editar categorías
- Eliminar categorías sin artículos asociados
- Ver el número de artículos por categoría

---

## 🎛️ Filtros y búsqueda

| Filtro | ID HTML | Tipo | Uso |
|--------|---------|------|-----|
| Buscar | `#filtro-busqueda` | input | Busca por nombre |
| Estado | `#filtro-estado` | select | Todos / Activo / Inactivo |
| Resultados | `#filtro-paginacion` | select | 10 / 20 / 50 / Todos |

**Filtros reales en frontend:**
- El buscador filtra en JavaScript sobre `#tabla-categorias-body`.
- Estado y paginación llaman a funciones de recarga de tabla.
- El botón `#btn-limpiar-filtros` restablece todo.

**Ordenación:**
- Las cabeceras con clase `.sortable` permiten ordenar por `data-sort`.

---

## 📐 Modal de nueva categoría

### Acciones reales
- `abrirModalNuevaCategoria()` abre el modal.
- `cerrarModalNuevaCategoria()` lo cierra.
- `generarSlug()` crea el slug a partir del nombre.

**Campos habituales del formulario:**
- Nombre
- Slug auto-generado
- Icono
- Orden
- Descripción
- Estado activo/inactivo

**Ejemplo de auto-slug:**
```javascript
const slug = nombre
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
```

---

## 📊 Datos pasados a la vista

```php
compact('categorias')
```

Cada categoría incluye normalmente:
- `id_categoria`
- `nombre_categoria`
- `slug_categoria`
- `icono_categoria`
- `orden_categoria`
- `descripcion_categoria`
- `estado_categoria`
- `total_articulos`

---

## 📱 Responsive Design

### Desktop
- Tabla completa visible.
- Toolbar de filtros en una fila.
- Modal centrado.

### Mobile
- Tabla con scroll horizontal.
- Filtros apilados.
- Botones y acciones compactados.

---

## 🔘 Botones y acciones

| Botón | Función |
|-------|---------|
| Nueva categoría | Abre el modal de alta |
| Editar | Abre el modal de edición |
| Eliminar | Borra la categoría si no tiene artículos |

---

## ⚠️ Puntos importantes

1. El slug se genera automáticamente desde el nombre.
2. La tabla se puede ordenar por varias columnas.
3. No debe borrarse una categoría con artículos vinculados.
4. El filtro de estado y paginación se gestiona desde la interfaz.
5. La tabla usa `#tabla-categorias-body` como contenedor principal.