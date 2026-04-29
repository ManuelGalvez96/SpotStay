/**
 * Lógica de filtrado dinámico para la gestión de propiedades (Inquilino/Propietario)
 * Cumple con los estándares de SpotStay: sin addEventListener y con asignación directa.
 */

const anteriorOnloadFiltrosGestion = window.onload;
window.onload = () => {
    if (anteriorOnloadFiltrosGestion) anteriorOnloadFiltrosGestion();
    iniciarFiltrosGestion();
};

function iniciarFiltrosGestion() {
    const entradaNombre = document.getElementById('busqueda-nombre');
    const contenedorGrilla = document.getElementById('contenedor-grid-propiedades');
    
    // Elementos del Selector Personalizado
    const selectorPersonalizado = document.getElementById('custom-select-ciudad');
    const entradaOcultaCiudad = document.getElementById('filtro-ciudad-valor');

    if (!entradaNombre || !selectorPersonalizado || !contenedorGrilla) return;

    const disparador = selectorPersonalizado.querySelector('.select-trigger');
    const opciones = selectorPersonalizado.querySelectorAll('.option-item');
    const textoSeleccionado = selectorPersonalizado.querySelector('.selected-value');

    let pausaBusqueda = null;

    // --- ASIGNACIÓN DIRECTA DE EVENTOS ---

    // 1. Buscador de texto con Debounce
    entradaNombre.oninput = () => {
        clearTimeout(pausaBusqueda);
        pausaBusqueda = setTimeout(actualizarFiltrosGrilla, 300);
    };

    // 2. Abrir/Cerrar selector
    disparador.onclick = (e) => {
        e.stopPropagation();
        selectorPersonalizado.classList.toggle('active');
    };

    // 3. Seleccionar opción del listado
    opciones.forEach(opcion => {
        opcion.onclick = function() {
            const valor = this.getAttribute('data-value');
            const texto = this.textContent;

            // Actualizar interfaz visual
            textoSeleccionado.textContent = texto;
            opciones.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            // Actualizar valor y disparar filtro
            entradaOcultaCiudad.value = valor;
            selectorPersonalizado.classList.remove('active');
            actualizarFiltrosGrilla();
        };
    });

    // 4. Cerrar selector al hacer clic fuera (Asignación directa al body/document)
    const cerrarAlClicarFuera = (e) => {
        if (!selectorPersonalizado.contains(e.target)) {
            selectorPersonalizado.classList.remove('active');
        }
    };
    
    // Guardamos el onclick previo del documento si existe
    const anteriorOnClickDocumento = document.onclick;
    document.onclick = (e) => {
        if (anteriorOnClickDocumento) anteriorOnClickDocumento(e);
        cerrarAlClicarFuera(e);
    };

    /**
     * Realiza la petición Fetch al servidor y actualiza la rejilla
     */
    function actualizarFiltrosGrilla() {
        const busqueda = entradaNombre.value;
        const ciudad = entradaOcultaCiudad.value;

        const parametros = new URLSearchParams();
        if (busqueda) parametros.append('q', busqueda);
        if (ciudad) parametros.append('ciudad', ciudad);

        const urlBase = window.location.pathname;
        const urlFinal = `${urlBase}?${parametros.toString()}`;

        // Efecto visual de carga
        contenedorGrilla.style.opacity = '0.6';
        contenedorGrilla.style.transition = 'opacity 0.2s ease';

        fetch(urlFinal, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(respuesta => {
            if (!respuesta.ok) throw new Error('Error en el servidor');
            return respuesta.text();
        })
        .then(html => {
            contenedorGrilla.innerHTML = html;
            contenedorGrilla.style.opacity = '1';
            
            // Re-ejecutar el temporizador para los nuevos elementos (si existe)
            if (typeof window.iniciarTemporizadorAlquileres === 'function') {
                window.iniciarTemporizadorAlquileres();
            }
        })
        .catch(error => {
            console.error('Error en el filtrado:', error);
            contenedorGrilla.style.opacity = '1';
        });
    }
}
