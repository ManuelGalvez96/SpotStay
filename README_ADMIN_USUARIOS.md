# 👥 README: ADMIN USUARIOS

**Vista:** `resources/views/admin/usuarios.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/UsuarioController.php`  
**Ruta:** `GET /admin/usuarios`

---

## 🎯 Propósito

Gestiona todos los usuarios de la plataforma. Permite:
- Ver listado paginado
- Filtrar por rol y estado
- Buscar por nombre o email
- Crear, editar y eliminar usuarios
- Cambiar rol y activar/desactivar cuentas

---

## 🎛️ Filtros y búsqueda

| Filtro | ID HTML | Tipo |
|--------|---------|------|
| Buscar | `#buscadorUsuarios` | input |
| Rol | `#selectRol` | select |
| Estado | `#selectEstado` | select |

El buscador filtra en tiempo real sobre `#tbodyUsuarios`.

---

## 📊 Datos y vista

```php
compact('usuarios', 'roles', 'totales')
```

La tabla suele incluir:
- Nombre
- Email
- Teléfono
- Ciudad
- Rol
- Estado
- Acciones

---

## 📱 Responsive

### Desktop
- Tabla completa.
- Filtros en una sola barra.

### Mobile
- Columnas menos importantes ocultas.
- Scroll horizontal si hace falta.

---

## 🔘 Botones

- Crear usuario
- Editar
- Cambiar rol
- Eliminar
- Activar / desactivar

---

## ⚠️ Puntos importantes

1. La paginación suele ser de 15 usuarios por página.
2. El filtro por estado afecta a usuarios activos/inactivos.
3. El rol se obtiene desde `tbl_rol_usuario` y `tbl_rol`.