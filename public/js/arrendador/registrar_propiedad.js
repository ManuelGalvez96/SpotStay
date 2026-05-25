/* =========================================================
   MAPA DE REGISTRO / EDICIÓN DE PROPIEDAD
   Se inicializa una sola vez y se reutiliza cada vez que el
   modal de formulario se abre, tanto para crear como para editar.
   ========================================================= */

function formatearDireccionAutocompletada(datos) {
    if (!datos) {
        return '';
    }

    var direccion = datos.address || {};
    var via = direccion.road || direccion.pedestrian || direccion.footway || direccion.path || direccion.street || '';
    var numero = direccion.house_number || '';
    var municipio = direccion.city || direccion.town || direccion.village || direccion.municipality || direccion.county || '';
    var codigoPostal = direccion.postcode || '';

    var primeraLinea = [via, numero].filter(Boolean).join(' ').trim();
    var segundaLinea = [municipio, codigoPostal].filter(Boolean).join(' ').trim();

    if (primeraLinea && segundaLinea) {
        return primeraLinea + ', ' + segundaLinea;
    }

    return primeraLinea || segundaLinea || datos.display_name || '';
}

(function () {
    var mapaInicializado = false;

    // Latitud/Longitud por defecto (Barcelona)
    var LAT_DEFAULT = 41.38684;
    var LNG_DEFAULT = 2.16959;
    var ZOOM_DEFAULT = 7;

    /**
     * Inicializa el mapa Leaflet y sus eventos.
     * Se llama una única vez cuando el contenedor existe en el DOM.
     */
    function inicializarMapa() {
        var mapContainer = document.getElementById('mapa-registro');
        if (!mapContainer || typeof L === 'undefined' || mapaInicializado) {
            return;
        }

        var inputDireccion = document.getElementById('direccion_propiedad');
        var inputLatitud   = document.getElementById('latitud_propiedad');
        var inputLongitud  = document.getElementById('longitud_propiedad');

        // Usar lat/lng guardados si ya existen (modo edición)
        var latInicial  = (inputLatitud  && inputLatitud.value)  ? parseFloat(inputLatitud.value)  : LAT_DEFAULT;
        var lngInicial  = (inputLongitud && inputLongitud.value)  ? parseFloat(inputLongitud.value)  : LNG_DEFAULT;
        var zoomInicial = (inputLatitud  && inputLatitud.value)  ? 17 : ZOOM_DEFAULT;

        var mapa    = L.map('mapa-registro').setView([latInicial, lngInicial], zoomInicial);
        var marcador = L.marker([latInicial, lngInicial], { draggable: true }).addTo(mapa);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19,
        }).addTo(mapa);

        // Exponer referencias globales para poder reutilizarlas
        window.mapaRegistro   = mapa;
        window.marcadorRegistro = marcador;
        mapaInicializado = true;

        // --- Funciones internas ---

        function actualizarInputs(lat, lng, direccion) {
            if (inputLatitud)  { inputLatitud.value  = lat.toFixed(7); }
            if (inputLongitud) { inputLongitud.value = lng.toFixed(7); }
            if (typeof direccion === 'string' && inputDireccion) {
                inputDireccion.value = direccion;
            }
            // Notificar al sistema de validación que los campos cambiaron
            if (typeof window.actualizarEstadoValidacionFormularioPropiedad === 'function') {
                window.actualizarEstadoValidacionFormularioPropiedad();
            }
        }

        function reverseGeocoding(lat, lng) {
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(function (r) { return r.json(); })
                .then(function (datos) {
                    actualizarInputs(lat, lng, formatearDireccionAutocompletada(datos));
                })
                .catch(function () {
                    // Si falla el geocoding, al menos guardamos las coordenadas
                    actualizarInputs(lat, lng, '');
                });
        }

        function actualizarDesdePosicion(posicion) {
            marcador.setLatLng(posicion);
            reverseGeocoding(posicion.lat, posicion.lng);
        }

        marcador.on('dragend', function () {
            actualizarDesdePosicion(marcador.getLatLng());
        });

        mapa.on('click', function (evento) {
            actualizarDesdePosicion(evento.latlng);
        });

        // Si al inicializar ya tenemos coordenadas (edición) no hacer reverse geocoding
        // para no sobreescribir la dirección ya guardada
        if (!inputLatitud || !inputLatitud.value) {
            reverseGeocoding(latInicial, lngInicial);
        }
    }

    /**
     * Centra el mapa en las coordenadas actuales de los inputs.
     * Se llama cada vez que el modal se abre.
     */
    window.centrarMapaEnCoordenadas = function () {
        if (!window.mapaRegistro || !window.marcadorRegistro) {
            return;
        }

        var inputLatitud   = document.getElementById('latitud_propiedad');
        var inputLongitud  = document.getElementById('longitud_propiedad');
        var inputDireccion = document.getElementById('direccion_propiedad');

        var lat = inputLatitud  && inputLatitud.value  ? parseFloat(inputLatitud.value)  : null;
        var lng = inputLongitud && inputLongitud.value ? parseFloat(inputLongitud.value) : null;

        setTimeout(function () {
            try {
                window.mapaRegistro.invalidateSize();

                if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                    window.mapaRegistro.setView([lat, lng], 17);
                    window.marcadorRegistro.setLatLng([lat, lng]);

                    // Rellenar la dirección autocompletada desde las coordenadas (Nominatim)
                    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                        .then(function (r) { return r.json(); })
                        .then(function (datos) {
                            var direccionAutocompletada = formatearDireccionAutocompletada(datos);

                            if (inputDireccion && direccionAutocompletada) {
                                inputDireccion.value = direccionAutocompletada;
                            }
                            // Notificar al validador que los campos cambiaron
                            if (typeof window.actualizarEstadoValidacionFormularioPropiedad === 'function') {
                                window.actualizarEstadoValidacionFormularioPropiedad();
                            }
                        })
                        .catch(function () {
                            // Si falla Nominatim, dejamos la dirección que había
                        });
                } else {
                    var pos = window.marcadorRegistro.getLatLng();
                    if (pos) {
                        window.mapaRegistro.setView([pos.lat, pos.lng], window.mapaRegistro.getZoom());
                    }
                }
            } catch (e) {
                console.error('Error al centrar mapa:', e);
            }
        }, 250);
    };

    // Intentar inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarMapa);
    } else {
        inicializarMapa();
    }

    // También intentar inicializar con window.onload como seguridad
    var onloadAnterior = window.onload;
    window.onload = function () {
        if (onloadAnterior) { onloadAnterior(); }
        inicializarMapa();
    };
})();
