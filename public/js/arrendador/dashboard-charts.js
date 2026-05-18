// Gráficos del Dashboard del Arrendador

document.addEventListener('DOMContentLoaded', function() {
    inicializarGraficos();
});

function inicializarGraficos() {
    // Obtener datos del HTML
    const datosEstados = obtenerDatosEstados();
    const ingresosMes = obtenerIngresosMes();
    const totalPropiedades = obtenerTotalPropiedades();
    const propiedadesAlquiladas = obtenerPropiedadesAlquiladas();
    
    // Crear gráfico de propiedades por estado
    if (datosEstados && document.getElementById('chartEstados')) {
        crearGraficoEstados(datosEstados);
    }
    
    // Crear gráfico de ocupación
    if (totalPropiedades && document.getElementById('chartIngresos')) {
        crearGraficoOcupacion(propiedadesAlquiladas, totalPropiedades);
    }
}

function obtenerDatosEstados() {
    const elemento = document.querySelector('[data-estados-json]');
    if (elemento) {
        try {
            return JSON.parse(elemento.getAttribute('data-estados-json'));
        } catch (e) {
            console.error('Error al parsear datos de estados:', e);
            return null;
        }
    }
    return null;
}

function obtenerIngresosMes() {
    const elemento = document.querySelector('[data-ingresos-mes]');
    return elemento ? parseFloat(elemento.getAttribute('data-ingresos-mes')) : 0;
}

function obtenerTotalPropiedades() {
    const elemento = document.querySelector('[data-total-propiedades]');
    return elemento ? parseInt(elemento.getAttribute('data-total-propiedades')) : 0;
}

function obtenerPropiedadesAlquiladas() {
    const elemento = document.querySelector('[data-propiedades-alquiladas]');
    return elemento ? parseInt(elemento.getAttribute('data-propiedades-alquiladas')) : 0;
}

function crearGraficoEstados(datosEstados) {
    const ctx = document.getElementById('chartEstados').getContext('2d');
    
    const estados = Object.keys(datosEstados);
    const cantidades = Object.values(datosEstados);
    
    const colores = {
        'publicada': '#19a974',
        'alquilada': '#0066cc',
        'inactiva': '#ff6b6b',
        'borrador': '#868e96'
    };
    
    const backgroundColors = estados.map(estado => colores[estado] || '#999');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: estados.map(e => e.charAt(0).toUpperCase() + e.slice(1)),
            datasets: [{
                data: cantidades,
                backgroundColor: backgroundColors,
                borderColor: 'white',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                }
            }
        }
    });
}

function crearGraficoOcupacion(alquiladas, total) {
    const ctx = document.getElementById('chartIngresos').getContext('2d');
    
    const porcentajeOcupacion = total > 0 ? (alquiladas / total * 100).toFixed(1) : 0;
    const disponibles = total - alquiladas;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Alquiladas', 'Disponibles'],
            datasets: [{
                data: [alquiladas, disponibles],
                backgroundColor: ['#19a974', '#e8e8e8'],
                borderColor: 'white',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
