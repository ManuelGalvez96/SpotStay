/* ============================================
   SUSCRIPCIONES JS - SpotStay Admin Dashboard
   Logica de interaccion suscripciones
   ============================================ */

var csrfToken = '';
var suscripcionIdActual = null;
var planActualModal = '';
var paginaActual = 1;

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
};

/* ── FILTROS ── */
var asignarEventosFiltros = function() {
    document.getElementById('selectPlanSus').onchange = function() {
        filtrarSuscripciones();
    };
    document.getElementById('selectEstadoSus').onchange = function() {
        filtrarSuscripciones();
    };
    document.getElementById('buscadorSus').onblur = function() {
        filtrarSuscripciones();
    };
    document.getElementById('buscadorSus').onkeyup = function() {
        if (this.value.length === 0) {
            filtrarSuscripciones();
        }
    };
    document.getElementById('btnExportarSus').onclick = function() {
        window.location.href = '/admin/suscripciones/exportar';
    };
};

var filtrarSuscripciones = function() {
    var plan = document.getElementById('selectPlanSus').value;
    var estado = document.getElementById('selectEstadoSus').value;
    var q = document.getElementById('buscadorSus').value;
    var url = '/admin/suscripciones/filtrar?';
    var params = [];
    if (plan) params.push('plan=' + encodeURIComponent(plan));
    if (estado) params.push('estado=' + encodeURIComponent(estado));
    if (q) params.push('q=' + encodeURIComponent(q));
    if (params.length > 0) {
        url += params.join('&');
    }

    fetch(url).then(function(r) {
        return r.json();
    }).then(function(data) {
        var contador = document.getElementById('contadorSus');
        if (contador) {
            contador.textContent = data.total + ' suscripciones encontradas';
        }
    }).catch(function(e) {
        console.error('Error filtrando:', e);
    });
};

/* ── TABLA ── */
var asignarEventosTabla = function() {
    var botonesVer = document.querySelectorAll('.btn-ver-sus');
    var botonesEditar = document.querySelectorAll('.btn-editar-sus');
    var i;
    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function(e) {
            e.preventDefault();
            abrirModal(this.getAttribute('data-id'));
        };
    }
    for (i = 0; i < botonesEditar.length; i++) {
        botonesEditar[i].onclick = function(e) {
            e.preventDefault();
            abrirModal(this.getAttribute('data-id'));
        };
    }
};

/* ── MODAL ── */
var abrirModal = function(id) {
    suscripcionIdActual = id;
    fetch('/admin/suscripciones/' + id).then(function(r) {
        return r.json();
    }).then(function(data) {
        rellenarModal(data);
        document.getElementById('modalOverlay').classList.add('visible');
        document.getElementById('modalSuscripcion').classList.add('visible');
    }).catch(function(e) {
        console.error('Error:', e);
    });
};

var rellenarModal = function(data) {
    var sus = data.suscripcion;
    var props = data.propiedades || [];
    var colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7','#CCE5FF','#FDE8C8'];
    var color = colores[(sus.id_usuario_fk || 0) % 10];
    var partes = (sus.nombre_usuario || '').split(' ');
    var iniciales = (partes[0] ? partes[0][0] : '').toUpperCase() + (partes[1] ? partes[1][0] : '').toUpperCase();

    /* Avatar y datos personales */
    document.getElementById('modalAvatarSus').style.background = color;
    document.getElementById('modalAvatarSus').textContent = iniciales;
    document.getElementById('modalNombreSus').textContent = sus.nombre_usuario || '';
    document.getElementById('modalEmailSus').textContent = sus.email_usuario || '';
    document.getElementById('modalTelefonoSus').textContent = sus.telefono_usuario || '';

    /* Datos del plan */
    var maxProps = sus.plan_suscripcion === 'pro' ? 10 : sus.plan_suscripcion === 'basico' ? 3 : 1;
    var precio = sus.plan_suscripcion === 'pro' ? 29.99 : sus.plan_suscripcion === 'basico' ? 9.99 : 0;

    /* Crear grid de detalles */
    var detallesGrid = document.getElementById('modalDetallesGrid');
    detallesGrid.innerHTML = '<p><strong>Plan:</strong> ' + (sus.plan_suscripcion ? sus.plan_suscripcion.charAt(0).toUpperCase() + sus.plan_suscripcion.slice(1) : '') + '</p>' +
        '<p><strong>Precio:</strong> ' + precio.toFixed(2) + ' €/mes</p>' +
        '<p><strong>Propiedades max:</strong> ' + maxProps + '</p>' +
        '<p><strong>Propiedades usadas:</strong> ' + props.length + '</p>' +
        '<p><strong>Inicio:</strong> ' + (sus.inicio_suscripcion || '—') + '</p>' +
        '<p><strong>Fin:</strong> ' + (sus.fin_suscripcion || '—') + '</p>' +
        '<p><strong>Estado:</strong> ' + (sus.estado_suscripcion ? sus.estado_suscripcion.charAt(0).toUpperCase() + sus.estado_suscripcion.slice(1) : '') + '</p>';

    planActualModal = sus.plan_suscripcion;
};


