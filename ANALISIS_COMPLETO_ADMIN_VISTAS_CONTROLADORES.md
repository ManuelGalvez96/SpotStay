# ANÁLISIS COMPLETO: VISTAS ADMIN Y CONTROLADORES — SpotStay

## 1. DASHBOARD

```json
{
  "vista": "admin/dashboard.blade.php",
  "ruta_get": "/admin/dashboard",
  "controller": "DashboardController::index",
  "archivo_controller": "app/Http/Controllers/Admin/DashboardController.php",
  
  "tablas_consultadas": [
    "tbl_usuario",
    "tbl_propiedad",
    "tbl_alquiler",
    "tbl_solicitud_arrendador",
    "tbl_solicitud_gestor",
    "tbl_rol",
    "tbl_rol_usuario",
    "tbl_notificacion",
    "tbl_incidencia",
    "tbl_categoria",
    "tbl_propiedad (alias inquilino)",
    "tbl_propiedad (alias arrendador)"
  ],
  
  "columnas_principales": {
    "tbl_usuario": "id_usuario, nombre_usuario",
    "tbl_propiedad": "id_propiedad, titulo_propiedad, calle_propiedad, numero_propiedad, piso_propiedad, puerta_propiedad, ciudad_propiedad, estado_propiedad",
    "tbl_alquiler": "id_alquiler, estado_alquiler, creado_alquiler",
    "tbl_notificacion": "titulo_notificacion, tipo_notificacion, color_notificacion, creado_notificacion",
    "tbl_incidencia": "id_incidencia, titulo_incidencia, estado_incidencia, actualizado_incidencia",
    "tbl_rol": "nombre_rol"
  },
  
  "joins": [
    {
      "tabla1": "tbl_alquiler",
      "tabla2": "tbl_propiedad",
      "condicion": "tbl_propiedad.id_propiedad = tbl_alquiler.id_propiedad_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_alquiler",
      "tabla2": "tbl_usuario (alias inquilino)",
      "condicion": "inquilino.id_usuario = tbl_alquiler.id_inquilino_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_propiedad",
      "tabla2": "tbl_usuario (alias arrendador)",
      "condicion": "arrendador.id_usuario = tbl_propiedad.id_arrendador_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_rol_usuario",
      "tabla2": "tbl_rol",
      "condicion": "tbl_rol.id_rol = tbl_rol_usuario.id_rol_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_notificacion",
      "tabla2": "tbl_usuario",
      "condicion": "tbl_usuario.id_usuario = tbl_notificacion.id_usuario_fk",
      "tipo": "INNER JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "13-107",
      "descripcion": "Obtiene KPIs: total usuarios, propiedades activas, solicitudes nuevas. Obtiene últimos alquileres pendientes, solicitudes, usuarios por rol, actividad reciente e incidencias inactivas",
      "retorna": "view('admin.dashboard', compact(...))"
    },
    {
      "metodo": "aprobarAlquiler($id)",
      "linea": "109-120",
      "descripcion": "Actualiza estado alquiler a 'activo', establece fecha aprobación, devuelve JSON",
      "transaccion": "NO",
      "actualiza": "tbl_alquiler",
      "cambios": "estado_alquiler='activo', aprobado_alquiler=ahora, actualizado_alquiler=ahora"
    },
    {
      "metodo": "rechazarAlquiler($id)",
      "linea": "122-130",
      "descripcion": "Actualiza estado alquiler a 'rechazado', devuelve JSON",
      "transaccion": "NO",
      "actualiza": "tbl_alquiler",
      "cambios": "estado_alquiler='rechazado', actualizado_alquiler=ahora"
    },
    {
      "metodo": "filtrarIncidenciasInactivas(Request $request)",
      "linea": "132-...",
      "descripcion": "Filtra incidencias por estado y búsqueda, devuelve datos formateados en JSON",
      "transaccion": "NO (lectura)"
    }
  ],
  
  "filtros": [],
  
  "botones_acciones": [
    "Aprobar alquiler (POST /admin/alquiler/{id}/aprobar)",
    "Rechazar alquiler (POST /admin/alquiler/{id}/rechazar)",
    "Ver todas las incidencias (/admin/incidencias)"
  ],
  
  "datos_pasados_vista": [
    "totalUsuarios: int",
    "propiedadesActivas: int",
    "alquileresPendientes: int",
    "solicitudesNuevas: int",
    "ultimosAlquileres: Collection(5 registros)",
    "ultimasSolicitudes: Collection",
    "usuariosPorRol: Collection (nombre_rol, total)",
    "actividadReciente: Collection(5 registros)",
    "ultimasIncidenciasInactivas: Collection"
  ],
  
  "flujo_resumido": "Usuario admin accede a /admin/dashboard → DashboardController::index() → Consulta BD (11 queries) → Calcula KPIs → Retorna datos a vista blade",
  
  "puntos_importantes": [
    "Utiliza dirección formateada con CONCAT_WS para mostrar en tabla",
    "Incidencias inactivas: marcadas si actualizado > 2 semanas",
    "Agrupa usuarios por rol con COUNT(*)",
    "Ordenamientos: por creado_alquiler DESC, actualizado_incidencia DESC, creado_notificacion DESC"
  ]
}
```

---

## 2. USUARIOS

