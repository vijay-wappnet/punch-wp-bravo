@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="two-columns-image-icons-with-cta-section @if($image_alignment_left_side) two-columns-image-icons-with-cta-section--image-left @endif"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="two-columns-image-icons-with-cta-section__wrapper">

      {{-- Content column --}}
      <div class="two-columns-image-icons-with-cta-section__column two-columns-image-icons-with-cta-section__column--content">
        <div class="two-columns-image-icons-with-cta-section__content">

          @if($heading_text)
            <{{ $heading_level }} class="two-columns-image-icons-with-cta-section__heading">
              {{ $heading_text }}
            </{{ $heading_level }}>
          @endif

          @if($description)
            <div class="fs-30 two-columns-image-icons-with-cta-section__description">
              {!! wp_kses_post($description) !!}
            </div>
          @endif

          @if(count($buttons) > 0)
            <div class="two-columns-image-icons-with-cta-section__buttons">
              @foreach($buttons as $button)
                <a href="{!! esc_url($button['url']) !!}"
                  class="btn twociiwcs-btn {!! esc_attr($button['class']) !!}"
                  @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                  @if($button['target'] === '_blank') rel="noopener noreferrer" @endif
                  @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                  @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                  <span>{{ $button['title'] }}</span>
                  <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="twociiwcs-btn__icon" aria-hidden="true">
                </a>
              @endforeach
            </div>
          @endif

        </div>
      </div>

      {{-- Image / icons visualization column --}}
      @if($image || count($icons) > 0)
        <div class="two-columns-image-icons-with-cta-section__column two-columns-image-icons-with-cta-section__column--visual">
          <div class="two-columns-image-icons-with-cta-section__image_icon js-tcic-orbit">

            @if(count($icons) > 0)
              <span class="two-columns-image-icons-with-cta-section__ring two-columns-image-icons-with-cta-section__ring--inner" aria-hidden="true"></span>
            @endif
            @if(count($icons) > 2)
              <span class="two-columns-image-icons-with-cta-section__ring two-columns-image-icons-with-cta-section__ring--outer" aria-hidden="true"></span>
            @endif

            @if($image)
              <div class="two-columns-image-icons-with-cta-section__center">
                <img src="{!! esc_url($image['url']) !!}"
                  alt="{!! esc_attr($image['alt']) !!}"
                  @if($image['width']) width="{!! esc_attr($image['width']) !!}" @endif
                  @if($image['height']) height="{!! esc_attr($image['height']) !!}" @endif
                  class="two-columns-image-icons-with-cta-section__center-image">
              </div>
            @endif

            @if(count($icons) > 0)
              <ul class="two-columns-image-icons-with-cta-section__icons" role="list">
                @foreach($icons as $icon)
                  <li class="two-columns-image-icons-with-cta-section__icon-item two-columns-image-icons-with-cta-section__icon-item--{{ $icon['ring'] }}"
                    style="--angle: {{ $icon['angle'] }}deg; --i: {{ $icon['order'] }};">
                    <img src="{!! esc_url($icon['url']) !!}"
                      alt="{!! esc_attr($icon['alt']) !!}"
                      loading="lazy"
                      class="two-columns-image-icons-with-cta-section__icon">
                  </li>
                @endforeach
              </ul>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
</section>
