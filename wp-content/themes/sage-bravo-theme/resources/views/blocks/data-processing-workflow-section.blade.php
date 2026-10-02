@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Build animation: resources/js/blocks/data-processing-workflow-section.js (the finished diagram is shown without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="data-processing-workflow-section js-dpw"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  {{-- Everything in the diagram is positioned against this box, so it scales as one piece --}}
  <div class="dpw__diagram js-dpw-diagram">

    {{-- Connection lines: behind the icons. Each is drawn on through a mask, so dashed lines are drawn on as dashed lines. --}}
    @if(count($connections) > 0)
      <svg class="dpw__lines" viewBox="0 0 775 250" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
        <defs>
          @foreach($connections as $connection)
            <mask id="{{ $blockId }}-mask-{{ $connection['index'] }}" maskUnits="userSpaceOnUse" x="-20" y="-20" width="815" height="290">
              <path class="dpw__line-reveal js-dpw-line" data-index="{{ $connection['index'] }}" d="{{ $connection['path'] }}" pathLength="1" />
            </mask>
          @endforeach
        </defs>
        @foreach($connections as $connection)
          <path class="dpw__line @if($connection['dashed']) dpw__line--dashed @endif"
            d="{{ $connection['path'] }}"
            mask="url(#{{ $blockId }}-mask-{{ $connection['index'] }})" />
        @endforeach
      </svg>
    @endif

    {{-- The uploaded icons are the complete visuals (pills, circles, logo, arrow): rendered as they are, positioned by their order --}}
    @foreach($elements as $element)
      <span class="dpw__item dpw__item--{{ $element['index'] }} js-dpw-item" data-index="{{ $element['index'] }}">
        <img src="{!! esc_url($element['icon']['url']) !!}"
          alt="{!! esc_attr($element['icon']['alt']) !!}"
          @if($element['icon']['width']) width="{!! esc_attr($element['icon']['width']) !!}" @endif
          @if($element['icon']['height']) height="{!! esc_attr($element['icon']['height']) !!}" @endif
          loading="lazy"
          decoding="async"
          class="dpw__item-image">
      </span>
    @endforeach
  </div>
</section>
