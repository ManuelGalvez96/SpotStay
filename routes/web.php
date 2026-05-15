<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Miembro\HomeController;
use App\Http\Controllers\Miembro\DetallePropiedadController;
use App\Http\Controllers\Miembro\MapaController;
use App\Http\Controllers\Miembro\SolicitudAlquilerController;
use App\Http\Controllers\Miembro\MensajesController;
use App\Http\Controllers\Miembro\SolicitudArrendadorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PropiedadController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\Admin\IncidenciaController;
use App\Http\Controllers\Admin\AlquilerController;
use App\Http\Controllers\Admin\SuscripcionController;
use App\Http\Controllers\Admin\CodigoGestorController;
use App\Http\Controllers\Admin\CodigoPropiedadController;
use App\Http\Controllers\inquilino\InquilinoController;
use App\Http\Controllers\inquilino\InquilinoIncidenciaController;
use App\Http\Controllers\inquilino\InquilinoPagoController;
use App\Http\Controllers\inquilino\InquilinoGastosController;
use App\Http\Controllers\Arrendador\DashboardController as ArrendadorDashboardController;
use App\Http\Controllers\Arrendador\PropiedadController as ArrendadorPropiedadController;
use App\Http\Controllers\Arrendador\SolicitudController as ArrendadorSolicitudController;
use App\Http\Controllers\Arrendador\PrecioGastoController as ArrendadorPrecioGastoController;
use App\Http\Controllers\Arrendador\InquilinoController as ArrendadorInquilinoController;
use App\Http\Controllers\Arrendador\MensajeController as ArrendadorMensajeController;
use App\Http\Controllers\Arrendador\ContratoController as ArrendadorContratoController;
use App\Http\Controllers\Arrendador\GestorController as ArrendadorGestorController;
use App\Http\Controllers\Arrendador\IncidenciaController as ArrendadorIncidenciaController;
use App\Http\Controllers\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Gestor\IncidenciaController as GestorIncidenciaController;
use App\Http\Controllers\Gestor\PropiedadController as GestorPropiedadController;
use App\Http\Controllers\Arrendador\ConfiguracionCobrosController;