```json
{
  "vista": "admin/usuarios.blade.php",
  "ruta_get": "/admin/usuarios",
  "controller": "UsuarioController::index",
  "archivo_controller": "app/Http/Controllers/Admin/UsuarioController.php",
  
  "tablas_consultadas": [
    "tbl_usuario",
    "tbl_rol_usuario",
    "tbl_rol",
    "tbl_propiedad",
    "tbl_suscripcion",
    "tbl_plan"
  ],
  
  "columnas_principales": {
    "tbl_usuario": "id_usuario, nombre_usuario, email_usuario, telefono_usuario, activo_usuario, creado_usuario, actualizado_usuario",
    "tbl_rol": "id_rol, nombre_rol, slug_rol",
    "tbl_propiedad": "id_arrendador_fk (COUNT(*) as total)",
    "tbl_suscripcion": "id_usuario_fk, plan_suscripcion, estado_suscripcion, id_plan_fk",
    "tbl_plan": "nombre_plan"
  },
  
  "joins": [
    {
      "tabla1": "tbl_usuario",
      "tabla2": "tbl_rol_usuario (subconsulta LEFT JOIN SUB)",
      "condicion": "roles_usuario.id_usuario_fk = tbl_usuario.id_usuario",
      "tipo": "LEFT JOIN SUB"
    },
    {
      "tabla1": "tbl_usuario",
      "tabla2": "tbl_propiedad (subconsulta LEFT JOIN)",
      "condicion": "props.id_arrendador_fk = tbl_usuario.id_usuario",
      "tipo": "LEFT JOIN"
    },
    {
      "tabla1": "tbl_rol_usuario",
      "tabla2": "tbl_rol",
      "condicion": "tbl_rol.id_rol = tbl_rol_usuario.id_rol_fk",
      "tipo": "INNER JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "80-175",
      "descripcion": "Lista usuarios paginados (10/página) con sus roles y propiedades. Enriquece con datos de suscripción",
      "retorna": "view('admin.usuarios', compact(...))"
    },
    {
      "metodo": "filtrar(Request $request)",
      "linea": "177-...",
      "descripcion": "Filtra usuarios por rol, estado, búsqueda. Retorna JSON paginado",
      "filtros": "rol, estado, búsqueda (nombre/email)"
    },
    {
      "metodo": "getKpisUsuarios()",
      "descripcion": "Retorna KPIs en JSON: total, activos, inactivos, este mes"
    },
    {
      "metodo": "crear(Request $request)",
      "descripcion": "Crea usuario con validación, asigna rol, genera hash contraseña"
    },
    {
      "metodo": "editar($id, Request $request)",
      "descripcion": "Actualiza datos usuario (sin cambiar rol ni email directamente)"
    },
    {
      "metodo": "toggleEstado($id)",
      "descripcion": "Alterna activo_usuario entre true/false"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "buscadorUsuarios",
      "busca_en": "nombre_usuario, email_usuario"
    },
    {
      "nombre": "Rol",
      "campo": "selectRol",
      "valores": "admin, arrendador, inquilino, gestor, miembro"
    },
    {
      "nombre": "Estado",
      "campo": "selectEstado",
      "valores": "activo, inactivo"
    }
  ],
  
  "botones_acciones": [
    "Nuevo usuario → Modal crear usuario",
    "Ver detalle usuario (GET /admin/usuarios/{id})",
    "Editar usuario (POST /admin/usuarios/{id}/editar)",
    "Toggle estado (POST /admin/usuarios/{id}/toggle-estado)",
    "Exportar (GET /admin/usuarios/exportar)"
  ],
  
  "datos_pasados_vista": [
    "usuarios: Paginated(10) con roles, suscripción, propiedades",
    "planesSuscripcion: Collection (id_plan, nombre_plan, slug_plan)",
    "totalUsuarios: int",
    "activos: int",
    "inactivos: int",
    "esteMes: int (usuarios creados este mes)"
  ],
  
  "flujo_resumido": "Usuario admin → GET /admin/usuarios → UsuarioController::index() → Consulta usuarios con roles, propiedades y suscripción → Paginación 10/página → Retorna a vista blade con KPIs",
  
  "puntos_importantes": [
    "Subconsulta de roles: agrupa múltiples roles por usuario con GROUP_CONCAT",
    "Enriquecimiento de suscripción: se agrega información de suscripción más reciente a cada usuario",
    "Ordenamiento: por actualizado_usuario DESC, creado_usuario DESC",
    "Válida límite de propiedades según plan de suscripción"
  ]
}
```

---

## 3. SOLICITUDES

```json
{
  "vista": "admin/solicitudes.blade.php",
  "ruta_get": "/admin/solicitudes",
  "controller": "SolicitudController::index",
  "archivo_controller": "app/Http/Controllers/Admin/SolicitudController.php",
  
  "tablas_consultadas": [
    "tbl_solicitud_arrendador",
    "tbl_solicitud_gestor",
    "tbl_usuario",
    "tbl_ciudad_usuario",
    "tbl_plan"
  ],
  
  "columnas_principales": {
    "tbl_solicitud_arrendador": "id_solicitud_arrendador, id_usuario_fk, estado_solicitud_arrendador, creado_solicitud_arrendador, id_plan_fk",
    "tbl_solicitud_gestor": "id_solicitud_gestor, id_usuario_fk, estado_solicitud_gestor, creado_solicitud_gestor",
    "tbl_usuario": "id_usuario, nombre_usuario, email_usuario, ciudad_usuario_usuario",
    "tbl_plan": "nombre_plan"
  },
  
  "joins": [
    {
      "tabla1": "tbl_solicitud_arrendador",
      "tabla2": "tbl_usuario",
      "condicion": "tbl_usuario.id_usuario = tbl_solicitud_arrendador.id_usuario_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_solicitud_gestor",
      "tabla2": "tbl_usuario",
      "condicion": "tbl_usuario.id_usuario = tbl_solicitud_gestor.id_usuario_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_solicitud_arrendador",
      "tabla2": "tbl_plan",
      "condicion": "tbl_plan.id_plan = tbl_solicitud_arrendador.id_plan_fk",
      "tipo": "LEFT JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index(Request $request)",
      "linea": "20-64",
      "descripcion": "Lista solicitudes de arrendador y gestor combinadas, paginadas (7/página). Calcula KPIs",
      "parametros_request": "estado (pendiente/aprobada/rechazada), rango (mes/3meses/anio/all), tipo (arrendador/gestor/all), q (búsqueda), ciudad",
      "retorna": "view('admin.solicitudes', compact(...))"
    },
    {
      "metodo": "aprobar(Request $request, $id)",
      "linea": "66-...",
      "descripcion": "Aprueba solicitud de arrendador o gestor. Crea rol, suscripción pendiente. Con transacción BD",
      "transaccion": "SÍ",
      "pasos": [
        "Inicia transacción",
        "Obtiene ID admin",
        "Actualiza estado_solicitud a 'aprobada'",
        "Si es arrendador: crea suscripción, resetea stripe_status",
        "Si es gestor: asigna rol, crea código gestor",
        "Commit"
      ]
    },
    {
      "metodo": "rechazar(Request $request, $id)",
      "linea": "...",
      "descripcion": "Rechaza solicitud con notas opcionales"
    },
    {
      "metodo": "filtrar(Request $request)",
      "descripcion": "Filtra solicitudes combinadas, retorna JSON"
    },
    {
      "metodo": "getKpisSolicitudes()",
      "descripcion": "Retorna KPIs: pendientes, aprobadas, rechazadas, total"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "q",
      "busca_en": "nombre, email, detalles solicitud"
    },
    {
      "nombre": "Rango temporal",
      "campo": "rango",
      "valores": "mes, 3meses, anio, all"
    },
    {
      "nombre": "Tipo solicitud",
      "campo": "tipo",
      "valores": "all, arrendador, gestor"
    },
    {
      "nombre": "Estado",
      "campo": "estado",
      "valores": "pendiente, aprobada, rechazada"
    },
    {
      "nombre": "Ciudad",
      "campo": "ciudad",
      "valores": "Madrid, Barcelona, Valencia, Sevilla, Bilbao"
    }
  ],
  
  "botones_acciones": [
    "Ver detalle solicitud (GET /admin/solicitudes/{id})",
    "Aprobar (POST /admin/solicitudes/{id}/aprobar?tipo=arrendador|gestor)",
    "Rechazar (POST /admin/solicitudes/{id}/rechazar?tipo=arrendador|gestor)"
  ],
  
  "datos_pasados_vista": [
    "solicitudesPendientes: Paginated(7) con datos combinados arrendador/gestor",
    "pendientesMes: int",
    "aprobadas: int",
    "rechazadas: int",
    "totalSolicitudes: int",
    "tiempoMedio: float (4.2)",
    "ultimasAprobadas: Collection(3)",
    "ultimasRechazadas: Collection(3)"
  ],
  
  "flujo_resumido": "Admin → GET /admin/solicitudes?estado=pendiente → SolicitudController::index() → Combina tbl_solicitud_arrendador + tbl_solicitud_gestor → Pagina 7/página → Calcula KPIs → Retorna vista con filtros activos",
  
  "puntos_importantes": [
    "Combina dos tablas (solicitud_arrendador y solicitud_gestor) mediante Collection en memory",
    "Transacción obligatoria para aprobar (crea rol + suscripción)",
    "Si plan_fk vacío en arrendador: no crea suscripción",
    "Código gestor se crea automáticamente al aprobar gestor",
    "Filtros por ciudad hardcodeados en vista (no dinámicos de BD)"
  ]
}
```

