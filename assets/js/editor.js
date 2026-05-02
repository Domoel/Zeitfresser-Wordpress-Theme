(function () {
  'use strict';

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

    const el = element.createElement;
    const __ = i18n.__;
    const PlainText = blockEditor.PlainText;

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

      edit: function (props) {
        const content = props.attributes.content || '';

        return el(
          'div',
          {
            className: 'ztfr-code is-editor-preview',
            'data-language': 'yaml'
          },
          el(
            'pre',
            { className: 'language-yaml' },
            el(PlainText, {
              tagName: 'code',
              className: 'language-yaml',
              value: content,
              placeholder: __('Write or paste YAML code here…', 'zeitfresser'),
              onChange: function (value) {
                props.setAttributes({ content: value });
              }
            })
          )
        );
      },

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
    function escapeHtml(text) {
      return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    window.tinymce.PluginManager.add('ztfr_code_block', function (editor) {
      function insertCodeBlock() {
        const selectedText = editor.selection.getContent({ format: 'text' });
        const code = selectedText || 'your_key: your_value';
        const safeCode = escapeHtml(code);

        editor.insertContent(
          '<pre class="language-yaml"><code class="language-yaml">' +
          safeCode +
          '</code></pre><p></p>'
        );
      }

      editor.addButton('ztfr_code_block', {
        text: 'Code',
        icon: false,
        onclick: insertCodeBlock
      });
    });
  }
})();
