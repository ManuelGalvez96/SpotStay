<p>Hola {{ $destinatarioNombre ?? 'contacto' }},</p>

<p>{{ $mensaje }}</p>

<hr>
<p>Detalles de la incidencia:</p>
<ul>
    <li><strong>Título:</strong> {{ $incidencia->titulo_incidencia ?? '' }}</li>
    <li><strong>Propiedad:</strong> {{ $incidencia->direccion_propiedad ?? '' }} — {{ $incidencia->ciudad_propiedad ?? '' }}</li>
    <li><strong>Fecha reporte:</strong> {{ $incidencia->creado_incidencia ?? '' }}</li>
    <li><strong>Prioridad:</strong> {{ $incidencia->prioridad_incidencia ?? '' }}</li>
</ul>

<p>Saludos,<br>El equipo de SpotStay</p>
