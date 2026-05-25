/**
 * Lógica exclusiva para la vista gestionar_propiedades
 */

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

    // 4. Cerrar selector al hacer clic fuera
    document.onclick = (e) => {
        if (!selectorPersonalizado.contains(e.target)) {
            selectorPersonalizado.classList.remove('active');
        }
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

        const urlFinal = `${window.location.pathname}?${parametros.toString()}`;

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
            
            // Re-ejecutar el temporizador para los nuevos elementos (si existe en comun.js)
            if (typeof iniciarTemporizadorAlquileres === 'function') {
                iniciarTemporizadorAlquileres();
            }
        })
        .catch(error => {
            console.error('Error en el filtrado:', error);
            contenedorGrilla.style.opacity = '1';
        });
    }
}

// Inicialización directa
iniciarFiltrosGestion();
