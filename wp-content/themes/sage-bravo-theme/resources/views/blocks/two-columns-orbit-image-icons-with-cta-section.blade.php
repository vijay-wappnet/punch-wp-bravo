@php
use Illuminate\Support\Facades\Vite;

$hasContent = $heading_text || $subheading_text || $description || count($buttons) > 0;
$hasVisual = count($pills) > 0 || count($icons) > 0;

// Column order: on mobile content_alignment_in_mobile decides - top = the content above the pills and icons,
// bottom = the content below them; from md up image_alignment_left_side does
$mobileVisualOrder = $mobile_position === 'top' ? 2 : 1;
$mobileContentOrder = 3 - $mobileVisualOrder;
$desktopVisualOrder = $visual_left ? 1 : 2;
$desktopContentOrder = 3 - $desktopVisualOrder;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Fan-out animation: resources/js/blocks/two-columns-orbit-image-icons-with-cta-section.js (the finished arrangement is shown without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="two-columns-orbit-image-icons-with-cta-section two-columns-orbit-image-icons-with-cta-section--align-{{ $content_alignment }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="row align-items-center gx-0 tcoi__row">

      {{-- Content column. The order classes move it (desktop and mobile); the markup is only written once. --}}
      @if($hasContent)
        <div class="col-12 @if($hasVisual) col-md-5 @endif order-{{ $mobileContentOrder }} order-md-{{ $desktopContentOrder }} tcoi__column tcoi__column--content">
          <div class="tcoi__content">

            @if($heading_text)
              <{{ $heading_level }} class="tcoi__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
            @endif

            @if($subheading_text)
              <{{ $subheading_level }} class="tcoi__subheading">{!! nl2br(esc_html($subheading_text)) !!}</{{ $subheading_level }}>
            @endif

            @if($description)
              <div class="tcoi__description">
                {!! wp_kses_post($description) !!}
              </div>
            @endif

            @if(count($buttons) > 0)
              <div class="tcoi__buttons">
                @foreach($buttons as $button)
                  <a href="{!! esc_url($button['url']) !!}"
                    class="{!! esc_attr($button['class']) !!}"
                    @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
                    @if($button['rel']) rel="{!! esc_attr($button['rel']) !!}" @endif
                    @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
                    @if($button['event_label']) data-google-event="{!! esc_attr($button['event_label']) !!}" @endif>
                    <span>{{ $button['title'] }}</span>
                    @if($button['arrow_span'])
                      <span class="btn-trans-border-arrow__icon" aria-hidden="true"></span>
                    @else
                      <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="tcoiicwcs-btn__icon" aria-hidden="true">
                    @endif
                  </a>
                @endforeach
              </div>
            @endif

          </div>
        </div>
      @endif

      {{-- Pills and icons column --}}
      @if($hasVisual)
        <div class="col-12 @if($hasContent) col-md-7 @endif order-{{ $mobileVisualOrder }} order-md-{{ $desktopVisualOrder }} tcoi__column tcoi__column--visual">
          <div class="tcoi__visual js-tcoi-visual">

            @if(count($pills) > 0)
              <ul class="tcoi__pills" role="list">
                @foreach($pills as $pill)
                  <li class="tcoi__pill tcoi__pill--{{ $pill['shade'] }}">{{ $pill['label'] }}</li>
                @endforeach
              </ul>
            @endif

            {{-- The orbit: the arc is centred on its own point; each icon is turned to its place on it. Icons start stacked behind the middle one and fan out. --}}
            @if(count($icons) > 0)
              <div class="tcoi__orbit">
                @if($arc)
                  <svg class="tcoi__arc" viewBox="-100 -100 200 200" aria-hidden="true" focusable="false">
                    <path class="tcoi__arc-line" d="{{ $arc }}" />
                  </svg>
                @endif

                <ul class="tcoi__icons" role="list">
                  @foreach($icons as $icon)
                    <li class="tcoi__icon-item" style="--angle: {{ $icon['angle'] }}; --rank: {{ $icon['rank'] }}; --z: {{ $icon['z'] }};">
                      <span class="tcoi__icon-pos">
                        <img src="{!! esc_url($icon['url']) !!}"
                          alt="{!! esc_attr($icon['alt']) !!}"
                          @if($icon['width']) width="{!! esc_attr($icon['width']) !!}" @endif
                          @if($icon['height']) height="{!! esc_attr($icon['height']) !!}" @endif
                          loading="lazy"
                          decoding="async"
                          class="tcoi__icon">
                      </span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
</section>