---

## 4. PROPIEDADES (Listado)

```json
{
  "vista": "admin/propiedades.blade.php",
  "ruta_get": "/admin/propiedades",
  "controller": "PropiedadController::index",
  "archivo_controller": "app/Http/Controllers/Admin/PropiedadController.php",
  
  "tablas_consultadas": [
    "tbl_propiedad",
    "tbl_usuario"
  ],
  
  "columnas_principales": {
    "tbl_propiedad": "id_propiedad, titulo_propiedad, calle_propiedad, numero_propiedad, piso_propiedad, puerta_propiedad, ciudad_propiedad, precio_propiedad, estado_propiedad, creado_propiedad",
    "tbl_usuario": "id_usuario, nombre_usuario, email_usuario"
  },
  
  "joins": [
    {
      "tabla1": "tbl_propiedad",
      "tabla2": "tbl_usuario",
      "condicion": "tbl_usuario.id_usuario = tbl_propiedad.id_arrendador_fk",
      "tipo": "INNER JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "...",
      "descripcion": "Lista todas las propiedades con arrendador. Obtiene KPIs: total, alquiladas, publicadas, borradores",
      "retorna": "view('admin.propiedades', compact(...))"
    },
    {
      "metodo": "filtrar(Request $request)",
      "descripcion": "Filtra propiedades por estado, ciudad, rango precio. Retorna JSON"
    },
    {
      "metodo": "crear(Request $request)",
      "descripcion": "Crea propiedad nueva. Valida arrendador existe con rol"
    },
    {
      "metodo": "actualizar(Request $request, $id)",
      "descripcion": "Actualiza propiedad existente"
    },
    {
      "metodo": "eliminar($id)",
      "descripcion": "Elimina propiedad (DELETE)"
    },
    {
      "metodo": "desactivar(Request $request, $id)",
      "descripcion": "Cambia estado a 'inactiva'"
    },
    {
      "metodo": "publicar(Request $request, $id)",
      "descripcion": "Cambia estado a 'publicada'"
    },
    {
      "metodo": "show($id)",
      "descripcion": "Retorna detalle propiedad en JSON"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "buscadorPropiedades",
      "busca_en": "dirección, ciudad"
    },
    {
      "nombre": "Estado",
      "campo": "selectEstado",
      "valores": "publicada, alquilada, borrador, inactiva"
    },
    {
      "nombre": "Ciudad",
      "campo": "selectCiudad",
      "valores": "madrid, barcelona, valencia, sevilla, bilbao"
    },
    {
      "nombre": "Precio",
      "campo": "selectPrecio",
      "valores": "0-500, 500-1000, 1000-2000, 2000+"
    }
  ],
  
  "botones_acciones": [
    "Añadir propiedad (GET /admin/propiedades/nueva → formulario)",
    "Editar propiedad (GET /admin/propiedades/{id}/editar)",
    "Ver detalle (GET /admin/propiedades/{id})",
    "Publicar/Desactivar (POST /admin/propiedades/{id}/publicar|desactivar)",
    "Descargar PDF (GET /admin/propiedades/{id}/descargar-pdf)",
    "Exportar listado (GET /admin/propiedades/exportar)"
  ],
  
  "datos_pasados_vista": [
    "propiedades: Paginated con arrendador info",
    "totalPropiedades: int",
    "alquiladas: int",
    "publicadas: int",
    "borradores: int (o inactivas)"
  ],
  
  "flujo_resumido": "Admin → GET /admin/propiedades → PropiedadController::index() → Consulta tbl_propiedad + tbl_usuario (arrendador) → Calcula KPIs por estado → Retorna vista con tabla y filtros",
  
  "puntos_importantes": [
    "Formulario GET /admin/propiedades/nueva solo prepara vista vacía",
    "POST /admin/propiedades/crear → INSERT en tbl_propiedad + asigna gestor_fk = arrendador_fk",
    "Validación límite propiedades según plan: obtenerColumnaPrecio() determina qué columna usar",
    "Campos de precio dinámicos según plan (precio_propiedad vs otros campos)"
  ]
}
```

---

## 5. PROPIEDADES CREAR/EDITAR

```json
{
  "vista": "admin/propiedades-crear.blade.php",
  "ruta_get_crear": "/admin/propiedades/nueva",
  "ruta_post_crear": "/admin/propiedades/crear",
  "ruta_get_editar": "/admin/propiedades/{id}/editar",
  "ruta_post_editar": "/admin/propiedades/{id}/editar",
  "controller": "PropiedadController::nueva | crear | editar | actualizar",
  "archivo_controller": "app/Http/Controllers/Admin/PropiedadController.php",
  
  "tablas_consultadas": [
    "tbl_propiedad",
    "tbl_usuario"
  ],
  
  "metodos_controller": [
    {
      "metodo": "nueva()",
      "linea": "7-10",
      "descripcion": "Devuelve vista en blanco para crear nueva propiedad",
      "retorna": "view('admin.propiedades-crear')"
    },
    {
      "metodo": "editar($id)",
      "linea": "12-32",
      "descripcion": "Obtiene propiedad existente con email arrendador. Valida que no esté alquilada",
      "validaciones": "Propiedad debe existir, no puede estar 'alquilada'",
      "retorna": "view('admin.propiedades-crear', ['propiedadEditando' => $propiedad])"
    },
    {
      "metodo": "crear(Request $request)",
      "linea": "34-121",
      "descripcion": "Crea nueva propiedad con validación completa",
      "validaciones": [
        "titulo: required|max:150",
        "calle, numero: required|max:150|20",
        "ciudad: required|max:100",
        "codigo_postal: required|max:10",
        "precio: required|numeric|min:0",
        "tipo: piso|casa|estudio|chalet",
        "habitaciones: 1|2|3|4|4+",
        "metros: integer|min:1",
        "banos: 1|2|3|3+",
        "estado: publicada|alquilada|borrador|inactiva",
        "extras: array de amueblado|piscina|terraza|garaje|ascensor|aire_acondicionado|calefaccion|trastero",
        "arrendador_email: required|email|existe en BD con rol 'arrendador'"
      ],
      "transaccion": "NO",
      "inserta": "INSERT INTO tbl_propiedad con 25 columnas",
      "campos_booleanos": "amueblado_propiedad, piscina_propiedad, terraza_propiedad, garaje_propiedad, ascensor_propiedad, aire_acondicionado_propiedad, calefaccion_propiedad, trastero_propiedad"
    },
    {
      "metodo": "actualizar(Request $request, $id)",
      "linea": "189-...",
      "descripcion": "Actualiza propiedad existente con validaciones similares a crear",
      "transaccion": "NO",
      "actualiza": "UPDATE tbl_propiedad WHERE id_propiedad"
    }
  ],
  
  "campos_formulario": {
    "titulo": "text|max:150|required",
    "calle": "text|required",
    "numero": "text|required",
    "piso": "text|nullable",
    "puerta": "text|nullable",
    "ciudad": "text|required",
    "codigo_postal": "text|required",
    "precio": "number|required|min:0",
    "tipo": "select|piso|casa|estudio|chalet",
    "habitaciones": "select|1|2|3|4|4+",
    "metros": "number|nullable",
    "banos": "select|1|2|3|3+",
    "estado": "select|publicada|alquilada|borrador|inactiva",
    "descripcion": "textarea|nullable",
    "extras": "checkboxes|múltiples selecciones",
    "adicional": "text|nullable|max:255",
    "arrendador_email": "email|required|debe existir en BD"
  },
  
  "flujo_resumido": [
    "GET /admin/propiedades/nueva → Vista vacía",
    "POST /admin/propiedades/crear → Valida, busca arrendador, INSERT tbl_propiedad, redirecciona a /admin/propiedades",
    "GET /admin/propiedades/{id}/editar → Obtiene propiedad, muestra en formulario",
    "POST /admin/propiedades/{id}/editar → Valida, busca arrendador, UPDATE tbl_propiedad, redirecciona"
  ],
  
  "puntos_importantes": [
    "Búsqueda de arrendador: JOIN tbl_usuario + tbl_rol_usuario + tbl_rol WHERE slug_rol='arrendador'",
    "Validación límite de propiedades según plan: solo si estado='publicada'",
    "Booleanos: se convierten de array de extras a 0|1 en BD",
    "Dirección se construye con CONCAT_WS en vista (no en controller)",
    "obtenerColumnaPrecio() retorna nombre columna según rol arrendador"
  ]
}
```

