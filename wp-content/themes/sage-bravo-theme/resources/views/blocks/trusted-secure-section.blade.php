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

      @if($heading_text)
        <div class="trusted-secure-section__content">
          <{{ $heading_level }} class="trusted-secure-section__heading">
            {{ $heading_text }}
          </{{ $heading_level }}>
        </div>
      @endif

    </div>
  </div>
</section>
