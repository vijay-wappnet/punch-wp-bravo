@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="left-right-media-content-section left-right-media-content-section--media-{{ $media_side }} left-right-media-content-section--mobile-content-{{ $mobile_content }} @unless($image) left-right-media-content-section--no-media @endunless"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="left-right-media-content-section__inner">

      {{-- Content column. Column order (desktop and mobile) is set in CSS from the modifier classes. --}}
      @if($title || $content || count($buttons) > 0)
        <div class="left-right-media-content-section__column left-right-media-content-section__column--content">
          <div class="left-right-media-content-section__content">

            @if($title)
              <{{ $heading_level }} class="left-right-media-content-section__heading">
                {{ $title }}
              </{{ $heading_level }}>
            @endif

            @if($content)
              <div class="fs-30 left-right-media-content-section__description">
                {!! wp_kses_post($content) !!}
              </div>
            @endif

            @if(count($buttons) > 0)
              <div class="left-right-media-content-section__buttons">
                @foreach($buttons as $button)
                  <a href="{!! esc_url($button['url']) !!}"
                    class="btn lrmcs-btn {!! esc_attr($button['class']) !!}"
                    @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                    @if($button['target'] === '_blank') rel="noopener noreferrer" @endif
                    @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                    @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                    <span>{{ $button['title'] }}</span>
                    <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="lrmcs-btn__icon" aria-hidden="true">
                  </a>
                @endforeach
              </div>
            @endif

          </div>
        </div>
      @endif

      {{-- Media column --}}
      @if($image)
        <div class="left-right-media-content-section__column left-right-media-content-section__column--media">
          <div class="left-right-media-content-section__media js-lrmcs-media">
            <img src="{!! esc_url($image['url']) !!}"
              alt="{!! esc_attr($image['alt']) !!}"
              @if($image['width']) width="{!! esc_attr($image['width']) !!}" @endif
              @if($image['height']) height="{!! esc_attr($image['height']) !!}" @endif
              loading="lazy"
              decoding="async"
              class="left-right-media-content-section__image">
          </div>
        </div>
      @endif

    </div>
  </div>
</section>
