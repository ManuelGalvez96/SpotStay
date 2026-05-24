document.addEventListener('DOMContentLoaded', function () {
    inicializarGastos();
    comprobarAlertasSesionGastos();
});

function inicializarGastos() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const historialLista = document.getElementById('historial-pagos-lista');
    const historialTab = document.querySelector('.tab-btn[data-tab="historial"]');
    const seccion = document.querySelector('.seccion-gastos-pagos');
    const filtroPropiedad = document.getElementById('filtro-propiedad-pagos');
    const formFiltroPropiedad = document.getElementById('form-filtro-propiedad');
    const totalPendienteValor = document.querySelector('.kpi-pago-card .kpi-pago-info .valor');
    const totalAtrasadoValor = document.querySelectorAll('.kpi-pago-card .kpi-pago-info .valor')[1];
    const kpiAccion = document.getElementById('btn-pagar-todo');
    const badgePendientes = document.querySelector('.tab-btn[data-tab="pendientes"] .badge-count');
    const pendientesLista = document.querySelector('#pendientes .lista-gastos-items');
    const footerPendientes = document.querySelector('#pendientes .footer-listado span');

    let historialCargado = false;
    let historialCargando = false;
    let timeoutNombreGasto;

    const filtroTipoGasto = document.getElementById('filtro-tipo-gasto');
    const filtroNombreGasto = document.getElementById('filtro-nombre-gasto');

    const filtroFechaDesde = document.getElementById('filtro-fecha-desde');
    const filtroFechaHasta = document.getElementById('filtro-fecha-hasta');
    const filtroOrden = document.getElementById('filtro-orden');

    const btnLimpiarPendientes = document.getElementById('btn-limpiar-pendientes');
    const btnLimpiarHistorial = document.getElementById('btn-limpiar-historial');

    tabs.forEach(tab => {
        tab.onclick = () => {
            const targetId = tab.getAttribute('data-tab');
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            const content = document.getElementById(targetId);
            if (content) content.classList.add('active');

            if (targetId === 'historial' && !historialCargado) {
                cargarHistorialPagos();
            }
        };
    });

    if (historialTab && historialTab.classList.contains('active')) {
        cargarHistorialPagos();
    }

    if (filtroPropiedad && formFiltroPropiedad) {
        filtroPropiedad.addEventListener('change', () => {
            cargarDatosPorPropiedad();
        });
    }

    if (filtroTipoGasto) {
        filtroTipoGasto.onchange = () => {
            cargarDatosPorPropiedad();
        };
    }

    if (filtroNombreGasto) {
        const errorConcepto = document.getElementById('error-filtro-concepto');
        filtroNombreGasto.oninput = () => {
            const valor = filtroNombreGasto.value.trim();
            const regex = /^[a-zA-Z0-9\s]*$/;

            if (valor.length > 50) {
                if (errorConcepto) {
                    errorConcepto.textContent = 'El concepto no puede tener más de 50 caracteres.';
                    errorConcepto.classList.remove('d-none');
                }
                return;
            }

            if (!regex.test(valor)) {
                if (errorConcepto) {
                    errorConcepto.textContent = 'Solo se permiten letras y números.';
                    errorConcepto.classList.remove('d-none');
                }
                return;
            }

            if (errorConcepto) {
                errorConcepto.textContent = '';
                errorConcepto.classList.add('d-none');
            }

            clearTimeout(timeoutNombreGasto);
            timeoutNombreGasto = setTimeout(() => {
                cargarDatosPorPropiedad();
            }, 300);
        };
    }

    if (filtroFechaDesde) {
        filtroFechaDesde.onchange = () => {
            if (filtroFechaHasta) {
                filtroFechaHasta.min = filtroFechaDesde.value || '';
            }
            cargarHistorialPagos(true);
        };
    }

    if (filtroFechaHasta) {
        filtroFechaHasta.onchange = () => {
            if (filtroFechaDesde) {
                filtroFechaDesde.max = filtroFechaHasta.value || '';
            }
            cargarHistorialPagos(true);
        };
    }

    if (filtroOrden) {
        filtroOrden.onchange = () => {
            cargarHistorialPagos(true);
        };
    }

    if (btnLimpiarPendientes) {
        btnLimpiarPendientes.onclick = () => {
            if (filtroTipoGasto) filtroTipoGasto.value = '';
            if (filtroNombreGasto) filtroNombreGasto.value = '';
            cargarDatosPorPropiedad();
        };
    }

    if (btnLimpiarHistorial) {
        btnLimpiarHistorial.onclick = () => {
            if (filtroFechaDesde) {
                filtroFechaDesde.value = '';
                filtroFechaDesde.max = '';
            }
            if (filtroFechaHasta) {
                filtroFechaHasta.value = '';
                filtroFechaHasta.min = '';
            }
            if (filtroOrden) filtroOrden.value = 'desc';
            cargarHistorialPagos(true);
        };
    }

    const btnPagarTodo = document.getElementById('btn-pagar-todo');
    if (btnPagarTodo) {
        btnPagarTodo.onclick = pagarTodo;
    }

    if (kpiAccion && (!filtroPropiedad || !filtroPropiedad.value)) {
        kpiAccion.classList.add('d-none', 'oculto');
        kpiAccion.style.display = 'none';
    }

    function cargarHistorialPagos(forzarRecarga = false) {
        if (!historialLista || historialCargando) return;
        if (historialCargado && !forzarRecarga) return;

        const historialUrl = seccion?.dataset?.historialUrl;
        if (!historialUrl) return;

        historialCargando = true;
        historialLista.innerHTML = `
            <div class="mensaje-vacio">
                <i class="bi bi-hourglass-split"></i>
                <p>Cargando historial de pagos...</p>
            </div>
        `;

        const propiedadId = document.getElementById('form-filtro-propiedad')?.querySelector('[name="propiedad_id"]')?.value || '';
        const url = new URL(historialUrl, window.location.origin);
        if (propiedadId) {
            url.searchParams.set('propiedad_id', propiedadId);
        }
        if (filtroFechaDesde && filtroFechaDesde.value) {
            url.searchParams.set('fecha_desde', filtroFechaDesde.value);
        }
        if (filtroFechaHasta && filtroFechaHasta.value) {
            url.searchParams.set('fecha_hasta', filtroFechaHasta.value);
        }
        if (filtroOrden && filtroOrden.value) {
            url.searchParams.set('orden', filtroOrden.value);
        }

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(respuesta => respuesta.json())
            .then(datos => {
                historialCargando = false;
                historialCargado = true;

                if (!datos.success || !Array.isArray(datos.data) || datos.data.length === 0) {
                    historialLista.innerHTML = `
                        <div class="mensaje-vacio">
                            <i class="bi bi-info-circle"></i>
                            <p>Aún no has realizado ningún pago con factura o suscripción.</p>
                        </div>
                    `;
                    return;
                }

                historialLista.innerHTML = datos.data.map(renderPagoHistorial).join('');
            })
            .catch(() => {
                historialCargando = false;
                historialLista.innerHTML = `
                    <div class="mensaje-vacio">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>No se pudo cargar el historial de pagos.</p>
                    </div>
                `;
            });
    }

    function cargarDatosPorPropiedad() {
        if (!formFiltroPropiedad) return;

        const historialUrl = seccion?.dataset?.historialUrl;
        const formData = new FormData(formFiltroPropiedad);
        const propiedadId = formData.get('propiedad_id') || '';
        const tipoGasto = filtroTipoGasto ? filtroTipoGasto.value : '';
        const nombreGasto = filtroNombreGasto ? filtroNombreGasto.value : '';
        
        const url = new URL(formFiltroPropiedad.action, window.location.origin);
        if (propiedadId) {
            url.searchParams.set('propiedad_id', propiedadId);
        }
        if (tipoGasto) {
            url.searchParams.set('tipo_gasto', tipoGasto);
        }
        if (nombreGasto) {
            url.searchParams.set('nombre_gasto', nombreGasto);
        }

        fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(respuesta => respuesta.json())
            .then(datos => {
                if (!datos.success) return;

                if (totalPendienteValor) {
                    totalPendienteValor.textContent = formatearImporte(datos.total_pendiente) + '€';
                }

                if (totalAtrasadoValor) {
                    totalAtrasadoValor.textContent = formatearImporte(datos.total_atrasado) + '€';
                }

                if (badgePendientes) {
                    badgePendientes.textContent = Array.isArray(datos.pendientes) ? datos.pendientes.length : 0;
                }

                if (footerPendientes) {
                    footerPendientes.textContent = 'Mostrando ' + (Array.isArray(datos.pendientes) ? datos.pendientes.length : 0) + ' recibos pendientes';
                }

                if (kpiAccion) {
                    if (datos.propiedad_seleccionada) {
                        kpiAccion.classList.remove('d-none', 'oculto');
                        kpiAccion.style.display = '';
                        
                        const textoPagarTodo = document.getElementById('texto-pagar-todo');
                        if (textoPagarTodo) {
                            if (tipoGasto) {
                                const tipoCapitalizado = tipoGasto.charAt(0).toUpperCase() + tipoGasto.slice(1);
                                textoPagarTodo.textContent = 'Pagar ' + tipoCapitalizado;
                            } else {
                                textoPagarTodo.textContent = 'Pagar Todo Ahora';
                            }
                        }
                    } else {
                        kpiAccion.classList.add('d-none', 'oculto');
                        kpiAccion.style.display = 'none';
                    }
                }

                if (pendientesLista) {
                    pendientesLista.innerHTML = renderPendientes(datos.pendientes || []);
                }

                historialCargado = false;
                if (document.querySelector('.tab-btn[data-tab="historial"]')?.classList.contains('active')) {
                    cargarHistorialPagos();
                } else if (historialLista) {
                    historialLista.innerHTML = `
                        <div class="mensaje-vacio">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Selecciona la pestaña de historial para cargar los pagos.</p>
                        </div>
                    `;
                }
            })
            .catch(() => {
                mostrarAlertaError('Error', 'No se pudieron actualizar los datos de la propiedad seleccionada.');
            });
    }
}

