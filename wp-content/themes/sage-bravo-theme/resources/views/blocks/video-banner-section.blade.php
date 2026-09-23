@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<div id="{{ $blockId }}" class="video-banner-section @if($show_dot_grid_canvas) js-dot-grid @endif" @if($section_bg) style="--overlay-color: {{ esc_attr($section_bg) }};" @endif>

  <div class="video-banner-section__media">
    @if($video_file)
      <video
        class="video-banner-section__video"
        autoplay
        muted
        loop
        playsinline
        preload="auto"
        aria-hidden="true"
        tabindex="-1"
        role="presentation">
        <source src="{{ esc_url($video_file) }}" type="video/mp4">
        @if($video_image)
          <img src="{{ esc_url($video_image) }}" alt="banner" class="video-banner-section__fallback-image">
        @endif
      </video>
    @elseif($video_image)
      <img src="{{ esc_url($video_image) }}" alt="banner" class="video-banner-section__image">
    @else
      <img src="{{ Vite::asset('resources/images/home-video.webp') }}" alt="home video banner" class="video-banner-section__fallback-image home-video-banner">
    @endif
  </div>

  @if($section_bg)
    <div class="video-banner-section__overlay"></div>
  @endif

  @if($show_dot_grid_canvas)
    <canvas class="dot-grid-canvas" aria-hidden="true"></canvas>
  @endif

  @if($heading_text)
    <div class="video-banner-section__content">
      <div class="video-banner-section__box">
        <{{ $heading_level }} class="video-banner-section__heading">
          {{ esc_html($heading_text) }}
        </{{ $heading_level }}>

        @if($button)
          <div class="video-banner-section__button-wrapper">
            @php
              $link = $button['button_link'] ?? [];
              $url = is_array($link) ? ($link['url'] ?? '#') : $link;
              $target = is_array($link) ? ($link['target'] ?? '') : '';
              $link_title = is_array($link) ? ($link['title'] ?? 'Button') : 'Button';
              $button_aria = $button['aria_label'] ?? '';
              $event_label = $button['button_google_event_label'] ?? '';
              $button_class = $button['button_class'] ?? '';
              $target_attr = $target ? 'target="' . esc_attr($target) . '"' : '';
            @endphp
            <a href="{{ esc_url($url) }}"
              @if($target_attr)
                {{ $target_attr }}
              @endif
              class="btn vbs-btn {{ esc_attr($button_class) }}"
              @if($button_aria)aria-label="{{ esc_attr($button_aria) }}"@endif
              @if($event_label)data-event-label="{{ esc_attr($event_label) }}"@endif>
                <span>{{ esc_html($link_title) }}</span>
                <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="vbs-btn__icon" aria-hidden="true">
            </a>
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- <div class="video-banner-section__arrow" role="button" tabindex="0" aria-label="Scroll to next section">
    <img src="{{ Vite::asset('resources/images/arrow-down.svg') }}" alt="flower" class="icon">
  </div> --}}

</div>