// Rutas Públicas
Route::get('/', function () {
    return view('inicio');
});
Route::get('/logout', [AuthController::class, 'logout']);

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/admin/usuarios/check-email', [AuthController::class, 'checkEmail']);
Route::get('/admin/usuarios/check-telefono', [AuthController::class, 'checkTelefono']);
Route::get('/admin/usuarios/check-dni', [AuthController::class, 'checkDni']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['role:admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/configuracion', function () {
        return view('admin.configuracion');
    });
    Route::post('/admin/alquiler/{id}/aprobar', [DashboardController::class, 'aprobarAlquiler']);
    Route::post('/admin/alquiler/{id}/rechazar', [DashboardController::class, 'rechazarAlquiler']);

    // Usuarios
    Route::get('/admin/usuarios', [UsuarioController::class, 'index']);
    Route::get('/admin/usuarios/filtrar', [UsuarioController::class, 'filtrar']);
    Route::get('/admin/usuarios/kpis', [UsuarioController::class, 'getKpisUsuarios']);
    Route::get('/admin/usuarios/exportar', [UsuarioController::class, 'exportar']);
    Route::get('/admin/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('/admin/usuarios/crear', [UsuarioController::class, 'crear']);
    Route::post('/admin/usuarios/{id}/editar', [UsuarioController::class, 'editar']);
    Route::post('/admin/usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado']);

    // Propiedades
    Route::get('/admin/propiedades', [PropiedadController::class, 'index']);
    Route::get('/admin/propiedades/nueva', [PropiedadController::class, 'nueva']);
    Route::post('/admin/propiedades/crear', [PropiedadController::class, 'crear']);
    Route::get('/admin/propiedades/filtrar', [PropiedadController::class, 'filtrar']);
    Route::get('/admin/propiedades/{id}/editar', [PropiedadController::class, 'editar']);
    Route::post('/admin/propiedades/{id}/editar', [PropiedadController::class, 'actualizar']);
    Route::get('/admin/propiedades/{id}', [PropiedadController::class, 'show']);
    Route::delete('/admin/propiedades/{id}', [PropiedadController::class, 'eliminar']);
    Route::post('/admin/propiedades/{id}/desactivar', [PropiedadController::class, 'desactivar']);
    Route::post('/admin/propiedades/{id}/publicar', [PropiedadController::class, 'publicar']);
    Route::get('/admin/propiedades/{id}/descargar-pdf', [PropiedadController::class, 'descargarPdf']);
    Route::get('/admin/propiedades/exportar', [PropiedadController::class, 'exportar']);

    // Solicitudes
    Route::get('/admin/solicitudes', [SolicitudController::class, 'index']);
    Route::get('/admin/solicitudes/filtrar', [SolicitudController::class, 'filtrar']);
    Route::get('/admin/solicitudes/kpis', [SolicitudController::class, 'getKpisSolicitudes']);
    Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show']);
    Route::post('/admin/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::post('/admin/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar']);

    // Incidencias
    Route::get('/admin/incidencias', [IncidenciaController::class, 'index']);
    Route::get('/admin/incidencias/filtrar', [IncidenciaController::class, 'filtrar']);
    Route::get('/admin/incidencias/kpis', [IncidenciaController::class, 'getKpisIncidencias']);
    Route::get('/admin/incidencias/{id}', [IncidenciaController::class, 'show']);
    Route::post('/admin/incidencias/{id}/estado', [IncidenciaController::class, 'cambiarEstado']);
    Route::post('/admin/incidencias/{id}/asignar', [IncidenciaController::class, 'asignar']);

    // Códigos de Gestor
    Route::get('/admin/codigos-gestores', [CodigoGestorController::class, 'index']);
    Route::post('/admin/codigos-gestores/generar', [CodigoGestorController::class, 'generar']);
    Route::post('/admin/codigos-gestores/cancelar', [CodigoGestorController::class, 'cancelar']);
    Route::post('/admin/codigos-gestores/validar', [CodigoGestorController::class, 'validar']);
    Route::get('/admin/codigos-gestores/{id}', [CodigoGestorController::class, 'show']);

    // Códigos de Propiedad
    Route::get('/admin/codigos-propiedades', [CodigoPropiedadController::class, 'index']);
    Route::post('/admin/codigos-propiedades/generar', [CodigoPropiedadController::class, 'generar']);
    Route::post('/admin/codigos-propiedades/cancelar', [CodigoPropiedadController::class, 'cancelar']);
    Route::post('/admin/codigos-propiedades/validar', [CodigoPropiedadController::class, 'validar']);
    Route::post('/admin/codigos-propiedades/registrar-uso', [CodigoPropiedadController::class, 'registrarUso']);
    Route::get('/admin/codigos-propiedades/{id}', [CodigoPropiedadController::class, 'show']);
    Route::get('/admin/codigos-propiedades/propiedad/{idPropiedad}', [CodigoPropiedadController::class, 'obtenerCodigosDePropiedad']);

    // Alquileres
    Route::get('/admin/alquileres', [AlquilerController::class, 'index']);
    Route::get('/admin/alquileres/nuevo', [AlquilerController::class, 'nueva']);
    Route::get('/admin/alquileres/{id}/editar', [AlquilerController::class, 'editar']);
    Route::get('/admin/alquileres/filtrar', [AlquilerController::class, 'filtrar']);
    Route::get('/admin/alquileres/{id}', [AlquilerController::class, 'show']);
    Route::post('/admin/alquileres/crear', [AlquilerController::class, 'crear']);
    Route::post('/admin/alquileres/{id}/actualizar', [AlquilerController::class, 'actualizar']);
    Route::post('/admin/alquileres/{id}/eliminar', [AlquilerController::class, 'eliminar']);
    Route::post('/admin/alquiler/{id}/aprobar', [AlquilerController::class, 'aprobar']);
    Route::post('/admin/alquiler/{id}/rechazar', [AlquilerController::class, 'rechazar']);

    // Suscripciones
    // Route::get('/admin/suscripciones', [SuscripcionController::class, 'index']);
    // Route::get('/admin/suscripciones/filtrar', [SuscripcionController::class, 'filtrar']);
    // Route::get('/admin/suscripciones/{id}', [SuscripcionController::class, 'show']);
    // Route::post('/admin/suscripciones/{id}/editar', [SuscripcionController::class, 'editar']);
    // Route::post('/admin/suscripciones/{id}/cancelar', [SuscripcionController::class, 'cancelar']);
    // Route::get('/admin/suscripciones/exportar', [SuscripcionController::class, 'exportar']);
});

