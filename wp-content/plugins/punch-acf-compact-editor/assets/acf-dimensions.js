/* Editor compatibility for the legacy ACF Dimensions markup/value schema. */
((acf, $) => {
  if (!acf || !$) return;

  const devices = {
    desktop: 'Desktop',
    tablet_landscape: 'Tablet landscape',
    tablet_portrait: 'Tablet portrait',
    mobile: 'Mobile',
  };
  const notify = (input, type = 'change') => {
    input.dispatchEvent(new input.ownerDocument.defaultView.Event(type, { bubbles: true }));
  };

  const initialize = (field) => {
    const $field = field.$el || field;
    $field.find('.acf-dimensions').each(function () {
      const $root = $(this);
      const $buttons = $root.find('.acf-dimensions__buttons a');

      $buttons.each(function () {
        const device = this.getAttribute('rel')?.replace('acf-dimensions__device--', '');
        $(this).attr({
          role: 'button',
          'aria-label': devices[device] || device,
          title: devices[device] || device,
          'aria-pressed': String(this.classList.contains('btn--active')),
        });
      });

      $root.find('.acf-dimensions__device').each(function () {
        const $device = $(this);
        const linked = $device.find('.input-linked').val() === '1';
        const device = Object.keys(devices).find(key => this.classList.contains(`acf-dimensions__device--${key}`));
        $device.find('.input-top').attr('aria-label', `${devices[device]} top`);
        $device.find('.input-bottom').attr('aria-label', `${devices[device]} bottom`).prop('readonly', linked);
        $device.find('select').attr('aria-label', `${devices[device]} unit`);
        $device.find('.btn--linker').attr({
          type: 'button',
          'aria-label': 'Link top and bottom',
          'aria-pressed': String(linked),
          title: 'Link top and bottom',
        }).toggleClass('btn--active', linked);
      });

      // Forms are moved/recreated as popovers open. Rebinding is idempotent.
      $root.off('.punchCompactEditor');
      $root.on('click.punchCompactEditor', '.acf-dimensions__buttons a', function (event) {
        event.preventDefault();
        const target = this.getAttribute('rel');
        const device = target?.replace('acf-dimensions__device--', '');
        if (!Object.hasOwn(devices, device)) return;
        $root.find('.acf-dimensions__device').removeClass('acf-dimensions__device--active');
        $root.find(`.${target}`).addClass('acf-dimensions__device--active');
        $buttons.removeClass('btn--active').attr('aria-pressed', 'false');
        $(this).addClass('btn--active').attr('aria-pressed', 'true');
      });
      $root.on('keydown.punchCompactEditor', '.acf-dimensions__buttons a', function (event) {
        if (event.key === ' ') {
          event.preventDefault();
          this.click();
        }
      });
      $root.on('input.punchCompactEditor change.punchCompactEditor', '.input-top', function () {
        const $device = $(this).closest('.acf-dimensions__device');
        if ($device.find('.input-linked').val() !== '1') return;
        const bottom = $device.find('.input-bottom')[0];
        if (bottom.value === this.value) return;
        bottom.value = this.value;
        notify(bottom, 'input');
      });
      $root.on('click.punchCompactEditor', '.btn--linker', function (event) {
        event.preventDefault();
        const $device = $(this).closest('.acf-dimensions__device');
        const linkedInput = $device.find('.input-linked')[0];
        const linked = linkedInput.value !== '1';
        linkedInput.value = linked ? '1' : '0';
        $(this).toggleClass('btn--active', linked).attr('aria-pressed', String(linked));
        const bottom = $device.find('.input-bottom')[0];
        bottom.readOnly = linked;
        if (linked) {
          bottom.value = $device.find('.input-top').val();
          notify(bottom, 'input');
        }
        notify(linkedInput);
      });
    });
  };

  ['ready', 'append', 'remount'].forEach(event => {
    acf.addAction(`${event}_field/type=dimensions`, initialize);
  });
})(window.acf, window.jQuery);
