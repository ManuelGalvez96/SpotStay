/**
 * Validación y envío de formulario para crear/editar propiedades
 */

document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Limpiar errores previos
        limpiarErrores();
        
        // Validar formulario
        var errores = validarFormulario();
        
        if (errores.length > 0) {
            // Mostrar alerta de validación
            var mensajeErrores = errores.join('\n');
            if (window.mostrarAlertaAdminValidacion) {
                window.mostrarAlertaAdminValidacion(mensajeErrores);
            } else {
                alert('Errores de validación:\n' + mensajeErrores);
            }
            return false;
        }
        
        // Si validación pasa, enviar formulario
        enviarFormulario(form);
    });
});

/**
 * Valida todos los campos del formulario
 * Retorna un array con mensajes de error (vacío si no hay errores)
 */
function validarFormulario() {
    var errores = [];
    
    // Campos obligatorios
    var titulo = document.getElementById('titulo');
    var calle = document.getElementById('calle');
    var numero = document.getElementById('numero');
    var ciudad = document.getElementById('ciudad');
    var codigoPostal = document.getElementById('codigo_postal');
    var precio = document.getElementById('precio');
    var arrendadorEmail = document.getElementById('arrendador_email');
    var estado = document.getElementById('estado');
    
    // Validar título
    if (!titulo || !titulo.value.trim()) {
        errores.push('• El título es obligatorio.');
    } else if (titulo.value.trim().length < 3) {
        errores.push('• El título debe tener al menos 3 caracteres.');
    }
    
    // Validar calle
    if (!calle || !calle.value.trim()) {
        errores.push('• La calle es obligatoria.');
    } else if (calle.value.trim().length < 3) {
        errores.push('• La calle debe tener al menos 3 caracteres.');
    }
    
    // Validar número
    if (!numero || !numero.value.trim()) {
        errores.push('• El número es obligatorio.');
    }
    
    // Validar ciudad
    if (!ciudad || !ciudad.value.trim()) {
        errores.push('• La ciudad es obligatoria.');
    } else if (ciudad.value.trim().length < 2) {
        errores.push('• La ciudad debe tener al menos 2 caracteres.');
    }
    
    // Validar código postal
    if (!codigoPostal || !codigoPostal.value.trim()) {
        errores.push('• El código postal es obligatorio.');
    } else if (!/^\d{5}$/.test(codigoPostal.value.trim())) {
        errores.push('• El código postal debe tener 5 dígitos.');
    }
    
    // Validar precio
    if (!precio || precio.value === '') {
        errores.push('• El precio es obligatorio.');
    } else if (isNaN(precio.value) || parseFloat(precio.value) < 0) {
        errores.push('• El precio debe ser un número mayor o igual a 0.');
    }
    
    // Validar email del arrendador
    if (!arrendadorEmail || !arrendadorEmail.value.trim()) {
        errores.push('• El email del arrendador es obligatorio.');
    } else if (!validarEmail(arrendadorEmail.value)) {
        errores.push('• El email del arrendador no es válido.');
    }
    
    // Validar estado
    if (!estado || !estado.value) {
        errores.push('• El estado de la propiedad es obligatorio.');
    }
    
    // Validar metros si está presente
    var metros = document.getElementById('metros');
    if (metros && metros.value && isNaN(metros.value)) {
        errores.push('• Los metros cuadrados deben ser un número válido.');
    }
    
    return errores;
}

/**
 * Valida si un email es válido
 */
function validarEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Envía el formulario vía AJAX
 */
function enviarFormulario(form) {
    var formData = new FormData(form);
    var isEditing = form.action.includes('/editar');
    var titulo = isEditing ? 'Propiedad actualizada' : 'Propiedad creada';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        // Si la respuesta es 422 (validación), parsear los errores
        if (response.status === 422) {
            return response.json().then(function(data) {
                var erroresServidor = [];
                if (data.errors) {
                    for (var campo in data.errors) {
                        if (data.errors[campo] && data.errors[campo].length > 0) {
                            erroresServidor.push('• ' + data.errors[campo][0]);
                        }
                    }
                }
                throw new Error(erroresServidor.length > 0 ? erroresServidor.join('\n') : 'Error de validación.');
            });
        }
        
        if (!response.ok) {
            return response.json().then(function(data) {
                throw new Error(data.message || 'Error en la solicitud');
            }).catch(function() {
                throw new Error('Error en la solicitud');
            });
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Mostrar alerta de éxito
            if (window.mostrarAlertaAdminExito) {
                window.mostrarAlertaAdminExito(
                    titulo,
                    isEditing 
                        ? 'Los cambios se guardaron correctamente.' 
                        : 'La propiedad se creó correctamente.'
                );
            }
            
            // Redirigir después de 1.5 segundos
            setTimeout(function() {
                window.location.href = '/admin/propiedades';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
    })
    .catch(function(error) {
        var mensajeError = error.message || 'Error al procesar el formulario.';
        
        // Mostrar alerta de error
        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error', mensajeError);
        } else {
            alert('Error: ' + mensajeError);
        }
    });
}

/**
 * Limpia los mensajes de error previos
 */
function limpiarErrores() {
    var errorElements = document.querySelectorAll('.campo-error');
    errorElements.forEach(function(el) {
        el.remove();
    });
}

/**
 * Muestra error en un campo específico
 */
function mostrarErrorValidacion(campoId, mensaje) {
    var campo = document.getElementById(campoId);
    if (!campo) return;
    
    var errorDiv = document.createElement('div');
    errorDiv.className = 'campo-error';
    errorDiv.style.color = '#c71c1c';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    errorDiv.textContent = mensaje;
    
    campo.parentNode.appendChild(errorDiv);
}
