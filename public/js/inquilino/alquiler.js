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
            nodo.innerText = "0h 0m (Completado)";
        } else {
            // Calculamos horas y minutos omitiendo segundos para no sobrecargar el dom visual
            let minutosTotales = Math.floor(diffMillis / 60000);
            let horas = Math.floor(minutosTotales / 60);
            let minutos = minutosTotales % 60;

            nodo.innerText = `${horas}h ${minutos}m`;
        }
    });
}