// Rutas Gestor
Route::middleware(['role:gestor'])->group(function () {
    Route::get('/gestor/dashboard', [GestorDashboardController::class, 'index'])->name('gestor.dashboard');
    Route::get('/gestor/incidencias', [GestorIncidenciaController::class, 'index'])->name('gestor.incidencias');
    Route::get('/gestor/incidencias/{id}', [GestorIncidenciaController::class, 'show'])->name('gestor.incidencias.show');
    Route::get('/gestor/propiedades', [GestorPropiedadController::class, 'index'])->name('gestor.propiedades');
    Route::get('/gestor/propiedades/{id}', [GestorPropiedadController::class, 'show'])->name('gestor.propiedades.show');
    Route::get('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'gastos'])->name('gestor.propiedades.gastos');
    Route::post('/gestor/propiedades/{id}/gastos', [GestorPropiedadController::class, 'storeGasto'])->name('gestor.propiedades.gastos.store');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/editar', [GestorPropiedadController::class, 'updateGasto'])->name('gestor.propiedades.gastos.update');
    Route::post('/gestor/propiedades/{id}/gastos/{gastoId}/eliminar', [GestorPropiedadController::class, 'destroyGasto'])->name('gestor.propiedades.gastos.destroy');
    Route::post('/gestor/propiedades/{id}/gastos/cuotas/{cuotaId}/pagos/{detalleId}', [GestorPropiedadController::class, 'marcarPagoGasto'])->name('gestor.propiedades.gastos.pago');
    Route::post('/gestor/incidencias/{id}/iniciar', [GestorIncidenciaController::class, 'iniciarGestion'])->name('gestor.incidencias.iniciar');
    Route::post('/gestor/incidencias/{id}/estado', [GestorIncidenciaController::class, 'cambiarEstado'])->name('gestor.incidencias.estado');
    Route::post('/gestor/incidencias/{id}/espera', [GestorIncidenciaController::class, 'marcarEspera'])->name('gestor.incidencias.espera');
    Route::post('/gestor/incidencias/{id}/intervencion', [GestorIncidenciaController::class, 'registrarIntervencion'])->name('gestor.incidencias.intervencion');
    Route::post('/gestor/incidencias/{id}/comunicacion', [GestorIncidenciaController::class, 'registrarComunicacion'])->name('gestor.incidencias.comunicacion');
    Route::post('/gestor/incidencias/{id}/documento', [GestorIncidenciaController::class, 'subirDocumento'])->name('gestor.incidencias.documento');
    Route::post('/gestor/incidencias/{id}/presupuesto', [GestorIncidenciaController::class, 'crearPresupuesto'])->name('gestor.incidencias.presupuesto');
});

// Rutas Arrendador
Route::middleware(['role:arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/arrendador/dashboard', [ArrendadorDashboardController::class, 'inicio'])->name('arrendador.dashboard');

    Route::get('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'inicio'])->name('arrendador.propiedades');
    Route::post('/arrendador/propiedades', [ArrendadorPropiedadController::class, 'guardar'])->name('arrendador.propiedades.store');
    Route::get('/arrendador/propiedades/{id}', [ArrendadorPropiedadController::class, 'mostrar'])->name('arrendador.propiedades.show');
    Route::post('/arrendador/propiedades/{id}/estado', [ArrendadorPropiedadController::class, 'alternarEstado'])->name('arrendador.propiedades.estado');

    Route::get('/arrendador/solicitudes', [ArrendadorSolicitudController::class, 'inicio'])->name('arrendador.solicitudes');
    Route::post('/arrendador/solicitudes/{id}/aprobar', [ArrendadorSolicitudController::class, 'aprobar'])->name('arrendador.solicitudes.aprobar');
    Route::post('/arrendador/solicitudes/{id}/rechazar', [ArrendadorSolicitudController::class, 'rechazar'])->name('arrendador.solicitudes.rechazar');
    Route::get('/arrendador/solicitudes/{id}/ver', [ArrendadorSolicitudController::class, 'ver'])->name('arrendador.solicitudes.ver');
    Route::post('/arrendador/solicitudes/{id}/actualizar', [ArrendadorSolicitudController::class, 'actualizar'])->name('arrendador.solicitudes.actualizar');
    Route::post('/arrendador/solicitudes/{id}/eliminar', [ArrendadorSolicitudController::class, 'eliminar'])->name('arrendador.solicitudes.eliminar');

    Route::get('/arrendador/precios-gastos', [ArrendadorPrecioGastoController::class, 'inicio'])->name('arrendador.precios-gastos');
    Route::post('/arrendador/precios-gastos/{id}', [ArrendadorPrecioGastoController::class, 'actualizar'])->name('arrendador.precios-gastos.actualizar');

    Route::get('/arrendador/inquilinos', [ArrendadorInquilinoController::class, 'inicio'])->name('arrendador.inquilinos');
    Route::get('/arrendador/inquilinos/{id}', [ArrendadorInquilinoController::class, 'mostrar'])->name('arrendador.inquilinos.show');

    Route::get('/arrendador/mensajes', [ArrendadorMensajeController::class, 'inicio'])->name('arrendador.mensajes');
    Route::get('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'mostrar'])->name('arrendador.mensajes.show');
    Route::post('/arrendador/mensajes/{id}', [ArrendadorMensajeController::class, 'enviar'])->name('arrendador.mensajes.enviar');

    Route::get('/arrendador/contratos', [ArrendadorContratoController::class, 'inicio'])->name('arrendador.contratos');
    Route::post('/arrendador/contratos/{id}/firmar', [ArrendadorContratoController::class, 'firmarArrendador'])->name('arrendador.contratos.firmar');
    Route::get('/arrendador/contratos/{id}/descargar-pdf', [ArrendadorContratoController::class, 'descargarPDF'])->name('arrendador.contratos.descargar-pdf');

    Route::get('/arrendador/gestor', [ArrendadorGestorController::class, 'inicio'])->name('arrendador.gestor');
    Route::post('/arrendador/gestor/{id}', [ArrendadorGestorController::class, 'actualizar'])->name('arrendador.gestor.actualizar');

    Route::get('/arrendador/incidencias', [ArrendadorIncidenciaController::class, 'inicio'])->name('arrendador.incidencias');
    Route::get('/arrendador/incidencias/{id}', [ArrendadorIncidenciaController::class, 'show'])->name('arrendador.incidencias.show');
    Route::post('/arrendador/incidencias/{id}/decision', [ArrendadorIncidenciaController::class, 'decidirPago'])->name('arrendador.incidencias.decision');
    Route::post('/arrendador/incidencias/{id}/pagar', [ArrendadorIncidenciaController::class, 'pagarPresupuesto'])->name('arrendador.incidencias.pagar');
});

Route::middleware(['role:miembro,inquilino,arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/miembro/inicio', [HomeController::class, 'index']);
    Route::get('/miembro/solicitud-arrendador', [SolicitudArrendadorController::class, 'create'])->name('miembro.arrendador.formulario');
    Route::post('/miembro/solicitud-arrendador', [SolicitudArrendadorController::class, 'store'])->name('miembro.arrendador.enviar');

    Route::get('/miembro/chat/{id}/mensajes', [MensajesController::class, 'obtenerMensajes'])->name('miembro.mensajes.mensajes');
    Route::post('/miembro/chat/{id}/mensaje', [MensajesController::class, 'enviarMensaje'])->name('miembro.mensajes.enviar');
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
});

// Rutas de configuración (Excluidas de 'arrendador.activo' para poder completarlas)
Route::middleware(['auth', 'role:miembro,inquilino,arrendador'])->group(function () {
    Route::get('/miembro/suscripcion', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'index'])->name('miembro.suscripcion.index');
    Route::post('/miembro/suscripcion/checkout', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'checkout'])->name('miembro.suscripcion.checkout');
    Route::get('/miembro/suscripcion/success', [App\Http\Controllers\Miembro\MiembroSuscripcionController::class, 'success'])->name('miembro.suscripcion.success');

    Route::get('/arrendador/configurar-stripe', [ConfiguracionCobrosController::class, 'index'])->name('arrendador.stripe.configurar');
    Route::post('/arrendador/guardar-iban', [ConfiguracionCobrosController::class, 'store'])->name('arrendador.guardar-iban');
});

