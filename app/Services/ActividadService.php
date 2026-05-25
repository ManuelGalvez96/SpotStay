<?php

namespace App\Services;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActividadService
{
    private function crear(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $url, string $icono, string $color, ?string $tipoEntidad, ?int $idEntidad): void
    {
        if (!Usuario::find($usuarioId)) {
            return;
        }

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $usuarioId,
            'tipo_notificacion' => $tipo,
            'titulo_notificacion' => $titulo,
            'mensaje_notificacion' => $mensaje,
            'url_notificacion' => $url,
            'icono_notificacion' => $icono,
            'color_notificacion' => $color,
            'tipo_entidad_notificacion' => $tipoEntidad,
            'id_entidad_notificacion' => $idEntidad,
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now(),
            'actualizado_notificacion' => Carbon::now(),
        ]);
    }

    public function mensajeNuevo(int $usuarioId, int $conversacionId, string $propiedadTitulo, string $nombreRemitente, string $extracto): void
    {
        $rutaMensaje = '/miembro/chat/' . $conversacionId;

        $usuario = Usuario::query()
            ->with('roles')
            ->find($usuarioId);

        if ($usuario && $usuario->roles->contains('slug_rol', 'gestor')) {
            $rutaMensaje = '/gestor/mensajes?activa=' . $conversacionId;
        }

        $this->crear(
            $usuarioId,
            'mensaje_nuevo',
            "Nuevo mensaje en {$propiedadTitulo}",
            "{$nombreRemitente}: {$extracto}",
            $rutaMensaje,
            'chat-dots',
            '#7C3AED',
            'conversacion',
            $conversacionId
        );
    }

    public function avisoImportante(int $usuarioId, string $titulo, string $mensaje, ?string $url = null): void
    {
        $this->crear(
            $usuarioId,
            'aviso_importante',
            $titulo,
            $mensaje,
            $url,
            'megaphone',
            '#035498',
            'aviso',
            null
        );
    }

    public function incidenciaCreada(int $usuarioId, int $incidenciaId, string $propiedadTitulo, string $incidenciaTitulo, string $reportaNombre = 'Un usuario'): void
    {
        $this->crear(
            $usuarioId,
            'nueva_incidencia',
            "Nueva incidencia en {$propiedadTitulo}",
            "{$incidenciaTitulo} — reportada por {$reportaNombre}",
            "/gestor/incidencias/{$incidenciaId}",
            'exclamation-triangle',
            '#DC2626',
            'incidencia',
            $incidenciaId
        );
    }

    public function incidenciaCambioEstado(int $usuarioId, int $incidenciaId, string $propiedadTitulo, string $estadoNuevo): void
    {
        $estadoLabel = str_replace('_', ' ', $estadoNuevo);
        $estadoLabel = ucfirst($estadoLabel);

        $this->crear(
            $usuarioId,
            'incidencia_actualizada',
            "Incidencia actualizada en {$propiedadTitulo}",
            "Cambió a {$estadoLabel}",
            "/gestor/incidencias/{$incidenciaId}",
            'arrow-left-right',
            '#2563EB',
            'incidencia',
            $incidenciaId
        );
    }

    public function pagoRealizado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $concepto, float $importe): void
    {
        $this->crear(
            $usuarioId,
            'pago_realizado',
            "Pago realizado en {$propiedadTitulo}",
            "{$concepto} — " . number_format($importe, 2, ',', '.') . " €",
            "/gestor/propiedades/{$propiedadId}",
            'check-circle',
            '#16A34A',
            'propiedad',
            $propiedadId
        );
    }

    public function pagoAtrasado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $mes, float $importe): void
    {
        $this->crear(
            $usuarioId,
            'pago_atrasado',
            "Pago atrasado en {$propiedadTitulo}",
            "Cuota de {$mes} — " . number_format($importe, 2, ',', '.') . " €",
            "/gestor/propiedades/{$propiedadId}",
            'clock-history',
            '#EA580C',
            'propiedad',
            $propiedadId
        );
    }

    public function gastoAtrasado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $categoria, float $importe): void
    {
        $this->crear(
            $usuarioId,
            'pago_atrasado',
            "Recibo atrasado en {$propiedadTitulo}",
            ucfirst($categoria) . " — " . number_format($importe, 2, ',', '.') . " €",
            "/gestor/propiedades/{$propiedadId}",
            'clock-history',
            '#EA580C',
            'propiedad',
            $propiedadId
        );
    }

    public function presupuestoCreado(int $usuarioId, int $incidenciaId, string $propiedadTitulo, float $importe): void
    {
        $this->crear(
            $usuarioId,
            'presupuesto_creado',
            "Presupuesto generado en {$propiedadTitulo}",
            number_format($importe, 2, ',', '.') . " € — pendiente de aprobación",
            "/gestor/incidencias/{$incidenciaId}",
            'cash-coin',
            '#D97706',
            'incidencia',
            $incidenciaId
        );
    }

    public function gastoCreado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $categoria, string $concepto, float $importe): void
    {
        $label = $concepto ?: ucfirst($categoria);
        $this->crear(
            $usuarioId,
            'gasto_creado',
            "Nuevo recibo en {$propiedadTitulo}",
            "{$label} — " . number_format($importe, 2, ',', '.') . " €",
            "/gestor/propiedades/{$propiedadId}",
            'receipt',
            '#0891B2',
            'propiedad',
            $propiedadId
        );
    }

    public static function tiposActividad(): array
    {
        return [
            'nueva_incidencia' => ['label' => 'Incidencias nuevas', 'color' => '#DC2626', 'icono' => 'exclamation-triangle'],
            'incidencia_actualizada' => ['label' => 'Cambios de estado', 'color' => '#2563EB', 'icono' => 'arrow-left-right'],
            'pago_realizado' => ['label' => 'Pagos recibidos', 'color' => '#16A34A', 'icono' => 'check-circle'],
            'pago_atrasado' => ['label' => 'Pagos atrasados', 'color' => '#EA580C', 'icono' => 'clock-history'],
            'presupuesto_creado' => ['label' => 'Presupuestos', 'color' => '#D97706', 'icono' => 'cash-coin'],
            'gasto_creado' => ['label' => 'Recibos creados', 'color' => '#0891B2', 'icono' => 'receipt'],
            'mensaje_nuevo' => ['label' => 'Mensajes', 'color' => '#7C3AED', 'icono' => 'chat-dots'],
            'aviso_importante' => ['label' => 'Avisos importantes', 'color' => '#035498', 'icono' => 'megaphone'],
            'alquiler_pendiente' => ['label' => 'Alquiler pendiente', 'color' => '#035498', 'icono' => 'calendar-event'],
            'propiedad_estado' => ['label' => 'Propiedad cambió de estado', 'color' => '#035498', 'icono' => 'building-gear'],
            'alquiler_creado' => ['label' => 'Nuevo alquiler creado', 'color' => '#059669', 'icono' => 'house-check'],
            'alquiler_aprobado' => ['label' => 'Alquiler aprobado', 'color' => '#16A34A', 'icono' => 'check-lg'],
            'contrato_firmado' => ['label' => 'Contrato firmado', 'color' => '#059669', 'icono' => 'file-earmark-check'],
        ];
    }

    public function propiedadEstadoCambiado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $estadoAnterior, string $estadoNuevo, string $realizadoPor = 'Admin'): void
    {
        $anterior = str_replace('_', ' ', $estadoAnterior);
        $nuevo = str_replace('_', ' ', $estadoNuevo);

        $this->crear(
            $usuarioId,
            'propiedad_estado',
            "Estado actualizado: {$propiedadTitulo}",
            "{$realizadoPor} cambió de {$anterior} a {$nuevo}",
            "/gestor/propiedades/{$propiedadId}",
            'building-gear',
            '#035498',
            'propiedad',
            $propiedadId
        );
    }

    public function alquilerCreado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $inquilinoNombre): void
    {
        $this->crear(
            $usuarioId,
            'alquiler_creado',
            "Nuevo alquiler en {$propiedadTitulo}",
            "Solicitado por {$inquilinoNombre}",
            "/gestor/propiedades/{$propiedadId}",
            'house-check',
            '#059669',
            'propiedad',
            $propiedadId
        );
    }

    public function alquilerAprobado(int $usuarioId, int $alquilerId, string $propiedadTitulo, string $inquilinoNombre): void
    {
        $this->crear(
            $usuarioId,
            'alquiler_aprobado',
            "Alquiler aprobado en {$propiedadTitulo}",
            "Aprobado para {$inquilinoNombre}",
            "/gestor/propiedades/{$alquilerId}",
            'check-lg',
            '#16A34A',
            'alquiler',
            $alquilerId
        );
    }

    public function contratoFirmado(int $usuarioId, int $propiedadId, string $propiedadTitulo, string $firmanteNombre): void
    {
        $this->crear(
            $usuarioId,
            'contrato_firmado',
            "Contrato firmado en {$propiedadTitulo}",
            "Firmado por {$firmanteNombre}",
            "/gestor/propiedades/{$propiedadId}",
            'file-earmark-check',
            '#059669',
            'propiedad',
            $propiedadId
        );
    }
}
