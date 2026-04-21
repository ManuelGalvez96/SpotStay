document.addEventListener('DOMContentLoaded', () => {
    // Iniciar el cálculo en la primera carga
    iniciarTemporizadorAlquileres();

    // Configurar el intervalo para actualizar cada 1 minuto
    setInterval(iniciarTemporizadorAlquileres, 60000);
});

function iniciarTemporizadorAlquileres() {
    const nodosTemporizador = document.querySelectorAll('.js-tiempo-restante');

    nodosTemporizador.forEach(nodo => {
        const fechaFinRaw = nodo.getAttribute('data-fecha-fin');
        if (!fechaFinRaw) return;

        // Formato esperado de DB: "YYYY-MM-DD" o "YYYY-MM-DD HH:mm:ss"
        const stringFecha = fechaFinRaw.split(' ')[0];
        const partesFecha = stringFecha.split('-');

        if (partesFecha.length !== 3) return;

        const year = parseInt(partesFecha[0], 10);
        const month = parseInt(partesFecha[1], 10) - 1; // En JS los meses empiezan por cero
        const day = parseInt(partesFecha[2], 10);

        // Construimos un objeto fecha apuntando al último segundo de ESE día a hora local
        const finDelDia = new Date(year, month, day, 23, 59, 59);
        const ahora = new Date(); // Hora local real del dispositivo del usuario

        // Diferencia en milisegundos
        let diffMillis = finDelDia - ahora;

        if (diffMillis <= 0) {
            // Ya ha cruzado la medianoche localmente
            const alertaGrid = nodo.closest('.contenedor-alerta-js');
            if (alertaGrid) {
                // Modificamos visualmente el contenedor como si viniera expirado del servidor
                const cajaExpirada = alertaGrid.closest('.alerta-fin-contrato');
                if (cajaExpirada) {
                    cajaExpirada.style.background = '#fff5f5';
                    cajaExpirada.style.borderColor = '#fca5a5';
                    cajaExpirada.style.color = '#b91c1c';
                    const icon = cajaExpirada.querySelector('i');
                    if (icon) icon.style.color = '#ef4444';
                }
                alertaGrid.innerHTML = `El contrato ha expirado (hace <strong>0 días</strong>). Tienes una semana para contactar al propietario y solucionar el inconveniente en el caso que quieras renovar el contrato.`;
            } else {
                // Para ver_propiedad.blade.php
                nodo.innerText = "¡Contrato expirado!";
                nodo.style.color = "red";
            }
        } else {
            // Calculamos horas y minutos omitiendo segundos para no sobrecargar el dom visual
            let minutosTotales = Math.floor(diffMillis / 60000);
            let horas = Math.floor(minutosTotales / 60);
            let minutos = minutosTotales % 60;

            nodo.innerText = `${horas}h ${minutos}m`;
        }
    });
}
