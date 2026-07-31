(function () {
    'use strict';

    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            return Promise.resolve();
        } finally {
            document.body.removeChild(textarea);
        }
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-wt-read-in-ai-copy]');

        if (!button) {
            return;
        }

        var prompt = button.getAttribute('data-wt-read-in-ai-prompt') || '';
        var label = button.getAttribute('data-wt-read-in-ai-label') || button.textContent;
        var copiedLabel = button.getAttribute('data-wt-read-in-ai-copied-label') || label;

        copyText(prompt).then(function () {
            button.textContent = copiedLabel;
            window.setTimeout(function () {
                button.textContent = label;
            }, 1800);
        });
    });
}());
