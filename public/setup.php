<?php
/**
 * Setup.php - Herramienta de diagnóstico y recuperación para SpotStay
 * Acceso: https://g06.daw2j23.es/setup.php
 */

set_time_limit(300);
ini_set('display_errors', 1);

function obtenerRutaLaravelBase(): string
{
    $candidatos = [
        dirname(__DIR__),
        dirname(__DIR__) . '/laravel',
        dirname(__FILE__) . '/..',
        dirname(__FILE__) . '/../laravel',
    ];

    $candidatos = array_values(array_unique(array_map('realpath', array_filter($candidatos))));

    foreach ($candidatos as $candidato) {
        if ($candidato && file_exists($candidato . '/bootstrap/app.php') && file_exists($candidato . '/vendor/autoload.php')) {
            return $candidato;
        }
    }

    return realpath(dirname(__DIR__)) ?: dirname(__DIR__);
}

function obtenerRutaEnv(string $basePath): ?string
{
    $candidatos = [
        $basePath . '/.env',
        $basePath . '/laravel/.env',
        dirname($basePath) . '/laravel/.env',
    ];

    foreach ($candidatos as $candidato) {
        if (file_exists($candidato)) {
            return $candidato;
        }
    }

    return null;
}

function imprimirBotonAccion(string $texto, string $accion, string $clase = ''): void
{
    $claseCss = trim('action-button ' . $clase);
    echo "<form method='post' style='display:inline-block;'>";
    echo "<input type='hidden' name='action' value='" . htmlspecialchars($accion, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<button type='submit' class='" . htmlspecialchars($claseCss, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . "</button>";
    echo "</form>";
}

// Estilos CSS
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
pre {
    background: #f4f4f4;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
    font-size: 12px;
    margin: 15px 0;
    border-left: 4px solid #00c4cc;
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
button.danger { background: #f44336; }
button.danger:hover { background: #d32f2f; }
button.warning { background: #ff9800; }
button.warning:hover { background: #f57c00; }
.action-button {
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
.action-button:hover { background: #00a8b8; }
.action-button.warning { background: #ff9800; }
.action-button.warning:hover { background: #f57c00; }
.action-button.danger { background: #f44336; }
.action-button.danger:hover { background: #d32f2f; }
.action-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
}
.credentials {
    background: #e8f5e9;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #4caf50;
    margin: 15px 0;
}
.credentials code {
    background: white;
    padding: 4px 8px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}
td, th {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}
th { background: #f5f5f5; font-weight: bold; }
</style>
";

// Detectar si estamos en servidor de producción o local
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']) || gethostname() === getenv('HOSTNAME');
$basePath = obtenerRutaLaravelBase();
$envPath = obtenerRutaEnv($basePath);
$requestAction = $_POST['action'] ?? $_GET['action'] ?? null;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Setup SpotStay</title>
    $styles
</head>
<body>
<div class='container'>
    <h1>🔧 Setup SpotStay - Herramienta de Recuperación</h1>
    <p>Última actualización: " . date('Y-m-d H:i:s') . "</p>
";

// Sección 1: DIAGNÓSTICO
echo "<h2>📋 Diagnóstico del Sistema</h2>";

// 1.1 Verificar archivo .env
$envExists = $envPath !== null;
echo "<div class='status " . ($envExists ? 'ok' : 'error') . "'>";
echo "<span class='icon'>" . ($envExists ? '✓' : '✗') . "</span>";
echo "<span><strong>.env</strong> " . ($envExists ? "existe en " . htmlspecialchars(str_replace($basePath, '.', $envPath), ENT_QUOTES, 'UTF-8') : "NO EXISTE") . "</span>";
echo "</div>";

// 1.2 Leer variables de .env
if ($envExists) {
    $env = parse_ini_file($envPath);
    echo "<table>";
    echo "<tr><th>Variable</th><th>Valor</th></tr>";
    $keyVars = ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($keyVars as $var) {
        $val = $env[$var] ?? 'no definida';
        $status = empty($val) ? 'warning' : 'ok';
        echo "<tr><td><code>$var</code></td><td>" . (strlen($val) > 50 ? substr($val, 0, 50) . '...' : $val) . "</td></tr>";
    }
    echo "</table>";
}

// 1.3 PHP Version
echo "<div class='status ok'>";
echo "<span class='icon'>ℹ️</span>";
echo "<span><strong>PHP Version:</strong> " . phpversion() . "</span>";
echo "</div>";

// 1.4 Extensiones críticas
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'json', 'tokenizer'];
foreach ($extensions as $ext) {
    $exists = extension_loaded($ext);
    echo "<div class='status " . ($exists ? 'ok' : 'error') . "'>";
    echo "<span class='icon'>" . ($exists ? '✓' : '✗') . "</span>";
    echo "<span><strong>Extensión $ext:</strong> " . ($exists ? "OK" : "FALTA") . "</span>";
    echo "</div>";
}

// 1.5 Directorios escribibles
$dirs = [
    'storage' => $basePath . '/storage',
    'bootstrap/cache' => $basePath . '/bootstrap/cache',
];
foreach ($dirs as $name => $path) {
    $writable = is_writable($path);
    echo "<div class='status " . ($writable ? 'ok' : 'error') . "'>";
    echo "<span class='icon'>" . ($writable ? '✓' : '✗') . "</span>";
    echo "<span><strong>$name</strong> escribible: " . ($writable ? "OK" : "NO - chmod -R 775 $name") . "</span>";
    echo "</div>";
}

// 1.6 Conexión a BD
echo "<h2>🗄️ Conexión a Base de Datos</h2>";

if (!file_exists("$basePath/vendor/autoload.php")) {
    echo "<div class='status error'>";
    echo "<span class='icon'>✗</span>";
    echo "<span><strong>Composer no instalado.</strong> Ejecuta: <code>composer install</code></span>";
    echo "</div>";
} else {
    require_once $basePath . "/vendor/autoload.php";
    
    if (!file_exists($basePath . "/bootstrap/app.php")) {
        echo "<div class='status error'>";
        echo "<span class='icon'>✗</span>";
        echo "<span><strong>Bootstrap falta.</strong> Proyecto Laravel corrupto.</span>";
        echo "</div>";
    } else {
        try {
            $app = require_once $basePath . "/bootstrap/app.php";
            $container = $app->make(\Illuminate\Contracts\Container\Container::class);
            $db = $container->make('db');
            
            $db->connection()->getPdo();
            
            echo "<div class='status ok'>";
            echo "<span class='icon'>✓</span>";
            echo "<span><strong>BD conectada correctamente</strong></span>";
            echo "</div>";
            
            // Verificar tablas
            $tables = $db->select('SHOW TABLES');
            $tableCount = count($tables);
            echo "<div class='status " . ($tableCount > 0 ? 'ok' : 'warning') . "'>";
            echo "<span class='icon'>" . ($tableCount > 0 ? '✓' : '⚠️') . "</span>";
            echo "<span><strong>Tablas encontradas:</strong> $tableCount</span>";
            echo "</div>";
            
            // Verificar tabla de usuarios
            if ($tableCount > 0) {
                try {
                    $result = $db->select('SELECT COUNT(*) as count FROM tbl_usuarios');
                    $userCount = $result[0]->count ?? 0;
                    echo "<div class='status " . ($userCount > 0 ? 'ok' : 'warning') . "'>";
                    echo "<span class='icon'>" . ($userCount > 0 ? '✓' : '⚠️') . "</span>";
                    echo "<span><strong>Usuarios en BD:</strong> $userCount</span>";
                    echo "</div>";
                } catch (\Exception $e) {
                    echo "<div class='status warning'>";
                    echo "<span class='icon'>⚠️</span>";
                    echo "<span><strong>Tabla de usuarios no existe.</strong> Necesita migraciones.</span>";
                    echo "</div>";
                }
            }
            
            // Sección 2: REPARACIONES
            echo "<h2>⚙️ Herramientas de Reparación</h2>";
            
            // Botón para limpiar caché
            if ($requestAction === 'clear-cache') {
                try {
                    \Illuminate\Support\Facades\Artisan::call('config:clear');
                    \Illuminate\Support\Facades\Artisan::call('route:clear');
                    \Illuminate\Support\Facades\Artisan::call('view:clear');
                    \Illuminate\Support\Facades\Artisan::call('cache:clear');
                    echo "<div class='status ok'>";
                    echo "<span class='icon'>✓</span>";
                    echo "<span><strong>Caché limpiada.</strong></span>";
                    echo "</div>";
                } catch (\Exception $e) {
                    echo "<div class='status error'>";
                    echo "<span class='icon'>✗</span>";
                    echo "<span><strong>Error limpiando caché:</strong> " . $e->getMessage() . "</span>";
                    echo "</div>";
                }
            }
            
            // Botón para ejecutar migraciones
            if ($requestAction === 'migrate') {
                echo "<pre>";
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
                    echo \Illuminate\Support\Facades\Artisan::output();
                    echo "\n✓ Migraciones ejecutadas";
                } catch (\Exception $e) {
                    echo "ERROR: " . $e->getMessage();
                }
                echo "</pre>";
            }
            
            // Botón para crear datos mínimos
            if ($requestAction === 'minimal') {
                try {
                    if ($db->getSchemaBuilder()->hasTable('tbl_usuarios')) {
                        $db->table('tbl_usuarios')->delete();

                        $userId = $db->table('tbl_usuarios')->insertGetId([
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

                        echo "<div class='status ok'>";
                        echo "<span class='icon'>✓</span>";
                        echo "<span><strong>Usuario admin creado (ID: $userId)</strong></span>";
                        echo "</div>";

                        echo "<div class='credentials'>";
                        echo "<strong>📧 Credenciales de acceso:</strong><br>";
                        echo "Email: <code>admin@spotsstay.local</code><br>";
                        echo "Contraseña: <code>admin123</code>";
                        echo "</div>";
                    } else {
                        echo "<div class='status warning'>";
                        echo "<span class='icon'>⚠️</span>";
                        echo "<span><strong>tbl_usuarios no existe.</strong> Ejecuta migraciones primero.</span>";
                        echo "</div>";
                    }
                } catch (\Exception $e) {
                    echo "<div class='status error'>";
                    echo "<span class='icon'>✗</span>";
                    echo "<span><strong>Error creando usuario:</strong> " . $e->getMessage() . "</span>";
                    echo "</div>";
                }
            }
            
            echo "<div class='action-group'>";
            imprimirBotonAccion('🧹 Limpiar Caché', 'clear-cache');
            imprimirBotonAccion('🔄 Ejecutar Migraciones', 'migrate', 'warning');
            imprimirBotonAccion('👤 Crear Usuario Admin', 'minimal', 'warning');
            echo "<form method='get' style='display:inline-block;'><button type='submit' class='action-button'>🚀 Ir al Login</button></form>";
            echo "</div>";
            
        } catch (\Exception $e) {
            echo "<div class='status error'>";
            echo "<span class='icon'>✗</span>";
            echo "<span><strong>Error de conexión:</strong> " . $e->getMessage() . "</span>";
            echo "</div>";
            
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}

echo "
    </div>
</body>
</html>
";
?>
