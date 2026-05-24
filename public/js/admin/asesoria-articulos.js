var csrfToken = document.querySelector('meta[name=csrf-token]').content;
var paginaActual = 1;
var sortCol = 'categoria';
var sortDir = 'asc';

function poblarSelectOrdenArticulo(maxOrden) {
    var select = document.querySelector('select[name="orden"]');
    if (!select) return;
    select.innerHTML = '';
    for (var i = 1; i <= maxOrden; i++) {
        var op = document.createElement('option');
        op.value = i;
        op.textContent = i;
        if (i === maxOrden) op.selected = true;
        select.appendChild(op);
    }
}

function poblarSelectOrdenFaq(maxOrden, selectedValue) {
    var select = document.querySelector('select[name="orden_faq"]');
    if (!select) return;
    select.innerHTML = '';
    var items = Math.max(maxOrden, 1);
    for (var i = 1; i <= items; i++) {
        var op = document.createElement('option');
        op.value = i;
        op.textContent = i;
        select.appendChild(op);
    }
    if (selectedValue !== undefined && selectedValue !== null) {
        select.value = selectedValue;
    }
}

function toggleOrdenFaqField() {
    var check = document.getElementById('check-destacado');
    var label = document.getElementById('label-orden-faq');
    if (!check || !label) return;
    if (check.checked) {
        label.style.display = '';
        fetch('/admin/asesoria/articulos/max-orden-faq')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                poblarSelectOrdenFaq(data.max_orden_faq);
            });
    } else {
        label.style.display = 'none';
    }
}

function generarSlugArticulo() {
    var titulo = document.querySelector('input[name="titulo"]');
    var slug = document.querySelector('input[name="slug"]');
    if (!titulo || !slug) return;
    var valor = titulo.value.toLowerCase().trim();
    valor = valor.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    valor = valor.replace(/[^a-z0-9\s-]/g, '');
    valor = valor.replace(/\s+/g, '-');
    valor = valor.replace(/-+/g, '-');
    slug.value = valor;
}

function actualizarOrdenPorCategoria() {
    var catSel = document.querySelector('select[name="id_categoria_fk"]');
    if (!catSel || !catSel.value) {
        var ordSel = document.querySelector('select[name="orden"]');
        if (ordSel) {
            ordSel.innerHTML = '<option value="1">1</option>';
        }
        return;
    }
    fetch('/admin/asesoria/articulos/max-orden/' + catSel.value)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var maxOrden = data.max_orden || 0;
            poblarSelectOrdenArticulo(maxOrden + 1);
        });
}

function abrirModalNuevoArticulo() {
    var modal = document.getElementById('modal-nuevo-articulo');
    if (!modal) return;
    var form = modal.querySelector('form');
    if (form) {
        form.reset();
        var catSel = form.querySelector('select[name="id_categoria_fk"]');
        if (catSel && catSel.options.length > 0) {
            catSel.selectedIndex = 0;
        }
    }
    if (typeof tinymce !== 'undefined') {
        tinymce.remove('#contenido-articulo');
        tinymce.init({
            selector: '#contenido-articulo',
            height: 350,
            plugins: 'lists link',
            toolbar: 'bold italic underline | bullist numlist | link',
            branding: false,
            promotion: false,
        });
    }
    actualizarOrdenPorCategoria();
    var check = document.getElementById('check-destacado');
    if (check) {
        check.checked = false;
        check.onchange = toggleOrdenFaqField;
    }
    toggleOrdenFaqField();
    var errorDiv = modal.querySelector('.mensaje-error-js');
    if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }
    modal.style.display = 'flex';
    generarSlugArticulo();

    if (typeof tinymce !== 'undefined') {
        tinymce.remove('#contenido-articulo');
        tinymce.init({
            selector: '#contenido-articulo',
            height: 350,
            plugins: 'lists link',
            toolbar: 'bold italic underline | bullist numlist | link',
            branding: false,
            promotion: false,
        });
    }
}

