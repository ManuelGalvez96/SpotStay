/**
 * Lógica de filtrado dinámico para la gestión de propiedades (Inquilino/Propietario)
 */
document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('busqueda-nombre');
    const contenedorGrid = document.getElementById('contenedor-grid-propiedades');
    
    // Elementos del Custom Select
    const customSelect = document.getElementById('custom-select-ciudad');
    const hiddenInputCiudad = document.getElementById('filtro-ciudad-valor');

    if (!inputNombre || !customSelect || !contenedorGrid) return;

    const trigger = customSelect.querySelector('.select-trigger');
    const options = customSelect.querySelectorAll('.option-item');
    const selectedText = customSelect.querySelector('.selected-value');

    let timeoutBusqueda = null;

    // --- EVENTOS ---

    // 1. Buscador de texto (Debounce)
    inputNombre.addEventListener('input', function () {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(actualizarFiltros, 300);
    });

    // 2. Custom Select - Abrir/Cerrar
    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        customSelect.classList.toggle('active');
    });

    // 3. Custom Select - Seleccionar Opción
    options.forEach(option => {
        option.addEventListener('click', function () {
            const value = this.dataset.value;
            const text = this.textContent;

            // Actualizar interfaz
            selectedText.textContent = text;
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            // Actualizar valor oculto y disparar filtro
            hiddenInputCiudad.value = value;
            customSelect.classList.remove('active');
            actualizarFiltros();
        });
    });

    // 4. Cerrar select al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!customSelect.contains(e.target)) {
            customSelect.classList.remove('active');
        }
    });

    /**
     * Realiza la petición Fetch al servidor y actualiza la rejilla
     */
    function actualizarFiltros() {
        const q = inputNombre.value;
        const ciudad = hiddenInputCiudad.value;

        // Preparamos los parámetros de búsqueda
        const params = new URLSearchParams();
        if (q) params.append('q', q);
        if (ciudad) params.append('ciudad', ciudad);

        // Construimos la URL final
        const baseUrl = window.location.pathname;
        const urlFinal = `${baseUrl}?${params.toString()}`;

        // Efecto visual de carga
        contenedorGrid.style.opacity = '0.6';
        contenedorGrid.style.transition = 'opacity 0.2s ease';

        // Petición AJAX mediante Fetch
        fetch(urlFinal, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            contenedorGrid.innerHTML = html;
            contenedorGrid.style.opacity = '1';
            
            // Re-ejecutar el temporizador para los nodos recién creados por AJAX
            if (typeof window.iniciarTemporizadorAlquileres === 'function') {
                window.iniciarTemporizadorAlquileres();
            }
        })
        .catch(error => {
            console.error('Error en el filtrado dinámico:', error);
            contenedorGrid.style.opacity = '1';
        });
    }
});