---

## 6. ALQUILERES (Listado)

```json
{
  "vista": "admin/alquileres.blade.php",
  "ruta_get": "/admin/alquileres",
  "controller": "AlquilerController::index",
  "archivo_controller": "app/Http/Controllers/Admin/AlquilerController.php",
  
  "tablas_consultadas": [
    "tbl_alquiler",
    "tbl_propiedad",
    "tbl_usuario (inquilino)",
    "tbl_usuario (arrendador)",
    "tbl_contrato"
  ],
  
  "columnas_principales": {
    "tbl_alquiler": "id_alquiler, id_propiedad_fk, id_inquilino_fk, estado_alquiler, fecha_inicio_alquiler, fecha_fin_alquiler",
    "tbl_propiedad": "titulo_propiedad, ciudad_propiedad, precio_propiedad",
    "inquilino": "nombre_usuario, email_usuario, telefono_usuario",
    "arrendador": "nombre_usuario, email_usuario, telefono_usuario",
    "tbl_contrato": "url_pdf_contrato"
  },
  
  "joins": [
    {
      "tabla1": "tbl_alquiler",
      "tabla2": "tbl_propiedad",
      "condicion": "tbl_propiedad.id_propiedad = tbl_alquiler.id_propiedad_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_alquiler",
      "tabla2": "tbl_usuario (inquilino)",
      "condicion": "inquilino.id_usuario = tbl_alquiler.id_inquilino_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_propiedad",
      "tabla2": "tbl_usuario (arrendador)",
      "condicion": "arrendador.id_usuario = tbl_propiedad.id_arrendador_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_alquiler",
      "tabla2": "tbl_contrato",
      "condicion": "c.id_alquiler_fk = tbl_alquiler.id_alquiler",
      "tipo": "LEFT JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "71-...",
      "descripcion": "Lista alquileres paginados (10/página) con toda información relacionada. Valida disponibilidad PDF",
      "retorna": "view('admin.alquileres', paginate(10))"
    },
    {
      "metodo": "nueva()",
      "descripcion": "GET /admin/alquileres/nueva → Muestra formulario para crear alquiler",
      "datos_pasados": "propiedadesPublicadas (estado='publicada'), inquilinos (rol='inquilino')"
    },
    {
      "metodo": "editar($id)",
      "descripcion": "GET /admin/alquileres/{id}/editar → Muestra formulario para editar"
    },
    {
      "metodo": "crear(Request $request)",
      "descripcion": "POST /admin/alquileres/crear → Crea alquiler nuevo"
    },
    {
      "metodo": "descargarContrato($id)",
      "descripcion": "GET /admin/alquileres/{id}/descargar-contrato → Descarga PDF del contrato"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "buscadorAlq",
      "busca_en": "propiedad, inquilino"
    },
    {
      "nombre": "Estado",
      "campo": "selectEstadoAlq",
      "valores": "activo, pendiente, finalizado, rechazado"
    },
    {
      "nombre": "Propiedad",
      "campo": "selectPropiedadAlq",
      "dinamico": "true (cargado de tbl_propiedad)"
    },
    {
      "nombre": "Mes",
      "campo": "selectMesAlq",
      "valores": "1-12 (enero-diciembre)"
    }
  ],
  
  "botones_acciones": [
    "Ver contrato PDF (GET /admin/alquileres/{id}/descargar-contrato)",
    "Crear alquiler (GET /admin/alquileres/nueva)"
  ],
  
  "datos_pasados_vista": [
    "alquileres: Paginated(10) con informacióncompleta",
    "propiedades: Collection de tbl_propiedad (para filtro dinámico)"
  ],
  
  "flujo_resumido": "Admin → GET /admin/alquileres → AlquilerController::index() → Query múltiples JOINs → Pagina 10/página → Valida PDFs existentes → Retorna vista con tabla filtrable",
  
  "puntos_importantes": [
    "Valida disponibilidad PDF en disco: revisa public/, storage/app/public/",
    "Propiedades dinámicas cargadas en vista para selector",
    "Ordenamiento por fecha_inicio_alquiler (no especificado, probablemente reciente primero)",
    "No permite crear alquiler desde esta vista, solo desde formulario"
  ]
}
```

---

## 7. ALQUILERES CREAR/EDITAR