function cerrarModalNuevoArticulo() {
    var modal = document.getElementById('modal-nuevo-articulo');
    if (modal) {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('#contenido-articulo');
        }
        modal.style.display = 'none';
        var form = modal.querySelector('form');
        if (form) {
            form.removeAttribute('data-edit-id');
            form.action = form.getAttribute('data-create-url');
        }
        var titulo = document.getElementById('modal-articulo-titulo');
        if (titulo) titulo.textContent = 'Nuevo artículo';
        var btn = document.getElementById('modal-articulo-boton');
        if (btn) btn.textContent = 'Crear artículo';
    }
}

function obtenerTokenCsrf() {
    var tag = document.querySelector('meta[name="csrf-token"]');
    return tag ? tag.getAttribute('content') : '';
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function stripHtml(html) {
    var d = document.createElement('div');
    d.innerHTML = html;
    return d.textContent || d.innerText || '';
}

function truncate(str, len) {
    if (str.length <= len) return str;
    return str.substring(0, len) + '…';
}

/* ================================================
   FILTROS, ORDENACIÓN Y PAGINACIÓN
   ================================================ */

function filtrarArticulos() {
    var busqueda = document.getElementById('filtro-busqueda').value;
    var estado = document.getElementById('filtro-estado').value;
    var categoria = document.getElementById('filtro-categoria') ? document.getElementById('filtro-categoria').value : '';
    var destacadoFiltro = document.getElementById('filtro-destacado') ? document.getElementById('filtro-destacado').value : '';
    var perPage = document.getElementById('filtro-paginacion').value;

    var params = new URLSearchParams();
    if (busqueda) params.set('q', busqueda);
    if (estado)   params.set('estado', estado);
    if (categoria) params.set('categoria', categoria);
    if (destacadoFiltro) {
        params.set('destacado_filtro', destacadoFiltro);
    }
    if (destacadoFiltro === '1') {
        if (sortCol !== 'orden_faq') {
            sortCol = 'orden_faq';
            sortDir = 'asc';
            paginaActual = 1;
            document.querySelectorAll('.sortable').forEach(function (th) {
                th.classList.remove('active');
                var arrow = th.querySelector('.sort-arrow');
                if (arrow) arrow.textContent = '';
            });
        }
    } else if (sortCol === 'orden_faq') {
        sortCol = 'categoria';
        sortDir = 'asc';
        paginaActual = 1;
        document.querySelectorAll('.sortable').forEach(function (th) {
            th.classList.remove('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '';
        });
        var th = document.querySelector('th[data-sort="categoria"]');
        if (th) {
            th.classList.add('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '\u25B2';
        }
    }
    params.set('sort', sortCol);
    params.set('direction', sortDir);
    params.set('page', paginaActual);
    params.set('per_page', perPage);

    var url = (typeof filtrarUrl !== 'undefined' ? filtrarUrl : '/admin/asesoria/articulos/filtrar') + '?' + params.toString();

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        actualizarTablaArticulos(data);
        actualizarPaginacionArticulos(data);
    })
    .catch(function (error) {
        console.error('Error al filtrar artículos:', error);
    });
}

function cambiarPaginaArticulos(n) {
    paginaActual = n;
    filtrarArticulos();
}

function ordenarArticulos(col) {
    if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        sortCol = col;
        sortDir = 'asc';
    }
    paginaActual = 1;

    document.querySelectorAll('.sortable').forEach(function (th) {
        th.classList.remove('active');
        var arrow = th.querySelector('.sort-arrow');
        if (arrow) arrow.textContent = '';
    });

    var th = document.querySelector('th[data-sort="' + col + '"]');
    if (th) {
        th.classList.add('active');
        var arrow = th.querySelector('.sort-arrow');
        if (arrow) arrow.textContent = sortDir === 'asc' ? '\u25B2' : '\u25BC';
    }

    filtrarArticulos();
}

