(function () {
    var formId = 'actividadFiltrosForm';
    var filtersWrapId = 'actividadFiltrosWrap';
    var timelineWrapId = 'actividadTimelineWrap';
    var timelineSelector = '.timeline';
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

    function replaceBlocksFromHtml(html, appendCards) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var currentFilters = document.getElementById(filtersWrapId);
        var nextFilters = doc.getElementById(filtersWrapId);
        if (currentFilters && nextFilters) {
            currentFilters.replaceWith(nextFilters);
        }

        var currentTimeline = document.getElementById(timelineWrapId);
        var nextTimeline = doc.getElementById(timelineWrapId);
        if (currentTimeline && nextTimeline) {
            if (appendCards) {
                var currentTimelineDiv = currentTimeline.querySelector(timelineSelector);
                var nextTimelineDiv = nextTimeline.querySelector(timelineSelector);
                if (currentTimelineDiv && nextTimelineDiv) {
                    var items = nextTimelineDiv.querySelectorAll('.timeline-link');
                    items.forEach(function (item) {
                        currentTimelineDiv.appendChild(item);
                    });
                }
                var currentPagination = currentTimeline.querySelector('.paginacion-cargar-mas');
                var nextPagination = nextTimeline.querySelector('.paginacion-cargar-mas');
                if (currentPagination && nextPagination) {
                    currentPagination.replaceWith(nextPagination);
                }
            } else {
                currentTimeline.replaceWith(nextTimeline);
            }
        }
    }

    function fetchAndRender(url, appendCards) {
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
                replaceBlocksFromHtml(html, appendCards);
                bindEvents();
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    function cargarMas() {
        var pagination = document.querySelector('.paginacion-cargar-mas');
        if (!pagination) return;
        var btn = pagination.querySelector('.btn-cargar-mas');
        if (!btn || btn.disabled) return;
        var url = btn.getAttribute('data-next-url');
        if (!url) return;

        btn.disabled = true;
        btn.textContent = 'Cargando...';

        fetchAndRender(url, true);
    }

    function bindEvents() {
        var form = getForm();
        if (!form) {
            return;
        }

        form.onsubmit = function (e) {
            e.preventDefault();
            fetchAndRender(buildUrlFromForm(form), false);
        };

        var searchInput = form.querySelector('input[type="search"]');
        if (searchInput) {
            searchInput.oninput = function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    fetchAndRender(buildUrlFromForm(form), false);
                }, 350);
            };
        }

        var dateInputs = form.querySelectorAll('input[type="date"]');
        dateInputs.forEach(function (input) {
            input.onchange = function () {
                fetchAndRender(buildUrlFromForm(form), false);
            };
        });

        var selects = form.querySelectorAll('select');
        selects.forEach(function (select) {
            select.onchange = function () {
                fetchAndRender(buildUrlFromForm(form), false);
            };
        });

        var cargarMasBtn = document.querySelector('.btn-cargar-mas');
        if (cargarMasBtn) {
            cargarMasBtn.onclick = cargarMas;
        }
    }

    bindEvents();
})();
