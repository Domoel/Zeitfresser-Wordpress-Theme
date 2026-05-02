/**
 * Code Block UI Enhancements
 *
 * Handles:
 * - Copy button
 * - Prism highlight trigger
 */

(function () {
  'use strict';

  function copyText(text, onSuccess, onError) {
    if (!text) {
      return;
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(onSuccess).catch(onError);
      return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
      document.execCommand('copy');
      onSuccess();
    } catch (error) {
      onError();
    }

    document.body.removeChild(textarea);
  }

  function initCodeUI() {
    const wrappers = document.querySelectorAll('.ztfr-code');

    if (!wrappers.length) {
      return;
    }

    wrappers.forEach(function (wrapper) {
      const code = wrapper.querySelector('code');

      if (!code) {
        return;
      }

      if (!wrapper.querySelector('.ztfr-code__copy')) {
        const button = document.createElement('button');
        button.className = 'ztfr-code__copy';
        button.type = 'button';
        button.setAttribute('aria-label', 'Copy code');
        button.textContent = 'Copy';

        button.addEventListener('click', function () {
          const text = code.textContent;

          copyText(
            text,
            function () {
              button.textContent = 'Copied';
              window.setTimeout(function () {
                button.textContent = 'Copy';
              }, 2000);
            },
            function () {
              button.textContent = 'Error';
              window.setTimeout(function () {
                button.textContent = 'Copy';
              }, 2000);
            }
          );
        });

        wrapper.appendChild(button);
      }
    });

    if (typeof Prism !== 'undefined') {
      Prism.highlightAll();
    }
  }

  document.addEventListener('DOMContentLoaded', initCodeUI);
})();
