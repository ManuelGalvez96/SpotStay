const crearOsoExito = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle cx="62" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="138" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#004A99" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#FFFFFF" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle cx="48" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="152" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#006400" stroke-width="2.5"/>
        <text x="100" y="165" font-size="32" font-weight="bold" text-anchor="middle" fill="#006400">✓</text>
    </svg>`;

const crearOsoError = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle cx="62" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="138" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#004A99" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#FFFFFF" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle cx="48" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="152" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="165" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>`;

function swalSuccess(title, text) {
    if (window.Swal) {
        return Swal.fire({
            title: title || 'Éxito',
            text: text || '',
            iconHtml: crearOsoExito(),
            customClass: { icon: 'oso-icon' },
            confirmButtonColor: '#035498'
        });
    }
    alert((title || 'Éxito') + '\n' + (text || ''));
}

function swalError(title, text) {
    if (window.Swal) {
        return Swal.fire({
            title: title || 'Error',
            text: text || '',
            iconHtml: crearOsoError(),
            customClass: { icon: 'oso-icon' },
            confirmButtonColor: '#d9534f'
        });
    }
    alert((title || 'Error') + '\n' + (text || ''));
}

window.swalSuccess = swalSuccess;
window.swalError = swalError;