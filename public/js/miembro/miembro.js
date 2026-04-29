/**
 * Scripts para el panel de Miembro de SpotStay
 */
window.onload = function () {
    // Arranca el panel una vez el DOM ya esta cargado.
    var botonPerfil = document.getElementById('boton-perfil');
    var submenu = document.getElementById('submenu-perfil');

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

    inicializarMapaDetalle();

    cargarFiltrosInicio();
};

function cargarFiltrosInicio() {
    var formulario = document.getElementById('form-filtros-inicio');
    var boton = document.getElementById('boton-aplicar-filtros');
    var botonBorrar = document.getElementById('boton-borrar-filtros');

    if (!formulario) {
        return;
    }

    var ids = [
        'ciudad-propiedad',
        'precio-minimo',
        'precio-maximo',
        'tipo-inmueble',
        'numero-habitaciones',
        'banos-propiedad',
        'metros-minimo',
        'metros-maximo',
        'amueblado-propiedad',
        'terraza-propiedad',
        'piscina-propiedad',
        'garaje-propiedad',
        'ascensor-propiedad',
        'aire-acondicionado-propiedad',
        'calefaccion-propiedad',
        'trastero-propiedad'
    ];

    function ejecutarBusqueda() {
        // Construye la query con los filtros activos.
        var parametros = new URLSearchParams();
        var i;

        // Recorre cada filtro y lo anade si tiene valor.
        for (i = 0; i < ids.length; i++) {
            var campo = document.getElementById(ids[i]);
            if (campo && campo.value !== '') {
                parametros.append(campo.name || ids[i], campo.value);
            }
        }

        // Genera la URL final con los parametros activos.
        var url = formulario.getAttribute('action') || window.location.pathname;
        // Convierte los parametros a query string.
        var query = parametros.toString();
        if (query !== '') {
            url += '?' + query;
        }

        // Pide el listado filtrado en JSON.
        fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('No se pudo cargar el listado');
                }

                return respuesta.json();
            })
            .then(function (datos) {
                var data = datos && datos.data ? datos.data : {};
                var propiedades = data.propiedades ? data.propiedades : [];
                var total = data.total ? data.total : 0;
                var gridActual = document.getElementById('grid-propiedades');
                var contadorActual = document.getElementById('contador-propiedades');
                var html = '';
                var i;

                // Construye el HTML del listado con los datos recibidos.
                if (gridActual) {
                    for (i = 0; i < propiedades.length; i++) {
                        var propiedad = propiedades[i] || {};
                        var id = propiedad.id_propiedad ? propiedad.id_propiedad : '';
                        var titulo = escaparHtml(propiedad.titulo_propiedad ? propiedad.titulo_propiedad : 'Propiedad');
                        var ciudad = escaparHtml(propiedad.ciudad_propiedad ? propiedad.ciudad_propiedad : '');
                        var direccion = escaparHtml(propiedad.direccion_propiedad ? propiedad.direccion_propiedad : '');
                        var precio = formatearPrecio(propiedad.precio_propiedad);
                        var ubicacion = '';

                        if (ciudad !== '' && direccion !== '') {
                            ubicacion = ciudad + ' · ' + direccion;
                        } else if (ciudad !== '') {
                            ubicacion = ciudad;
                        } else {
                            ubicacion = direccion;
                        }

                        html +=
                            "<a class='link-propiedad' href='/miembro/propiedad/" + id + "'>" +
                            "<article class='tarjeta-propiedad'>" +
                            "<div class='imagen-propiedad'>" +
                            "<span class='etiqueta-precio-tarjeta'>" + precio + "</span>" +
                            "</div>" +
                            "<div class='contenido-propiedad'>" +
                            "<h3 class='titulo-propiedad'>" + titulo + "</h3>" +
                            "<p class='ubicacion-propiedad'>" + ubicacion + "</p>" +
                            "<p class='precio-propiedad'>" + precio + " / mes</p>" +
                            "</div>" +
                            "</article>" +
                            "</a>";
                    }

                    // Si no hay resultados, muestra el mensaje vacio.
                    if (html === '') {
                        html = "<div class='estado-vacio'><p>No hay propiedades disponibles en este momento.</p></div>";
                    }

                    gridActual.innerHTML = html;
                }

                if (contadorActual) {
                    contadorActual.innerHTML = total + ' resultados';
                }
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    formulario.onsubmit = function (evento) {
        // Evita recargar pagina y usa fetch.
        evento.preventDefault();
        ejecutarBusqueda();
    };

    if (boton) {
        boton.onclick = function (evento) {
            // Aplica filtros con un click del usuario.
            if (evento) {
                evento.preventDefault();
            }
            ejecutarBusqueda();
        };
    }

    if (botonBorrar) {
        botonBorrar.onclick = function (evento) {
            var i;

            if (evento) {
                evento.preventDefault();
            }

            for (i = 0; i < ids.length; i++) {
                var campo = document.getElementById(ids[i]);
                if (campo) {
                    campo.value = '';
                }
            }

            ejecutarBusqueda();
        };
    }
}

function formatearPrecio(valor) {
    if (valor === null || valor === undefined || valor === '') {
        return 'Sin precio';
    }

    var numero = Number(valor);
    if (isNaN(numero)) {
        return 'Sin precio';
    }

    return numero.toLocaleString('es-ES', {
        maximumFractionDigits: 0
    }) + ' €';
}

function escaparHtml(texto) {
    var mapaCaracteres = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return String(texto).replace(/[&<>"']/g, function (caracter) {
        return mapaCaracteres[caracter];
    });
}

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


