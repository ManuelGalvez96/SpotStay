function inicializarEdicionInline() {
  var card = document.getElementById('gastos-propiedad');
  if (!card) return;

  function toText(node) {
    return node ? node.textContent.trim() : '';
  }

  card.addEventListener('click', function (e) {
    var target = e.target;
    var row = target.closest('.cuota-row');
    if (!row) return;

    var editBtn = row.querySelector('.btn-cuota-edit');
    var saveBtn = row.querySelector('.btn-cuota-save');
    var cancelBtn = row.querySelector('.btn-cuota-cancel');
    var conceptoSpan = row.querySelector('.display-concepto');
    var categoriaSpan = row.querySelector('.display-categoria');
    var fechaSpan = row.querySelector('.display-fecha');
    var gastoId = row.dataset.gastoId;
    var propiedadId = row.dataset.propiedadId;
    var importeDefault = row.dataset.importe || '';
    var original = {};

    function enterEditMode() {
      original = {
        concepto: toText(conceptoSpan),
        categoria: toText(categoriaSpan),
        fecha: toText(fechaSpan),
        importe: importeDefault
      };

      var select = document.createElement('select');
      select.name = 'categoria_gasto';
      ['', 'luz', 'agua', 'gas', 'internet', 'comunidad', 'otros'].forEach(function (k) {
        var opt = document.createElement('option');
        opt.value = k;
        opt.text = k === '' ? 'Selecciona una categoría' : k.charAt(0).toUpperCase() + k.slice(1);
        if (k === original.categoria.toLowerCase()) opt.selected = true;
        select.appendChild(opt);
      });
      categoriaSpan.innerHTML = '';
      categoriaSpan.appendChild(select);

      var conceptoInput = document.createElement('input');
      conceptoInput.type = 'text';
      conceptoInput.name = 'concepto_gasto';
      conceptoInput.value = original.concepto === 'Sin concepto' ? '' : original.concepto;
      conceptoInput.maxLength = 200;
      conceptoSpan.innerHTML = '';
      conceptoSpan.appendChild(conceptoInput);

      var importeInput = document.createElement('input');
      importeInput.type = 'number';
      importeInput.step = '0.01';
      importeInput.min = '0.01';
      importeInput.name = 'importe_estimado';
      importeInput.value = parseFloat(original.importe) || '';
      var fechaInput = document.createElement('input');
      fechaInput.type = 'date';
      fechaInput.name = 'fecha_inicio_gasto';
      fechaInput.value = row.dataset.mes || '';
      fechaSpan.innerHTML = '';
      fechaSpan.appendChild(fechaInput);
      row.querySelector('.detalle-acciones').appendChild(importeInput);

      editBtn.style.display = 'none';
      saveBtn.style.display = '';
      cancelBtn.style.display = '';
    }

    function exitEditMode(reset) {
      if (reset) {
        conceptoSpan.textContent = original.concepto || '';
        categoriaSpan.textContent = original.categoria || '';
        fechaSpan.textContent = original.fecha || '';
      } else {
        var sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
        var ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
        var im = row.querySelector('input[name="importe_estimado"]');
        var fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

        categoriaSpan.textContent = sel ? (sel.value ? sel.value.charAt(0).toUpperCase() + sel.value.slice(1) : '') : categoriaSpan.textContent;
        conceptoSpan.textContent = ci ? ci.value || 'Sin concepto' : conceptoSpan.textContent;
        if (im && im.value) row.dataset.importe = im.value;
        fechaSpan.textContent = fi ? fi.value : fechaSpan.textContent;
      }

      var imEl = row.querySelector('input[name="importe_estimado"]');
      if (imEl && imEl.parentNode) imEl.parentNode.removeChild(imEl);

      editBtn.style.display = '';
      saveBtn.style.display = 'none';
      cancelBtn.style.display = 'none';
    }

    if (target.classList.contains('btn-cuota-edit')) {
      e.preventDefault();
      enterEditMode();
    }

    if (target.classList.contains('btn-cuota-cancel')) {
      e.preventDefault();
      exitEditMode(true);
    }

    if (target.classList.contains('btn-cuota-save')) {
      e.preventDefault();
      var sel = categoriaSpan.querySelector('select[name="categoria_gasto"]');
      var ci = conceptoSpan.querySelector('input[name="concepto_gasto"]');
      var im = row.querySelector('input[name="importe_estimado"]');
      var fi = fechaSpan.querySelector('input[name="fecha_inicio_gasto"]');

      var payload = new FormData();
      var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      payload.append('_token', csrf);
      payload.append('categoria_gasto', sel ? sel.value : '');
      payload.append('concepto_gasto', ci ? ci.value : '');
      payload.append('importe_estimado', im ? im.value : (row.dataset.importe || '0'));
      payload.append('fecha_inicio_gasto', fi ? fi.value : (row.dataset.mes || ''));

      var url = '/gestor/propiedades/' + propiedadId + '/gastos/' + gastoId + '/editar';
      fetch(url, {
        method: 'POST',
        body: payload,
        headers: { 'Accept': 'application/json' }
      }).then(async function (res) {
        if (res.ok) {
          if (window.swalSuccess) swalSuccess('Guardado', 'Cambios guardados correctamente').then(function () { window.location.reload(); }); else window.location.reload();
        } else if (res.status === 422) {
          var data = await res.json();
          if (window.swalError) swalError('Error de validación', data?.message || 'Error de validación.'); else alert(data?.message || 'Error de validación.');
        } else {
          if (window.swalError) swalError('Error', 'Error al guardar.'); else alert('Error al guardar.');
        }
      }).catch(function () {
        if (window.swalError) swalError('Error de red', 'No se ha podido conectar'); else alert('Error de red.');
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', inicializarEdicionInline);
