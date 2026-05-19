<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadSeeder extends Seeder
{
    public function run(): void
    {
        $gestores = DB::table('tbl_usuario as u')
            ->join('tbl_rol_usuario as ru', 'ru.id_usuario_fk', '=', 'u.id_usuario')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
            ->where('r.slug_rol', 'gestor')
            ->select('u.id_usuario', 'u.nombre_usuario')
            ->get();

        foreach ($gestores as $gestor) {
            $this->crearNotificacionesParaGestor($gestor->id_usuario, $gestor->nombre_usuario);
        }
    }

    private function crearNotificacionesParaGestor(int $gestorId, string $gestorNombre): void
    {
        $propiedades = DB::table('tbl_propiedad')
            ->where('id_gestor_fk', $gestorId)
            ->select('id_propiedad', 'titulo_propiedad', 'estado_propiedad')
            ->get();

        if ($propiedades->isEmpty()) {
            return;
        }

        $propiedadIds = $propiedades->pluck('id_propiedad')->toArray();

        $this->notificarIncidencias($gestorId, $propiedadIds, $propiedades);
        $this->notificarPagos($gestorId, $propiedadIds, $propiedades);
        $this->notificarGastos($gestorId, $propiedadIds, $propiedades);
        $this->notificarPropiedades($gestorId, $propiedades);
        $this->notificarAlquileres($gestorId, $propiedadIds, $propiedades);
        $this->notificarContratos($gestorId, $propiedadIds, $propiedades);
    }

    private function notificarIncidencias(int $gestorId, array $propiedadIds, $propiedades): void
    {
        $incidencias = DB::table('tbl_incidencia')
            ->whereIn('id_propiedad_fk', $propiedadIds)
            ->select('id_incidencia', 'id_propiedad_fk', 'titulo_incidencia', 'estado_incidencia', 'creado_incidencia')
            ->get();

        foreach ($incidencias as $incidencia) {
            $propiedad = $propiedades->firstWhere('id_propiedad', $incidencia->id_propiedad_fk);
            if (!$propiedad) continue;

            $diasAtras = rand(1, 25);
            $fecha = $this->fechaAleatoria($diasAtras);

            $this->insertar(
                $gestorId, 'nueva_incidencia',
                "Nueva incidencia en {$propiedad->titulo_propiedad}",
                "{$incidencia->titulo_incidencia} — reportada por Inquilino",
                "/gestor/incidencias/{$incidencia->id_incidencia}",
                'exclamation-triangle', '#DC2626',
                'incidencia', $incidencia->id_incidencia,
                $fecha
            );

            if (in_array($incidencia->estado_incidencia, ['en_proceso', 'resuelta'])) {
                $estadoLabel = str_replace('_', ' ', $incidencia->estado_incidencia);
                $estadoLabel = ucfirst($estadoLabel);

                $this->insertar(
                    $gestorId, 'incidencia_actualizada',
                    "Incidencia actualizada en {$propiedad->titulo_propiedad}",
                    "Cambió a {$estadoLabel}",
                    "/gestor/incidencias/{$incidencia->id_incidencia}",
                    'arrow-left-right', '#2563EB',
                    'incidencia', $incidencia->id_incidencia,
                    $this->fechaAleatoria(rand(1, 5))
                );
            }

            if ($incidencia->estado_incidencia === 'en_proceso' && rand(0, 1)) {
                $importe = rand(50, 500);
                $this->insertar(
                    $gestorId, 'presupuesto_creado',
                    "Presupuesto generado en {$propiedad->titulo_propiedad}",
                    number_format($importe, 2, ',', '.') . " € — pendiente de aprobación",
                    "/gestor/incidencias/{$incidencia->id_incidencia}",
                    'cash-coin', '#D97706',
                    'incidencia', $incidencia->id_incidencia,
                    $this->fechaAleatoria(rand(1, 3))
                );
            }
        }
    }

    private function notificarPagos(int $gestorId, array $propiedadIds, $propiedades): void
    {
        $cuotas = DB::table('tbl_alquiler_cuota as ac')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'ac.id_alquiler_fk')
            ->whereIn('a.id_propiedad_fk', $propiedadIds)
            ->whereIn('ac.estado', ['pagado', 'atrasado'])
            ->select('ac.id_alquiler_cuota', 'ac.estado', 'ac.importe_base', 'ac.mes_cuota', 'a.id_propiedad_fk')
            ->get();

        foreach ($cuotas as $cuota) {
            $propiedad = $propiedades->firstWhere('id_propiedad', $cuota->id_propiedad_fk);
            if (!$propiedad) continue;

            $mes = $cuota->mes_cuota ? Carbon::parse($cuota->mes_cuota)->translatedFormat('F Y') : '';

            if ($cuota->estado === 'pagado') {
                $this->insertar(
                    $gestorId, 'pago_realizado',
                    "Pago realizado en {$propiedad->titulo_propiedad}",
                    "Cuota de {$mes} — " . number_format($cuota->importe_base, 2, ',', '.') . " €",
                    "/gestor/propiedades/{$cuota->id_propiedad_fk}",
                    'check-circle', '#16A34A',
                    'propiedad', $cuota->id_propiedad_fk,
                    $this->fechaAleatoria(rand(1, 10))
                );
            } else {
                $this->insertar(
                    $gestorId, 'pago_atrasado',
                    "Pago atrasado en {$propiedad->titulo_propiedad}",
                    "Cuota de {$mes} — " . number_format($cuota->importe_base, 2, ',', '.') . " €",
                    "/gestor/propiedades/{$cuota->id_propiedad_fk}",
                    'clock-history', '#EA580C',
                    'propiedad', $cuota->id_propiedad_fk,
                    $this->fechaAleatoria(rand(1, 3))
                );
            }
        }
    }

    private function notificarGastos(int $gestorId, array $propiedadIds, $propiedades): void
    {
        $gastos = DB::table('tbl_gasto_cuota as gc')
            ->join('tbl_gasto as g', 'g.id_gasto', '=', 'gc.id_gasto_fk')
            ->whereIn('g.id_propiedad_fk', $propiedadIds)
            ->select('gc.id_gasto_cuota', 'gc.importe_total_cuota', 'gc.estado_cuota', 'gc.mes_cuota', 'g.id_propiedad_fk', 'g.categoria_gasto', 'g.concepto_gasto')
            ->get();

        $vistos = [];
        foreach ($gastos as $gasto) {
            $propiedad = $propiedades->firstWhere('id_propiedad', $gasto->id_propiedad_fk);
            if (!$propiedad) continue;
            $key = $gasto->categoria_gasto . '_' . $gasto->id_propiedad_fk;
            if (in_array($key, $vistos)) continue;
            $vistos[] = $key;

            $label = $gasto->concepto_gasto ?: ucfirst($gasto->categoria_gasto);

            $this->insertar(
                $gestorId, 'gasto_creado',
                "Nuevo recibo en {$propiedad->titulo_propiedad}",
                "{$label} — " . number_format($gasto->importe_total_cuota, 2, ',', '.') . " €",
                "/gestor/propiedades/{$gasto->id_propiedad_fk}",
                'receipt', '#0891B2',
                'propiedad', $gasto->id_propiedad_fk,
                $this->fechaAleatoria(rand(5, 20))
            );
        }
    }

    private function notificarPropiedades(int $gestorId, $propiedades): void
    {
        $estadosPosibles = ['publicada', 'borrador', 'inactiva', 'alquilada'];

        foreach ($propiedades as $propiedad) {
            $estadoAnterior = $estadosPosibles[array_rand($estadosPosibles)];
            if ($estadoAnterior === $propiedad->estado_propiedad) {
                $estadoAnterior = 'borrador';
            }

            $this->insertar(
                $gestorId, 'propiedad_estado',
                "Estado actualizado: {$propiedad->titulo_propiedad}",
                "Admin cambió de {$estadoAnterior} a {$propiedad->estado_propiedad}",
                "/gestor/propiedades/{$propiedad->id_propiedad}",
                'building-gear', '#035498',
                'propiedad', $propiedad->id_propiedad,
                $this->fechaAleatoria(rand(10, 28))
            );
        }
    }

    private function notificarAlquileres(int $gestorId, array $propiedadIds, $propiedades): void
    {
        $alquileres = DB::table('tbl_alquiler')
            ->whereIn('id_propiedad_fk', $propiedadIds)
            ->select('id_alquiler', 'id_propiedad_fk', 'id_inquilino_fk', 'estado_alquiler', 'creado_alquiler')
            ->get();

        $vistos = [];
        foreach ($alquileres as $alquiler) {
            $propiedad = $propiedades->firstWhere('id_propiedad', $alquiler->id_propiedad_fk);
            if (!$propiedad) continue;

            $key = 'creado_' . $alquiler->id_alquiler;
            if (!in_array($key, $vistos)) {
                $vistos[] = $key;

                $inquilino = DB::table('tbl_usuario')
                    ->where('id_usuario', $alquiler->id_inquilino_fk)
                    ->value('nombre_usuario') ?? 'Inquilino';

                $this->insertar(
                    $gestorId, 'alquiler_creado',
                    "Nuevo alquiler en {$propiedad->titulo_propiedad}",
                    "Solicitado por {$inquilino}",
                    "/gestor/propiedades/{$propiedad->id_propiedad}",
                    'house-check', '#059669',
                    'propiedad', $propiedad->id_propiedad,
                    $this->fechaAleatoria(rand(15, 30))
                );
            }

            if ($alquiler->estado_alquiler === 'activo') {
                $keyAprobado = 'aprobado_' . $alquiler->id_alquiler;
                if (!in_array($keyAprobado, $vistos)) {
                    $vistos[] = $keyAprobado;

                    $inquilino = DB::table('tbl_usuario')
                        ->where('id_usuario', $alquiler->id_inquilino_fk)
                        ->value('nombre_usuario') ?? 'Inquilino';

                    $this->insertar(
                        $gestorId, 'alquiler_aprobado',
                        "Alquiler aprobado en {$propiedad->titulo_propiedad}",
                        "Aprobado para {$inquilino}",
                        "/gestor/propiedades/{$alquiler->id_alquiler}",
                        'check-lg', '#16A34A',
                        'alquiler', $alquiler->id_alquiler,
                        $this->fechaAleatoria(rand(5, 15))
                    );
                }
            }
        }
    }

    private function notificarContratos(int $gestorId, array $propiedadIds, $propiedades): void
    {
        $contratos = DB::table('tbl_contrato as c')
            ->join('tbl_alquiler as a', 'a.id_alquiler', '=', 'c.id_alquiler_fk')
            ->whereIn('a.id_propiedad_fk', $propiedadIds)
            ->where('c.estado_contrato', 'firmado')
            ->select('c.id_contrato', 'a.id_propiedad_fk')
            ->get();

        foreach ($contratos as $contrato) {
            $propiedad = $propiedades->firstWhere('id_propiedad', $contrato->id_propiedad_fk);
            if (!$propiedad) continue;

            $this->insertar(
                $gestorId, 'contrato_firmado',
                "Contrato firmado en {$propiedad->titulo_propiedad}",
                "Firmado por Arrendador",
                "/gestor/propiedades/{$contrato->id_propiedad_fk}",
                'file-earmark-check', '#059669',
                'propiedad', $contrato->id_propiedad_fk,
                $this->fechaAleatoria(rand(5, 20))
            );
        }
    }

    private function insertar(
        int    $gestorId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $url,
        string $icono,
        string $color,
        string $tipoEntidad,
        int    $idEntidad,
        string $fecha
    ): void
    {
        if (!DB::table('tbl_usuario')->where('id_usuario', $gestorId)->exists()) {
            return;
        }

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
            'leida_en_notificacion' => null,
            'creado_notificacion' => $fecha,
            'actualizado_notificacion' => $fecha,
        ]);
    }

    private function fechaAleatoria(int $maxDiasAtras): string
    {
        return Carbon::now()
            ->subDays(rand(0, $maxDiasAtras))
            ->subHours(rand(0, 23))
            ->subMinutes(rand(0, 59))
            ->format('Y-m-d H:i:s');
    }
}
