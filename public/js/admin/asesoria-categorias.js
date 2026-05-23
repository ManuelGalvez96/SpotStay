
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

function obtenerSlugsExistentes() {
    var slugs = [];
    document.querySelectorAll('.tabla-admin tbody tr td[data-label="ENLACE"] code').forEach(function (el) {
        slugs.push(el.textContent.trim());
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
    if (modal) modal.style.display = 'none';
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

document.querySelectorAll('form[data-ajax-nueva-categoria="true"]').forEach(function (form) {
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var modal = document.getElementById('modal-nueva-categoria');
        var errorDiv = form.querySelector('.mensaje-error-js');
        if (errorDiv) { errorDiv.style.display = 'none'; errorDiv.textContent = ''; }
        var nombre = form.querySelector('input[name="nombre"]');
        var slug = form.querySelector('input[name="slug"]');
        var orden = form.querySelector('select[name="orden"]');
        var icono = form.querySelector('input[name="icono"]');
        var errors = [];
        if (!nombre || !nombre.value.trim()) errors.push('El nombre es obligatorio.');
        if (!slug || !slug.value.trim()) errors.push('El enlace es obligatorio.');
        var slugsExistentes = obtenerSlugsExistentes();
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
        if (btn) { btn.disabled = true; btn.textContent = 'Creando...'; }
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
            if (window.swalSuccess) {
                swalSuccess('Categoría creada', resultado.datos.message || 'Categoría creada correctamente.').then(function () {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        })
        .catch(function (error) {
            if (errorDiv) { errorDiv.textContent = error.message || 'Error al procesar la solicitud.'; errorDiv.style.display = ''; }
            else if (window.swalError) swalError('Error', error.message || 'No se pudo crear la categoría.');
        })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.textContent = 'Crear categoría'; }
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
                    swalSuccess('¡Éxito!', 'Categoría activada.');
                } else {
                    badge.textContent = 'Inactivo';
                    badge.className = 'badge-estado badge-inactivo';
                    swalSuccess('¡Éxito!', 'Categoría desactivada.');
                }
            }

            tr.classList.toggle('fila-inactiva');
        } else {
            swalError('Error', data.message || 'No se pudo cambiar el estado de la categoría.');
        }
    })
    .catch(function (error) {
        console.error('Error en toggle estado categoría:', error);
        swalError('Error', 'No se pudo cambiar el estado de la categoría.');
    });
}
