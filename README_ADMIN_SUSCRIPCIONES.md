# 🎫 README: ADMIN SUSCRIPCIONES

**Vista:** `resources/views/admin/suscripciones.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/SuscripcionController.php`  
**Ruta:** `GET /admin/suscripciones`

---

## 🎯 Propósito

Gestiona las suscripciones de la plataforma. Permite:
- Ver suscripciones activas y expiradas
- Filtrar por estado y plan
- Ver KPIs de ingresos y expiraciones
- Consultar usuarios suscritos

---

## 🎛️ Filtros

| Filtro | ID HTML | Tipo |
|--------|---------|------|
| Buscar | `#buscadorSus` | input |
| Plan | `#selectPlanSus` | select |
| Estado | `#selectEstadoSus` | select |

---

## 📊 KPIs

- Total activas
- Total plan pro
- Total plan básico
- Total expiradas
- Ingresos del mes
- Ingresos históricos

---

## 📱 Responsive

La vista usa bloques/divs en lugar de una tabla clásica, por lo que se adapta mejor a móvil.

---

## ⚠️ Puntos importantes

1. La paginación suele mostrar 10 suscripciones por página.
2. Se calculan métricas de negocio en el controlador.
3. Los datos suelen incluir días restantes y propiedades usadas.