Route::middleware(['role:miembro,inquilino,arrendador', 'arrendador.activo'])->group(function () {
    Route::get('/miembro/propiedad/{id}', [DetallePropiedadController::class, 'show'])->name('miembro.detalle_propiedad');
    Route::post('/miembro/propiedad/{id}/solicitud-alquiler', [SolicitudAlquilerController::class, 'store'])->name('miembro.solicitud_alquiler.store');
    Route::post('/miembro/propiedad/{id}/chat', [MensajesController::class, 'iniciarDesdePropiedad'])->name('miembro.mensajes.iniciar');
    Route::get('/miembro/chat', [MensajesController::class, 'index'])->name('miembro.mensajes.index');
    Route::get('/miembro/chat/{id}', [MensajesController::class, 'show'])->name('miembro.mensajes.show');
    Route::get('/miembro/chat/{id}/mensajes', [MensajesController::class, 'obtenerMensajes'])->name('miembro.mensajes.mensajes');
    Route::post('/miembro/chat/{id}/mensaje', [MensajesController::class, 'enviarMensaje'])->name('miembro.mensajes.enviar');
    Route::get('/miembro/mapa', [MapaController::class, 'index'])->name('miembro.mapa');

    Route::get('/inquilino/gestionar-propiedades', [InquilinoController::class, 'gestionarPropiedades'])->name('gestionar_propiedades');
    Route::get('/inquilino/propiedad/{id}', [InquilinoController::class, 'verPropiedad'])->name('inquilino.ver_propiedad');

    // Rutas de Incidencias (Controlador Especializado)
    Route::get('/inquilino/propiedad/{id}/incidencias', [InquilinoIncidenciaController::class, 'getIncidencias'])->name('inquilino.get_incidencias');
    Route::get('/inquilino/incidencias/estados', [InquilinoIncidenciaController::class, 'obtenerEstadosIncidencias'])->name('inquilino.get_estados_incidencias');
    Route::get('/inquilino/incidencia/{id}/detalle', [InquilinoIncidenciaController::class, 'getDetalleIncidencia'])->name('inquilino.get_detalle_incidencia');
    Route::post('/inquilino/propiedad/{id}/incidencia', [InquilinoIncidenciaController::class, 'reportarIncidencia'])->name('inquilino.reportar_incidencia');
    Route::post('/inquilino/incidencia/{id}/decision-pago', [InquilinoIncidenciaController::class, 'decidirPagoIncidencia'])->name('inquilino.decision_pago_incidencia');
    Route::post('/inquilino/incidencia/{id}/pagar-presupuesto', [InquilinoIncidenciaController::class, 'pagarPresupuestoIncidencia'])->name('inquilino.pagar_presupuesto_incidencia');
    Route::post('/inquilino/incidencia/{id}/cerrar', [InquilinoIncidenciaController::class, 'cerrarIncidencia'])->name('inquilino.cerrar_incidencia');

    // Rutas de Pagos e Historial (Controlador Especializado)
    Route::post('/inquilino/cuotas/{cuotaId}/pagar', [InquilinoPagoController::class, 'pagarCuotaAlquiler'])->name('inquilino.pagar_cuota');
    Route::post('/inquilino/pagar-todo', [InquilinoPagoController::class, 'pagarTodo'])->name('inquilino.pagar_todo');
    Route::get('/inquilino/alquiler/{id}/historial-suministros', [InquilinoPagoController::class, 'obtenerHistorialSuministros'])->name('inquilino.historial_suministros');
    Route::get('/inquilino/alquiler/{id}/historial-alquiler', [InquilinoPagoController::class, 'obtenerHistorialAlquiler'])->name('inquilino.historial_alquiler');
    Route::get('/inquilino/pago/success', [InquilinoPagoController::class, 'stripeSuccess'])->name('inquilino.pago.success');
    Route::get('/inquilino/gastos', [InquilinoGastosController::class, 'index'])->name('inquilino.historial_pagos');
    Route::get('/inquilino/verificar-pagos-pdf', [InquilinoPagoController::class, 'verificarPagosConPdf'])->name('inquilino.verificar_pagos_pdf');

    Route::get('/inquilino/alquiler/{id}/estado-contrato', [InquilinoController::class, 'obtenerEstadoContrato'])->name('inquilino.estado_contrato');
    Route::get('/miembro/mapa/propiedades', [MapaController::class, 'propiedades'])->name('miembro.mapa.propiedades');
});