```json
{
  "vista": "admin/alquileres-crear.blade.php",
  "ruta_get_crear": "/admin/alquileres/nueva",
  "ruta_post_crear": "/admin/alquileres/crear",
  "ruta_get_editar": "/admin/alquileres/{id}/editar",
  "ruta_post_editar": "/admin/alquileres/{id}/actualizar",
  "controller": "AlquilerController::nueva | crear | editar | actualizar",
  "archivo_controller": "app/Http/Controllers/Admin/AlquilerController.php",
  
  "tablas_consultadas": [
    "tbl_propiedad (estado='publicada')",
    "tbl_usuario (rol='inquilino')",
    "tbl_alquiler"
  ],
  
  "metodos_controller": [
    {
      "metodo": "nueva()",
      "linea": "7-22",
      "descripcion": "Retorna vista en blanco con propiedades publicadas e inquilinos disponibles",
      "retorna": "view('admin.alquileres-crear', compact('propiedadesPublicadas', 'inquilinos'))"
    },
    {
      "metodo": "editar($id)",
      "linea": "27-50",
      "descripcion": "Obtiene alquiler existente y datos relacionados",
      "retorna": "view('admin.alquileres-crear', compact(..., 'alquiler'))"
    },
    {
      "metodo": "crear(Request $request)",
      "linea": "...",
      "descripcion": "Crea alquiler nuevo con validaciones"
    },
    {
      "metodo": "actualizar(Request $request, $id)",
      "linea": "...",
      "descripcion": "Actualiza alquiler existente"
    }
  ],
  
  "campos_formulario": {
    "id_propiedad": "select|required",
    "id_inquilino": "select|required",
    "fecha_inicio_alquiler": "date|required",
    "fecha_fin_alquiler": "date|nullable",
    "estado_alquiler": "select|activo|pendiente|finalizado|rechazado"
  },
  
  "flujo_resumido": "GET /admin/alquileres/nueva → Vista vacía + propiedades publicadas e inquilinos | POST /admin/alquileres/crear → Valida, INSERT, redirecciona",
  
  "puntos_importantes": [
    "Solo muestra propiedades con estado='publicada'",
    "Solo muestra inquilinos con rol='inquilino'",
    "propiedadesPublicadas incluye precio_propiedad como referencia"
  ]
}
```

---

## 8. INCIDENCIAS

```json
{
  "vista": "admin/incidencias.blade.php",
  "ruta_get": "/admin/incidencias",
  "controller": "IncidenciaController::index",
  "archivo_controller": "app/Http/Controllers/Admin/IncidenciaController.php",
  
  "tablas_consultadas": [
    "tbl_incidencia",
    "tbl_propiedad",
    "tbl_usuario (reporta/inquilino)",
    "tbl_usuario (asignado/gestor)",
    "tbl_usuario (arrendador)",
    "tbl_categoria",
    "tbl_historial_incidencia"
  ],
  
  "columnas_principales": {
    "tbl_incidencia": "id_incidencia, titulo_incidencia, estado_incidencia, prioridad_incidencia, creado_incidencia, actualizado_incidencia, id_reporta_fk, id_asignado_fk, id_categoria_fk",
    "tbl_propiedad": "titulo_propiedad, ciudad_propiedad, dirección (CONCAT_WS)",
    "reporta (inquilino)": "nombre_usuario, email_usuario",
    "asignado (gestor)": "nombre_usuario",
    "arrendador": "nombre_usuario, email_usuario",
    "tbl_categoria": "nombre_categoria"
  },
  
  "joins": [
    {
      "tabla1": "tbl_incidencia",
      "tabla2": "tbl_propiedad",
      "condicion": "tbl_propiedad.id_propiedad = tbl_incidencia.id_propiedad_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_incidencia",
      "tabla2": "tbl_usuario (reporta)",
      "condicion": "reporta.id_usuario = tbl_incidencia.id_reporta_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_incidencia",
      "tabla2": "tbl_usuario (asignado)",
      "condicion": "asignado.id_usuario = tbl_incidencia.id_asignado_fk",
      "tipo": "LEFT JOIN"
    },
    {
      "tabla1": "tbl_propiedad",
      "tabla2": "tbl_usuario (arrendador)",
      "condicion": "arrendador.id_usuario = tbl_propiedad.id_arrendador_fk",
      "tipo": "LEFT JOIN"
    },
    {
      "tabla1": "tbl_incidencia",
      "tabla2": "tbl_categoria",
      "condicion": "tbl_categoria.id_categoria = tbl_incidencia.id_categoria_fk",
      "tipo": "LEFT JOIN"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "12-140",
      "descripcion": "Agrupa incidencias por estado (abierta, esperando_decision, esperando_pago, solucionada, resuelta). Marca inactividad (>14 días sin cambios)",
      "agrupa": "5 colecciones según estado_incidencia",
      "marca_inactividad": "actualizado_incidencia < ahora - 14 días = inactivo=true",
      "retorna": "view('admin.incidencias', compact(...))"
    },
    {
      "metodo": "show($id)",
      "linea": "179-250",
      "descripcion": "Retorna JSON detalle incidencia con historial y encargado_pago",
      "joins_adicionales": "tbl_historial_incidencia, múltiples búsquedas de usuario encargado_pago",
      "logica_encargado": "Si estado='esperando_pago': preferir gestor asignado, si no, arrendador. Otros: solo asignado si existe"
    },
    {
      "metodo": "cambiarEstado(Request $request, $id)",
      "linea": "252-...",
      "descripcion": "Actualiza estado incidencia e inserta en historial",
      "transaccion": "SÍ",
      "inserta": "tbl_historial_incidencia con comentario y cambio_estado"
    },
    {
      "metodo": "asignar(Request $request, $id)",
      "descripcion": "Asigna incidencia a un gestor"
    },
    {
      "metodo": "filtrar(Request $request)",
      "descripcion": "Filtra incidencias por estado, búsqueda, etc. Retorna JSON"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "buscadorInc",
      "busca_en": "titulo_incidencia, titulo_propiedad, ciudad, categoría, nombres"
    },
    {
      "nombre": "Categoría",
      "campo": "selectCategoria",
      "dinamico": "true (cargado de tbl_categoria)"
    },
    {
      "nombre": "Prioridad",
      "campo": "selectPrioridad",
      "valores": "urgente, alta, media, baja"
    },
    {
      "nombre": "Propiedad",
      "campo": "selectPropiedad",
      "dinamico": "true (cargado de tbl_propiedad)"
    }
  ],
  
  "botones_acciones": [
    "Ver detalle (GET /admin/incidencias/{id} → JSON)",
    "Cambiar estado (POST /admin/incidencias/{id}/estado)",
    "Asignar gestor (POST /admin/incidencias/{id}/asignar)",
    "Contactar (POST /admin/incidencias/{id}/contactar)"
  ],
  
  "datos_pasados_vista": [
    "abiertas: Collection (incidencias estado='abierta')",
    "esperandoDecision: Collection",
    "esperandoPago: Collection",
    "solucionadas: Collection",
    "resueltas: Collection",
    "totalAbiertas, totalEsperandoDecision, etc: int",
    "urgentes: int (prioridad='urgente' AND estado activo)",
    "gestores: Collection (usuarios con rol='gestor')",
    "propiedades: Collection (dinámica para filtro)",
    "inquilinos: Collection (usuarios con rol='inquilino')",
    "categorias: Collection (estado='activa')"
  ],
  
  "flujo_resumido": "Admin → GET /admin/incidencias → IncidenciaController::index() → Agrupa por estado (5 queries clonadas) → Marca inactividad → Carga filtros dinámicos (gestores, propiedades, inquilinos, categorías) → Retorna vista con 5 columnas de estados",
  
  "puntos_importantes": [
    "Marca inactividad automáticamente en memoria (no en BD): si actualizado_incidencia < 14 días",
    "Estados: abierta, esperando_decision, esperando_pago, solucionada, resuelta (5 grupos independientes)",
    "Encargado de pago: lógica compleja según estado",
    "Historial de cambios registrado en tbl_historial_incidencia"
  ]
}
```

