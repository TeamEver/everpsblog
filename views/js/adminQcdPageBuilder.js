/**
 * Bridge Ever Blog rich text fields to QCD Page Builder.
 */
(function () {
    'use strict';

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function findField(root, fieldName) {
        var fields = root.querySelectorAll('textarea, input, select');
        for (var i = 0; i < fields.length; i += 1) {
            var field = fields[i];
            var name = String(field.getAttribute('name') || '');
            var id = String(field.id || '');

            if (
                name === fieldName ||
                name.indexOf('[' + fieldName + ']') !== -1 ||
                id === fieldName ||
                id.slice(-fieldName.length - 1) === '_' + fieldName
            ) {
                return field;
            }
        }

        return null;
    }

    function getAnchor(field) {
        if (!field) {
            return null;
        }

        var editor = field.nextElementSibling;
        if (editor && (editor.classList.contains('tox-tinymce') || editor.classList.contains('mce-tinymce'))) {
            return editor;
        }

        return field;
    }

    function createButton(model) {
        var wrapper = document.createElement('div');
        wrapper.className = 'ever-qcdpb-actions qcdpb-bo-actions';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary btn-sm';
        button.setAttribute('data-ever-qcdpb-edit-builder', '1');
        button.setAttribute('data-ever-qcdpb-builder-url', String(model.builder_url || ''));
        button.setAttribute('data-ever-qcdpb-modal-title', String(model.label || 'Page Builder'));
        button.textContent = String(model.label || 'Editer avec Page Builder');

        wrapper.appendChild(button);

        return wrapper;
    }

    function injectTargetsForForm(formId, targets) {
        var root = document.getElementById(formId);
        if (!root || !targets || typeof targets !== 'object') {
            return;
        }

        Object.keys(targets).forEach(function (fieldName) {
            var field = findField(root, fieldName);
            if (!field || field.dataset.everQcdpbInjected === '1') {
                return;
            }

            var model = targets[fieldName] || {};
            if (!model.builder_url) {
                return;
            }

            field.dataset.everQcdpbInjected = '1';
            var anchor = getAnchor(field);
            if (anchor) {
                anchor.insertAdjacentElement('afterend', createButton(model));
            }
        });
    }

    function closeModal(overlay) {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }

    function openModal(button) {
        var url = String(button.getAttribute('data-ever-qcdpb-builder-url') || '').trim();
        var title = String(button.getAttribute('data-ever-qcdpb-modal-title') || 'QCD Page Builder').trim();

        if (!url) {
            window.alert('URL Page Builder indisponible pour ce champ.');
            return;
        }

        var overlay = document.createElement('div');
        overlay.className = 'qcdpb-bo-modal ever-qcdpb-modal';
        overlay.innerHTML = '<div class="qcdpb-bo-modal__dialog ever-qcdpb-modal__dialog">'
            + '<div class="qcdpb-bo-modal__header ever-qcdpb-modal__header">'
            + '<strong>' + escapeHtml(title) + '</strong>'
            + '<button type="button" class="btn btn-sm btn-light ever-qcdpb-close">Fermer</button>'
            + '</div>'
            + '<iframe class="qcdpb-bo-modal__frame ever-qcdpb-modal__frame" src="' + escapeHtml(url) + '"></iframe>'
            + '</div>';

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay || event.target.classList.contains('ever-qcdpb-close')) {
                closeModal(overlay);
            }
        });

        document.body.appendChild(overlay);
    }

    function bindButtons(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var buttons = scope.querySelectorAll('[data-ever-qcdpb-edit-builder]');

        Array.prototype.forEach.call(buttons, function (button) {
            if (button.dataset.everQcdpbBound === '1') {
                return;
            }

            button.dataset.everQcdpbBound = '1';
            button.addEventListener('click', function () {
                openModal(button);
            });
        });
    }

    function injectTargets() {
        var configs = window.everQcdPageBuilderTargets || {};
        Object.keys(configs).forEach(function (formId) {
            injectTargetsForForm(formId, configs[formId]);
        });
    }

    function injectStyle() {
        if (document.getElementById('ever-qcdpb-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'ever-qcdpb-style';
        style.textContent = ''
            + '.ever-qcdpb-actions{margin:.45rem 0 .9rem;}'
            + '.ever-qcdpb-modal{position:fixed;inset:0;z-index:1050;background:rgba(15,23,42,.62);display:flex;align-items:center;justify-content:center;padding:2rem;}'
            + '.ever-qcdpb-modal__dialog{width:min(1180px,96vw);height:min(820px,92vh);background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 30px 80px rgba(15,23,42,.35);display:flex;flex-direction:column;}'
            + '.ever-qcdpb-modal__header{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1rem;border-bottom:1px solid #e5e7eb;background:#111827;color:#fff;}'
            + '.ever-qcdpb-modal__frame{width:100%;height:100%;border:0;flex:1;}';
        document.head.appendChild(style);
    }

    function init() {
        injectStyle();
        injectTargets();
        bindButtons(document);

        window.setTimeout(function () {
            injectTargets();
            bindButtons(document);
        }, 400);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
