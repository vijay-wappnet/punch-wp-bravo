@php
use Illuminate\Support\Facades\Vite;

$hasContent = $heading_text || $button;

// Column order: on mobile mobile_image_position decides, from md up image_alignment_left_side does
$mobileImageOrder = $mobile_image_position === 'top' ? 1 : 2;
$mobileContentOrder = 3 - $mobileImageOrder;
$desktopImageOrder = $image_left ? 1 : 2;
$desktopContentOrder = 3 - $desktopImageOrder;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Diagram build animation: resources/js/blocks/predictive-insights-diagram-section.js (the finished diagram is shown without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="predictive-insights-diagram-section predictive-insights-diagram-section--image-{{ $image_left ? 'left' : 'right' }} predictive-insights-diagram-section--align-{{ $content_alignment }} predictive-insights-diagram-section--mobile-align-{{ $content_alignment_mobile }} js-pid"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container-fluid">
    <div class="row align-items-center gx-0 pid__row">

      {{-- Image + diagram column. The order classes move it (desktop and mobile); the markup is only written once. --}}
      @if($image)
        <div class="col-12 order-{{ $mobileImageOrder }} order-md-{{ $desktopImageOrder }} pid__column pid__column--image">
          {{-- Everything in the diagram is positioned against this wrapper, so it stays attached to the image wherever it sits --}}
          <div class="pid__media js-pid-media">
            <img src="{!! esc_url($image['url']) !!}"
              alt="{!! esc_attr($image['alt']) !!}"
              @if($image['width']) width="{!! esc_attr($image['width']) !!}" @endif
              @if($image['height']) height="{!! esc_attr($image['height']) !!}" @endif
              loading="lazy"
              decoding="async"
              class="pid__image js-pid-image">

            {{-- Connection lines: behind the icons, above the image. Each is drawn on through a mask, so dashed lines are drawn on as dashed lines. --}}
            @if(count($connections) > 0)
              <svg class="pid__lines" viewBox="0 0 312 346" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
                <defs>
                  @foreach($connections as $connection)
                    <mask id="{{ $blockId }}-mask-{{ $connection['index'] }}" maskUnits="userSpaceOnUse" x="-40" y="0" width="400" height="346">
                      <path class="pid__line-reveal js-pid-line" data-index="{{ $connection['index'] }}" d="{{ $connection['path'] }}" pathLength="1" />
                    </mask>
                  @endforeach
                </defs>
                @foreach($connections as $connection)
                  <path class="pid__line @if($connection['dashed']) pid__line--dashed @endif"
                    d="{{ $connection['path'] }}"
                    mask="url(#{{ $blockId }}-mask-{{ $connection['index'] }})" />
                @endforeach
              </svg>
            @endif

            {{-- The uploaded icons are the complete visuals (nodes, badge, Action pill): rendered as they are, positioned by their order --}}
            @foreach($elements as $element)
              <span class="pid__item pid__item--{{ $element['slot'] }} js-pid-item" data-index="{{ $element['index'] }}">
                <img src="{!! esc_url($element['icon']['url']) !!}"
                  alt="{!! esc_attr($element['icon']['alt']) !!}"
                  @if($element['icon']['width']) width="{!! esc_attr($element['icon']['width']) !!}" @endif
                  @if($element['icon']['height']) height="{!! esc_attr($element['icon']['height']) !!}" @endif
                  loading="lazy"
                  decoding="async"
                  class="pid__item-image">
              </span>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Content column --}}
      @if($hasContent)
        <div class="col-12 order-{{ $mobileContentOrder }} order-md-{{ $desktopContentOrder }} pid__column pid__column--content">
          <div class="pid__content">

            @if($heading_text)
              <{{ $heading_level }} class="pid__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
            @endif

            @if($button)
              <div class="pid__buttons">
                <a href="{!! esc_url($button['url']) !!}"
                  class="{!! esc_attr($button['class']) !!}"
                  @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                  @if($button['rel']) rel="{!! esc_attr($button['rel']) !!}" @endif
                  @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                  @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                  <span>{{ $button['title'] }}</span>
                  @if($button['arrow_span'])
                    <span class="btn-trans-border-arrow__icon" aria-hidden="true"></span>
                  @else
                    <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="pid-btn__icon" aria-hidden="true">
                  @endif
                </a>
              </div>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
</section>
