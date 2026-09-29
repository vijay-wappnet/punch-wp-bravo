@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="trusted-secure-section trusted-secure-section--align-{{ $content_alignment }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="trusted-secure-section__inner">

      @if(count($icons) > 0)
        <ul class="trusted-secure-section__icons">
          @foreach($icons as $icon)
            <li class="trusted-secure-section__icon">
              <img src="{!! esc_url($icon['url']) !!}"
                alt="{!! esc_attr($icon['alt']) !!}"
                @if($icon['width']) width="{{ $icon['width'] }}" @endif
                @if($icon['height']) height="{{ $icon['height'] }}" @endif
                loading="lazy" decoding="async">
            </li>
          @endforeach
        </ul>
      @endif

      @if($heading_text || $description || count($buttons) > 0)
        <div class="trusted-secure-section__content">

          @if($heading_text)
            <{{ $heading_level }} class="trusted-secure-section__heading">
              {{ $heading_text }}
            </{{ $heading_level }}>
          @endif

          @if($description)
            <div class="trusted-secure-section__description">
              {!! wp_kses_post($description) !!}
            </div>
          @endif

          @if(count($buttons) > 0)
            <div class="trusted-secure-section__buttons">
              @foreach($buttons as $button)
                <a href="{!! esc_url($button['url']) !!}"
                  class="{{ trim('btn tss-btn ' . $button['class']) }}"
                  @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                  @if($button['target'] === '_blank') rel="noopener noreferrer" @endif
                  @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                  @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                  {{ $button['title'] }}
                </a>
              @endforeach
            </div>
          @endif

        </div>
      @endif

    </div>
  </div>
</section>
