function gastosRenderFila(cuota, detalles, propiedadId) {
  var esAtrasado = ['pendiente', 'parcial', 'atrasado'].includes(cuota.estado_cuota) && new Date(cuota.vencimiento_cuota + 'T00:00:00') < new Date();
  var estadoVisual = esAtrasado ? 'atrasado' : cuota.estado_cuota;

  var categoriaLabel = {
    luz: 'Luz', agua: 'Agua', gas: 'Gas',
    internet: 'Internet', comunidad: 'Comunidad',
    otros: 'Otros', base_propiedad: 'Base propiedad'
  }[cuota.categoria_gasto] || cuota.categoria_gasto || 'Sin categoría';

  var vencDate = new Date(cuota.vencimiento_cuota + 'T00:00:00');
  var vencLabel = vencDate.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });

  function formatDate(d) { return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }); }
  var periodoLabel = '';
  if (cuota.fecha_inicio_gasto && cuota.fecha_fin_gasto) {
    periodoLabel = formatDate(new Date(cuota.fecha_inicio_gasto + 'T00:00:00')) + ' \u2192 ' + formatDate(new Date(cuota.fecha_fin_gasto + 'T00:00:00'));
  } else {
    periodoLabel = new Date(cuota.mes_cuota + 'T00:00:00').toLocaleDateString('es-ES', { month: '2-digit', year: 'numeric' });
  }

  var ambitoLabel = (cuota.ambito_gasto || 'propiedad') === 'contrato'
    ? 'Contrato #' + cuota.id_alquiler_fk
    : 'Propiedad';

  var htmlDetalles = '';
  (detalles || []).forEach(function (det) {
    if (String(det.id_gasto_cuota_fk) !== String(cuota.id_gasto_cuota)) return;
    var importeDet = parseFloat(det.importe_detalle).toFixed(2).replace('.', ',');
    htmlDetalles += '<div class="detalle-pago-item"><span>' + (det.nombre_usuario || 'Usuario') + ': ' + importeDet + ' EUR (' + det.estado_detalle.charAt(0).toUpperCase() + det.estado_detalle.slice(1) + ')</span></div>';
  });

  var botonEditar = '';
  if (cuota.estado_cuota !== 'pagado') {
    botonEditar = '<button type="button" class="btn-cuota-edit">Editar</button> ' +
      '<button type="button" class="btn-cuota-delete">Eliminar</button>';
  }

  return '<tr class="cuota-row" data-gasto-id="' + cuota.id_gasto_fk + '" data-propiedad-id="' + propiedadId + '" data-importe="' + cuota.importe_total_cuota + '" data-mes="' + cuota.mes_cuota + '" data-fecha-inicio="' + (cuota.fecha_inicio_gasto || '') + '" data-fecha-fin="' + (cuota.fecha_fin_gasto || '') + '">' +
    '<td class="display-mes">' + periodoLabel + '</td>' +
    '<td class="display-concepto">' + (cuota.concepto_gasto || 'Sin concepto') + '</td>' +
    '<td class="display-categoria">' + categoriaLabel + '</td>' +
    '<td class="display-ambito">' + ambitoLabel + '</td>' +
    '<td class="display-fecha">' + vencLabel + '</td>' +
    '<td><span class="badge-estado badge-gasto-' + estadoVisual + '">' + estadoVisual.charAt(0).toUpperCase() + estadoVisual.slice(1).replace('_', ' ') + '</span></td>' +
    '<td><div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;"><div class="detalle-acciones" style="min-width:160px;">' + botonEditar + '</div><div class="detalle-pagos-lista">' + htmlDetalles + '</div></div></td>' +
    '</tr>';
}

function gastosCargarFiltros() {
  var form = document.getElementById('gastosFiltrosForm');
  if (!form) return;
  var propiedadId = form.dataset.propiedadId;
  if (!propiedadId) return;

  var params = new URLSearchParams();
  document.querySelectorAll('[data-filtro-gasto]').forEach(function (el) {
    if (el.value) params.set(el.name, el.value);
  });

  var url = '/gestor/propiedades/' + propiedadId + '/gastos/filtrar?' + params.toString();

  fetch(url, {
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.success) return;
      var tbody = document.getElementById('gastosTableBody');
      if (!tbody) return;

      var cuotas = data.cuotas || [];
      var rawDetalles = data.detalles || [];
      var detallesArray = Array.isArray(rawDetalles) ? rawDetalles : Object.values(rawDetalles).flat();

      if (cuotas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="tabla-vacia">No hay gastos con esos filtros.</td></tr>';
        return;
      }

      var html = '';
      cuotas.forEach(function (c) {
        html += gastosRenderFila(c, detallesArray, propiedadId);
      });
      tbody.innerHTML = html;
    })
    .catch(function () {});
}

function gastosLimpiarFiltros() {
  document.querySelectorAll('[data-filtro-gasto]').forEach(function (el) {
    el.value = '';
  });
  gastosCargarFiltros();
}

document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('gastosFiltrosForm');
  if (!form) return;

  var timeout = null;
  form.addEventListener('input', function () {
    clearTimeout(timeout);
    timeout = setTimeout(gastosCargarFiltros, 300);
  });
  form.addEventListener('change', function () {
    clearTimeout(timeout);
    gastosCargarFiltros();
  });
});
