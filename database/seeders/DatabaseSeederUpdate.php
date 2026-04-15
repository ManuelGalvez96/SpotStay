<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UsuarioSeeder::class,
            SuscripcionSeeder::class,
            PropiedadSeeder::class,
            AlquilerSeeder::class,
            ContratoSeeder::class,
            PagoSeeder::class,
            IncidenciaSeeder::class,
            HistorialIncidenciaSeeder::class,
            ConversacionSeeder::class,
            MensajeSeeder::class,
            NotificacionSeeder::class,
            SolicitudArrendadorSeeder::class,
        ]);
    }
}
