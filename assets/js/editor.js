(function () {
  'use strict';

  /**
   * Escape HTML before saving it into the code block markup.
   *
   * @param {string} text Raw code.
   * @returns {string} Escaped code.
   */
  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  /**
   * Gutenberg block
   */
  if (
    window.wp &&
    window.wp.blocks &&
    window.wp.element &&
    window.wp.i18n &&
    window.wp.components &&
    window.wp.blockEditor
  ) {
    const blocks = window.wp.blocks;
    const element = window.wp.element;
    const i18n = window.wp.i18n;
    const blockEditor = window.wp.blockEditor;
    const components = window.wp.components;

    const el = element.createElement;
    const __ = i18n.__;
    const PlainText = blockEditor.PlainText;
    const Button = components.Button;
    const Modal = components.Modal;
    const TextareaControl = components.TextareaControl;
    const useState = element.useState;
    const useEffect = element.useEffect;
    const Fragment = element.Fragment;

    function EditCodeBlock(props) {
      const content = props.attributes.content || '';
      const setAttributes = props.setAttributes;
      const state = useState(false);
      const isDialogOpen = state[0];
      const setDialogOpen = state[1];

      function updateCode(value) {
        setAttributes({ content: value });
      }

      useEffect(function () {
        if (!isDialogOpen) {
          return;
        }

        window.setTimeout(function () {
          const textarea = document.querySelector('.ztfr-code__modal-textarea textarea');

          if (textarea) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
          }
        }, 50);
      }, [isDialogOpen]);

      return el(
        Fragment,
        null,
        el(
          'div',
          {
            className: 'ztfr-code is-editor-preview',
            'data-language': 'yaml'
          },
          el(
            'div',
            { className: 'ztfr-code__editor-actions' },
            el(
              Button,
              {
                variant: 'secondary',
                onClick: function () {
                  setDialogOpen(true);
                }
              },
              __('Edit code', 'zeitfresser')
            )
          ),
          el(
            'pre',
            { className: 'language-yaml' },
            el(PlainText, {
              tagName: 'code',
              className: 'language-yaml',
              value: content,
              placeholder: __('Write or paste YAML code here…', 'zeitfresser'),
              onChange: updateCode
            })
          )
        ),
        isDialogOpen &&
          el(
            Modal,
            {
              title: __('Edit code block', 'zeitfresser'),
              onRequestClose: function () {
                setDialogOpen(false);
              },
              className: 'ztfr-code__modal'
            },
            el(TextareaControl, {
              label: __('Code', 'zeitfresser'),
              value: content,
              onChange: updateCode,
              help: __('Paste your YAML code here. Indentation and line breaks are preserved.', 'zeitfresser'),
              rows: 18,
              className: 'ztfr-code__modal-textarea'
            }),
            el(
              'div',
              { className: 'ztfr-code__modal-actions' },
              el(
                Button,
                {
                  variant: 'primary',
                  onClick: function () {
                    setDialogOpen(false);
                  }
                },
                __('Done', 'zeitfresser')
              )
            )
          )
      );
    }

    blocks.registerBlockType('ztfr/code-block', {
      title: __('Code', 'zeitfresser'),
      icon: 'editor-code',
      category: 'formatting',
      description: __('Insert a styled YAML code block.', 'zeitfresser'),
      supports: {
        html: false
      },
      attributes: {
        content: {
          type: 'string',
          default: ''
        }
      },

      edit: EditCodeBlock,

      save: function (props) {
        const content = props.attributes.content || '';

        return el(
          'pre',
          { className: 'language-yaml' },
          el(
            'code',
            { className: 'language-yaml' },
            content
          )
        );
      }
    });
  }

  /**
   * Classic Editor TinyMCE button
   */
  if (window.tinymce && window.tinymce.PluginManager) {
    window.tinymce.PluginManager.add('ztfr_code_block', function (editor) {
      function insertCodeBlockFromDialog() {
        editor.windowManager.open({
          title: 'Insert code block',
          body: [
            {
              type: 'textbox',
              name: 'code',
              label: 'Code',
              multiline: true,
              minWidth: 700,
              minHeight: 350,
              value: ''
            }
          ],
          onsubmit: function (event) {
            const code = event.data.code || 'your_key: your_value';
            const safeCode = escapeHtml(code);

            editor.insertContent(
              '<pre class="language-yaml"><code class="language-yaml">' +
              safeCode +
              '</code></pre><p></p>'
            );

            editor.focus();

            return true;
          }
        });
      }

      editor.addButton('ztfr_code_block', {
        text: 'Code',
        icon: false,
        onclick: insertCodeBlockFromDialog
      });
    });
  }
})();
