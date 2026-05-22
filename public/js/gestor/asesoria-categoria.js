(function () {
    var lastTarget = null;

    function openFromHash() {
        var hash = location.hash;
        if (!hash || hash.indexOf('#art-') !== 0) return;

        var id = hash.replace('#art-', '');
        if (!id) return;

        var target = '#art' + id;
        if (target === lastTarget) return;

        var button = document.querySelector('[data-bs-target="' + target + '"]');
        if (!button) return;

        if (lastTarget) {
            var prev = bootstrap.Collapse.getInstance(lastTarget);
            if (prev) prev.hide();
        }

        var collapse = new bootstrap.Collapse(target, { toggle: false });
        collapse.show();
        lastTarget = target;

        setTimeout(function () {
            button.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }

    openFromHash();
    window.addEventListener('hashchange', openFromHash);
})();