// Utilidades de mantenimiento (rutas públicas para poder usarlas incluso con BD vacía)
Route::get('/ejecutar-migraciones', function () {
    set_time_limit(120);
    try {
        // Verificar conexión a BD
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        
        echo "<h2>⚙️ Ejecutando migraciones y seeders...</h2>";
        
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        echo "<pre style='background:#f4f4f4;padding:15px;border-radius:8px;max-height:500px;overflow:auto;font-size:12px;'>$output</pre>";
        
        if (str_contains($output, 'Seeding') || str_contains($output, 'DONE') || str_contains($output, 'Migrated')) {
            echo "<p style='color:green;font-weight:bold;font-size:18px;margin-top:20px;'>✓ Migraciones y seeders ejecutados correctamente.</p>";
            echo "<a href='/login' style='display:inline-block;padding:12px 24px;background:#00c4cc;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ir al login</a>";
            echo "&nbsp;<a href='/diagnostico' style='display:inline-block;padding:12px 24px;background:#666;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ver diagnóstico</a>";
        } else {
            echo "<p style='color:orange;font-weight:bold;'>⚠️ Proceso completado pero verifica el output arriba.</p>";
            echo "<a href='/crear-datos-minimos' style='display:inline-block;padding:12px 24px;background:#ff9800;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ir a datos mínimos (alternativa)</a>";
        }
    } catch (\Throwable $e) {
        echo "<h2>❌ Error de conexión a BD</h2>";
        echo "<p style='color:red;font-weight:bold;font-size:14px;'>" . $e->getMessage() . "</p>";
        echo "<p style='color:#666;margin-top:10px;'><strong>Soluciones:</strong></p>";
        echo "<ul style='color:#666;'>";
        echo "<li>Verifica que MySQL está corriendo en tu servidor WAMP</li>";
        echo "<li>Comprueba las credenciales en <code>.env</code></li>";
        echo "<li>Usa la opción de datos mínimos para poder entrar:</li>";
        echo "</ul>";
        echo "<a href='/crear-datos-minimos' style='display:inline-block;padding:12px 24px;background:#ff5722;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Crear datos mínimos (sin migraciones)</a>";
    }
    die;
});

