<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencia #{{ $incidencia->id_incidencia }} - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/incidencias.css') }}" />
</head>
<body>
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>{{ $incidencia->titulo_incidencia }}</h1>
            <p class="subtitulo">{{ $incidencia->titulo_propiedad }} · {{ $incidencia->direccion_propiedad }}, {{ $incidencia->ciudad_propiedad }}</p>
        </div>
        <div class="acciones-cabecera">
            <a class="btn-volver" href="{{ route('arrendador.incidencias', ['arrendador_id' => $arrendadorId]) }}">Volver a incidencias</a>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Dashboard</a>
        </div>
    </header>

    @if(session('ok'))
        <div class="alerta ok">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alerta error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alerta error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="incidencia-grid">
        <article class="card card-main">
            <div class="cabecera-card">
                <div>
                    <p class="kicker">Incidencia #{{ $incidencia->id_incidencia }}</p>
                    <h2>{{ $incidencia->titulo_incidencia }}</h2>
                </div>
                <div class="estado-stack">
                    <span class="estado estado-{{ str_replace('_', '-', $incidencia->estado_incidencia) }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
                    <span class="estado prioridad">Prioridad {{ ucfirst(strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia)) }}</span>
                </div>
            </div>

            <div class="datos-grid">
                <div>
                    <span class="dato-label">Reporta</span>
                    <span class="dato-valor">{{ $incidencia->nombre_reporta }}</span>
                </div>
                <div>
                    <span class="dato-label">Asignado a</span>
                    <span class="dato-valor">{{ $incidencia->nombre_asignado ?: 'Sin asignar' }}</span>
                </div>
                <div>
                    <span class="dato-label">Creada</span>
                    <span class="dato-valor">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="dato-label">Presupuesto</span>
                    <span class="dato-valor">{{ !is_null($incidencia->presupuesto_importe_incidencia) ? number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') . ' €' : 'Sin presupuesto' }}</span>
                </div>
            </div>

            <div class="bloque-descripcion">
                <span class="dato-label">Descripción</span>
                <p>{{ $incidencia->descripcion_incidencia }}</p>
            </div>

            @if(!is_null($incidencia->presupuesto_importe_incidencia))
                <div class="bloque-presupuesto">
                    <h3>Presupuesto de reparación</h3>
                    <p class="presupuesto-importe">{{ number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') }} €</p>
                    <p>{{ $incidencia->detalle_presupuesto_incidencia ?: 'Sin detalle adicional.' }}</p>
                </div>
            @endif

            @if($accionActual === 'esperando_decision')
                <div class="bloque-accion">
                    <h3>Decidir quién paga</h3>
                    <p>La incidencia está esperando tu decisión. Indica si la paga el arrendador o el inquilino para pasar al siguiente estado.</p>
                    <form method="POST" action="{{ route('arrendador.incidencias.decision', ['id' => $incidencia->id_incidencia, 'arrendador_id' => $arrendadorId]) }}" class="form-decision">
                        @csrf
                        <button type="submit" name="responsable_pago" value="arrendador" class="btn-accion btn-primario">La pago yo</button>
                        <button type="submit" name="responsable_pago" value="inquilino" class="btn-accion btn-secundario">La paga el inquilino</button>
                    </form>
                </div>
            @elseif($accionActual === 'esperando_pago')
                <div class="bloque-accion">
                    <h3>Esperando pago</h3>
                    <p>Responsable del pago: <strong>{{ ucfirst($incidencia->responsable_pago_incidencia ?: 'sin definir') }}</strong>.</p>
                    @if($incidencia->responsable_pago_incidencia === 'arrendador')
                        <form method="POST" action="{{ route('arrendador.incidencias.pagar', ['id' => $incidencia->id_incidencia, 'arrendador_id' => $arrendadorId]) }}" class="form-decision">
                            @csrf
                            <button type="submit" class="btn-accion btn-primario">Pagar</button>
                        </form>
                    @else
                        <p class="nota-pago">El inquilino es responsable del pago de esta incidencia.</p>
                    @endif
                </div>
            @elseif($accionActual === 'resuelta')
                <div class="bloque-accion info">
                    <h3>Incidencia resuelta</h3>
                    <p>El presupuesto ya se pagó y la incidencia está pendiente de cierre.</p>
                </div>
            @elseif($accionActual === 'cerrada')
                <div class="bloque-accion info">
                    <h3>Incidencia cerrada</h3>
                    <p>La incidencia quedó cerrada definitivamente.</p>
                </div>
            @else
                <div class="bloque-accion info">
                    <h3>Seguimiento</h3>
                    <p>La incidencia sigue su curso normal. Aquí podrás ver el presupuesto cuando el gestor lo emita.</p>
                </div>
            @endif
        </article>

        <aside class="columna-lateral">
            <article class="card">
                <h3>Historial</h3>
                <div class="timeline">
                    @forelse($historial as $item)
                        <div class="timeline-item">
                            <p>{{ $item->comentario_historial ?: 'Sin comentario' }}</p>
                            <span>{{ $item->nombre_usuario }} · {{ \Carbon\Carbon::parse($item->creado_historial)->format('d/m/Y H:i') }}</span>
                            @if($item->cambio_estado_historial)
                                <small>{{ ucfirst(str_replace('_', ' ', $item->cambio_estado_historial)) }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="muted">Aún no hay historial.</p>
                    @endforelse
                </div>
            </article>

            <article class="card">
                <h3>Documentación</h3>
                <div class="docs-lista">
                    @forelse($documentos as $doc)
                        <div class="doc-item">
                            <p>{{ $doc->nombre_documento }}</p>
                            <div class="doc-meta">
                                <span>{{ str_replace('_', ' ', $doc->tipo_documento) }}</span>
                                @if($doc->url_documento && $doc->url_documento !== 'sin-archivo')
                                    <a href="{{ $doc->url_documento }}" target="_blank" rel="noopener">Abrir</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted">Sin documentos vinculados.</p>
                    @endforelse
                </div>
            </article>
        </aside>
    </section>
</div>
</body>
</html>