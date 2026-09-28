@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="floating-content-card-section floating-content-card-section--align-{{ $content_alignment }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  @if($bg_image_style)
    <div class="floating-content-card-section__bg" style="{!! esc_attr($bg_image_style) !!}" aria-hidden="true"></div>
  @endif

  <div class="container">
    <div class="floating-content-card-section__inner">
      <div class="floating-content-card-section__card">

        @if($heading_text)
          <{{ $heading_level }} class="floating-content-card-section__heading">
            {{ $heading_text }}
          </{{ $heading_level }}>
        @endif

        @if($description)
          <div class="floating-content-card-section__description">
            {!! wp_kses_post($description) !!}
          </div>
        @endif

        @if(count($buttons) > 0)
          <div class="floating-content-card-section__buttons">
            @foreach($buttons as $button)
              <a href="{!! esc_url($button['url']) !!}"
                class="btn fccs-btn {!! esc_attr($button['class']) !!}"
                @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                @if($button['target'] === '_blank') rel="noopener noreferrer" @endif
                @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                <span>{{ $button['title'] }}</span>
                <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="fccs-btn__icon" aria-hidden="true">
              </a>
            @endforeach
          </div>
        @endif

      </div>
    </div>
  </div>
</section>
