window.onload = function () {
    var mapContainer = document.getElementById('mapa-registro');
    if (!mapContainer || typeof L === 'undefined') {
        return;
    }

    var inputDireccion = document.getElementById('direccion_propiedad');
    var inputLatitud = document.getElementById('latitud_propiedad');
    var inputLongitud = document.getElementById('longitud_propiedad');

    var latInicial = 41.38684;
    var lngInicial = 2.16959;
    var zoomInicial = 7;

    // Centra el mapa en la ubicación inicial o en la seleccionada anteriormente
    if (inputLatitud && inputLatitud.value && inputLongitud && inputLongitud.value) {
        latInicial = parseFloat(inputLatitud.value);
        lngInicial = parseFloat(inputLongitud.value);
        zoomInicial = 17;
    }

    var mapa = L.map('mapa-registro').setView([latInicial, lngInicial], zoomInicial);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19,
    }).addTo(mapa);

    var marcador = L.marker([latInicial, lngInicial], {
        draggable: true,
    }).addTo(mapa);

    function actualizarInputs(lat, lng, direccion) {
        if (inputLatitud) {
            inputLatitud.value = lat.toFixed(7);
        }

        if (inputLongitud) {
            inputLongitud.value = lng.toFixed(7);
        }

        if (typeof direccion === 'string' && inputDireccion) {
            inputDireccion.value = direccion;
        }
    }

    // Función para realizar reverse geocoding usando Nominatim
    // Obtiene la dirección a partir de latitud y longitud y actualiza los inputs correspondientes
    function reverseGeocoding(lat, lng) {
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
            .then(function (respuesta) {
                return respuesta.json();
            })
            .then(function (datos) {
                actualizarInputs(lat, lng, datos.display_name || '');
            })
            .catch(function () {
                // Si falla el geocoding, mantiene lat/lng para no bloquear el formulario
                actualizarInputs(lat, lng, '');
            });
    }

    // Funcion para actualizar la posición del marcador y hacer reverse geocoding cada vez que el marcador cambia de posición
    function actualizarDesdePosicion(posicion) {
        marcador.setLatLng(posicion);
        reverseGeocoding(posicion.lat, posicion.lng);
    }

    // Para cuando el marcador se arrastra
    marcador.on('dragend', function () {
        actualizarDesdePosicion(marcador.getLatLng());
    });

    // Para cuando se hace clic en el mapa
    mapa.on('click', function (evento) {
        actualizarDesdePosicion(evento.latlng);
    });

    reverseGeocoding(latInicial, lngInicial);

    var form = document.getElementById('form-registrar-propiedad');
    if (!form) {
        return;
    }

    // Valida que se haya seleccionado una ubicación en el mapa antes de permitir enviar el formulario
    form.onsubmit = function (evento) {
        var tieneLat = inputLatitud && inputLatitud.value.trim() !== '';
        var tieneLng = inputLongitud && inputLongitud.value.trim() !== '';
        var tieneDireccion = inputDireccion && inputDireccion.value.trim() !== '';

        if (!tieneLat || !tieneLng || !tieneDireccion) {
            evento.preventDefault();
            alert('Debes seleccionar una ubicacion en el mapa para completar direccion, latitud y longitud.');
        }
    };
};
