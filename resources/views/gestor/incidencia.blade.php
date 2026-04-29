@extends('layouts.gestor')
@section('titulo', 'Incidencia #' . $incidencia->id_incidencia . ' - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/gestor/incidencia.css') }}">
@endsection

@section('content')
<div class="incidencia-shell">
    <a href="{{ url('/gestor/dashboard') }}" class="volver-link">← Volver al dashboard</a>

    @if(session('ok'))
        <div class="alerta ok" id="flash-ok" data-msg="{{ session('ok') }}"></div>
    @endif

    @if(session('error'))
        <div class="alerta error" id="flash-error" data-msg="{{ session('error') }}"></div>
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

    <section class="incidencia-head card-gestor">
        <div>
            <p class="kicker">INCIDENCIA #{{ $incidencia->id_incidencia }}</p>
            <h1>{{ $incidencia->titulo_incidencia }}</h1>
            <p class="meta-linea">{{ $incidencia->direccion_propiedad }}, {{ $incidencia->ciudad_propiedad }} · Reporta: {{ $incidencia->nombre_reporta }}</p>
        </div>
        <div class="estado-box">
            <span class="badge estado {{ str_replace('_', '-', $incidencia->estado_incidencia) }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
            <span class="badge prioridad prioridad-{{ strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia) }}">{{ ucfirst(strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia)) }}</span>
            @if($incidencia->esperando_de_incidencia)
                <span class="badge espera">Esperando {{ $incidencia->esperando_de_incidencia }}</span>
            @endif
        </div>
    </section>

    <section class="incidencia-grid">
        <div class="col-main">
            <article class="card-gestor">
                <h2>Detalle de la incidencia</h2>
                <p class="descripcion">{{ $incidencia->descripcion_incidencia }}</p>
                <div class="datos-grid">
                    <div>
                        <span class="dato-label">Categoría</span>
                        <span class="dato-valor">{{ ucfirst($incidencia->categoria_incidencia) }}</span>
                    </div>
                    <div>
                        <span class="dato-label">Asignado a</span>
                        <span class="dato-valor">{{ $incidencia->nombre_asignado ?: 'Sin asignar' }}</span>
                    </div>
                    <div>
                        <span class="dato-label">Arrendador</span>
                        <span class="dato-valor">{{ $incidencia->nombre_arrendador }}</span>
                    </div>
                    <div>
                        <span class="dato-label">Creada</span>
                        <span class="dato-valor">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </article>

            <article class="card-gestor">
                <h2>Acciones del gestor</h2>

                @if($accionActual === 'presupuesto')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/presupuesto') }}" enctype="multipart/form-data" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>Generar presupuesto de reparación</h3>
                        <p>Introduce el coste para enviarlo al arrendador. La incidencia pasará a esperando decisión.</p>
                        <input type="number" step="0.01" min="0" name="importe" placeholder="Importe (EUR)" required>
                        <textarea name="detalle_presupuesto" required placeholder="Detalle del presupuesto"></textarea>
                        <input type="file" name="pdf_presupuesto" accept="application/pdf">
                        <button type="submit" class="btn-principal">Confirmar presupuesto</button>
                    </form>
                @elseif($accionActual === 'sin_permiso_asignado')
                    <div class="bloque-accion bloque-unico">
                        <h3>Sin acciones disponibles</h3>
                        <p>Esta incidencia está asignada a otra persona. Solo el usuario asignado puede proponer el presupuesto.</p>
                    </div>
                @elseif($accionActual === 'esperando_decision')
                    <div class="bloque-accion bloque-unico">
                        <h3>Pendiente del arrendador</h3>
                        <p>Presupuesto enviado. El arrendador debe decidir si paga él o el inquilino.</p>
                        @if(!is_null($incidencia->presupuesto_importe_incidencia))
                            <p><strong>Importe propuesto:</strong> {{ number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') }} EUR</p>
                        @endif
                    </div>
                @elseif($accionActual === 'esperando_pago')
                    <div class="bloque-accion bloque-unico">
                        <h3>Pendiente de pago</h3>
                        <p>Se está esperando el pago del presupuesto por parte de: {{ $incidencia->responsable_pago_incidencia ?: 'sin definir' }}.</p>
                    </div>
                @elseif($accionActual === 'resuelta')
                    <div class="bloque-accion bloque-unico">
                        <h3>Incidencia resuelta</h3>
                        <p>El presupuesto ya se ha pagado. Falta que el inquilino confirme la resolución para cerrarla.</p>
                    </div>
                @elseif($accionActual === 'cerrada')
                    <div class="bloque-accion bloque-unico">
                        <h3>Incidencia cerrada</h3>
                        <p>El inquilino ha confirmado la resolución y la incidencia está cerrada definitivamente.</p>
                    </div>
                @else
                    <div class="bloque-accion bloque-unico">
                        <h3>Sin acciones disponibles</h3>
                        <p>No hay acciones pendientes para el gestor en este estado.</p>
                    </div>
                @endif
            </article>
        </div>

        <aside class="col-side">
            <article class="card-gestor">
                <h2>Historial</h2>
                <div class="timeline">
                    @forelse($historial as $item)
                        <div class="timeline-item">
                            <p class="timeline-comentario">{{ $item->comentario_historial ?: 'Sin comentario' }}</p>
                            <p class="timeline-meta">{{ $item->nombre_usuario }} · {{ \Carbon\Carbon::parse($item->creado_historial)->format('d/m/Y H:i') }}</p>
                            @if($item->cambio_estado_historial)
                                <span class="badge estado-mini">{{ ucfirst(str_replace('_', ' ', $item->cambio_estado_historial)) }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="vacio">Aún no hay historial.</p>
                    @endforelse
                </div>
            </article>

            <article class="card-gestor">
                <h2>Documentación</h2>
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
                        <p class="vacio">Sin documentos vinculados.</p>
                    @endforelse
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection
