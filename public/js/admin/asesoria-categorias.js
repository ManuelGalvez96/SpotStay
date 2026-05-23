
var csrfToken = document.querySelector('meta[name=csrf-token]').content;
var iconoSeleccionado = null;

var iconosDisponibles = [
    'bi bi-folder', 'bi bi-folder2', 'bi bi-folder-check', 'bi bi-folder-plus',
    'bi bi-file-text', 'bi bi-file-earmark-text', 'bi bi-file-earmark', 'bi bi-file-check',
    'bi bi-journal-text', 'bi bi-journal', 'bi bi-book', 'bi bi-bookmark',
    'bi bi-bookmark-check', 'bi bi-clipboard', 'bi bi-clipboard-check', 'bi bi-files',
    'bi bi-house', 'bi bi-house-door', 'bi bi-building', 'bi bi-buildings',
    'bi bi-bank', 'bi bi-bank2', 'bi bi-shop', 'bi bi-shop-window',
    'bi bi-shield', 'bi bi-shield-check', 'bi bi-shield-exclamation', 'bi bi-lock',
    'bi bi-unlock', 'bi bi-key', 'bi bi-cash', 'bi bi-cash-stack',
    'bi bi-coin', 'bi bi-calculator', 'bi bi-receipt', 'bi bi-credit-card',
    'bi bi-wallet', 'bi bi-chat', 'bi bi-chat-dots', 'bi bi-chat-square',
    'bi bi-envelope', 'bi bi-telephone', 'bi bi-megaphone', 'bi bi-wrench',
    'bi bi-wrench-adjustable', 'bi bi-tools', 'bi bi-gear', 'bi bi-gears',
    'bi bi-sliders', 'bi bi-compass', 'bi bi-map', 'bi bi-pin-map',
    'bi bi-geo-alt', 'bi bi-signpost', 'bi bi-person', 'bi bi-people',
    'bi bi-person-badge', 'bi bi-person-check', 'bi bi-person-plus', 'bi bi-person-x',
    'bi bi-check-circle', 'bi bi-exclamation-triangle', 'bi bi-exclamation-circle', 'bi bi-info-circle',
    'bi bi-question-circle', 'bi bi-star', 'bi bi-star-fill', 'bi bi-heart',
    'bi bi-heart-fill', 'bi bi-bell', 'bi bi-calendar', 'bi bi-clock',
    'bi bi-alarm', 'bi bi-camera', 'bi bi-eye', 'bi bi-search',
    'bi bi-flag', 'bi bi-globe', 'bi bi-graph-up', 'bi bi-graph-down',
    'bi bi-grid', 'bi bi-layers', 'bi bi-lightbulb', 'bi bi-pencil',
    'bi bi-pin', 'bi bi-plus-circle', 'bi bi-printer', 'bi bi-rocket',
    'bi bi-tag', 'bi bi-trash', 'bi bi-truck', 'bi bi-tv',
    'bi bi-umbrella', 'bi bi-activity', 'bi bi-arrow-up-circle', 'bi bi-arrow-down-circle',
    'bi bi-droplet', 'bi bi-fire', 'bi bi-sun', 'bi bi-moon',
    'bi bi-cloud', 'bi bi-gift', 'bi bi-award', 'bi bi-bar-chart',
];

function poblarSelectOrden(maxOrden) {
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

function obtenerSlugsExistentes(excluirSlug) {
    var slugs = [];
    document.querySelectorAll('.tabla-admin tbody tr td[data-label="ENLACE"] code').forEach(function (el) {
        var slug = el.textContent.trim();
        if (slug !== excluirSlug) slugs.push(slug);
    });
    return slugs;
}

function generarSlug() {
    var nombre = document.querySelector('input[name="nombre"]');
    var slug = document.querySelector('input[name="slug"]');
    if (!nombre || !slug) return;
    var valor = nombre.value.toLowerCase().trim();
    valor = valor.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    valor = valor.replace(/[^a-z0-9\s-]/g, '');
    valor = valor.replace(/\s+/g, '-');
    valor = valor.replace(/-+/g, '-');
    slug.value = valor;
}

function abrirModalNuevaCategoria() {
    var modal = document.getElementById('modal-nueva-categoria');
    if (!modal) return;
    var form = modal.querySelector('form');
    if (form) {
        form.reset();
        var sel = form.querySelector('select[name="orden"]');
        if (sel && sel.options.length) sel.value = sel.options[sel.options.length - 1].value;
    }
    iconoSeleccionado = null;
    var preview = document.getElementById('icono-preview');
    if (preview) preview.innerHTML = '<i class="bi bi-question-circle"></i>';
    var hidden = form.querySelector('input[name="icono"]');
    if (hidden) hidden.value = 'bi bi-question-circle';
    var errorDiv = modal.querySelector('.mensaje-error-js');
    if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }
    modal.style.display = 'flex';
    generarSlug();
}

function cerrarModalNuevaCategoria() {
    var modal = document.getElementById('modal-nueva-categoria');
    if (modal) {
        modal.style.display = 'none';
        var form = modal.querySelector('form');
        if (form) {
            form.removeAttribute('data-edit-id');
            form.action = form.getAttribute('data-create-url');
        }
        var titulo = document.getElementById('modal-categoria-titulo');
        if (titulo) titulo.textContent = 'Nueva categoría';
        var btn = document.getElementById('modal-categoria-boton');
        if (btn) btn.textContent = 'Crear categoría';
    }
}

function abrirSelectorIconos() {
    var modal = document.getElementById('modal-selector-iconos');
    if (!modal) return;
    var items = modal.querySelectorAll('.icono-picker-item');
    items.forEach(function (el) { el.classList.remove('seleccionado'); });
    if (iconoSeleccionado) {
        var coincidente = modal.querySelector('.icono-picker-item[data-icono="' + iconoSeleccionado.replace(/"/g, '') + '"]');
        if (coincidente) coincidente.classList.add('seleccionado');
    }
    modal.style.display = 'flex';
}

function cerrarSelectorIconos() {
    var modal = document.getElementById('modal-selector-iconos');
    if (modal) modal.style.display = 'none';
}

function guardarIconoSeleccionado() {
    var seleccionado = document.querySelector('#modal-selector-iconos .icono-picker-item.seleccionado');
    if (!seleccionado) return;
    iconoSeleccionado = seleccionado.getAttribute('data-icono');
    var preview = document.getElementById('icono-preview');
    if (preview) preview.innerHTML = '<i class="' + iconoSeleccionado + '"></i>';
    var hidden = document.querySelector('input[name="icono"]');
    if (hidden) hidden.value = iconoSeleccionado;
    cerrarSelectorIconos();
}

function cargarIconos() {
    var grid = document.getElementById('icono-picker-grid');
    if (!grid) return;
    grid.innerHTML = '';
    iconosDisponibles.forEach(function (icono) {
        var div = document.createElement('div');
        div.className = 'icono-picker-item';
        div.setAttribute('data-icono', icono);
        div.innerHTML = '<i class="' + icono + '"></i>';
        div.addEventListener('click', function () {
            grid.querySelectorAll('.icono-picker-item').forEach(function (el) { el.classList.remove('seleccionado'); });
            this.classList.add('seleccionado');
        });
        grid.appendChild(div);
    });
}

function obtenerTokenCsrf() {
    var tag = document.querySelector('meta[name="csrf-token"]');
    return tag ? tag.getAttribute('content') : '';
}

document.querySelectorAll('form[data-ajax-form="true"]').forEach(function (form) {
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var modal = document.getElementById('modal-nueva-categoria');
        var errorDiv = form.querySelector('.mensaje-error-js');
        if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }
        var nombre = form.querySelector('input[name="nombre"]');
        var slug = form.querySelector('input[name="slug"]');
        var orden = form.querySelector('select[name="orden"]');
        var icono = form.querySelector('input[name="icono"]');
        var editId = form.getAttribute('data-edit-id');
        var errors = [];
        if (!nombre || !nombre.value.trim()) errors.push('El nombre es obligatorio.');
        if (!slug || !slug.value.trim()) errors.push('El enlace es obligatorio.');
        var slugExcluir = null;
        if (editId) {
            var tr = document.querySelector('tr[data-id="' + editId + '"]');
            if (tr) slugExcluir = tr.querySelector('td[data-label="ENLACE"] code').textContent.trim();
        }
        var slugsExistentes = obtenerSlugsExistentes(slugExcluir);
        if (slug && slug.value.trim() && slugsExistentes.includes(slug.value.trim())) {
            errors.push('El enlace ya está en uso. Modifica el nombre para generar un enlace diferente.');
        }
        if (!orden || !orden.value) errors.push('Selecciona un orden.');
        if (!icono || !icono.value) errors.push('Selecciona un icono.');
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
                var msg = resultado.datos && resultado.datos.message ? resultado.datos.message : 'Error al crear la categoría.';
                if (resultado.datos && resultado.datos.errors) {
                    var campos = Object.keys(resultado.datos.errors);
                    if (campos.length > 0 && resultado.datos.errors[campos[0]].length > 0) msg = resultado.datos.errors[campos[0]][0];
                }
                throw new Error(msg);
            }
            cerrarModalNuevaCategoria();
            var mensajeExito = resultado.datos.message || (esEdicion ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.');
            var tituloExito = esEdicion ? 'Categoría actualizada' : 'Categoría creada';
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
            else if (window.swalError) swalError('Error', error.message || (esEdicion ? 'No se pudo actualizar la categoría.' : 'No se pudo crear la categoría.'));
        })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.textContent = esEdicion ? 'Guardar cambios' : 'Crear categoría'; }
        });
    });
});

