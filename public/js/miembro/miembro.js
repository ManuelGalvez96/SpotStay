/**
 * Scripts para el panel de Miembro de SpotStay
 */
document.addEventListener('DOMContentLoaded', function () {
    const botonPerfil = document.getElementById('boton-perfil');
    const submenu = document.getElementById('submenu-perfil');

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
});