function comprobarAlertasSesionGastos() {
    const data = document.getElementById('data-session');
    if (!data) return;
    const exito = data.getAttribute('data-exito');
    const error = data.getAttribute('data-error');
    if (exito) {
        mostrarAlertaExito('Pago exitoso', exito);
    }
    if (error) {
        mostrarAlertaError('Error en el pago', error);
    }
}

function pagarTodo() {
    let total = 0;
    const items = document.querySelectorAll('#pendientes .gasto-item-row');
    items.forEach(item => {
        const importe = parseFloat(item.querySelector('.item-importe')?.textContent?.replace(/[^0-9,]/g, '').replace(',', '.') || '0');
        total += importe;
    });

    const propiedadId = document.getElementById('form-filtro-propiedad')?.querySelector('[name="propiedad_id"]')?.value || '';
    const tipoGasto = document.getElementById('filtro-tipo-gasto')?.value || '';
    const nombreGasto = document.getElementById('filtro-nombre-gasto')?.value || '';

    let tituloModal = 'Pagar todo';
    if (tipoGasto) {
        tituloModal = 'Pagar ' + tipoGasto.charAt(0).toUpperCase() + tipoGasto.slice(1);
    }

    mostrarAlertaConfirmacion(
        tituloModal,
        'Se procesarán todos tus pagos pendientes en una sola transacción segura.' +
            '<br><br>Importe total: <strong>' + total.toFixed(2).replace('.', ',') + ' €</strong>',
        'Sí, pagar ahora',
        'Cancelar'
    ).then(resultado => {
        if (!resultado.isConfirmed) return;

        fetch('/inquilino/pagar-todo', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                propiedad_id: propiedadId || null,
                tipo_gasto: tipoGasto || null,
                nombre_gasto: nombreGasto || null
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.url) {
                window.location.href = data.url;
            } else {
                mostrarAlertaError('Error', data.message || 'No se pudo iniciar el pago.');
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de conexión', err.message || 'Ocurrió un error al conectar con el servidor.');
        });
    });
}