document.onkeydown = function (evento) {
    if (evento.key === 'Escape') {
        cerrarModalNuevaCategoria();
        cerrarSelectorIconos();
    }
};

function asignarToggleCategorias() {
    var toggles = document.querySelectorAll('.toggle-switch');
    for (var i = 0; i < toggles.length; i++) {
        var toggle = toggles[i];
        toggle.onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            toggleEstadoCategoria(id);
        };
    }
}

function toggleEstadoCategoria(id) {
    var url = '/admin/asesoria/categoria/' + id + '/toggle-estado';

    fetch(url, {
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
            swalError('Error', data.message || 'No se pudo cambiar el estado de la categoría.');
        }
    })
    .catch(function (error) {
        console.error('Error en toggle estado categoría:', error);
        swalError('Error', 'No se pudo cambiar el estado de la categoría.');
    });
}

function asignarBotonesEditar() {
    var btns = document.querySelectorAll('.btn-editar');
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModalEditarCategoria(id);
        };
    }
}

function abrirModalEditarCategoria(id) {
    var modal = document.getElementById('modal-nueva-categoria');
    if (!modal) return;

    fetch('/admin/asesoria/categoria/' + id + '/editar')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var cat = data.categoria;
            var form = modal.querySelector('form');
            if (!form) return;

            form.setAttribute('data-edit-id', id);
            form.action = '/admin/asesoria/categoria/' + id + '/actualizar';

            form.querySelector('input[name="nombre"]').value = cat.nombre;
            form.querySelector('input[name="slug"]').value = cat.slug;

            poblarSelectOrden(data.maxOrden);
            var sel = form.querySelector('select[name="orden"]');
            if (sel) sel.value = cat.orden;

            iconoSeleccionado = cat.icono;
            var preview = document.getElementById('icono-preview');
            if (preview) preview.innerHTML = '<i class="' + cat.icono + '"></i>';
            var hidden = form.querySelector('input[name="icono"]');
            if (hidden) hidden.value = cat.icono;

            document.getElementById('modal-categoria-titulo').textContent = 'Editar categoría';
            var btn = document.getElementById('modal-categoria-boton');
            if (btn) btn.textContent = 'Guardar cambios';

            var errorDiv = modal.querySelector('.mensaje-error-js');
            if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }

            modal.style.display = 'flex';
        })
        .catch(function (error) {
            console.error('Error al cargar categoría:', error);
            if (window.swalError) swalError('Error', 'No se pudo cargar la categoría.');
        });
}

function asignarBotonesEliminar() {
    var btns = document.querySelectorAll('.btn-eliminar:not(.btn-eliminar--disabled)');
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function (event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            eliminarCategoria(id);
        };
    }
}

function eliminarCategoria(id) {
    var tr = document.querySelector('tr[data-id="' + id + '"]');
    var nombre = tr ? tr.querySelector('td[data-label="NOMBRE"]').textContent.trim() : '';

    if (window.Swal) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Estás seguro de que quieres eliminar la categoría ' + nombre + '? Esta acción no se puede deshacer.',
            iconHtml: crearOsoPregunta(),
            customClass: { icon: 'oso-icon' },
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch('/admin/asesoria/categoria/' + id + '/eliminar', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    swalSuccess('Categoría eliminada', 'Categoría eliminada correctamente.').then(function () {
                        window.location.reload();
                    });
                } else {
                    swalError('Error', data.message || 'No se pudo eliminar la categoría.');
                }
            })
            .catch(function (error) {
                console.error('Error al eliminar categoría:', error);
                swalError('Error', 'No se pudo eliminar la categoría.');
            });
        });
    }
}

/* ================================================
   FILTROS, ORDENACIÓN Y PAGINACIÓN
   ================================================ */
var paginaActual = 1;
var sortCol = 'orden';
var sortDir = 'asc';

function filtrarCategorias() {
    var busqueda = document.getElementById('filtro-busqueda').value;
    var estado = document.getElementById('filtro-estado').value;
    var perPage = document.getElementById('filtro-paginacion').value;

    var params = new URLSearchParams();
    if (busqueda) params.set('q', busqueda);
    if (estado)   params.set('estado', estado);
    params.set('sort', sortCol);
    params.set('direction', sortDir);
    params.set('page', paginaActual);
    params.set('per_page', perPage);

    var url = (typeof filtrarUrl !== 'undefined' ? filtrarUrl : '/admin/asesoria/filtrar') + '?' + params.toString();

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        actualizarTabla(data);
        actualizarPaginacion(data);
    })
    .catch(function (error) {
        console.error('Error al filtrar categorías:', error);
    });
}

