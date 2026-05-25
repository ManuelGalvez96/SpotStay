(function () {
    var formId = 'propiedadesFiltrosForm';
    var cardId = 'propiedadesTablaCard';
    var mobileListSelector = '.propiedades-lista-mobile';
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

    function replaceResultsFromHtml(html, appendCards) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var currentCard = getCard();
        var nextCard = doc.getElementById(cardId);

        if (currentCard && nextCard) {
            if (appendCards) {
                var currentList = currentCard.querySelector(mobileListSelector);
                var nextList = nextCard.querySelector(mobileListSelector);
                if (currentList && nextList) {
                    var cards = nextList.querySelectorAll('.propiedad-card');
                    cards.forEach(function (card) {
                        currentList.appendChild(card);
                    });
                }
                var currentPagination = currentCard.querySelector('.paginacion-cargar-mas');
                var nextPagination = nextCard.querySelector('.paginacion-cargar-mas');
                if (currentPagination && nextPagination) {
                    currentPagination.replaceWith(nextPagination);
                }
            } else {
                currentCard.replaceWith(nextCard);
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
                replaceResultsFromHtml(html, appendCards);
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

        var textInputs = form.querySelectorAll('input[type="text"]');
        textInputs.forEach(function (input) {
            input.oninput = function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    fetchAndRender(buildUrlFromForm(form), false);
                }, 350);
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

        var paginationDesktop = document.querySelector('.paginacion-desktop');
        if (paginationDesktop) {
            paginationDesktop.addEventListener('click', function (e) {
                var link = e.target.closest('a');
                if (!link) return;
                e.preventDefault();
                fetchAndRender(link.href, false);
            });
        }
    }

    bindEvents();
})();