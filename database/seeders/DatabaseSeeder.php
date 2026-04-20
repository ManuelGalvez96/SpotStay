<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            ArrendadorDemoSeeder::class,
            SuscripcionSeeder::class,
            PropiedadSeeder::class,
            AlquilerSeeder::class,
            ContratoSeeder::class,
            PagoSeeder::class,
            IncidenciaSeeder::class,
            HistorialIncidenciaSeeder::class,
            SolicitudArrendadorSeeder::class,
            ConversacionSeeder::class,
            ConversacionUsuarioSeeder::class,
            MensajeSeeder::class,
            NotificacionSeeder::class,
            ChatbotSesionSeeder::class,
            ChatbotMensajeSeeder::class,
            DocumentoSeeder::class,
        ]);
    }
}