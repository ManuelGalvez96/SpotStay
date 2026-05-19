<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\PropiedadPermisosSeeder;
use Database\Seeders\GastoSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders en orden correcto para respetar las foreign keys
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UsuarioSeeder::class,
            PlanSeeder::class,
            ArrendadorDemoSeeder::class,
            SuscripcionSeeder::class,
            PropiedadSeeder::class,
            PropiedadPermisosSeeder::class,
            AlquilerSeeder::class,
            GastoSeeder::class,
            ContratoSeeder::class,
            PagoSeeder::class,
            IncidenciaSeeder::class,
            HistorialIncidenciaSeeder::class,
            SolicitudArrendadorSeeder::class,
            ConversacionSeeder::class,
            ConversacionUsuarioSeeder::class,
            MensajeSeeder::class,
            NotificacionSeeder::class,
            ActividadSeeder::class,
            ChatbotSesionSeeder::class,
            ChatbotMensajeSeeder::class,
            DocumentoSeeder::class,
        ]);
    }
}