---

## 9. SUSCRIPCIONES

```json
{
  "vista": "admin/suscripciones.blade.php",
  "ruta_get": "/admin/suscripciones",
  "controller": "SuscripcionController::index",
  "archivo_controller": "app/Http/Controllers/Admin/SuscripcionController.php",
  
  "tablas_consultadas": [
    "tbl_suscripcion",
    "tbl_usuario",
    "tbl_propiedad"
  ],
  
  "columnas_principales": {
    "tbl_suscripcion": "id_suscripcion, id_usuario_fk, plan_suscripcion, estado_suscripcion, precio_pagado_suscripcion, max_propiedades_suscripcion, inicio_suscripcion, fin_suscripcion, creado_suscripcion",
    "tbl_usuario": "nombre_usuario, email_usuario",
    "tbl_propiedad": "COUNT(*) as propiedades_usadas (estado_propiedad != 'inactiva')"
  },
  
  "joins": [
    {
      "tabla1": "tbl_suscripcion",
      "tabla2": "tbl_usuario",
      "condicion": "tbl_usuario.id_usuario = tbl_suscripcion.id_usuario_fk",
      "tipo": "INNER JOIN"
    },
    {
      "tabla1": "tbl_suscripcion",
      "tabla2": "tbl_propiedad (subconsulta)",
      "condicion": "subconsulta COUNT WHERE id_arrendador_fk = id_usuario_fk",
      "tipo": "SUBQUERY"
    }
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "6-80",
      "descripcion": "Lista suscripciones paginadas (10/página) con conteos y cálculos KPI. Incluye próximas a expirar",
      "retorna": "view('admin.suscripciones', compact(...))"
    },
    {
      "metodo": "show($id)",
      "linea": "85-120",
      "descripcion": "Retorna JSON detalle suscripción + propiedades relacionadas"
    },
    {
      "metodo": "filtrar(Request $request)",
      "linea": "125-...",
      "descripcion": "Filtra suscripciones por plan y estado. Retorna JSON"
    }
  ],
  
  "calculos_kpi": {
    "totalActivas": "COUNT(estado_suscripcion='activa')",
    "totalPro": "COUNT(plan_suscripcion='pro' AND estado='activa')",
    "totalBasico": "COUNT(plan_suscripcion='basico' AND estado='activa')",
    "totalGratuito": "COUNT(plan_suscripcion='gratuito' AND estado='activa')",
    "totalExpiradas": "COUNT(estado_suscripcion='expirada')",
    "pctPro": "round(totalPro / (totalPro+totalBasico+totalGratuito) * 100)",
    "pctBasico": "round(totalBasico / ... * 100)",
    "pctGratuito": "round(totalGratuito / ... * 100)",
    "ingresosMes": "(totalPro * 29.99) + (totalBasico * 9.99)"
  },
  
  "filtros": [
    {
      "nombre": "Plan",
      "campo": "selectPlanSus",
      "valores": "pro, basico, gratuito"
    },
    {
      "nombre": "Estado",
      "campo": "selectEstadoSus",
      "valores": "activa, expirada, cancelada"
    },
    {
      "nombre": "Búsqueda",
      "campo": "buscadorSus",
      "busca_en": "nombre_usuario, email_usuario"
    }
  ],
  
  "botones_acciones": [
    "Exportar (btnExportarSus)"
  ],
  
  "datos_pasados_vista": [
    "suscripciones: Paginated(10)",
    "totalActivas, totalPro, totalBasico, totalGratuito, totalExpiradas: int",
    "pctPro, pctBasico, pctGratuito: int (porcentajes)",
    "precioPro: 29.99, precioBasico: 9.99",
    "ingresosMes: float",
    "proximasExpirar: Collection(5) con suscripciones próximas a expirar o expiradas"
  ],
  
  "flujo_resumido": "Admin → GET /admin/suscripciones → SuscripcionController::index() → Query paginado con subconsulta de propiedades → Calcula KPIs y porcentajes → Obtiene próximas a expirar (< 30 días) → Retorna vista",
  
  "puntos_importantes": [
    "Subconsulta de propiedades: excluye estado='inactiva'",
    "Próximas a expirar: estado='expirada' OR (estado='activa' AND fin_suscripcion <= ahora+30 días)",
    "Precios hardcodeados: 29.99 (pro), 9.99 (básico), 0 (gratuito)",
    "Ordenamiento: por creado_suscripcion DESC"
  ]
}
```

---

## 10. PLANES (Gestión)

```json
{
  "vista": "admin/planes.blade.php",
  "ruta_get": "/admin/planes",
  "controller": "ConfiguracionController::planes",
  "archivo_controller": "app/Http/Controllers/Admin/ConfiguracionController.php",
  
  "tablas_consultadas": [
    "tbl_plan"
  ],
  
  "columnas_principales": {
    "tbl_plan": "id_plan, nombre_plan, slug_plan, rol_destino, precio_plan, max_propiedades_plan, descripcion_plan, activo_plan"
  },
  
  "metodos_controller": [
    {
      "metodo": "planes()",
      "linea": "88-92",
      "descripcion": "Obtiene todos los planes ordenados por id",
      "retorna": "view('admin.planes', compact('planes'))"
    },
    {
      "metodo": "crearPlan(Request $request)",
      "linea": "94-122",
      "descripcion": "Crea nuevo plan con validaciones",
      "validaciones": [
        "nombre_plan: required|max:50",
        "slug_plan: required|max:30|unique",
        "rol_destino: miembro|arrendador|inquilino|gestor",
        "precio_plan: required|numeric|min:0",
        "max_propiedades_plan: required|integer|min:0|max:255",
        "descripcion_plan: nullable|string",
        "activo_plan: boolean"
      ],
      "transaccion": "NO",
      "inserta": "INSERT INTO tbl_plan"
    },
    {
      "metodo": "actualizarPlan(Request $request, $id)",
      "linea": "124-...",
      "descripcion": "Actualiza plan existente"
    },
    {
      "metodo": "eliminarPlan(Request $request, $id)",
      "descripcion": "Elimina plan"
    }
  ],
  
  "filtros": [],
  
  "botones_acciones": [
    "Crear plan",
    "Editar plan (Modal)",
    "Eliminar plan (Modal confirmación)",
    "Toggle activo/inactivo"
  ],
  
  "datos_pasados_vista": [
    "planes: Collection (todas columnas de tbl_plan)"
  ],
  
  "flujo_resumido": "Admin → GET /admin/planes → ConfiguracionController::planes() → SELECT * FROM tbl_plan → Retorna vista con tabla editable",
  
  "ruta_crear_plan": "POST /admin/planes/crear",
  "ruta_actualizar_plan": "POST /admin/planes/{id}/actualizar",
  "ruta_eliminar_plan": "POST /admin/planes/{id}/eliminar",
  
  "puntos_importantes": [
    "Slug único en BD: no puede repetirse",
    "Rol destino: determina a qué tipo de usuario aplica el plan",
    "Max propiedades: 0 = ilimitado, >0 = límite exacto",
    "Activo: boolean, controla si es visible para usuarios"
  ]
}
```

