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

        // Calculamos la diferencia absoluta para medir el tiempo (ya sea futuro o pasado)
        let diffAbsoluta = Math.abs(diffMillis);

        if (diffMillis <= 0) {
            // Ya ha cruzado la medianoche localmente (Expirado)
            let diasPasados = Math.floor(diffAbsoluta / 86400000);

            const alertaGrid = nodo.closest('.contenedor-alerta-js');
            if (alertaGrid) {
                // Modificamos visualmente el contenedor como si viniera expirado del servidor
                const cajaExpirada = alertaGrid.closest('.alerta-fin-contrato');
                if (cajaExpirada) {
                    cajaExpirada.classList.add('estado-expirado');
                }

                let textoTiempo = "";
                if (diasPasados >= 1) {
                    textoTiempo = `hace <strong>${diasPasados} día${diasPasados > 1 ? 's' : ''}</strong>`;
                } else {
                    let horas = Math.floor(diffAbsoluta / 3600000);
                    let minutos = Math.floor((diffAbsoluta % 3600000) / 60000);
                    textoTiempo = `hace <strong>${horas}h ${minutos}m</strong>`;
                }

                alertaGrid.innerHTML = `El contrato finalizó ${textoTiempo}. <br>Para mas informacion entra en ver detalles.`;
            } else {
                // Para ver_propiedad.blade.php
                let textoTiempo = diasPasados >= 1
                    ? `hace ${diasPasados} día${diasPasados > 1 ? 's' : ''}`
                    : `hace ${Math.floor(diffAbsoluta / 3600000)}h ${Math.floor((diffAbsoluta % 3600000) / 60000)}m`;

                const cardGestion = nodo.closest('.card-gestion');
                if (cardGestion) {
                    cardGestion.classList.add('estado-expirado');

                    const label = cardGestion.querySelector('.label');
                    if (label) {
                        label.innerText = 'CONTRATO FINALIZADO';
                    }

                    const valorKpi = cardGestion.querySelector('.valor-kpi');
                    if (valorKpi) valorKpi.style.display = 'none'; // Ocultar "HOY"

                    // Eliminar el texto "Vence en" y "." del párrafo que contiene el temporizador
                    const pNota = nodo.closest('p.nota');
                    if (pNota) {
                        Array.from(pNota.childNodes).forEach(child => {
                            if (child.nodeType === Node.TEXT_NODE) {
                                child.textContent = '';
                            }
                        });
                    }
                }

                nodo.innerText = `¡El contrato finalizó ${textoTiempo}!`;
                nodo.classList.add('texto-expirado');
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
