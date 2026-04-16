@extends('layouts.gestor')
@section('titulo', 'Incidencia #' . $incidencia->id_incidencia . ' - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/gestor/incidencia.css') }}">
@endsection

@section('content')
<div class="incidencia-shell">
    <a href="{{ url('/gestor/dashboard') }}" class="volver-link">← Volver al dashboard</a>

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

                @if($accionActual === 'iniciar')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/iniciar') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>1. Aceptar / iniciar gestión</h3>
                        <p>Primera acción obligatoria para mover la incidencia a en proceso.</p>
                        <button type="submit" class="btn-principal">Iniciar gestión</button>
                    </form>
                @elseif($accionActual === 'comunicacion')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/comunicacion') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>2. Registrar comunicación</h3>
                        <p>Registra el primer contacto operativo para dejar trazabilidad.</p>
                        <select name="destinatario" required>
                            <option value="">Destinatario</option>
                            <option value="arrendador">Arrendador</option>
                            <option value="empresa">Empresa</option>
                            <option value="inquilino">Inquilino</option>
                        </select>
                        <textarea name="mensaje" required placeholder="Escribe el mensaje"></textarea>
                        <button type="submit" class="btn-principal">Guardar y continuar</button>
                    </form>
                @elseif($accionActual === 'presupuesto')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/presupuesto') }}" enctype="multipart/form-data" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>3. Generar presupuesto</h3>
                        <p>Calcula el coste de intervención y envíalo para validación.</p>
                        <input type="number" step="0.01" min="0" name="importe" placeholder="Importe (EUR)" required>
                        <textarea name="detalle_presupuesto" required placeholder="Detalle del presupuesto"></textarea>
                        <input type="file" name="pdf_presupuesto" accept="application/pdf">
                        <button type="submit" class="btn-principal">Guardar y continuar</button>
                    </form>
                @elseif($accionActual === 'documento')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/documento') }}" enctype="multipart/form-data" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>4. Subir documentación</h3>
                        <p>Adjunta pruebas del presupuesto o intervención planificada.</p>
                        <input type="text" name="descripcion" maxlength="150" placeholder="Nombre o descripción del archivo">
                        <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf" required>
                        <button type="submit" class="btn-principal">Guardar y continuar</button>
                    </form>
                @elseif($accionActual === 'intervencion')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/intervencion') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>5. Registrar intervención</h3>
                        <p>Documenta la actuación realizada antes del cierre.</p>
                        <textarea name="comentario_intervencion" required placeholder="Comentario de intervención"></textarea>
                        <button type="submit" class="btn-principal">Guardar y continuar</button>
                    </form>
                @elseif($accionActual === 'cierre')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/estado') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>6. Cerrar o dejar en espera</h3>
                        <p>Último paso: marca la incidencia como resuelta o en espera.</p>
                        <select name="estado" required>
                            <option value="">Selecciona estado</option>
                            @foreach($siguientesEstados as $estado)
                                <option value="{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                            @endforeach
                        </select>
                        <textarea name="comentario" placeholder="Comentario del cambio (opcional)"></textarea>
                        <button type="submit" class="btn-principal">Actualizar estado</button>
                    </form>
                @elseif($accionActual === 'reanudar')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/estado') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>Incidencia en espera</h3>
                        <p>Actualmente está en espera de {{ $incidencia->esperando_de_incidencia ?: 'respuesta externa' }}.</p>
                        <input type="hidden" name="estado" value="en_proceso">
                        <textarea name="comentario" placeholder="Indica por qué se reanuda la gestión"></textarea>
                        <button type="submit" class="btn-principal">Reanudar gestión</button>
                    </form>
                @elseif($accionActual === 'reabrir')
                    <form method="POST" action="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia . '/estado') }}" class="bloque-accion bloque-unico">
                        @csrf
                        <h3>Incidencia resuelta</h3>
                        <p>Si vuelve a abrirse el caso, puedes reactivar el flujo.</p>
                        <input type="hidden" name="estado" value="en_proceso">
                        <textarea name="comentario" placeholder="Motivo de reapertura"></textarea>
                        <button type="submit" class="btn-principal">Reabrir incidencia</button>
                    </form>
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
