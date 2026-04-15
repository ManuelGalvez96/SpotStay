<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        // Obtén arrendadores
        $arrendadores = DB::table('tbl_rol_usuario')
            ->join('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol', 'arrendador')
            ->pluck('tbl_rol_usuario.id_usuario_fk')
            ->toArray();

        // Emails de los 3 arrendadores principales
        $principalesEmails = ['carlos@spotstay.com', 'elena@spotstay.com', 'roberto.mora@spotstay.com'];

        foreach ($arrendadores as $idUsuario) {
            $email = DB::table('tbl_usuario')
                ->where('id_usuario', $idUsuario)
                ->value('email_usuario');

            $plan = in_array($email, $principalesEmails) ? 'pro' : 'basico';
            $maxPropiedades = $plan === 'pro' ? 10 : 3;

            DB::table('tbl_suscripcion')->insert([
                'id_usuario_fk' => $idUsuario,
                'plan_suscripcion' => $plan,
                'max_propiedades_suscripcion' => $maxPropiedades,
                'inicio_suscripcion' => Carbon::now()->subMonths(6)->toDateString(),
                'fin_suscripcion' => Carbon::now()->addMonths(6)->toDateString(),
                'estado_suscripcion' => 'activa',
                'creado_suscripcion' => Carbon::now(),
            ]);
        }
    }
}
