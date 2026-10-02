@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="listed-item-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    {{-- Two columns on desktop (the order down the first column, then the second); one column on mobile --}}
    <ul class="lis__list" role="list">
      @foreach($items as $item)
        <li class="lis__item">
          {{-- The same check mark for every item; decorative --}}
          <svg class="lis__check" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
            <circle cx="10" cy="10" r="10" class="lis__check-circle" />
            <path d="M5.6 10.4 8.7 13.4 14.4 7.2" class="lis__check-mark" />
          </svg>
          <p class="lis__text">{{ $item }}</p>
        </li>
      @endforeach
    </ul>
  </div>
</section>
