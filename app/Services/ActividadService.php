<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActividadService
{
    private function crear(int $gestorId, string $tipo, string $titulo, string $mensaje, ?string $url, string $icono, string $color, ?string $tipoEntidad, ?int $idEntidad): void
    {
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $gestorId,
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

    public function incidenciaCreada(int $gestorId, int $incidenciaId, string $propiedadTitulo, string $incidenciaTitulo, string $reportaNombre = 'Un usuario'): void
    {
        $this->crear(
            $gestorId,
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

    public function incidenciaCambioEstado(int $gestorId, int $incidenciaId, string $propiedadTitulo, string $estadoNuevo): void
    {
        $estadoLabel = str_replace('_', ' ', $estadoNuevo);
        $estadoLabel = ucfirst($estadoLabel);

        $this->crear(
            $gestorId,
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

    public function pagoRealizado(int $gestorId, int $propiedadId, string $propiedadTitulo, string $concepto, float $importe): void
    {
        $this->crear(
            $gestorId,
            'pago_realizado',
            "Pago recibido en {$propiedadTitulo}",
            "{$concepto} — " . number_format($importe, 2, ',', '.') . " €",
            "/gestor/propiedades/{$propiedadId}",
            'check-circle',
            '#16A34A',
            'propiedad',
            $propiedadId
        );
    }

    public function pagoAtrasado(int $gestorId, int $propiedadId, string $propiedadTitulo, string $mes, float $importe): void
    {
        $this->crear(
            $gestorId,
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

    public function gastoAtrasado(int $gestorId, int $propiedadId, string $propiedadTitulo, string $categoria, float $importe): void
    {
        $this->crear(
            $gestorId,
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

    public function presupuestoCreado(int $gestorId, int $incidenciaId, string $propiedadTitulo, float $importe): void
    {
        $this->crear(
            $gestorId,
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

    public function gastoCreado(int $gestorId, int $propiedadId, string $propiedadTitulo, string $categoria, string $concepto, float $importe): void
    {
        $label = $concepto ?: ucfirst($categoria);
        $this->crear(
            $gestorId,
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
            'alquiler_pendiente' => ['label' => 'Alquiler pendiente', 'color' => '#035498', 'icono' => 'calendar-event'],
        ];
    }
}
