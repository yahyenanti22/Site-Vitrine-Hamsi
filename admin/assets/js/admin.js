/**
 * HAMSI — interactions de l'espace d'administration.
 * Uniquement des interactions d'interface : toutes les données sont
 * traitées côté serveur par PHP.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    /* ---------- Menu latéral (mobile) ---------- */
    var sidebar = document.getElementById('sidebar');
    var toggle = document.querySelector('.topbar__toggle');
    var backdrop = document.querySelector('.sidebar-backdrop');

    function setSidebar(open) {
        if (!sidebar) return;
        sidebar.classList.toggle('is-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        }
        if (backdrop) backdrop.hidden = !open;
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('is-open'));
        });
    }
    if (backdrop) backdrop.addEventListener('click', function () { setSidebar(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
            setSidebar(false);
            if (toggle) toggle.focus();
        }
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth > 880) setSidebar(false);
    });

    /* ---------- Confirmation avant les actions destructrices ---------- */
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-confirm]');
        if (form && !window.confirm(form.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-confirm]');
        if (link && !window.confirm(link.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });

    /* ---------- Afficher / masquer un mot de passe ---------- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-toggle-password'));
            if (!input) return;
            var shown = input.type === 'text';
            input.type = shown ? 'password' : 'text';
            button.setAttribute('aria-label', shown ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        });
    });

    /* ---------- Aperçu d'une image avant envoi ---------- */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var preview = document.getElementById(input.getAttribute('data-preview'));
            var label = input.closest('.image-picker, .field');
            var hint = label ? label.querySelector('[data-file-name]') : null;
            var file = input.files && input.files[0];
            if (!file) return;

            if (hint) hint.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' Ko)';
            if (preview && file.type.indexOf('image/') === 0) {
                var reader = new FileReader();
                reader.onload = function (ev) { preview.src = ev.target.result; };
                reader.readAsDataURL(file);
            }
        });
    });

    /* ---------- Champs couleur : sélecteur et code hexadécimal synchronisés ---------- */
    document.querySelectorAll('.color-field').forEach(function (field) {
        var picker = field.querySelector('input[type="color"]');
        var text = field.querySelector('input[type="text"]');
        if (!picker || !text) return;

        picker.addEventListener('input', function () {
            text.value = picker.value.toUpperCase();
            updatePreview();
        });
        text.addEventListener('input', function () {
            var value = text.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(value)) {
                picker.value = value;
                updatePreview();
            }
        });
    });

    /* Aperçu en direct des couleurs sur la page Paramètres */
    function updatePreview() {
        var preview = document.querySelector('.theme-preview');
        if (!preview) return;
        preview.querySelectorAll('[data-swatch-for]').forEach(function (swatch) {
            var source = document.getElementById(swatch.getAttribute('data-swatch-for'));
            if (source) swatch.style.background = source.value;
        });
        var bg = document.getElementById('color_background');
        if (bg) preview.style.background = bg.value;
    }
    updatePreview();
})();