function actualizarTablaArticulos(data) {
    var tbody = document.getElementById('tabla-articulos-body');
    if (!tbody) return;

    var rows = data.data || [];
    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #999; padding: 20px;">No hay artículos para mostrar</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < rows.length; i++) {
        var art = rows[i];
        var activo = art.estado ? '1' : '0';
        var estadoLabel = art.estado ? 'Activo' : 'Inactivo';
        var estadoClass = art.estado ? 'activo' : 'inactivo';
        var inactivaClass = activo === '0' ? 'fila-inactiva' : '';
        var toggleClass = activo === '1' ? 'activo' : '';

        var destacadoClass = art.destacado ? 'activo' : '';
        var destacadoIcono = art.destacado ? 'bi-star-fill' : 'bi-star';

        var categoriaNombre = art.categoria ? escHtml(art.categoria.nombre) : '-';
        var contenidoPreview = truncate(stripHtml(art.contenido || ''), 80);

        html += '<tr data-id="' + art.id + '" data-activo="' + activo + '" class="' + inactivaClass + '">'
            + '<td data-label="CATEGORÍA">' + categoriaNombre + '</td>'
            + '<td data-label="ORDEN">' + art.orden + '</td>'
            + '<td data-label="TÍTULO">' + escHtml(art.titulo) + '</td>'
            + '<td data-label="CONTENIDO" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#6B7280;font-size:13px;">' + escHtml(contenidoPreview) + '</td>'
            + '<td data-label="ESTADO"><span class="badge-estado badge-' + estadoClass + '">' + estadoLabel + '</span></td>'
            + '<td data-label="DESTACADO"><button class="btn-destacado ' + destacadoClass + '" data-id="' + art.id + '" title="' + (art.destacado ? 'Quitar destacado' : 'Marcar como destacado') + '"><i class="bi ' + destacadoIcono + '"></i></button></td>'
            + '<td data-label="ORDEN DESTACADO">' + (art.orden_faq != null ? art.orden_faq : '-') + '</td>'
            + '<td data-label="ACCIONES"><div class="acciones-tabla">'
            + '<button class="btn-accion btn-editar-articulo" data-id="' + art.id + '" title="Editar"><i class="bi bi-pencil"></i></button>'
            + '<button class="btn-accion btn-eliminar-articulo" data-id="' + art.id + '" title="Eliminar"><i class="bi bi-trash"></i></button>'
            + '<div class="toggle-switch ' + toggleClass + '" data-id="' + art.id + '"><div class="toggle-circulo"></div></div>'
            + '</div></td>'
            + '</tr>';
    }

    tbody.innerHTML = html;

    asignarToggleArticulos();
    asignarBotonesEditarArticulo();
    asignarBotonesEliminarArticulo();
    asignarBotonesDestacado();
}

function actualizarPaginacionArticulos(data) {
    var container = document.getElementById('paginacion-articulos');
    if (!container) return;

    var current = data.current_page;
    var last = data.last_page;
    var total = data.total;

    if (last <= 1) {
        container.innerHTML = '';
        return;
    }

    var html = '<ul style="display:flex;gap:4px;padding:0;margin:0;list-style:none;">';

    var prevDisabled = current <= 1 ? 'disabled' : '';
    html += '<li class="page-item"><button class="page-link ' + prevDisabled + '" data-page="' + (current - 1) + '">&laquo;</button></li>';

    var startPage = Math.max(1, current - 2);
    var endPage = Math.min(last, current + 2);

    if (startPage > 1) {
        html += '<li class="page-item"><button class="page-link" data-page="1">1</button></li>';
        if (startPage > 2) html += '<li class="page-item"><span class="page-link disabled">...</span></li>';
    }

    for (var p = startPage; p <= endPage; p++) {
        var active = p === current ? 'active' : '';
        html += '<li class="page-item"><button class="page-link ' + active + '" data-page="' + p + '">' + p + '</button></li>';
    }

    if (endPage < last) {
        if (endPage < last - 1) html += '<li class="page-item"><span class="page-link disabled">...</span></li>';
        html += '<li class="page-item"><button class="page-link" data-page="' + last + '">' + last + '</button></li>';
    }

    var nextDisabled = current >= last ? 'disabled' : '';
    html += '<li class="page-item"><button class="page-link ' + nextDisabled + '" data-page="' + (current + 1) + '">&raquo;</button></li>';

    html += '</ul>';

    html += '<div style="font-size:12px;color:#9CA3AF;text-align:center;width:100%;margin-top:4px;">'
        + total + ' artículo(s) &mdash; Página ' + current + ' de ' + last
        + '</div>';

    container.innerHTML = html;

    asignarEventosPaginacionArticulos();
}

