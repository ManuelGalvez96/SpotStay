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
    asignarEventosModalPlanes();
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

    /* Avatar y datos */
    document.getElementById('modalAvatarSus').textContent = iniciales;
    document.getElementById('modalAvatarSus').style.background = color;
    document.getElementById('modalNombreSus').textContent = sus.nombre_usuario || '';
    document.getElementById('modalEmailSus').textContent = sus.email_usuario || '';
    document.getElementById('modalTelefonoSus').textContent = sus.telefono_usuario || '';

    /* Badges header */
    document.getElementById('modalBadgePlanSus').textContent = sus.plan_suscripcion ? sus.plan_suscripcion.charAt(0).toUpperCase() + sus.plan_suscripcion.slice(1) : '';
    document.getElementById('modalBadgePlanSus').className = 'badge-plan badge-plan-' + (sus.plan_suscripcion || '');
    document.getElementById('modalBadgeEstadoSus').textContent = sus.estado_suscripcion ? sus.estado_suscripcion.charAt(0).toUpperCase() + sus.estado_suscripcion.slice(1) : '';
    document.getElementById('modalBadgeEstadoSus').className = 'badge-estado badge-sus-' + (sus.estado_suscripcion || '');

    var maxProps = sus.plan_suscripcion === 'pro' ? 10 : sus.plan_suscripcion === 'basico' ? 3 : 1;
    var precio = sus.plan_suscripcion === 'pro' ? 29.99 : sus.plan_suscripcion === 'basico' ? 9.99 : 0;

    /* Datos plan grid */
    document.getElementById('dataPlanSus').textContent = sus.plan_suscripcion ? sus.plan_suscripcion.charAt(0).toUpperCase() + sus.plan_suscripcion.slice(1) : '';
    document.getElementById('dataPlanSus').className = 'badge-plan badge-plan-' + (sus.plan_suscripcion || '');
    document.getElementById('dataPrecioSus').textContent = precio.toFixed(2) + ' €/mes';
    document.getElementById('dataMaxPropsSus').textContent = maxProps;
    document.getElementById('dataUsadasSus').textContent = props.length;
    document.getElementById('dataInicioSus').textContent = sus.inicio_suscripcion || '—';
    document.getElementById('dataFinSus').textContent = sus.fin_suscripcion || '—';
    document.getElementById('dataEstadoSus').textContent = sus.estado_suscripcion ? sus.estado_suscripcion.charAt(0).toUpperCase() + sus.estado_suscripcion.slice(1) : '';
    document.getElementById('dataEstadoSus').className = 'badge-estado badge-sus-' + (sus.estado_suscripcion || '');

    /* Calcular días restantes */
    var diasRestantes = '—';
    if (sus.fin_suscripcion) {
        var hoy = new Date();
        var fin = new Date(sus.fin_suscripcion);
        var diff = Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24));
        diasRestantes = diff > 0 ? diff + ' días' : 'Expirada hace ' + Math.abs(diff) + ' días';
    }
    document.getElementById('dataDiasSus').textContent = diasRestantes;

    /* Barra de uso */
    var pct = maxProps > 0 ? Math.min(100, Math.round(props.length / maxProps * 100)) : 0;
    var colorBarra = sus.plan_suscripcion === 'pro' ? '#035498' : sus.plan_suscripcion === 'basico' ? '#D97706' : '#CBD5E1';
    document.getElementById('barraUsoPropSus').style.width = pct + '%';
    document.getElementById('barraUsoPropSus').style.background = colorBarra;
    document.getElementById('labelUsoPropSus').textContent = 'USO DE PROPIEDADES (' + props.length + '/' + maxProps + ')';
    document.getElementById('barraLeyIzqSus').textContent = props.length + ' propiedades activas';
    document.getElementById('barraLeyDerSus').textContent = (maxProps - props.length) + ' disponibles';

    /* Lista de propiedades */
    var lista = document.getElementById('listaPropsSus');
    lista.innerHTML = '';
    var coloresProp = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9'];
    var i;
    for (i = 0; i < props.length; i++) {
        var prop = props[i];
        var div = document.createElement('div');
        div.className = 'prop-modal-item';
        var colorProp = coloresProp[i % 5];
        var estadoBadge = prop.estado_propiedad === 'alquilada' ? 'badge-sus-activa' : 'badge-sus-cancelada';
        div.innerHTML = '<div class="prop-modal-thumb" style="background:' + colorProp + '"></div>' +
            '<span class="prop-modal-nombre">' + (prop.titulo_propiedad || '') + ', ' + (prop.ciudad_propiedad || '') + '</span>' +
            '<span class="badge-estado ' + estadoBadge + '">' + (prop.estado_propiedad ? prop.estado_propiedad.charAt(0).toUpperCase() + prop.estado_propiedad.slice(1) : '') + '</span>';
        lista.appendChild(div);
    }

    /* Marcar plan activo */
    var botonesP = document.querySelectorAll('.btn-plan-modal');
    var j;
    for (j = 0; j < botonesP.length; j++) {
        botonesP[j].classList.remove('activo');
        if (botonesP[j].getAttribute('data-plan') === sus.plan_suscripcion) {
            botonesP[j].classList.add('activo');
        }
    }
    planActualModal = sus.plan_suscripcion;

    /* Rellenar inputs */
    document.getElementById('editPrecioSus').value = precio.toFixed(2);
    document.getElementById('editMaxPropsSus').value = maxProps;
    document.getElementById('editInicioSus').value = sus.inicio_suscripcion ? sus.inicio_suscripcion.substring(0, 10) : '';
    document.getElementById('editFinSus').value = sus.fin_suscripcion ? sus.fin_suscripcion.substring(0, 10) : '';
    document.getElementById('editNotasSus').value = '';
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
        plan: planActualModal,
        precio: document.getElementById('editPrecioSus').value,
        max_propiedades: document.getElementById('editMaxPropsSus').value,
        fecha_inicio: document.getElementById('editInicioSus').value,
        fecha_fin: document.getElementById('editFinSus').value,
        notas: document.getElementById('editNotasSus').value
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
    var botonesPlanModal = document.querySelectorAll('.btn-plan-modal');
    var k;
    for (k = 0; k < botonesPlanModal.length; k++) {
        botonesPlanModal[k].onclick = function() {
            var plan = this.getAttribute('data-plan');
            var todos = document.querySelectorAll('.btn-plan-modal');
            var m;
            for (m = 0; m < todos.length; m++) {
                todos[m].classList.remove('activo');
            }
            this.classList.add('activo');
            planActualModal = plan;
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

/* ── MODAL PLANES ── */
var abrirModalPlanes = function() {
    fetch('/admin/planes/datos')
        .then(function(r) {
            return r.json();
        })
        .then(function(data) {
            var elGrat = document.getElementById('usuariosGratuito');
            var elBas = document.getElementById('usuariosBasico');
            var elPro = document.getElementById('usuariosPro');

            if (elGrat) {
                elGrat.textContent = (data.gratuito || 0) + ' usuarios activos';
            }
            if (elBas) {
                elBas.textContent = (data.basico || 0) + ' usuarios activos';
            }
            if (elPro) {
                elPro.textContent = (data.pro || 0) + ' usuarios activos';
            }

            if (data.precio_basico) {
                document.getElementById('planBasicoPrecio').value = data.precio_basico;
            }
            if (data.precio_pro) {
                document.getElementById('planProPrecio').value = data.precio_pro;
            }
            if (data.max_basico) {
                document.getElementById('planBasicoMaxProps').value = data.max_basico;
            }
            if (data.max_pro) {
                document.getElementById('planProMaxProps').value = data.max_pro;
            }
        })
        .catch(function(e) {
            console.error('Error:', e);
        });

    document.getElementById('modalOverlayPlanes').classList.add('visible');
    document.getElementById('modalPlanes').classList.add('visible');
};

var cerrarModalPlanes = function() {
    document.getElementById('modalOverlayPlanes').classList.remove('visible');
    document.getElementById('modalPlanes').classList.remove('visible');
};

var guardarPlanes = function() {
    var datos = {
        gratuito: {
            precio: 0,
            max_propiedades: document.getElementById('planGratuitoMaxProps').value,
            descripcion: document.getElementById('planGratuitoDesc').value
        },
        basico: {
            precio: document.getElementById('planBasicoPrecio').value,
            max_propiedades: document.getElementById('planBasicoMaxProps').value,
            descripcion: document.getElementById('planBasicoDesc').value
        },
        pro: {
            precio: document.getElementById('planProPrecio').value,
            max_propiedades: document.getElementById('planProMaxProps').value,
            descripcion: document.getElementById('planProDesc').value
        }
    };

    fetch('/admin/planes/guardar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(datos)
    })
    .then(function(r) {
        return r.json();
    })
    .then(function(data) {
        if (data.success) {
            cerrarModalPlanes();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(function(e) {
        console.error('Error:', e);
    });
};

var asignarEventosModalPlanes = function() {
    document.getElementById('btnVerPlanes').onclick = function() {
        abrirModalPlanes();
    };

    document.getElementById('btnCerrarModalPlanes').onclick = function() {
        cerrarModalPlanes();
    };

    document.getElementById('modalOverlayPlanes').onclick = function(e) {
        if (e.target === this) {
            cerrarModalPlanes();
        }
    };

    document.getElementById('btnCancelarPlanes').onclick = function() {
        cerrarModalPlanes();
    };

    document.getElementById('btnGuardarPlanes').onclick = function() {
        guardarPlanes();
    };
};