Route::get('/crear-datos-minimos', function () {
    set_time_limit(60);
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        
        echo "<h2>📋 Creando datos mínimos para acceso básico...</h2>";
        
        // Verificar si ya existen tablas
        $tablas = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tieneTablas = !empty($tablas);
        
        if (!$tieneTablas) {
            echo "<p style='color:orange;'>⚠️ No hay tablas. Ejecutando migraciones primero...</p>";
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }
        
        // Limpiar datos existentes (opcional)
        \Illuminate\Support\Facades\DB::table('tbl_usuarios')->truncate();
        
        // Crear usuario admin
        $adminId = \Illuminate\Support\Facades\DB::table('tbl_usuarios')->insertGetId([
            'nombre_usuario' => 'Admin',
            'apellido_usuario' => 'SpotStay',
            'email_usuario' => 'admin@spotsstay.local',
            'password_usuario' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'telefono_usuario' => '666666666',
            'dni_usuario' => '12345678A',
            'rol_usuario' => 'admin',
            'estado_usuario' => 'activo',
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        
        echo "<p style='color:green;'>✓ Usuario admin creado</p>";
        echo "<div style='background:#e8f5e9;padding:15px;border-radius:8px;margin:15px 0;border-left:4px solid green;'>";
        echo "<strong>📧 Credenciales de acceso:</strong><br>";
        echo "Email: <code>admin@spotsstay.local</code><br>";
        echo "Contraseña: <code>admin123</code>";
        echo "</div>";
        
        echo "<a href='/login' style='display:inline-block;padding:12px 24px;background:#00c4cc;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ir al login</a>";
        echo "&nbsp;<a href='/ejecutar-migraciones' style='display:inline-block;padding:12px 24px;background:#4caf50;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ejecutar migraciones completas</a>";
        
    } catch (\Throwable $e) {
        echo "<h2>❌ Error al crear datos mínimos</h2>";
        echo "<p style='color:red;font-weight:bold;'>" . $e->getMessage() . "</p>";
        echo "<p style='color:#666;margin-top:20px;'><strong>Solución manual:</strong> Ejecuta el SQL en phpMyAdmin.</p>";
        echo "<a href='/diagnostico' style='display:inline-block;padding:12px 24px;background:#ff9800;color:white;text-decoration:none;border-radius:8px;margin-top:10px;'>Ver diagnóstico</a>";
    }
    die;
});

Route::get('/limpiar-cache', function () {
    set_time_limit(60);
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "<p style='color:green;font-weight:bold;font-size:18px;'>✓ Cachés limpiadas correctamente.</p>";
        echo "<a href='/login' style='display:inline-block;padding:12px 24px;background:#00c4cc;color:white;text-decoration:none;border-radius:8px;'>Ir al login</a>";
    } catch (\Throwable $e) {
        echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
    }
    die;
});

