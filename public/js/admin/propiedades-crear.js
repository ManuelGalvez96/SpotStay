var inicializarFormularioPropiedadAdmin = function () {
    var form = document.getElementById('formCrearPropiedad');

    if (!form) {
        return;
    }

    var campos = {
        titulo: document.getElementById('titulo'),
        calle: document.getElementById('calle'),
        numero: document.getElementById('numero'),
        ciudad: document.getElementById('ciudad'),
        codigoPostal: document.getElementById('codigo_postal'),
        precio: document.getElementById('precio'),
        metros: document.getElementById('metros'),
        estado: document.getElementById('estado'),
        emailArrendador: document.getElementById('arrendador_email')
    };

    var errores = {
        titulo: document.getElementById('errorTituloPropiedad'),
        calle: document.getElementById('errorCallePropiedad'),
        numero: document.getElementById('errorNumeroPropiedad'),
        ciudad: document.getElementById('errorCiudadPropiedad'),
        codigoPostal: document.getElementById('errorCodigoPostalPropiedad'),
        precio: document.getElementById('errorPrecioPropiedad'),
        metros: null,
        estado: document.getElementById('errorEstadoPropiedad'),
        emailArrendador: document.getElementById('errorEmailArrendadorPropiedad')
    };

    var botonEnviar = form.querySelector('button[type="submit"]');

    function valorLimpio(elemento) {
        return elemento && typeof elemento.value === 'string' ? elemento.value.trim() : '';
    }

    function textoError(elemento, mensaje) {
        if (elemento) {
            elemento.textContent = mensaje;
        }
    }

    function limpiarError(campo) {
        textoError(errores[campo], ' ');
    }

    function limpiarTodosLosErrores() {
        for (var clave in errores) {
            limpiarError(clave);
        }
    }

    function validarEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validarTitulo() {
        var valor = valorLimpio(campos.titulo);
        if (!valor) {
            textoError(errores.titulo, 'El título es obligatorio.');
            return false;
        }
        if (valor.length < 3) {
            textoError(errores.titulo, 'El título debe tener al menos 3 caracteres.');
            return false;
        }
        limpiarError('titulo');
        return true;
    }

    function validarCalle() {
        var valor = valorLimpio(campos.calle);
        if (!valor) {
            textoError(errores.calle, 'La calle es obligatoria.');
            return false;
        }
        if (valor.length < 3) {
            textoError(errores.calle, 'La calle debe tener al menos 3 caracteres.');
            return false;
        }
        limpiarError('calle');
        return true;
    }

    function validarNumero() {
        var valor = valorLimpio(campos.numero);
        if (!valor) {
            textoError(errores.numero, 'El número es obligatorio.');
            return false;
        }
        limpiarError('numero');
        return true;
    }

    function validarCiudad() {
        var valor = valorLimpio(campos.ciudad);
        if (!valor) {
            textoError(errores.ciudad, 'La ciudad es obligatoria.');
            return false;
        }
        if (valor.length < 2) {
            textoError(errores.ciudad, 'La ciudad debe tener al menos 2 caracteres.');
            return false;
        }
        limpiarError('ciudad');
        return true;
    }

    function validarCodigoPostal() {
        var valor = valorLimpio(campos.codigoPostal);
        if (!valor) {
            textoError(errores.codigoPostal, 'El código postal es obligatorio.');
            return false;
        }
        if (!/^\d{5}$/.test(valor)) {
            textoError(errores.codigoPostal, 'El código postal debe tener 5 dígitos.');
            return false;
        }
        limpiarError('codigoPostal');
        return true;
    }

    function validarPrecio() {
        var valor = valorLimpio(campos.precio);
        if (!valor) {
            textoError(errores.precio, 'El precio es obligatorio.');
            return false;
        }
        if (isNaN(valor) || parseFloat(valor) < 0) {
            textoError(errores.precio, 'El precio debe ser un número mayor o igual a 0.');
            return false;
        }
        limpiarError('precio');
        return true;
    }

    function validarEstado() {
        var valor = valorLimpio(campos.estado);
        if (!valor) {
            textoError(errores.estado, 'El estado de la propiedad es obligatorio.');
            return false;
        }
        limpiarError('estado');
        return true;
    }

    function validarEmailArrendador() {
        var valor = valorLimpio(campos.emailArrendador);
        if (!valor) {
            textoError(errores.emailArrendador, 'El email del arrendador es obligatorio.');
            return false;
        }
        if (!validarEmail(valor)) {
            textoError(errores.emailArrendador, 'El email del arrendador no es válido.');
            return false;
        }
        limpiarError('emailArrendador');
        return true;
    }

    function validarMetros() {
        if (!campos.metros || !valorLimpio(campos.metros)) {
            return true;
        }
        var valor = valorLimpio(campos.metros);
        if (isNaN(valor)) {
            textoError(errores.metros, 'Los metros cuadrados deben ser un número válido.');
            return false;
        }
        return true;
    }

    function validarFormulario() {
        if (!validarTitulo()) return false;
        if (!validarCalle()) return false;
        if (!validarNumero()) return false;
        if (!validarCiudad()) return false;
        if (!validarCodigoPostal()) return false;
        if (!validarPrecio()) return false;
        if (!validarEmailArrendador()) return false;
        if (!validarEstado()) return false;
        if (!validarMetros()) return false;
        return true;
    }

    function activarValidacionCampo(campo, validarFn, limpiarSiValido) {
        if (!campo) {
            return;
        }

        campo.onblur = validarFn;
        campo.oninput = function () {
            if (limpiarSiValido()) {
                validarFn();
            }
        };
    }

    activarValidacionCampo(campos.titulo, validarTitulo, function () { return valorLimpio(campos.titulo).length >= 3; });
    activarValidacionCampo(campos.calle, validarCalle, function () { return valorLimpio(campos.calle).length >= 3; });
    activarValidacionCampo(campos.numero, validarNumero, function () { return valorLimpio(campos.numero).length > 0; });
    activarValidacionCampo(campos.ciudad, validarCiudad, function () { return valorLimpio(campos.ciudad).length >= 2; });
    activarValidacionCampo(campos.codigoPostal, validarCodigoPostal, function () { return /^\d{5}$/.test(valorLimpio(campos.codigoPostal)); });
    activarValidacionCampo(campos.precio, validarPrecio, function () { return valorLimpio(campos.precio).length > 0; });
    activarValidacionCampo(campos.estado, validarEstado, function () { return valorLimpio(campos.estado).length > 0; });
    activarValidacionCampo(campos.emailArrendador, validarEmailArrendador, function () { return validarEmail(valorLimpio(campos.emailArrendador)); });

    if (campos.metros) {
        campos.metros.onblur = validarMetros;
        campos.metros.oninput = function () {
            if (!valorLimpio(campos.metros) || !isNaN(valorLimpio(campos.metros))) {
                limpiarError('metros');
            }
        };
    }

    function mostrarErroresServidor(erroresServidor) {
        var mapaCampos = {
            titulo: 'titulo',
            calle: 'calle',
            numero: 'numero',
            ciudad: 'ciudad',
            codigo_postal: 'codigoPostal',
            precio: 'precio',
            metros: 'metros',
            estado: 'estado',
            arrendador_email: 'emailArrendador'
        };

        for (var campoServidor in erroresServidor) {
            if (mapaCampos[campoServidor] && erroresServidor[campoServidor] && erroresServidor[campoServidor].length) {
                textoError(errores[mapaCampos[campoServidor]], erroresServidor[campoServidor][0]);
            }
        }
    }

    function enviarFormulario() {
        var formData = new FormData(form);
        var isEditing = form.action.indexOf('/editar') !== -1;
        var tituloAlerta = isEditing ? 'Propiedad actualizada' : 'Propiedad creada';
        var botonOriginal = botonEnviar ? botonEnviar.innerHTML : '';

        if (botonEnviar) {
            botonEnviar.disabled = true;
            botonEnviar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(function (response) {
            if (response.status === 422) {
                return response.json().then(function (data) {
                    mostrarErroresServidor(data.errors || {});
                    throw new Error('Revisa los campos marcados.');
                });
            }

            if (!response.ok) {
                return response.json().then(function (data) {
                    throw new Error(data.message || 'Error en la solicitud');
                }).catch(function () {
                    throw new Error('Error en la solicitud');
                });
            }

            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                if (window.mostrarAlertaAdminExito) {
                    window.mostrarAlertaAdminExito(
                        tituloAlerta,
                        isEditing ? 'Los cambios se guardaron correctamente.' : 'La propiedad se creó correctamente.'
                    );
                }

                setTimeout(function () {
                    window.location.href = '/admin/propiedades';
                }, 1500);
                return;
            }

            throw new Error(data.message || 'Error desconocido');
        })
        .catch(function (error) {
            if (error && error.message === 'Revisa los campos marcados.') {
                return;
            }

            var mensajeError = error && error.message ? error.message : 'Error al procesar el formulario.';

            if (window.mostrarAlertaAdminError) {
                window.mostrarAlertaAdminError('Error', mensajeError);
            } else {
                alert('Error: ' + mensajeError);
            }
        })
        .finally(function () {
            if (botonEnviar) {
                botonEnviar.disabled = false;
                botonEnviar.innerHTML = botonOriginal;
            }
        });
    }

    form.onsubmit = function (evento) {
        evento.preventDefault();

        limpiarTodosLosErrores();

        if (!validarFormulario()) {
            return false;
        }

        enviarFormulario();
        return false;
    };

    limpiarTodosLosErrores();
};

inicializarFormularioPropiedadAdmin();