---

## 11. CONFIGURACIÓN (Notificaciones)

```json
{
  "vista": "admin/configuracion.blade.php",
  "ruta_get": "/admin/configuracion",
  "ruta_post": "/admin/configuracion/notificaciones",
  "controller": "ConfiguracionController::index | crearNotificacion",
  "archivo_controller": "app/Http/Controllers/Admin/ConfiguracionController.php",
  
  "tablas_consultadas": [
    "tbl_usuario",
    "tbl_rol_usuario",
    "tbl_rol",
    "tbl_notificacion"
  ],
  
  "columnas_principales": {
    "tbl_usuario": "id_usuario, nombre_usuario, email_usuario, activo_usuario",
    "tbl_rol": "slug_rol, nombre_rol"
  },
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "8-37",
      "descripcion": "Obtiene usuarios activos con sus roles y roles disponibles en BD",
      "retorna": "view('admin.configuracion', compact('usuariosActivos', 'rolesDisponibles'))"
    },
    {
      "metodo": "crearNotificacion(Request $request)",
      "linea": "39-117",
      "descripcion": "Crea notificación importante y la envía a usuarios según destino/alcance",
      "validaciones": [
        "destino: required|in(todos, slugs de roles)",
        "alcance_destino: required|in(todos, usuario)",
        "usuario_destino: nullable|integer|exists:tbl_usuario",
        "titulo_notificacion: required|max:200",
        "mensaje_notificacion: required|max:1000",
        "url_notificacion: nullable|max:500"
      ],
      "logica": [
        "Si alcance='todos': envía a todos usuarios de ese rol (o todos los usuarios si destino='todos')",
        "Si alcance='usuario': envía solo al usuario seleccionado (validando que tenga el rol)",
        "Valida que usuario destino exista y esté activo",
        "Valida que usuario tenga el rol seleccionado (si destino != 'todos')"
      ],
      "transaccion": "NO (usa ServicioActividad)",
      "metodo_envio": "actividadService->avisoImportante($usuarioId, titulo, mensaje, url)"
    }
  ],
  
  "campos_formulario": {
    "destino": "select|required|todos o slug rol",
    "alcance_destino": "select|required|todos o usuario",
    "usuario_destino": "select|nullable (mostrado si alcance='usuario')",
    "titulo_notificacion": "text|required|max:200",
    "mensaje_notificacion": "textarea|required|max:1000",
    "url_notificacion": "text|nullable|max:500"
  },
  
  "flujo_resumido": [
    "GET /admin/configuracion → Obtiene usuarios activos y roles → Retorna vista",
    "POST /admin/configuracion/notificaciones → Valida datos → Determina usuarios destino → Envía notificación a todos → Redirecciona"
  ],
  
  "datos_pasados_vista": [
    "usuariosActivos: Collection con nombre, email, roles_usuario (GROUP_CONCAT)",
    "rolesDisponibles: Collection (slug_rol, nombre_rol)"
  ],
  
  "puntos_importantes": [
    "Roles usuarios obtenidos con GROUP_CONCAT en subconsulta",
    "Validación de pertenencia a rol: evita enviar notificación a usuario que no tiene ese rol",
    "ActividadService::avisoImportante() inserta en tbl_notificacion"
  ]
}
```

---

## 12. ASESORÍA (Categorías)

```json
{
  "vista": "admin/asesoria.blade.php",
  "ruta_get": "/admin/asesoria",
  "ruta_get_filtrar": "/admin/asesoria/filtrar",
  "ruta_post": "/admin/asesoria/categoria/crear",
  "controller": "AsesoriaController::index | filtrar | store | toggleEstado | edit | update | destroy",
  "archivo_controller": "app/Http/Controllers/Admin/AsesoriaController.php",
  
  "modelo": "CategoriaArticulo (Laravel Model)",
  
  "tablas_consultadas": [
    "tbl_asesoria_categoria",
    "tbl_asesoria_articulo"
  ],
  
  "columnas_principales": {
    "tbl_asesoria_categoria": "id, nombre, slug, icono, orden, estado",
    "tbl_asesoria_articulo": "COUNT(*), COUNT(destacado=true)"
  },
  
  "relaciones_modelo": [
    "articulos: hasMany(ArticuloAsesoria)",
    "articulos_count: withCount(['articulos'])",
    "destacados_count: withCount(['articulos as destacados_count' => función con where destacado=true])"
  ],
  
  "metodos_controller": [
    {
      "metodo": "index()",
      "linea": "8-14",
      "descripcion": "Obtiene orden siguiente e retorna vista",
      "retorna": "view('admin.asesoria', compact('nextOrden'))"
    },
    {
      "metodo": "filtrar(Request $request)",
      "linea": "16-57",
      "descripcion": "Filtra categorías con búsqueda, estado, ordenamientos. Retorna JSON paginado",
      "parametros_request": "q (búsqueda), estado (0/1), sort (orden/nombre/slug/articulos), direction (asc/desc), per_page (10/20/50/0=todos)",
      "retorna": "response()->json(paginate)"
    },
    {
      "metodo": "store(Request $request)",
      "linea": "59-84",
      "descripcion": "Crea categoría nueva. Desplaza órdenes de otras categorías si es necesario",
      "validaciones": [
        "nombre: required|max:255",
        "slug: required|max:255|unique:tbl_asesoria_categoria,slug",
        "icono: required|max:50",
        "orden: required|integer|min:1"
      ],
      "logica": "Si orden ya existe, incrementa orden de categorías >= a esa orden",
      "transaccion": "NO (usa incremento automático)",
      "inserta": "INSERT INTO tbl_asesoria_categoria",
      "retorna": "JSON {message, categoria}"
    },
    {
      "metodo": "toggleEstado($id)",
      "linea": "86-102",
      "descripcion": "Alterna estado de categoría (0/1) y retorna JSON",
      "transaccion": "NO",
      "retorna": "JSON {success, message, estado, destacados_count}"
    },
    {
      "metodo": "edit($id)",
      "linea": "104-113",
      "descripcion": "Retorna JSON con categoría y maxOrden para edición"
    },
    {
      "metodo": "update(Request $request, $id)",
      "linea": "115-149",
      "descripcion": "Actualiza categoría con reordenamiento si es necesario"
    },
    {
      "metodo": "destroy($id)",
      "descripcion": "Elimina categoría"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Búsqueda",
      "campo": "filtro-busqueda",
      "busca_en": "nombre"
    },
    {
      "nombre": "Estado",
      "campo": "filtro-estado",
      "valores": "todos, 1 (activo), 0 (inactivo)"
    },
    {
      "nombre": "Número de resultados",
      "campo": "filtro-paginacion",
      "valores": "10, 20, 50, 0 (todos)"
    }
  ],
  
  "botones_acciones": [
    "Nueva categoría (modal)",
    "Editar (modal)",
    "Toggle estado",
    "Eliminar (confirmación)",
    "Limpiar filtros"
  ],
  
  "datos_pasados_vista": [
    "nextOrden: int (orden siguiente disponible)"
  ],
  
  "llamadas_ajax": [
    "GET /admin/asesoria/filtrar → Obtiene tabla con paginación",
    "POST /admin/asesoria/categoria/crear → Crea categoría",
    "POST /admin/asesoria/categoria/{id}/toggle-estado → Alterna estado",
    "GET /admin/asesoria/categoria/{id}/editar → Obtiene datos para modal edición",
    "POST /admin/asesoria/categoria/{id}/actualizar → Actualiza",
    "DELETE /admin/asesoria/categoria/{id}/eliminar → Elimina"
  ],
  
  "flujo_resumido": "Admin → GET /admin/asesoria → Carga vista con nextOrden → Usuario filtra/crea/edita via AJAX → Respuestas JSON actualizan tabla",
  
  "puntos_importantes": [
    "Uso de modelos Laravel (no Query Builder puro)",
    "withCount() para contar artículos y destacados relacionados",
    "Reordenamiento inteligente: si cambias orden, desplaza otras automáticamente",
    "Estado booleano: true/false en BD (migración debe ser boolean)",
    "Slug único en BD: validación en controller y modelo"
  ]
}
```

