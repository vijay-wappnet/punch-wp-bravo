@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Dot grid: same hover effect as the fullscreen menu's .menu-right (resources/js/dot-grid.js, initialised on every .js-dot-grid).
     Where the dots are strongest is set from the modifier classes in the block's CSS (--dots-left/center/right, --dots-mobile-top/bottom). --}}
<section id="{{ $blockId }}"
  class="cta-banner-section js-dot-grid cta-banner-section--button-{{ $button_alignment }} cta-banner-section--mobile-button-{{ $button_alignment_mobile }} cta-banner-section--align-{{ $content_alignment }} cta-banner-section--mobile-align-{{ $content_alignment_mobile }} cta-banner-section--dots-{{ $dot_alignment }} cta-banner-section--dots-mobile-{{ $dot_alignment_mobile }}"
  data-dot-style="varied"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>
  <canvas class="dot-grid-canvas" aria-hidden="true"></canvas>

  <div class="cta-banner-section__inner container">
    @if($heading_text || $description)
      <div class="cta-banner-section__content">
        @if($heading_text)
          <{{ $heading_level }} class="cta-banner-section__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
        @endif

        @if($description)
          <div class="cta-banner-section__description">
            {!! wp_kses_post($description) !!}
          </div>
        @endif
      </div>
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
