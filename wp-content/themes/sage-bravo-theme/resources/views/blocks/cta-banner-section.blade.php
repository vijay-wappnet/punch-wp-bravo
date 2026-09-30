@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Dot grid: same hover effect as the fullscreen menu's .menu-right (resources/js/dot-grid.js, initialised on every .js-dot-grid) --}}
<section id="{{ $blockId }}"
  class="cta-banner-section js-dot-grid"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>
  <canvas class="dot-grid-canvas" aria-hidden="true"></canvas>

  <div class="cta-banner-section__inner container">
    @if($heading_text)
      <{{ $heading_level }} class="cta-banner-section__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
    @endif

    @if(count($buttons) > 0)
      <div class="cta-banner-section__buttons">
        @foreach($buttons as $button)
          <a href="{!! esc_url($button['url']) !!}"
            class="{!! esc_attr($button['class']) !!}"
            @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
            @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
            @if($button['rel']) rel="{!! esc_attr($button['rel']) !!}" @endif
            @if($button['event_label']) data-event="{!! esc_attr($button['event_label']) !!}" @endif>
            <span>{{ $button['title'] }}</span>
            <span class="cta-banner-section__btn-icon" aria-hidden="true"></span>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</section>
