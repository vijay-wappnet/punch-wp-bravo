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
{{-- Overlay animation: resources/js/blocks/insight-analytics-feature-section.js (the cards are simply shown in place without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="insight-analytics-feature-section insight-analytics-feature-section--image-{{ $image_left ? 'left' : 'right' }} insight-analytics-feature-section--align-{{ $content_alignment }} insight-analytics-feature-section--mobile-align-{{ $content_alignment_mobile }} js-iafs"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container-fluid">
    <div class="row align-items-center gx-0 iafs__row">

      {{-- Image column. The order classes move it (desktop and mobile); the markup is only written once. --}}
      @if($image)
        <div class="col-12 order-{{ $mobileImageOrder }} order-md-{{ $desktopImageOrder }} iafs__column iafs__column--image">
          {{-- The overlay cards are positioned against this wrapper, so they stay attached to the image wherever it sits --}}
          <div class="iafs__media">
            <img src="{!! esc_url($image['url']) !!}"
              alt="{!! esc_attr($image['alt']) !!}"
              @if($image['width']) width="{!! esc_attr($image['width']) !!}" @endif
              @if($image['height']) height="{!! esc_attr($image['height']) !!}" @endif
              loading="lazy"
              decoding="async"
              class="iafs__image">

            @foreach($overlays as $overlay)
              <div class="iafs__overlay iafs__overlay--{{ $overlay['side'] }} iafs__overlay--{{ $overlay['edge'] }} js-iafs-overlay"
                @if($overlay['style']) style="{!! esc_attr($overlay['style']) !!}" @endif>
                @if($overlay['icon'])
                  <img src="{!! esc_url($overlay['icon']['url']) !!}"
                    alt="{!! esc_attr($overlay['title'] ? '' : $overlay['icon']['alt']) !!}"
                    @if($overlay['title']) aria-hidden="true" @endif
                    loading="lazy"
                    decoding="async"
                    class="iafs__overlay-icon">
                @endif
                @if($overlay['title'])
                  <span class="iafs__overlay-title">{{ $overlay['title'] }}</span>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Content column --}}
      @if($hasContent)
        <div class="col-12 order-{{ $mobileContentOrder }} order-md-{{ $desktopContentOrder }} iafs__column iafs__column--content">
          <div class="iafs__content">

            @if($heading_text)
              <{{ $heading_level }} class="iafs__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
            @endif

            @if($button)
              <div class="iafs__buttons">
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
                    <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="iafs-btn__icon" aria-hidden="true">
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
