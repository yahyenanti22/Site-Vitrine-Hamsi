/**
 * HAMSI — interactions du site public.
 * Aucune donnée n'est chargée ici : tout le contenu est généré par PHP.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    /* ---------- Menu mobile ---------- */
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');

    function setMenu(open) {
        if (!toggle || !nav) return;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        nav.classList.toggle('is-open', open);
        document.body.classList.toggle('nav-open', open);
    }

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            setMenu(toggle.getAttribute('aria-expanded') !== 'true');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) setMenu(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                setMenu(false);
                toggle.focus();
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 960) setMenu(false);
        });
    }

    /* ---------- Onglets de la fiche produit ---------- */
    document.querySelectorAll('[data-tabs]').forEach(function (container) {
        var tabs = Array.prototype.slice.call(container.querySelectorAll('[role="tab"]'));
        var panels = tabs.map(function (tab) { return document.getElementById(tab.getAttribute('aria-controls')); });

        function activate(index, focus) {
            tabs.forEach(function (tab, i) {
                var active = i === index;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.setAttribute('tabindex', active ? '0' : '-1');
                if (panels[i]) panels[i].hidden = !active;
            });
            if (focus) tabs[index].focus();
        }

        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () { activate(i, false); });
            tab.addEventListener('keydown', function (e) {
                var next = null;
                if (e.key === 'ArrowRight') next = (i + 1) % tabs.length;
                if (e.key === 'ArrowLeft') next = (i - 1 + tabs.length) % tabs.length;
                if (e.key === 'Home') next = 0;
                if (e.key === 'End') next = tabs.length - 1;
                if (next !== null) { e.preventDefault(); activate(next, true); }
            });
        });

        activate(0, false);
    });
})();