function asignarEventosFiltrosArticulos() {
    var busqueda = document.getElementById('filtro-busqueda');
    var estado = document.getElementById('filtro-estado');
    var categoria = document.getElementById('filtro-categoria');
    var perPage = document.getElementById('filtro-paginacion');
    var limpiar = document.getElementById('btn-limpiar-filtros');

    function onFilterChange() {
        paginaActual = 1;
        filtrarArticulos();
    }

    if (busqueda) busqueda.addEventListener('input', onFilterChange);
    if (estado) estado.addEventListener('change', onFilterChange);
    var destacadoFiltroEl = document.getElementById('filtro-destacado');
    if (destacadoFiltroEl) destacadoFiltroEl.addEventListener('change', onFilterChange);
    if (categoria) {
        categoria.addEventListener('change', onFilterChange);
        var modalCat = document.querySelector('#modal-nuevo-articulo select[name="id_categoria_fk"]');
        if (modalCat) {
            modalCat.addEventListener('change', function () {
                actualizarOrdenPorCategoria();
            });
        }
    }
    if (perPage) perPage.addEventListener('change', onFilterChange);
    if (limpiar) limpiar.addEventListener('click', function () {
        if (busqueda) busqueda.value = '';
        if (estado) estado.value = '';
        if (categoria) categoria.value = '';
        if (destacadoFiltroEl) destacadoFiltroEl.value = '';
        if (perPage) perPage.value = '10';
        paginaActual = 1;
        sortCol = 'categoria';
        sortDir = 'asc';
        document.querySelectorAll('.sortable').forEach(function (th) {
            th.classList.remove('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '';
        });
        var th = document.querySelector('th[data-sort="categoria"]');
        if (th) {
            th.classList.add('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '\u25B2';
        }
        filtrarArticulos();
    });
}

function asignarEventosPaginacionArticulos() {
    var links = document.querySelectorAll('#paginacion-articulos .page-link[data-page]');
    for (var i = 0; i < links.length; i++) {
        links[i].onclick = function (event) {
            event.preventDefault();
            var page = parseInt(this.getAttribute('data-page'));
            if (!isNaN(page)) cambiarPaginaArticulos(page);
        };
    }

    document.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.onclick = function () {
            var col = this.getAttribute('data-sort');
            ordenarArticulos(col);
        };
    });
}

/* ================================================
   FORMULARIO AJAX — CREAR / EDITAR
   ================================================ */

document.querySelectorAll('form[data-ajax-form-articulo="true"]').forEach(function (form) {
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var modal = document.getElementById('modal-nuevo-articulo');
        var errorDiv = form.querySelector('.mensaje-error-js');
        if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }

        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }

        var titulo = form.querySelector('input[name="titulo"]');
        var slug = form.querySelector('input[name="slug"]');
        var categoria = form.querySelector('select[name="id_categoria_fk"]');
        var orden = form.querySelector('select[name="orden"]');
        var contenido = form.querySelector('textarea[name="contenido"]');

        var errors = [];
        if (!titulo || !titulo.value.trim()) errors.push('El título es obligatorio.');
        if (!slug || !slug.value.trim()) errors.push('El enlace es obligatorio.');
        if (!categoria || !categoria.value) errors.push('Selecciona una categoría.');
        if (!orden || !orden.value) errors.push('Selecciona un orden.');
        if (!contenido || !contenido.value.trim()) errors.push('El contenido es obligatorio.');

        var editId = form.getAttribute('data-edit-id');
        var slugExcluir = null;
        if (editId) {
            var tr = document.querySelector('tr[data-id="' + editId + '"]');
            if (tr) {
                var tituloTd = tr.querySelector('td[data-label="TÍTULO"]');
                if (tituloTd) slugExcluir = tituloTd.textContent.trim();
            }
        }

        if (errors.length) {
            if (errorDiv) { errorDiv.textContent = errors.join(' '); errorDiv.style.display = ''; }
            return;
        }

        var btn = form.querySelector('button[type="submit"]');
        var esEdicion = !!form.getAttribute('data-edit-id');
        if (btn) { btn.disabled = true; btn.textContent = esEdicion ? 'Guardando...' : 'Creando...'; }

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': obtenerTokenCsrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        })
        .then(function (resp) {
            return resp.json().catch(function () { return {}; }).then(function (datos) { return { ok: resp.ok, datos: datos }; });
        })
        .then(function (resultado) {
            if (!resultado.ok) {
                var msg = resultado.datos && resultado.datos.message ? resultado.datos.message : 'Error al crear el artículo.';
                if (resultado.datos && resultado.datos.errors) {
                    var campos = Object.keys(resultado.datos.errors);
                    if (campos.length > 0 && resultado.datos.errors[campos[0]].length > 0) msg = resultado.datos.errors[campos[0]][0];
                }
                throw new Error(msg);
            }
            cerrarModalNuevoArticulo();
            var mensajeExito = resultado.datos.message || (esEdicion ? 'Artículo actualizado correctamente.' : 'Artículo creado correctamente.');
            var tituloExito = esEdicion ? 'Artículo actualizado' : 'Artículo creado';
            if (window.swalSuccess) {
                swalSuccess(tituloExito, mensajeExito).then(function () {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        })
        .catch(function (error) {
            if (errorDiv) { errorDiv.textContent = error.message || 'Error al procesar la solicitud.'; errorDiv.style.display = ''; }
            else if (window.swalError) swalError('Error', error.message || (esEdicion ? 'No se pudo actualizar el artículo.' : 'No se pudo crear el artículo.'));
        })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.textContent = esEdicion ? 'Guardar cambios' : 'Crear artículo'; }
        });
    });
});

