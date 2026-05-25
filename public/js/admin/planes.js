// Ejecutar inmediatamente: la plantilla carga los scripts al final del body
var iniciarValidacionPlanAdmin = function () {
    var el = document.getElementById('planes-messages');
    if (el) {
        var success = el.getAttribute('data-success') || '';
        var error = el.getAttribute('data-error') || '';

        if (success && success.trim().length) {
            if (window.swalSuccess) {
                window.swalSuccess('Plan creado/actualizado', success);
            } else if (window.Swal) {
                Swal.fire('Plan creado/actualizado', success, 'success');
            }
        }

        if (error && error.trim().length) {
            if (window.swalError) {
                window.swalError('Revisa los datos', error);
            } else if (window.Swal) {
                Swal.fire('Revisa los datos', error, 'error');
            }
        }
    }

    var deleteButtons = document.querySelectorAll('.btn-eliminar-plan');
    if (deleteButtons && deleteButtons.length) {
        Array.prototype.forEach.call(deleteButtons, function(btn) {
            btn.onclick = function () {
                var planId = btn.getAttribute('data-plan-id');
                var form = document.getElementById('form-eliminar-' + planId);
                var card = btn.closest('.plan-card');
                var planName = '';
                if (card) {
                    var h3 = card.querySelector('h3');
                    if (h3) planName = h3.textContent.trim();
                }

                if (window.Swal) {
                    Swal.fire({
                        title: 'Eliminar plan',
                        html: '¿Eliminar el plan <strong>' + (planName || '') + '</strong>? Esta acción no se puede deshacer.',
                        iconHtml: (window.crearOsoPregunta ? window.crearOsoPregunta() : (window.crearOsoError ? window.crearOsoError() : undefined)),
                        customClass: { icon: 'oso-icon' },
                        showCancelButton: true,
                        confirmButtonText: 'Eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d9534f'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            if (form) form.submit();
                        }
                    });
                } else {
                    if (confirm('¿Eliminar el plan ' + (planName || '') + '?')) {
                        if (form) form.submit();
                    }
                }
            };
        });
    }

    var formCrearPlan = document.querySelector('.plan-form-creacion');

    if (!formCrearPlan) {
        return;
    }

    var campos = {
        nombre: formCrearPlan.querySelector('[name="nombre_plan"]'),
        slug: formCrearPlan.querySelector('[name="slug_plan"]'),
        rol: formCrearPlan.querySelector('[name="rol_destino"]'),
        precio: formCrearPlan.querySelector('[name="precio_plan"]'),
        maxPropiedades: formCrearPlan.querySelector('[name="max_propiedades_plan"]')
    };

    var errores = {
        nombre: document.getElementById('errorNombrePlan'),
        slug: document.getElementById('errorSlugPlan'),
        rol: document.getElementById('errorRolPlan'),
        precio: document.getElementById('errorPrecioPlan'),
        maxPropiedades: document.getElementById('errorMaxPropiedadesPlan')
    };

    var botonEnviar = formCrearPlan.querySelector('button[type="submit"]');

    function valorLimpio(elemento) {
        return elemento && typeof elemento.value === 'string' ? elemento.value.trim() : '';
    }

    function mostrarError(campo, mensaje) {
        if (errores[campo]) {
            errores[campo].textContent = mensaje;
        }
    }

    function limpiarError(campo) {
        if (errores[campo]) {
            errores[campo].textContent = ' ';
        }
    }

    function limpiarTodosLosErrores() {
        for (var clave in errores) {
            limpiarError(clave);
        }
    }

    function validarBoton() {
        botonEnviar.disabled = !(
            valorLimpio(campos.nombre) &&
            valorLimpio(campos.slug) &&
            valorLimpio(campos.rol) &&
            valorLimpio(campos.precio) &&
            valorLimpio(campos.maxPropiedades)
        );
    }

    campos.nombre.onblur = function () {
        if (!valorLimpio(campos.nombre)) {
            mostrarError('nombre', 'El nombre del plan no puede estar vacío.');
        } else {
            limpiarError('nombre');
        }
        validarBoton();
    };

    campos.nombre.oninput = function () {
        if (valorLimpio(campos.nombre)) {
            limpiarError('nombre');
        }
        validarBoton();
    };

    campos.slug.onblur = function () {
        if (!valorLimpio(campos.slug)) {
            mostrarError('slug', 'El slug del plan no puede estar vacío.');
        } else {
            limpiarError('slug');
        }
        validarBoton();
    };

    campos.slug.oninput = function () {
        if (valorLimpio(campos.slug)) {
            limpiarError('slug');
        }
        validarBoton();
    };

    campos.rol.onblur = function () {
        if (!valorLimpio(campos.rol)) {
            mostrarError('rol', 'Debes seleccionar un rol destino.');
        } else {
            limpiarError('rol');
        }
        validarBoton();
    };

    campos.rol.onchange = function () {
        if (valorLimpio(campos.rol)) {
            limpiarError('rol');
        }
        validarBoton();
    };

    campos.precio.onblur = function () {
        if (!valorLimpio(campos.precio)) {
            mostrarError('precio', 'El precio del plan no puede estar vacío.');
        } else {
            limpiarError('precio');
        }
        validarBoton();
    };

    campos.precio.oninput = function () {
        if (valorLimpio(campos.precio)) {
            limpiarError('precio');
        }
        validarBoton();
    };

    campos.maxPropiedades.onblur = function () {
        if (!valorLimpio(campos.maxPropiedades)) {
            mostrarError('maxPropiedades', 'Debes indicar el máximo de propiedades.');
        } else {
            limpiarError('maxPropiedades');
        }
        validarBoton();
    };

    campos.maxPropiedades.oninput = function () {
        if (valorLimpio(campos.maxPropiedades)) {
            limpiarError('maxPropiedades');
        }
        validarBoton();
    };

    formCrearPlan.onsubmit = function (evento) {
        var valido = valorLimpio(campos.nombre) && valorLimpio(campos.slug) && valorLimpio(campos.rol) && valorLimpio(campos.precio) && valorLimpio(campos.maxPropiedades);
        if (!valido) {
            evento.preventDefault();
            if (!valorLimpio(campos.nombre)) {
                mostrarError('nombre', 'El nombre del plan no puede estar vacío.');
            } else if (!valorLimpio(campos.slug)) {
                mostrarError('slug', 'El slug del plan no puede estar vacío.');
            } else if (!valorLimpio(campos.rol)) {
                mostrarError('rol', 'Debes seleccionar un rol destino.');
            } else if (!valorLimpio(campos.precio)) {
                mostrarError('precio', 'El precio del plan no puede estar vacío.');
            } else if (!valorLimpio(campos.maxPropiedades)) {
                mostrarError('maxPropiedades', 'Debes indicar el máximo de propiedades.');
            }
            return false;
        }
        return true;
    };

    limpiarTodosLosErrores();
    validarBoton();
};

iniciarValidacionPlanAdmin();
