(function () {
    var formId = 'propiedadesFiltrosForm';
    var cardId = 'propiedadesTablaCard';
    var debounceTimer = null;

    function getForm() {
        return document.getElementById(formId);
    }

    function getCard() {
        return document.getElementById(cardId);
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

    function replaceResultsFromHtml(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var currentCard = getCard();
        var nextCard = doc.getElementById(cardId);

        if (currentCard && nextCard) {
            currentCard.replaceWith(nextCard);
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
                replaceResultsFromHtml(html);
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

        var kpiCards = document.querySelectorAll('.kpi-clickable');
        kpiCards.forEach(function (card) {
            card.onclick = function () {
                var filterKey = card.getAttribute('data-filter-key');
                var filterValue = card.getAttribute('data-filter-value') || '';

                var estadoSelect = form.querySelector('select[name="estado"]');
                var operativoSelect = form.querySelector('select[name="operativo"]');

                if (estadoSelect && filterKey !== 'estado') {
                    estadoSelect.value = '';
                }

                if (operativoSelect && filterKey !== 'operativo') {
                    operativoSelect.value = '';
                }

                var target = form.querySelector('[name="' + filterKey + '"]');
                if (target) {
                    target.value = filterValue;
                    fetchAndRender(buildUrlFromForm(form));
                }
            };
        });

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

        var selects = form.querySelectorAll('select');
        selects.forEach(function (select) {
            select.onchange = function () {
                fetchAndRender(buildUrlFromForm(form));
            };
        });

        var card = getCard();
        if (!card) {
            return;
        }

        var linkSelector = '.paginacion-admin a, .th-sort';
        var links = card.querySelectorAll(linkSelector);
        links.forEach(function (link) {
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
