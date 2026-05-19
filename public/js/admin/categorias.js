// Manejo del modal de crear categoría
function iniciarCrearCategoria() {
    const formCrearCategoria = document.getElementById('formCrearCategoria');
    const inputNombreCategoria = document.getElementById('nombreCategoria');
    const inputDescripcionCategoria = document.getElementById('descripcionCategoria');
    const btnGuardarCategoria = document.getElementById('btnGuardarCategoria');
    const modalCrearCategoria = document.getElementById('modalCrearCategoria');
    const errorNombreCategoria = document.getElementById('errorNombreCategoria');
    const selectCategoria = document.getElementById('selectCategoria');

    if (!btnGuardarCategoria) return;

    btnGuardarCategoria.onclick = function() {
        const nombreCategoria = inputNombreCategoria.value.trim();
        const descripcionCategoria = inputDescripcionCategoria.value.trim();

        // Validar que el nombre no esté vacío
        if (!nombreCategoria) {
            errorNombreCategoria.textContent = 'El nombre de la categoría es obligatorio.';
            return;
        }

        errorNombreCategoria.textContent = '';
        btnGuardarCategoria.disabled = true;
        btnGuardarCategoria.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';

        fetch('/admin/categorias/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                nombre_categoria: nombreCategoria,
                descripcion_categoria: descripcionCategoria,
            }),
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Mostrar alerta de éxito
                mostrarAlertaAdminExito('Categoría creada correctamente');
                
                // Resetear formulario
                formCrearCategoria.reset();
                errorNombreCategoria.textContent = '';

                // Agregar la nueva categoría al select
                if (selectCategoria && data.data) {
                    var nuevoOption = document.createElement('option');
                    nuevoOption.value = data.data.id_categoria;
                    nuevoOption.textContent = data.data.nombre_categoria;
                    selectCategoria.appendChild(nuevoOption);
                }

                // Cerrar modal
                var modalInstance = bootstrap.Modal.getInstance(modalCrearCategoria);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Recargar incidencias si es necesario
                if (typeof filtrarIncidencias === 'function') {
                    filtrarIncidencias();
                }
            } else {
                errorNombreCategoria.textContent = data.message || 'Error al crear la categoría.';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            errorNombreCategoria.textContent = 'Error en la solicitud. Intenta de nuevo.';
        })
        .finally(function() {
            btnGuardarCategoria.disabled = false;
            btnGuardarCategoria.innerHTML = '<i class="bi bi-check"></i> Crear Categoría';
        });
    };

    // Limpiar errores cuando se abre el modal
    if (modalCrearCategoria && modalCrearCategoria.addEventListener) {
        modalCrearCategoria.addEventListener('show.bs.modal', function() {
            formCrearCategoria.reset();
            errorNombreCategoria.textContent = '';
        });
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarCrearCategoria);
} else {
    iniciarCrearCategoria();
}