function iniciarPago(tipo, id) {
    const btn = event?.currentTarget || event?.target;
    if (!btn) return;

    const originalText = btn.innerHTML;
    const row = btn.closest('.gasto-item-row');
    const concepto = row?.querySelector('.concepto')?.textContent || 'este concepto';
    const importe = row?.querySelector('.item-importe')?.textContent || '';

    mostrarAlertaConfirmacion(
        'Confirmar pago',
        'Vas a pagar <strong>' + concepto + '</strong>.<br><br>Importe: <strong>' + importe + '</strong>',
        'Sí, pagar ahora',
        'Cancelar'
    ).then(resultado => {
        if (!resultado.isConfirmed) return;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        btn.disabled = true;

        let url = '/inquilino/cuotas/' + id + '/pagar?tipo=' + tipo;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.url) {
                window.location.href = data.url;
            } else {
                mostrarAlertaError('Error', data.message || 'No se pudo iniciar el pago.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de conexión', err.message || 'Ocurrió un error al conectar con el servidor.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}

function renderPagoHistorial(pago) {
    const concepto = escapeHtml(pago.concepto_pago || 'Pago');
    const referencia = escapeHtml(pago.referencia_pago || '');
    const fecha = formatearFechaPago(pago.creado_pago);
    const importe = formatearImporte(pago.importe_pago);
    const facturaUrl = obtenerUrlFactura(pago.factura_url);
    const etiquetaTipo = pago.tipo_pago === 'suscripcion' ? 'Suscripción' : 'Factura';

    return `
        <div class="gasto-item-row">
            <div class="item-icon-circle blue">
                <i class="bi bi-check-lg"></i>
            </div>
            <div class="item-info">
                <span class="concepto">${concepto}</span>
                <span class="desc">Ref: ${referencia}</span>
            </div>
            <div class="item-vencimiento">
                <span class="date">${fecha}</span>
                <span class="status-text">${etiquetaTipo}</span>
            </div>
            <div class="item-status">
                <span class="badge-estado pagado">Pagado</span>
            </div>
            <div class="item-importe">${importe}€</div>
            <div class="item-accion">
                ${facturaUrl ? `<a href="${facturaUrl}" target="_blank" class="btn-pagar-item btn-ver-pdf">Ver PDF</a>` : `<button class="btn-pagar-item btn-ver-pdf" disabled style="opacity: 0.5;">Sin PDF</button>`}
            </div>
        </div>
    `;
}

function renderPendientes(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return `
            <div class="mensaje-vacio">
                <i class="bi bi-check-circle"></i>
                <p>No tienes pagos pendientes. ¡Estás al día!</p>
            </div>
        `;
    }

    return items.map(item => {
        const concepto = escapeHtml(item.concepto || 'Pago');
        const descripcion = escapeHtml(item.descripcion || '');
        const color = escapeHtml(item.color || 'blue');
        const icono = escapeHtml(item.icono || 'bi-cash');
        const fechaVencimiento = formatearFechaCorta(item.fecha_vencimiento);
        const estado = escapeHtml(item.estado || 'pendiente');
        const importe = formatearImporte(item.importe);
        const tipo = escapeHtml(item.tipo || '');
        const id = String(item.id || '');
        const etiquetaEstado = estado === 'atrasado' ? 'Atrasado' : 'Pendiente';
        const textoDias = estado === 'atrasado'
            ? `Hace ${Math.round(Math.abs(Number(item.dias_restantes || 0)))} días`
            : 'Vence pronto';

        return `
            <div class="gasto-item-row" data-id="${id}" data-tipo="${tipo}">
                <div class="item-icon-circle ${color}">
                    <i class="bi ${icono}"></i>
                </div>
                <div class="item-info">
                    <span class="concepto">${concepto}</span>
                    <span class="desc">${descripcion}</span>
                </div>
                <div class="item-vencimiento">
                    <span class="date">${fechaVencimiento}</span>
                    <span class="status-text ${estado}">${textoDias}</span>
                </div>
                <div class="item-status">
                    <span class="badge-estado ${estado}">${etiquetaEstado}</span>
                </div>
                <div class="item-importe">${importe}€</div>
                <div class="item-accion">
                    <button class="btn-pagar-item" onclick="iniciarPago('${tipo}', ${id})">Pagar</button>
                </div>
            </div>
        `;
    }).join('');
}

function formatearFechaPago(fecha) {
    if (!fecha) return '';
    const fechaObjeto = new Date(fecha);
    if (Number.isNaN(fechaObjeto.getTime())) return '';

    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(fechaObjeto);
}

function formatearImporte(importe) {
    const valor = Number(importe || 0);
    return valor.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatearFechaCorta(fecha) {
    if (!fecha) return '';
    const fechaObjeto = new Date(fecha);
    if (Number.isNaN(fechaObjeto.getTime())) return '';

    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(fechaObjeto);
}

function obtenerUrlFactura(urlFactura) {
    if (!urlFactura) return '';
    if (/^https?:\/\//i.test(urlFactura)) return urlFactura;
    return `/${String(urlFactura).replace(/^\/+/, '')}`;
}

function escapeHtml(texto) {
    return String(texto)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