document.onkeydown = function (evento) {
    if (evento.key === 'Escape') {
        cerrarModalNuevoArticulo();
    }
};

/* ================================================
   TOGGLE ESTADO
   ================================================ */

function asignarToggleArticulos() {
    var toggles = document.querySelectorAll('.toggle-switch');
    for (var i = 0; i < toggles.length; i++) {
        var toggle = toggles[i];
        toggle.onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            toggleEstadoArticulo(id);
        };
    }
}

function toggleEstadoArticulo(id) {
    fetch('/admin/asesoria/articulos/' + id + '/toggle-estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        if (data.success) {
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            if (!tr) return;

            var toggle = tr.querySelector('.toggle-switch');
            if (toggle) toggle.classList.toggle('activo');

            var nuevoActivo = tr.getAttribute('data-activo') === '1' ? '0' : '1';
            tr.setAttribute('data-activo', nuevoActivo);

            var badge = tr.querySelector('.badge-estado');
            if (badge) {
                if (nuevoActivo === '1') {
                    badge.textContent = 'Activo';
                    badge.className = 'badge-estado badge-activo';
                } else {
                    badge.textContent = 'Inactivo';
                    badge.className = 'badge-estado badge-inactivo';
                }
            }

            if (nuevoActivo === '1') {
                tr.classList.remove('fila-inactiva');
            } else {
                tr.classList.add('fila-inactiva');
            }
        } else {
            swalError('Error', data.message || 'No se pudo cambiar el estado del artículo.');
        }
    })
    .catch(function (error) {
        console.error('Error en toggle estado artículo:', error);
        swalError('Error', 'No se pudo cambiar el estado del artículo.');
    });
}

/* ================================================
   TOGGLE DESTACADO
   ================================================ */

function asignarBotonesDestacado() {
    var btns = document.querySelectorAll('.btn-destacado');
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            toggleDestacadoArticulo(this, id);
        };
    }
}

function toggleDestacadoArticulo(btn, id) {
    fetch('/admin/asesoria/articulos/' + id + '/toggle-destacado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        if (data.success) {
            var icon = btn.querySelector('i');
            if (data.destacado) {
                btn.classList.add('activo');
                icon.className = 'bi bi-star-fill';
                btn.title = 'Quitar destacado';
            } else {
                btn.classList.remove('activo');
                icon.className = 'bi bi-star';
                btn.title = 'Marcar como destacado';
            }

            if (data.affected) {
                for (var j = 0; j < data.affected.length; j++) {
                    var aff = data.affected[j];
                    var row = document.querySelector('#tabla-articulos-admin tbody tr[data-id="' + aff.id + '"]');
                    if (row) {
                        var td = row.querySelector('td[data-label="ORDEN DESTACADO"]');
                        if (td) td.textContent = aff.orden_faq != null ? aff.orden_faq : '-';
                    }
                }
            }
        } else {
            swalError('Error', data.message || 'No se pudo cambiar el destacado del artículo.');
        }
    })
    .catch(function (error) {
        console.error('Error en toggle destacado artículo:', error);
        swalError('Error', 'No se pudo cambiar el destacado del artículo.');
    });
}

