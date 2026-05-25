@php
$anuncios = [
    1 => ['titulo' => 'SeguroHogar',     'descripcion' => 'Protege tu hogar contra imprevistos',   'boton' => 'Contratar seguro',    'icono' => 'bi-shield-check'],
    2 => ['titulo' => 'MudanzasExpress', 'descripcion' => 'Mudanza rápida y sin preocupaciones',   'boton' => 'Pedir presupuesto',   'icono' => 'bi-truck'],
    3 => ['titulo' => 'ReformasYa',      'descripcion' => 'Renueva tu casa al mejor precio',       'boton' => 'Solicitar reforma',   'icono' => 'bi-tools'],
    4 => ['titulo' => 'Hipoteca Fácil',  'descripcion' => 'La mejor hipoteca para tu vivienda',    'boton' => 'Calcular cuota',      'icono' => 'bi-bank'],
    5 => ['titulo' => 'DecoHogar',       'descripcion' => 'Decora tu casa con estilo y ahorrando', 'boton' => 'Ver catálogo',        'icono' => 'bi-palette'],
];

$numImagen = array_rand($anuncios);

// Busca el archivo con cualquier extensión (jpg, jpeg, png, webp)
$archivo = null;
foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $ruta = public_path("img/anuncios/anuncio_{$numImagen}.{$ext}");
    if (file_exists($ruta)) {
        $archivo = "img/anuncios/anuncio_{$numImagen}.{$ext}";
        break;
    }
}

// Si no existe, escanea todos los archivos disponibles y usa el primero que encuentre
$imagenesEncontradas = glob(public_path('img/anuncios/anuncio_*.*'));
$archivos = [];
foreach ($imagenesEncontradas as $f) {
    if (preg_match('/anuncio_(\d+)\.\w+$/', $f, $m)) {
        $archivos[(int)$m[1]] = basename($f);
    }
}
if ($archivos) {
    $numImagen = array_rand($archivos);
    $archivo = 'img/anuncios/' . $archivos[$numImagen];
}

$anuncio = $anuncios[$numImagen] ?? $anuncios[1];
@endphp
<div class="anuncio-contenedor">
    <span class="anuncio-etiqueta">Anuncio</span>
    <a href="#" class="anuncio-enlace" style="background-image: url('{{ $archivo ? asset($archivo) : '' }}');">
        <div class="anuncio-overlay">
            <i class="bi {{ $anuncio['icono'] }} anuncio-icono"></i>
            <span class="anuncio-titulo">{{ $anuncio['titulo'] }}</span>
            <span class="anuncio-descripcion">{{ $anuncio['descripcion'] }}</span>
            <span class="anuncio_boton">{{ $anuncio['boton'] }}</span>
        </div>
    </a>
</div>
