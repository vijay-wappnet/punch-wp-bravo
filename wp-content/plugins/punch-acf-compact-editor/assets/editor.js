(function () {
  'use strict';

  const preparedDocuments = new WeakSet();
  const preparedFrames = new WeakSet();

  function selectInlineBlock(event) {
    if (event.type === 'pointerdown' && event.button !== 0) return;
    const target = event.target?.nodeType === 3 ? event.target.parentElement : event.target;
    if (typeof target?.closest !== 'function') return;
    const field = target.closest('[data-acf-inline-fields], [data-acf-inline-contenteditable]');
    if (!field?.closest('.acf-editor-preview')) return;
    const block = field.closest('.block-editor-block-list__block[data-block]');
    const clientId = block?.getAttribute('data-block');
    const data = window.wp?.data;
    if (!clientId || !data?.select || !data?.dispatch) return;
    const store = data.select('core/block-editor');
    if (!store?.getBlockName?.(clientId)?.startsWith('acf/')) return;
    if (store.getSelectedBlockClientId?.() === clientId) return;

    // ACF stops bubbling mouse/focus events on inline targets. Select first,
    // so its isSelected-dependent form can mount before the toolbar opens.
    // null preserves the field's focus/caret; do not cancel or replay events.
    data.dispatch('core/block-editor')?.selectBlock?.(clientId, null);
  }

  function prepareDocument(root) {
    if (!root || preparedDocuments.has(root)) return;
    preparedDocuments.add(root);
    root.addEventListener('pointerdown', selectInlineBlock, true);
    root.addEventListener('focusin', selectInlineBlock, true);
    // Covers keyboard/assistive-technology clicks without a pointer event.
    root.addEventListener('click', selectInlineBlock, true);
    root.addEventListener('click', function (event) {
      if (typeof event.target?.closest !== 'function') return;
      if (event.target.closest('[data-acf-inline-fields], [data-acf-inline-contenteditable]')) return;
      if (!event.target.closest('.acf-editor-preview a[href], .acf-editor-preview form')) return;
      event.preventDefault();
      event.stopPropagation();
    }, true);
    root.addEventListener('submit', function (event) {
      if (!event.target.closest('.acf-editor-preview form')) return;
      event.preventDefault();
      event.stopPropagation();
    }, true);
  }

  function prepareCanvas() {
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    if (!iframe) return;
    try { prepareDocument(iframe.contentDocument); } catch (error) {}
    if (!preparedFrames.has(iframe)) {
      preparedFrames.add(iframe);
      iframe.addEventListener('load', prepareCanvas);
    }
  }

  function start() {
    prepareDocument(document);
    if (document.body) {
      new MutationObserver(prepareCanvas).observe(document.body, { childList: true, subtree: true });
    }
    prepareCanvas();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
