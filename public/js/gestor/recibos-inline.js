document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function toText(node) {
        return node ? node.textContent.trim() : '';
    }

    document.querySelectorAll('.recibo-row').forEach(function (row) {
        const editBtn = row.querySelector('.btn-edit');
        const deleteBtn = row.querySelector('.btn-delete');
        const saveBtn = row.querySelector('.btn-save');
        const cancelBtn = row.querySelector('.btn-cancel');

        const categoriaSpan = row.querySelector('.display-categoria');
        const conceptoSpan = row.querySelector('.display-concepto');
        const importeSpan = row.querySelector('.display-importe');
        const fechaSpan = row.querySelector('.display-fecha');

        const gastoId = row.dataset.gastoId;
        const propiedadId = row.dataset.propiedadId;

        let original = {};

        function enterEditMode() {
            original = {
                categoria: toText(categoriaSpan),
                concepto: toText(conceptoSpan),
                importe: toText(importeSpan).replace(/[€\s\.]/g, '').replace(',', '.'),
                fecha: toText(fechaSpan)
            };

            // replace spans with inputs
            const select = document.createElement('select');
            select.name = 'categoria_gasto';
            ['','luz','agua','gas','internet','comunidad','otros'].forEach(function(k){
                const opt = document.createElement('option');
                opt.value = k;
                opt.text = k === '' ? 'Selecciona una categoría' : k.charAt(0).toUpperCase() + k.slice(1);
                if (k === original.categoria.toLowerCase()) opt.selected = true;
                select.appendChild(opt);
            });
            categoriaSpan.innerHTML = '';
            categoriaSpan.appendChild(select);

            const conceptoInput = document.createElement('input');
            conceptoInput.type = 'text';
            conceptoInput.name = 'concepto_gasto';
            conceptoInput.value = original.concepto;
            conceptoInput.maxLength = 200;
            conceptoSpan.innerHTML = '';
            conceptoSpan.appendChild(conceptoInput);

            const importeInput = document.createElement('input');
            importeInput.type = 'number';
            importeInput.step = '0.01';
            importeInput.min = '0.01';
            importeInput.name = 'importe_estimado';
            importeInput.value = parseFloat(original.importe) || '';
            importeSpan.innerHTML = '';
            importeSpan.appendChild(importeInput);

            const fechaInput = document.createElement('input');
            fechaInput.type = 'date';
            fechaInput.name = 'fecha_inicio_gasto';
            fechaInput.value = original.fecha || '';
            fechaSpan.innerHTML = '';
            fechaSpan.appendChild(fechaInput);

            editBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
            saveBtn.style.display = '';
            cancelBtn.style.display = '';
        }

        function exitEditMode(reset = false) {
            if (reset) {
                categoriaSpan.textContent = original.categoria || '';
                conceptoSpan.textContent = original.concepto || '';
                importeSpan.textContent = original.importe ? (parseFloat(original.importe).toFixed(2).replace('.', ',') + ' EUR') : '';
                fechaSpan.textContent = original.fecha || '';
            } else {
                // keep whatever is currently in the inputs
                const sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
                const ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
                const im = importeSpan.querySelector('input[name="importe_estimado"]');
                const fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

                categoriaSpan.textContent = sel ? (sel.value ? sel.value.charAt(0).toUpperCase() + sel.value.slice(1) : '') : categoriaSpan.textContent;
                conceptoSpan.textContent = ci ? ci.value : conceptoSpan.textContent;
                importeSpan.textContent = im && im.value ? (parseFloat(im.value).toFixed(2).replace('.', ',') + ' EUR') : importeSpan.textContent;
                fechaSpan.textContent = fi ? fi.value : fechaSpan.textContent;
            }

            editBtn.style.display = '';
            deleteBtn.style.display = '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }

        editBtn?.addEventListener('click', function () {
            enterEditMode();
        });

        cancelBtn?.addEventListener('click', function () {
            exitEditMode(true);
        });

        deleteBtn?.addEventListener('click', function () {
            const proceed = () => {
                const url = `/gestor/propiedades/${propiedadId}/gastos/${gastoId}/eliminar`;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(r => {
                    if (r.ok) {
                        if (window.swalSuccess) swalSuccess('Eliminado', 'Recibo eliminado correctamente').then(() => location.reload()); else location.reload();
                    } else {
                        if (window.swalError) swalError('Error', 'Error al eliminar.'); else alert('Error al eliminar.');
                    }
                }).catch(() => { if (window.swalError) swalError('Error de red', 'No se ha podido conectar'); else alert('Error de red.'); });
            };

            if (window.Swal) {
                Swal.fire({
                    title: '¿Eliminar recibo?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => { if (result.isConfirmed) proceed(); });
            } else {
                if (confirm('¿Seguro que quieres eliminar este recibo?')) proceed();
            }
        });

        saveBtn?.addEventListener('click', function () {
            const sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
            const ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
            const im = importeSpan.querySelector('input[name="importe_estimado"]');
            const fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

            const payload = new FormData();
            payload.append('_token', csrf);
            payload.append('categoria_gasto', sel ? sel.value : '');
            payload.append('concepto_gasto', ci ? ci.value : '');
            payload.append('importe_estimado', im ? im.value : '');
            payload.append('fecha_inicio_gasto', fi ? fi.value : '');

            const url = `/gestor/propiedades/${propiedadId}/gastos/${gastoId}/editar`;
            fetch(url, {
                method: 'POST',
                body: payload,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(async (res) => {
                if (res.ok) {
                    if (window.swalSuccess) swalSuccess('Guardado', 'Cambios guardados correctamente').then(() => location.reload()); else location.reload();
                } else if (res.status === 422) {
                    const data = await res.json();
                    if (window.swalError) swalError('Error de validación', (data?.message) || 'Error de validación.'); else alert((data?.message) || 'Error de validación.');
                } else {
                    if (window.swalError) swalError('Error', 'Error al guardar.'); else alert('Error al guardar.');
                }
            }).catch(() => { if (window.swalError) swalError('Error de red', 'No se ha podido conectar'); else alert('Error de red.'); });
        });
    });

    // Inline edit for cuotas (rows in the monthly table)
    document.querySelectorAll('.cuota-row').forEach(function (row) {
        const editBtn = row.querySelector('.btn-cuota-edit');
        const saveBtn = row.querySelector('.btn-cuota-save');
        const cancelBtn = row.querySelector('.btn-cuota-cancel');

        const conceptoSpan = row.querySelector('.display-concepto');
        const categoriaSpan = row.querySelector('.display-categoria');
        const fechaSpan = row.querySelector('.display-fecha');

        const gastoId = row.dataset.gastoId;
        const propiedadId = row.dataset.propiedadId;
        const importeDefault = row.dataset.importe || '';
        const mesDefault = row.dataset.mes || '';

        let original = {};

        function enterEditMode() {
            original = {
                concepto: toText(conceptoSpan),
                categoria: toText(categoriaSpan),
                fecha: toText(fechaSpan),
                importe: importeDefault
            };

            const select = document.createElement('select');
            select.name = 'categoria_gasto';
            ['','luz','agua','gas','internet','comunidad','otros'].forEach(function(k){
                const opt = document.createElement('option');
                opt.value = k;
                opt.text = k === '' ? 'Selecciona una categoría' : k.charAt(0).toUpperCase() + k.slice(1);
                if (k === original.categoria.toLowerCase()) opt.selected = true;
                select.appendChild(opt);
            });
            categoriaSpan.innerHTML = '';
            categoriaSpan.appendChild(select);

            const conceptoInput = document.createElement('input');
            conceptoInput.type = 'text';
            conceptoInput.name = 'concepto_gasto';
            conceptoInput.value = original.concepto === 'Sin concepto' ? '' : original.concepto;
            conceptoInput.maxLength = 200;
            conceptoSpan.innerHTML = '';
            conceptoSpan.appendChild(conceptoInput);

            const importeInput = document.createElement('input');
            importeInput.type = 'number';
            importeInput.step = '0.01';
            importeInput.min = '0.01';
            importeInput.name = 'importe_estimado';
            importeInput.value = parseFloat(original.importe) || '';
            // show importe input next to fecha
            const fechaInput = document.createElement('input');
            fechaInput.type = 'date';
            fechaInput.name = 'fecha_inicio_gasto';
            fechaInput.value = mesDefault ? mesDefault : '';
            fechaSpan.innerHTML = '';
            fechaSpan.appendChild(fechaInput);

            // store importe input in a data attribute holder
            row.querySelector('.detalle-acciones').appendChild(importeInput);

            editBtn.style.display = 'none';
            saveBtn.style.display = '';
            cancelBtn.style.display = '';
        }

        function exitEditMode(reset = false) {
            if (reset) {
                conceptoSpan.textContent = original.concepto || '';
                categoriaSpan.textContent = original.categoria || '';
                fechaSpan.textContent = original.fecha || '';
            } else {
                const sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
                const ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
                const im = row.querySelector('input[name="importe_estimado"]');
                const fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

                categoriaSpan.textContent = sel ? (sel.value ? sel.value.charAt(0).toUpperCase() + sel.value.slice(1) : '') : categoriaSpan.textContent;
                conceptoSpan.textContent = ci ? ci.value || 'Sin concepto' : conceptoSpan.textContent;
                if (im && im.value) {
                    // not displayed in table; keep dataset updated
                    row.dataset.importe = im.value;
                }
                fechaSpan.textContent = fi ? fi.value : fechaSpan.textContent;
            }

            // remove any importe input inside acciones
            const imEl = row.querySelector('input[name="importe_estimado"]');
            if (imEl && imEl.parentNode) imEl.parentNode.removeChild(imEl);

            editBtn.style.display = '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }

        editBtn?.addEventListener('click', function () {
            enterEditMode();
        });

        cancelBtn?.addEventListener('click', function () {
            exitEditMode(true);
        });

        saveBtn?.addEventListener('click', function () {
            const sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
            const ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
            const im = row.querySelector('input[name="importe_estimado"]');
            const fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

            const payload = new FormData();
            payload.append('_token', csrf);
            payload.append('categoria_gasto', sel ? sel.value : '');
            payload.append('concepto_gasto', ci ? ci.value : '');
            payload.append('importe_estimado', im ? im.value : (row.dataset.importe || '0'));
            payload.append('fecha_inicio_gasto', fi ? fi.value : (row.dataset.mes || ''));

            const url = `/gestor/propiedades/${propiedadId}/gastos/${gastoId}/editar`;
            fetch(url, {
                method: 'POST',
                body: payload,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(async (res) => {
                if (res.ok) {
                    if (window.swalSuccess) swalSuccess('Guardado', 'Cambios guardados correctamente').then(() => location.reload()); else location.reload();
                } else if (res.status === 422) {
                    const data = await res.json();
                    if (window.swalError) swalError('Error de validación', (data?.message) || 'Error de validación.'); else alert((data?.message) || 'Error de validación.');
                } else {
                    if (window.swalError) swalError('Error', 'Error al guardar.'); else alert('Error al guardar.');
                }
            }).catch(() => { if (window.swalError) swalError('Error de red', 'No se ha podido conectar'); else alert('Error de red.'); });
        });
    });
});