---

## 13. ASESORÍA ARTÍCULOS

```json
{
  "vista": "admin/asesoria-articulos.blade.php",
  "ruta_get": "/admin/asesoria/articulos",
  "ruta_get_filtrar": "/admin/asesoria/articulos/filtrar",
  "ruta_post": "/admin/asesoria/articulos/crear",
  "ruta_post_toggle_estado": "/admin/asesoria/articulos/{articulo}/toggle-estado",
  "ruta_post_toggle_destacado": "/admin/asesoria/articulos/{articulo}/toggle-destacado",
  "controller": "AsesoriaController::articulos | filtrarArticulos | storeArticulo | toggleEstadoArticulo | toggleDestacadoArticulo | ...",
  "archivo_controller": "app/Http/Controllers/Admin/AsesoriaController.php",
  
  "modelo": "ArticuloAsesoria (Laravel Model) + CategoriaArticulo",
  
  "tablas_consultadas": [
    "tbl_asesoria_articulo",
    "tbl_asesoria_categoria"
  ],
  
  "columnas_principales": {
    "tbl_asesoria_articulo": "id, id_categoria_fk, titulo, contenido, estado, destacado, orden_faq, creado_articulo, actualizado_articulo",
    "tbl_asesoria_categoria": "id, nombre"
  },
  
  "metodos_controller": [
    {
      "metodo": "articulos()",
      "linea": "...",
      "descripcion": "Carga vista con lista de categorías para selector",
      "retorna": "view('admin.asesoria-articulos', compact('categorias'))"
    },
    {
      "metodo": "filtrarArticulos(Request $request)",
      "descripcion": "Filtra artículos por categoría, búsqueda, estado, destacado. Retorna JSON paginado"
    },
    {
      "metodo": "storeArticulo(Request $request)",
      "descripcion": "Crea nuevo artículo con validaciones"
    },
    {
      "metodo": "toggleEstadoArticulo($articulo)",
      "descripcion": "Alterna estado del artículo"
    },
    {
      "metodo": "toggleDestacadoArticulo($articulo)",
      "descripcion": "Alterna estado destacado del artículo"
    },
    {
      "metodo": "maxOrdenFaq()",
      "descripcion": "Retorna orden máximo actual en destacados (para orden_faq)"
    }
  ],
  
  "filtros": [
    {
      "nombre": "Categoría",
      "campo": "filtro-categoria",
      "dinamico": "true"
    },
    {
      "nombre": "Búsqueda",
      "campo": "filtro-busqueda",
      "busca_en": "titulo"
    },
    {
      "nombre": "Estado",
      "campo": "filtro-estado",
      "valores": "todos, 1 (activo), 0 (inactivo)"
    },
    {
      "nombre": "Destacado",
      "campo": "filtro-destacado",
      "valores": "todos, 1 (solo destacados), 0 (no destacados)"
    },
    {
      "nombre": "Número de resultados",
      "campo": "filtro-paginacion",
      "valores": "10, 20, 50, 0 (todos)"
    }
  ],
  
  "botones_acciones": [
    "Nuevo artículo (modal con TinyMCE)",
    "Editar (modal)",
    "Toggle estado",
    "Toggle destacado",
    "Eliminar",
    "Limpiar filtros"
  ],
  
  "columnas_tabla": [
    "Categoría",
    "Orden",
    "Título",
    "Contenido (preview)",
    "Estado",
    "Destacado",
    "Orden Destacado (solo si destacado=true)",
    "Acciones"
  ],
  
  "flujo_resumido": "Admin → GET /admin/asesoria/articulos → Carga vista + categorías dinámicas → Usuario filtra/crea/edita via AJAX → Respuestas JSON actualizan tabla",
  
  "puntos_importantes": [
    "TinyMCE cargado en vista para edición HTML de contenido",
    "orden_faq: solo se usa si destacado=true",
    "Dos campos de orden: 'orden' (en categoría) y 'orden_faq' (en FAQ)",
    "Estado y destacado: booleanos independientes",
    "Contenido puede ser HTML complejo (guardado en tbl_asesoria_articulo)"
  ]
}
```

---

## RESUMEN GENERAL

### Rutas GET (Listados)

| Vista | Ruta | Controller::Metodo | Paginación |
|-------|------|-------------------|-----------|
| dashboard | GET /admin/dashboard | DashboardController::index | NO |
| usuarios | GET /admin/usuarios | UsuarioController::index | SÍ (10) |
| solicitudes | GET /admin/solicitudes | SolicitudController::index | SÍ (7) |
| propiedades | GET /admin/propiedades | PropiedadController::index | SÍ (?) |
| alquileres | GET /admin/alquileres | AlquilerController::index | SÍ (10) |
| incidencias | GET /admin/incidencias | IncidenciaController::index | NO (grupos) |
| suscripciones | GET /admin/suscripciones | SuscripcionController::index | SÍ (10) |
| planes | GET /admin/planes | ConfiguracionController::planes | NO |
| configuracion | GET /admin/configuracion | ConfiguracionController::index | NO |
| asesoria (categorías) | GET /admin/asesoria | AsesoriaController::index | AJAX |
| asesoria-articulos | GET /admin/asesoria/articulos | AsesoriaController::articulos | AJAX |

### Transacciones (DB::beginTransaction)

- SolicitudController::aprobar() - Crea rol + suscripción
- IncidenciaController::cambiarEstado() - Actualiza estado + historial

### Subconsultas/JOINs complejos

- UsuarioController::index - Roles + Propiedades (2 subconsultas)
- PropiedadController::index - Arrendador + estado grupos
- AlquilerController::index - 4 JOINs (propiedad, inquilino, arrendador, contrato)
- IncidenciaController::index - 5 JOINs + clonación de query

### Campos JSON/Normalizados

- NINGÚN campo JSON en tablas
- Dirección formateada con CONCAT_WS en SELECT (no en vista)
- Roles combinados con GROUP_CONCAT en subconsulta

---

**Documento generado**: 27 de mayo de 2026
**Proyecto**: SpotStay - Panel administrativo
**Versión BD**: Última migración applicada
