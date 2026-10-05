@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="brand-logo-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    {{-- 5 logos per row on desktop, 3 on mobile. justify-content-center centres an unfinished last row. --}}
    <ul class="row row-cols-3 row-cols-md-5 justify-content-center align-items-center gx-0 bls__list" role="list">
      @foreach($logos as $logo)
        <li class="col bls__item">
          <img src="{!! esc_url($logo['url']) !!}"
            alt="{!! esc_attr($logo['alt']) !!}"
            @if($logo['width']) width="{!! esc_attr($logo['width']) !!}" @endif
            @if($logo['height']) height="{!! esc_attr($logo['height']) !!}" @endif
            loading="lazy"
            decoding="async"
            class="bls__logo">
        </li>
      @endforeach
    </ul>
  </div>
</section>
