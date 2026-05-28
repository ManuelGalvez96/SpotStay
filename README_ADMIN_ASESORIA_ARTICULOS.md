# ✍️ README: ADMIN ASESORÍA (ARTÍCULOS)

**Vista:** `resources/views/admin/asesoria-articulos.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/AsesoriaController.php`  
**Ruta:** `GET /admin/asesoria/articulos` | `GET /admin/asesoria/{id}/articulos`

---

## 🎯 Propósito

Gestiona los **artículos de asesoría y ayuda**. Permite:
- Ver listado de artículos
- Filtrar por categoría, estado, destacado y texto
- Crear artículos con editor TinyMCE
- Editar artículos
- Eliminar artículos
- Marcar artículos como destacados para FAQ
- Cambiar el orden de publicación

---

## 🎛️ Filtros y búsqueda

| Filtro | ID HTML | Tipo | Uso |
|--------|---------|------|-----|
| Categoría | `#filtro-categoria` | select | Filtra por categoría |
| Búsqueda | `#filtro-busqueda` | input | Busca por título |
| Estado | `#filtro-estado` | select | Todos / Activo / Inactivo |
| Destacado | `#filtro-destacado` | select | Todos / Solo destacados / No destacados |
| Paginación | `#filtro-paginacion` | select | 10 / 20 / 50 / Todos |

**Comportamiento real:**
- El buscador filtra filas del cuerpo `#tabla-articulos-body`.
- El resto de filtros recargan la tabla con parámetros.
- `#btn-limpiar-filtros` restablece el formulario.

---

## 🖊️ Editor TinyMCE

**Versión real cargada:** TinyMCE 5.10.9 desde CDN.

**Uso:**
- Se aplica a `textarea.tinymce-editor`.
- Sincroniza el contenido HTML al guardar.
- Se usa para el campo `contenido` del artículo.

```javascript
tinymce.init({
    selector: 'textarea.tinymce-editor',
    language: 'es',
    menubar: false,
    height: 400,
    plugins: ['link', 'image', 'lists', 'paste', 'code'],
    toolbar: 'undo redo | bold italic underline | bullist numlist | link image | code',
    paste_as_text: true
});
```

---

## 📐 Tabla sorteable

**Columnas principales:**
- Categoría
- Orden
- Título
- Contenido
- Estado
- Destacado
- Orden destacado
- Acciones

Las cabeceras con `.sortable` usan `data-sort` y alternan `sort-asc` / `sort-desc`.

---

## 📊 Datos pasados a la vista

```php
compact('articulos', 'categorias')
```

- `articulos`: paginador con artículos y relaciones.
- `categorias`: categorías activas para el selector del formulario.

---

## 📱 Responsive Design

### Desktop
- La tabla muestra todas las columnas.
- El editor tiene una altura amplia.
- Los filtros se alinean en una sola barra.

### Mobile
- Tabla con scroll horizontal.
- Filtros apilados verticalmente.
- Editor más compacto.

---

## 🔘 Botones y acciones

| Botón | Función |
|-------|---------|
| Nuevo artículo | Abre el modal de creación |
| Editar | Abre el modal de edición |
| Eliminar | Borra el artículo |

---

## ⚠️ Puntos importantes

1. TinyMCE es la versión 5.10.9, no la 6.
2. El campo contenido guarda HTML enriquecido.
3. El filtro de destacado afecta a artículos tipo FAQ.
4. El listado usa paginación y ordenación por cabeceras.
5. Las categorías del selector deben estar activas.