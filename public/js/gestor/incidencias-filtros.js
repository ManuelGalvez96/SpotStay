(function () {
    var formId = 'incidenciasFiltrosForm';
    var filtersWrapId = 'incidenciasFiltrosWrap';
    var tableWrapId = 'incidenciasTablaWrap';
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

        var currentFilters = document.getElementById(filtersWrapId);
        var nextFilters = doc.getElementById(filtersWrapId);
        if (currentFilters && nextFilters) {
            currentFilters.replaceWith(nextFilters);
        }

        var currentTable = document.getElementById(tableWrapId);
        var nextTable = doc.getElementById(tableWrapId);
        if (currentTable && nextTable) {
            currentTable.replaceWith(nextTable);
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

        form.onsubmit = function (e) {
            e.preventDefault();
            fetchAndRender(buildUrlFromForm(form));
        };

        var textInputs = form.querySelectorAll('input[type="text"]');
        textInputs.forEach(function (input) {
            input.oninput = function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    fetchAndRender(buildUrlFromForm(form));
                }, 350);
            };
        });

        var dateInput = form.querySelector('input[type="date"]');
        if (dateInput) {
            dateInput.onchange = function () {
                fetchAndRender(buildUrlFromForm(form));
            };
        }

        var selects = form.querySelectorAll('select');
        selects.forEach(function (select) {
            select.onchange = function () {
                fetchAndRender(buildUrlFromForm(form));
            };
        });

        var tableWrap = document.getElementById(tableWrapId);
        if (!tableWrap) {
            return;
        }

        var paginationLinks = tableWrap.querySelectorAll('.paginacion-admin a');
        paginationLinks.forEach(function (link) {
            link.onclick = function (e) {
                e.preventDefault();
                var href = link.getAttribute('href');
                if (href) {
                    fetchAndRender(href);
                }
            };
        });
    }

    bindEvents();
})();
