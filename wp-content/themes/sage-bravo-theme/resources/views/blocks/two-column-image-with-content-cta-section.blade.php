@php
use Illuminate\Support\Facades\Vite;

$hasContent = $heading_text || $content || count($buttons) > 0;

// Column order: on mobile content_alignment_in_mobile decides - top = the content on top (image below),
// bottom = the content at the bottom (image on top); from md up image_alignment_left_side does
$mobileImageOrder = $mobile_position === 'top' ? 2 : 1;
$mobileContentOrder = 3 - $mobileImageOrder;
$desktopImageOrder = $image_left ? 1 : 2;
$desktopContentOrder = 3 - $desktopImageOrder;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="two-column-image-with-content-cta-section two-column-image-with-content-cta-section--image-{{ $image_left ? 'left' : 'right' }} two-column-image-with-content-cta-section--align-{{ $content_alignment }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="row align-items-center gx-0 tciwccs__row">

      {{-- Image column. The order classes move it (desktop and mobile); the markup is only written once. --}}
      @if($image)
        <div class="col-12 col-md-5 order-{{ $mobileImageOrder }} order-md-{{ $desktopImageOrder }} tciwccs__column tciwccs__column--image">
          <div class="tciwccs__media">
            <img src="{!! esc_url($image['url']) !!}"
              alt="{!! esc_attr($image['alt']) !!}"
              @if($image['width']) width="{!! esc_attr($image['width']) !!}" @endif
              @if($image['height']) height="{!! esc_attr($image['height']) !!}" @endif
              loading="lazy"
              decoding="async"
              class="tciwccs__image">
          </div>
        </div>
      @endif

      {{-- Content column --}}
      @if($hasContent)
        <div class="col-12 @if($image) col-md-7 @endif order-{{ $mobileContentOrder }} order-md-{{ $desktopContentOrder }} tciwccs__column tciwccs__column--content">
          <div class="tciwccs__content">

            @if($heading_text)
              <{{ $heading_level }} class="tciwccs__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
            @endif

            @if($content)
              <div class="tciwccs__description">
                {!! wp_kses_post($content) !!}
              </div>
            @endif

            @if(count($buttons) > 0)
              <div class="tciwccs__buttons">
                @foreach($buttons as $button)
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
                      <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="tciwccs-btn__icon" aria-hidden="true">
                    @endif
                  </a>
                @endforeach
              </div>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
</section>