var marcarPlanActivo = function(plan) {
    // Función simplificada - los botones de plan fueron removidos del modal
    planActualModal = plan;
};

var cerrarModal = function() {
    document.getElementById('modalOverlay').classList.remove('visible');
    document.getElementById('modalSuscripcion').classList.remove('visible');
    suscripcionIdActual = null;
    planActualModal = '';
};

var guardarCambios = function() {
    if (!suscripcionIdActual) return;

    var datos = {
        plan: planActualModal
    };

    fetch('/admin/suscripciones/' + suscripcionIdActual + '/editar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(datos)
    }).then(function(r) {
        return r.json();
    }).then(function(data) {
        if (data.success) {
            cerrarModal();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
        }
    }).catch(function(e) {
        console.error('Error:', e);
    });
};

var cancelarSuscripcion = function() {
    if (!suscripcionIdActual) return;
    if (!confirm('¿Estás seguro de que deseas cancelar esta suscripción?')) return;

    fetch('/admin/suscripciones/' + suscripcionIdActual + '/cancelar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({})
    }).then(function(r) {
        return r.json();
    }).then(function(data) {
        if (data.success) {
            cerrarModal();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
        }
    }).catch(function(e) {
        console.error('Error:', e);
    });
};

var asignarEventosModal = function() {
    /* Cerrar modal */
    document.getElementById('btnCerrarModal').onclick = function() {
        cerrarModal();
    };

    document.getElementById('modalOverlay').onclick = function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    };

    /* Guardar cambios */
    document.getElementById('btnGuardarSus').onclick = function() {
        guardarCambios();
    };

    /* Cancelar suscripción */
    document.getElementById('btnCancelarSus').onclick = function() {
        cancelarSuscripcion();
    };

    /* Botones de plan */
    var botonesPlan = document.querySelectorAll('.btn-plan');
    var i;
    for (i = 0; i < botonesPlan.length; i++) {
        botonesPlan[i].onclick = function() {
            marcarPlanActivo(this.getAttribute('data-plan'));
            planActualModal = this.getAttribute('data-plan');
        };
    }
};

/* ── PAGINACIÓN ── */
var asignarEventosPaginacion = function() {
    var btnAnt = document.getElementById('btnAnteriorSus');
    var btnSig = document.getElementById('btnSiguienteSus');

    if (btnAnt) {
        btnAnt.onclick = function() {
            if (paginaActual > 1) {
                cambiarPagina(paginaActual - 1);
            }
        };
    }

    if (btnSig) {
        btnSig.onclick = function() {
            cambiarPagina(paginaActual + 1);
        };
    }

    var nums = document.querySelectorAll('#paginasSus .pag-numero');
    var i;
    for (i = 0; i < nums.length; i++) {
        nums[i].onclick = function() {
            cambiarPagina(parseInt(this.getAttribute('data-pagina')));
        };
    }
};

var cambiarPagina = function(num) {
    paginaActual = num;
    window.location.href = '/admin/suscripciones?page=' + num;
};