/* ================================================
   EDITAR
   ================================================ */

function asignarBotonesEditarArticulo() {
    var btns = document.querySelectorAll('.btn-editar-articulo');
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModalEditarArticulo(id);
        };
    }
}

function abrirModalEditarArticulo(id) {
    var modal = document.getElementById('modal-nuevo-articulo');
    if (!modal) return;

    fetch('/admin/asesoria/articulos/' + id + '/editar')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var art = data.articulo;
            var form = modal.querySelector('form');
            if (!form) return;

            form.setAttribute('data-edit-id', id);
            form.action = '/admin/asesoria/articulos/' + id + '/actualizar';

            form.querySelector('input[name="titulo"]').value = art.titulo;
            form.querySelector('input[name="slug"]').value = art.slug;
            form.querySelector('select[name="id_categoria_fk"]').value = art.id_categoria_fk;

            poblarSelectOrdenArticulo(data.maxOrden);
            var sel = form.querySelector('select[name="orden"]');
            if (sel) sel.value = art.orden;

            var textarea = form.querySelector('textarea[name="contenido"]');
            textarea.value = art.contenido;

            var destacadoCheck = form.querySelector('input[name="destacado"]');
            if (destacadoCheck) {
                destacadoCheck.checked = art.destacado;
                destacadoCheck.onchange = toggleOrdenFaqField;
                var labelFaq = document.getElementById('label-orden-faq');
                if (art.destacado) {
                    labelFaq.style.display = '';
                    fetch('/admin/asesoria/articulos/max-orden-faq')
                        .then(function (r) { return r.json(); })
                        .then(function (faqData) {
                            poblarSelectOrdenFaq(faqData.max_orden_faq, art.orden_faq);
                        });
                } else {
                    labelFaq.style.display = 'none';
                }
            }

            document.getElementById('modal-articulo-titulo').textContent = 'Editar artículo';
            var btn = document.getElementById('modal-articulo-boton');
            if (btn) btn.textContent = 'Guardar cambios';

            var errorDiv = modal.querySelector('.mensaje-error-js');
            if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }

            modal.style.display = 'flex';

            if (typeof tinymce !== 'undefined') {
                tinymce.remove('#contenido-articulo');
                tinymce.init({
                    selector: '#contenido-articulo',
                    height: 350,
                    plugins: 'lists link',
                    toolbar: 'bold italic underline | bullist numlist | link',
                    branding: false,
                    promotion: false,
                });
            }
        })
        .catch(function (error) {
            console.error('Error al cargar artículo:', error);
            if (window.swalError) swalError('Error', 'No se pudo cargar el artículo.');
        });
}

/* ================================================
   ELIMINAR
   ================================================ */

function asignarBotonesEliminarArticulo() {
    var btns = document.querySelectorAll('.btn-eliminar-articulo');
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            eliminarArticulo(id);
        };
    }
}

function eliminarArticulo(id) {
    var tr = document.querySelector('tr[data-id="' + id + '"]');
    var titulo = tr ? tr.querySelector('td[data-label="TÍTULO"]').textContent.trim() : '';

    if (window.Swal) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Estás seguro de que quieres eliminar el artículo "' + titulo + '"? Esta acción no se puede deshacer.',
            iconHtml: crearOsoPregunta(),
            customClass: { icon: 'oso-icon' },
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch('/admin/asesoria/articulos/' + id + '/eliminar', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    swalSuccess('Artículo eliminado', 'Artículo eliminado correctamente.').then(function () {
                        window.location.reload();
                    });
                } else {
                    swalError('Error', data.message || 'No se pudo eliminar el artículo.');
                }
            })
            .catch(function (error) {
                console.error('Error al eliminar artículo:', error);
                swalError('Error', 'No se pudo eliminar el artículo.');
            });
        });
    }
}
