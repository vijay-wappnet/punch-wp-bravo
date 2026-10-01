@php
// The two drawings (desktop and mobile) share one design but have different proportions, so each has its own
// viewBox and paths. They are measured from the design references; the item positions in the block's CSS use the same numbers.
//   dashed: the rectangle round the centre card, drawn clockwise from the top item
//   solid : from the top item, round the right of the card, back to the bottom item
$drawings = [
    'desktop' => [
        'view_box' => '0 0 855 378',
        'width'    => 855,
        'height'   => 378,
        'dashed'   => 'M427 35 H772 V343 H83 V35 H427',
        'solid'    => 'M427 71 V87 H669 V287 H427 V308',
    ],
    'mobile' => [
        'view_box' => '0 0 524 443',
        'width'    => 524,
        'height'   => 443,
        'dashed'   => 'M262 37 H497 V410 H27 V37 H262',
        'solid'    => 'M262 71 V87 H368 V345 H262 V375',
    ],
];
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Animation: resources/js/blocks/tech-ecosystem-connections-section.js (the finished drawing is shown without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="tech-ecosystem-connections-section tech-ecosystem-connections-section--align-{{ $content_alignment }} tech-ecosystem-connections-section--mobile-align-{{ $content_alignment_mobile }} js-tec"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  @if($bg_image_style)
    <div class="tech-ecosystem-connections-section__bg" style="{!! esc_attr($bg_image_style) !!}" aria-hidden="true"></div>
  @endif

  @if($heading_text)
    <div class="container">
      <{{ $heading_level }} class="tec__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
    </div>
  @endif

  {{-- Outside the container: the drawing is sized from the screen width (as in the design), so it can be wider than the container --}}
  @if(count($logos) > 0 || count($items) > 0)
    <div class="tec__visual-wrap">
      <div class="tec__visual js-tec-visual">

        {{-- Connection lines: decorative, behind the card and the items --}}
        @foreach($drawings as $name => $drawing)
          <svg class="tec__lines tec__lines--{{ $name }}" viewBox="{{ $drawing['view_box'] }}" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
            <defs>
              {{-- The dashed line is revealed through this mask, so it is drawn on as a dashed line --}}
              <mask id="{{ $blockId }}-mask-{{ $name }}" maskUnits="userSpaceOnUse" x="0" y="0" width="{{ $drawing['width'] }}" height="{{ $drawing['height'] }}">
                <path class="tec__dashed-reveal js-tec-dashed-reveal" d="{{ $drawing['dashed'] }}" pathLength="1" />
              </mask>
            </defs>
            <path class="tec__dashed" d="{{ $drawing['dashed'] }}" mask="url(#{{ $blockId }}-mask-{{ $name }})" />
            <path class="tec__solid js-tec-solid" d="{{ $drawing['solid'] }}" pathLength="1" />
          </svg>
        @endforeach

        @if(count($logos) > 0)
          <div class="tec__card js-tec-card">
            <ul class="tec__logos" role="list">
              @foreach($logos as $logo)
                <li class="tec__logo">
                  <img src="{!! esc_url($logo['url']) !!}"
                    alt="{!! esc_attr($logo['alt']) !!}"
                    @if($logo['width']) width="{!! esc_attr($logo['width']) !!}" @endif
                    @if($logo['height']) height="{!! esc_attr($logo['height']) !!}" @endif
                    loading="lazy"
                    decoding="async">
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- One markup for every position; the position class sets where it sits and how it looks --}}
        @foreach($items as $item)
          <div class="tec__item tec__item--{{ $item['position'] }} js-tec-item">
            @if($item['position'] === 'top' && $item['icon'])
              <span class="tec__item-icon">
                <img src="{!! esc_url($item['icon']['url']) !!}"
                  alt="{!! esc_attr($item['title'] ? '' : $item['icon']['alt']) !!}"
                  @if($item['icon']['width']) width="{!! esc_attr($item['icon']['width']) !!}" @endif
                  @if($item['icon']['height']) height="{!! esc_attr($item['icon']['height']) !!}" @endif
                  loading="lazy"
                  decoding="async">
              </span>
              @if($item['title'])
                <span class="visually-hidden">{{ $item['title'] }}</span>
              @endif
            @else
              {{-- Side and bottom items are text-only pills in the design, so their icon is not shown --}}
              @if($item['title'])
                <span class="tec__item-title">{{ $item['title'] }}</span>
              @endif
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif
</section>
