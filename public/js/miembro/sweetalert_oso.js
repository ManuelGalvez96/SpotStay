var crearOsoExito = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel éxito sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#228B22" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#228B22">✓</text>
    </svg>
    `;
};

/* SVG del oso con cartel de error */
var crearOsoError = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel error sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>
    `;
};

var mostrarAlertaExito = function(titulo, mensaje) {
    if (typeof Swal === 'undefined') {
        alert(titulo + " - " + mensaje);
        return;
    }

    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoExito(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#035498'
    });
};

var mostrarAlertaError = function(titulo, mensaje) {
    if (typeof Swal === 'undefined') {
        alert(titulo + " - " + mensaje);
        return;
    }

    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoError(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#d9534f'
    });
};
