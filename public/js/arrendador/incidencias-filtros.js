(function () {
    var formId = 'incidenciasFiltrosForm';
    var filtrosWrapId = 'incidenciasFiltrosWrap';
    var tablaWrapId = 'incidenciasTablaWrap';
    var debounceTimer = null;

    function getForm() {
        return document.getElementById(formId);
    }

    function buildUrlFromForm(form) {
        var params = new URLSearchParams(new FormData(form));

        Array.from(params.keys()).forEach(function (key) {
            var value = params.get(key);
            if (value === null || String(value).trim() === '') {
                params.delete(key);
            }
        });

        var query = params.toString();
        return query ? form.action + '?' + query : form.action;
    }

    function replaceBlocksFromHtml(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var currentFiltros = document.getElementById(filtrosWrapId);
        var nextFiltros = doc.getElementById(filtrosWrapId);
        if (currentFiltros && nextFiltros) {
            currentFiltros.replaceWith(nextFiltros);
        }

        var currentTabla = document.getElementById(tablaWrapId);
        var nextTabla = doc.getElementById(tablaWrapId);
        if (currentTabla && nextTabla) {
            currentTabla.replaceWith(nextTabla);
        }
    }

    function fetchAndRender(url) {
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.text();
            })
            .then(function (html) {
                replaceBlocksFromHtml(html);
                bindEvents();
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    function bindEvents() {
        var form = getForm();
        if (!form) {
            return;
        }

        form.onsubmit = function (evento) {
            evento.preventDefault();
            fetchAndRender(buildUrlFromForm(form));
        };

        var inputsTexto = form.querySelectorAll('input[type="text"]');
        inputsTexto.forEach(function (input) {
            input.oninput = function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    fetchAndRender(buildUrlFromForm(form));
                }, 350);
            };
        });

        var inputFecha = form.querySelector('input[type="date"]');
        if (inputFecha) {
            inputFecha.onchange = function () {
                fetchAndRender(buildUrlFromForm(form));
            };
        }

        var selects = form.querySelectorAll('select');
        selects.forEach(function (select) {
            select.onchange = function () {
                fetchAndRender(buildUrlFromForm(form));
            };
        });
    }

    bindEvents();
})();