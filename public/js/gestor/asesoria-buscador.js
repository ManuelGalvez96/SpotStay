(function () {
    var searchInputs = document.querySelectorAll('[data-search-endpoint]');

    searchInputs.forEach(function (input) {
        var endpoint = input.getAttribute('data-search-endpoint');
        var container = input.closest('.asesoria-buscador');
        var dropdown = container.querySelector('.asesoria-sugerencias');
        var lastQuery = '';
        var currentRequest = null;

        input.addEventListener('input', function () {
            var query = input.value.trim();

            if (query.length < 1) {
                dropdown.innerHTML = '';
                dropdown.classList.remove('show');
                lastQuery = '';
                return;
            }

            if (query === lastQuery) return;
            lastQuery = query;

            if (currentRequest) {
                currentRequest.abort();
            }

            currentRequest = new AbortController();
            var signal = currentRequest.signal;

            fetch(endpoint + '?q=' + encodeURIComponent(query), { signal: signal })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    currentRequest = null;
                    renderResults(data, dropdown);
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                    console.error('Error al buscar:', err);
                });
        });
    });

    function renderResults(data, dropdown) {
        if (!data || data.length === 0) {
            dropdown.innerHTML = '<div class="asesoria-sugerencia-vacio">Sin resultados</div>';
            dropdown.classList.add('show');
            return;
        }

        var html = '';
        data.forEach(function (item) {
            var badge = item.categoria_nombre
                ? '<span class="asesoria-sugerencia-badge">' + escapeHtml(item.categoria_nombre) + '</span>'
                : '';
            html += '<a href="' + item.url + '" class="asesoria-sugerencia-item">'
                + badge
                + '<span class="asesoria-sugerencia-titulo">' + escapeHtml(item.titulo) + '</span>'
                + '</a>';
        });
        dropdown.innerHTML = html;
        dropdown.classList.add('show');
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    document.addEventListener('click', function (e) {
        var dropdowns = document.querySelectorAll('.asesoria-sugerencias.show');
        dropdowns.forEach(function (dd) {
            if (!dd.contains(e.target) && !dd.previousElementSibling.contains(e.target)) {
                dd.classList.remove('show');
            }
        });
    });
})();
