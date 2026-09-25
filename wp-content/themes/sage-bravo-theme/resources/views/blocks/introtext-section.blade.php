@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<div id="{{ $blockId }}" class="intro-section" @if($bg_color) style="background-color: {{ esc_attr($bg_color) }};" @endif>

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-12 intro-section__content-wrapper" style="text-align: {{ esc_attr($content_alignment) }};">

        {{-- Heading --}}
        @if($heading_text)
            <{{ $heading_level }} class="intro-section__heading">
                {{ $heading_text }}
            </{{ $heading_level }}>
        @endif

        {{-- Content/Body --}}
        @if($content)
            <div class="intro-section__content">
                {!! wp_kses_post($content) !!}
            </div>
        @endif

        {{-- Buttons from Repeater --}}
        @if(count($buttons) > 0)
            <div class="intro-section__buttons">
                @foreach($buttons as $button)
                    @php
                        $link = $button['button_link'] ?? [];
                        $url = is_array($link) ? ($link['url'] ?? '#') : $link;
                        $link_title = is_array($link) ? ($link['title'] ?? 'Button') : 'Button';
                        $target = is_array($link) ? ($link['target'] ?? '') : '';
                        $button_aria = $button['aria_label'] ?? '';
                        $event_label = $button['button_google_event_label'] ?? '';
                        $button_class = $button['button_class'] ?? '';
                    @endphp

                    <a href="{{ esc_url($url) }}"
                      target="{{ esc_attr($target) }}"
                      class="btn its-btn {{ esc_attr($button_class) }}"
                      @if($button_aria)aria-label="{{ esc_attr($button_aria) }}"@endif
                      @if($event_label)data-event-label="{{ esc_attr($event_label) }}"@endif>
                        {{ esc_html($link_title) }}
                    </a>
                @endforeach
            </div>
        @endif
      </div>
    </div>
  </div>
</div>
