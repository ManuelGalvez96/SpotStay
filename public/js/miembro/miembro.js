/**
 * Scripts para el panel de Miembro de SpotStay
 */
window.FiltrosMiembro = {
    obtenerValor: function (idCampo) {
        var campo = document.getElementById(idCampo);
        if (!campo || campo.value === "") {
            return "";
        }

        return campo.value;
    },

    obtenerFiltrosMapa: function () {
        return {
            precio_minimo: this.obtenerValor("precio-minimo"),
            precio_maximo: this.obtenerValor("precio-maximo"),
            tipo_inmueble: this.obtenerValor("tipo-inmueble"),
            habitaciones: this.obtenerValor("numero-habitaciones"),
            metros_minimo: this.obtenerValor("metros-minimo"),
            metros_maximo: this.obtenerValor("metros-maximo"),
        };
    },

    registrarBotonAplicar: function (idBoton, callback) {
        var boton = document.getElementById(idBoton);
        if (!boton || typeof callback !== "function") {
            return;
        }

        boton.onclick = function () {
            callback();
        };
    },
};

document.addEventListener('DOMContentLoaded', function () {
    const botonPerfil = document.getElementById('boton-perfil');
    const submenu = document.getElementById('submenu-perfil');

    // Toggle del submenú de perfil al hacer clic en el nombre/foto
    if (botonPerfil && submenu) {
        botonPerfil.onclick = function (e) {
            e.stopPropagation();
            submenu.classList.toggle('activo');
        };

        // Cerrar el submenú si se hace clic fuera de él
        document.onclick = function () {
            submenu.classList.remove('activo');
        };

        // Evitar que clics dentro del submenú lo cierren
        submenu.onclick = function (e) {
            e.stopPropagation();
        };
    }

    inicializarFiltrosInicio();
    inicializarMapaDetalle();
});

function inicializarMapaDetalle() {
    var mapaDetalle = document.getElementById('mapa-detalle');
    if (!mapaDetalle || typeof L === 'undefined') {
        return;
    }

    // Obtener latitud y longitud
    var lat = parseFloat(mapaDetalle.dataset.lat || '');
    var lng = parseFloat(mapaDetalle.dataset.lng || '');
    // Validar que latitud y longitud sean números válidos
    if (isNaN(lat) || isNaN(lng)) {
        return;
    }

    // Obtener título y dirección para popup. valores por defecto si no se encuentran
    var titulo = mapaDetalle.dataset.titulo || 'Propiedad';
    var direccion = mapaDetalle.dataset.direccion || 'Direccion no disponible';

    // Inicializar el mapa centrado en la ubicación de la propiedad
    var mapa = L.map('mapa-detalle', {
        zoomControl: true,
    }).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(mapa);

    L.marker([lat, lng])
        .addTo(mapa)
        .bindPopup('<strong>' + escaparHtml(titulo) + '</strong><br>' + escaparHtml(direccion))
        .openPopup();
}


function inicializarFiltrosInicio() {
    var formFiltros = document.querySelector('#panel-filtros-miembro form');
    if (!formFiltros) {
        return;
    }

    formFiltros.addEventListener('submit', function (evento) {
        evento.preventDefault();
        aplicarFiltrosInicio(formFiltros);
    });
}

function aplicarFiltrosInicio(formFiltros) {
    var params = new URLSearchParams();
    var formData = new FormData(formFiltros);

    formData.forEach(function (valor, clave) {
        var texto = String(valor).trim();
        if (texto !== '') {
            params.append(clave, texto);
        }
    });

    var action = formFiltros.getAttribute('action') || window.location.pathname;
    var url = params.toString() === '' ? action : action + '?' + params.toString();

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error('No se pudo cargar el listado filtrado');
            }

            return respuesta.text();
        })
        .then(function (html) {
            var parser = new DOMParser();
            var documento = parser.parseFromString(html, 'text/html');
            var nuevaGrid = documento.querySelector('.grid-propiedades');
            var nuevoContador = documento.querySelector('.contador-propiedades');
            var gridActual = document.querySelector('.grid-propiedades');
            var contadorActual = document.querySelector('.contador-propiedades');

            if (nuevaGrid && gridActual) {
                gridActual.innerHTML = nuevaGrid.innerHTML;
            }

            if (nuevoContador && contadorActual) {
                contadorActual.innerHTML = nuevoContador.innerHTML;
            }

            window.history.replaceState({}, '', url);
        })
        .catch(function () {
            // Si falla el fetch, se usa fallback por navegación normal
            window.location.href = url;
        });
}