function cambiarPagina(n) {
    paginaActual = n;
    filtrarCategorias();
}

function ordenar(col) {
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

    filtrarCategorias();
}

function actualizarTabla(data) {
    var tbody = document.getElementById('tabla-categorias-body');
    if (!tbody) return;

    var rows = data.data || [];
    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #999; padding: 20px;">No hay categorías para mostrar</td></tr>';
        return;
    }

    var html = '';
    for (var i = 0; i < rows.length; i++) {
        var cat = rows[i];
        var activo = cat.estado ? '1' : '0';
        var estadoLabel = cat.estado ? 'Activo' : 'Inactivo';
        var estadoClass = cat.estado ? 'activo' : 'inactivo';
        var inactivaClass = activo === '0' ? 'fila-inactiva' : '';
        var toggleClass = activo === '1' ? 'activo' : '';

        var deleteBtn = '';
        if (cat.articulos_count > 0) {
            deleteBtn = '<span class="tooltip-wrapper" data-tooltip="No puedes eliminar esta categoría porque tiene artículos.">'
                + '<button class="btn-accion btn-eliminar btn-eliminar--disabled" disabled>'
                + '<i class="bi bi-trash"></i></button></span>';
        } else {
            deleteBtn = '<button class="btn-accion btn-eliminar" data-id="' + cat.id + '" title="Eliminar">'
                + '<i class="bi bi-trash"></i></button>';
        }

        html += '<tr data-id="' + cat.id + '" data-activo="' + activo + '" class="' + inactivaClass + '">'
            + '<td data-label="ORDEN">' + cat.orden + '</td>'
            + '<td data-label="NOMBRE">' + escHtml(cat.nombre) + '</td>'
            + '<td data-label="ENLACE"><code>' + escHtml(cat.slug) + '</code></td>'
            + '<td data-label="ARTÍCULOS">' + cat.articulos_count + '</td>'
            + '<td data-label="ICONO"><i class="bi ' + escHtml(cat.icono) + '"></i></td>'
            + '<td data-label="ESTADO"><span class="badge-estado badge-' + estadoClass + '">' + estadoLabel + '</span></td>'
            + '<td data-label="ACCIONES"><div class="acciones-tabla">'
            + '<button class="btn-accion btn-editar" data-id="' + cat.id + '" title="Editar"><i class="bi bi-pencil"></i></button>'
            + deleteBtn
            + '<div class="toggle-switch ' + toggleClass + '" data-id="' + cat.id + '"><div class="toggle-circulo"></div></div>'
            + '</div></td>'
            + '</tr>';
    }

    tbody.innerHTML = html;

    asignarToggleCategorias();
    asignarBotonesEditar();
    asignarBotonesEliminar();
}

function actualizarPaginacion(data) {
    var container = document.getElementById('paginacion-categorias');
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
        + total + ' categoría(s) &mdash; Página ' + current + ' de ' + last
        + '</div>';

    container.innerHTML = html;

    asignarEventosPaginacion();
}

function asignarEventosFiltros() {
    var busqueda = document.getElementById('filtro-busqueda');
    var estado = document.getElementById('filtro-estado');
    var perPage = document.getElementById('filtro-paginacion');
    var limpiar = document.getElementById('btn-limpiar-filtros');

    function onFilterChange() {
        paginaActual = 1;
        filtrarCategorias();
    }

    if (busqueda) busqueda.addEventListener('input', onFilterChange);
    if (estado) estado.addEventListener('change', onFilterChange);
    if (perPage) perPage.addEventListener('change', onFilterChange);
    if (limpiar) limpiar.addEventListener('click', function () {
        if (busqueda) busqueda.value = '';
        if (estado) estado.value = '';
        if (perPage) perPage.value = '10';
        paginaActual = 1;
        sortCol = 'orden';
        sortDir = 'asc';
        document.querySelectorAll('.sortable').forEach(function (th) {
            th.classList.remove('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '';
        });
        filtrarCategorias();
    });
}

function asignarEventosPaginacion() {
    var links = document.querySelectorAll('#paginacion-categorias .page-link[data-page]');
    for (var i = 0; i < links.length; i++) {
        links[i].onclick = function (event) {
            event.preventDefault();
            var page = parseInt(this.getAttribute('data-page'));
            if (!isNaN(page)) cambiarPagina(page);
        };
    }

    document.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.onclick = function () {
            var col = this.getAttribute('data-sort');
            ordenar(col);
        };
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