Route::get('/setup', function () {
    $styles = "
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f5f5;
        padding: 20px;
        color: #333;
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 30px;
    }
    h1 { color: #00c4cc; margin-bottom: 10px; font-size: 28px; }
    h2 { color: #333; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #00c4cc; padding-bottom: 10px; }
    .status { 
        display: flex; 
        align-items: center;
        padding: 12px 15px;
        margin: 10px 0;
        border-radius: 5px;
        border-left: 4px solid #ccc;
    }
    .status.ok { 
        background: #e8f5e9;
        border-left-color: #4caf50;
        color: #2e7d32;
    }
    .status.error { 
        background: #ffebee;
        border-left-color: #f44336;
        color: #c62828;
    }
    .status.warning { 
        background: #fff3e0;
        border-left-color: #ff9800;
        color: #e65100;
    }
    .icon { font-size: 20px; margin-right: 10px; }
    code { 
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
    button {
        background: #00c4cc;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin: 8px 8px 8px 0;
        transition: background 0.3s;
    }
    button:hover { background: #00a8b8; }
    button.warning { background: #ff9800; }
    button.warning:hover { background: #f57c00; }
    .credentials {
        background: #e8f5e9;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #4caf50;
        margin: 15px 0;
    }
    .action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 20px 0;
    }
    </style>
    ";
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Setup SpotStay</title>$styles</head><body><div class='container'>";
    echo "<h1>🔧 Setup SpotStay</h1>";
    echo "<p>Actualización: " . date('Y-m-d H:i:s') . "</p>";
    
    // Diagnóstico
    echo "<h2>📋 Diagnóstico</h2>";
    echo "<div class='status ok'><span class='icon'>ℹ️</span><span><strong>PHP:</strong> " . phpversion() . "</span></div>";
    
    $envExists = file_exists(base_path('.env'));
    echo "<div class='status " . ($envExists ? 'ok' : 'error') . "'>";
    echo "<span class='icon'>" . ($envExists ? '✓' : '✗') . "</span>";
    echo "<span>.env: " . ($envExists ? "existe" : "NO EXISTE") . "</span></div>";
    
    // Intentar conexión BD
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "<div class='status ok'><span class='icon'>✓</span><span>Conexión BD: OK</span></div>";
        
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tableCount = count($tables);
        echo "<div class='status " . ($tableCount > 0 ? 'ok' : 'warning') . "'>";
        echo "<span class='icon'>" . ($tableCount > 0 ? '✓' : '⚠️') . "</span>";
        echo "<span>Tablas: $tableCount</span></div>";
        
        // Acciones
        echo "<h2>⚙️ Acciones</h2>";
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] === 'clear-cache') {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                echo "<div class='status ok'><span class='icon'>✓</span><span>Caché limpiada</span></div>";
            }
            
            if ($_GET['action'] === 'migrate') {
                echo "<pre style='background:#f4f4f4;padding:15px;margin:15px 0;border-radius:5px;max-height:400px;overflow:auto;'>";
                \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
                echo \Illuminate\Support\Facades\Artisan::output();
                echo "</pre>";
            }
            
            if ($_GET['action'] === 'minimal') {
                try {
                    \Illuminate\Support\Facades\DB::table('tbl_usuarios')->delete();
                    
                    \Illuminate\Support\Facades\DB::table('tbl_usuarios')->insert([
                        'nombre_usuario' => 'Admin',
                        'apellido_usuario' => 'SpotStay',
                        'email_usuario' => 'admin@spotsstay.local',
                        'password_usuario' => \Illuminate\Support\Facades\Hash::make('admin123'),
                        'telefono_usuario' => '666666666',
                        'dni_usuario' => '12345678A',
                        'rol_usuario' => 'admin',
                        'estado_usuario' => 'activo',
                        'creado_usuario' => now(),
                        'actualizado_usuario' => now(),
                    ]);
                    
                    echo "<div class='status ok'><span class='icon'>✓</span><span>Usuario admin creado</span></div>";
                    echo "<div class='credentials'><strong>📧 Credenciales:</strong><br>Email: <code>admin@spotsstay.local</code><br>Contraseña: <code>admin123</code></div>";
                } catch (\Exception $e) {
                    echo "<div class='status error'><span class='icon'>✗</span><span>Error: " . $e->getMessage() . "</span></div>";
                }
            }
        }
        
        echo "<div class='action-group'>";
        echo "<button onclick=\"window.location.href='?action=clear-cache'\">🧹 Limpiar Caché</button>";
        echo "<button class='warning' onclick=\"window.location.href='?action=migrate'\">🔄 Migraciones</button>";
        echo "<button class='warning' onclick=\"window.location.href='?action=minimal'\">👤 Usuario Admin</button>";
        echo "<button onclick=\"window.location.href='/login'\">🚀 Ir al Login</button>";
        echo "</div>";
        
    } catch (\Exception $e) {
        echo "<div class='status error'><span class='icon'>✗</span><span>BD: ERROR - " . $e->getMessage() . "</span></div>";
    }
    
    echo "</div></body></html>";
    die;
});

Route::get('/diagnostico', function () {
    echo "<h2>🔍 Diagnóstico del servidor</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>";
    echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
    echo "<tr><td>PDO MySQL</td><td>" . (extension_loaded('pdo_mysql') ? '✓ OK' : '✗ NO DISPONIBLE') . "</td></tr>";
    echo "<tr><td>MySQLi</td><td>" . (extension_loaded('mysqli') ? '✓ OK' : '✗ NO DISPONIBLE') . "</td></tr>";
    echo "<tr><td>vendor/autoload.php</td><td>" . (file_exists(base_path('vendor/autoload.php')) ? '✓ Existe' : '✗ NO EXISTE - Ejecutar composer install') . "</td></tr>";
    echo "<tr><td>.env</td><td>" . (file_exists(base_path('.env')) ? '✓ Existe' : '✗ NO EXISTE') . "</td></tr>";
    echo "<tr><td>APP_KEY</td><td>" . (env('APP_KEY') ? '✓ Configurada' : '✗ NO CONFIGURADA - php artisan key:generate') . "</td></tr>";
    echo "<tr><td>APP_ENV</td><td>" . env('APP_ENV', 'no definido') . "</td></tr>";
    echo "<tr><td>APP_DEBUG</td><td>" . (env('APP_DEBUG') ? 'true' : 'false') . "</td></tr>";
    echo "<tr><td>DB_CONNECTION</td><td>" . env('DB_CONNECTION', 'no definido') . "</td></tr>";
    echo "<tr><td>DB_HOST</td><td>" . env('DB_HOST', 'no definido') . "</td></tr>";
    echo "<tr><td>DB_DATABASE</td><td>" . env('DB_DATABASE', 'no definido') . "</td></tr>";
    echo "<tr><td>Storage escribible</td><td>" . (is_writable(storage_path()) ? '✓ OK' : '✗ NO - chmod -R 775 storage/') . "</td></tr>";
    echo "<tr><td>Cache escribible</td><td>" . (is_writable(base_path('bootstrap/cache')) ? '✓ OK' : '✗ NO - chmod -R 775 bootstrap/cache/') . "</td></tr>";

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "<tr><td>Conexión BD</td><td style='color:green;'>✓ OK</td></tr>";
    } catch (\Throwable $e) {
        echo "<tr><td>Conexión BD</td><td style='color:red;'>✗ " . $e->getMessage() . "</td></tr>";
    }

    echo "</table>";
    die;
});
