@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="pricing-plans-section pricing-plans-section--align-{{ $content_alignment }} pricing-plans-section--mobile-align-{{ $content_alignment_mobile }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    {{-- One column per plan: the cards stack on mobile and share the row from md up. Each card fills its column so they have equal heights. --}}
    <div class="row {{ $columns }} g-3 pps__row">
      @foreach($plans as $plan)
        <div class="col">
          <article class="pps__card @if($plan['is_popular']) pps__card--popular @endif"
            @if($plan['card_style']) style="{!! esc_attr($plan['card_style']) !!}" @endif>

            {{-- The tag belongs to its own card, so it stays attached when the cards stack --}}
            @if($plan['is_popular'])
              <span class="pps__tag" @if($plan['tag_style']) style="{!! esc_attr($plan['tag_style']) !!}" @endif>{{ $plan['popular_label'] }}</span>
            @endif

            <div class="pps__head">
              @if($plan['name'])
                <h3 class="pps__name">{{ $plan['name'] }}</h3>
              @endif

              @if($plan['price'])
                <p class="pps__price">
                  <span class="pps__amount">{{ $plan['price'] }}</span>
                  @if($plan['period']) <span class="pps__period">{{ $plan['period'] }}</span> @endif
                </p>
              @endif

              @if($plan['description'])
                <p class="pps__description">{!! nl2br(esc_html($plan['description'])) !!}</p>
              @endif
            </div>

            @if(count($plan['features']) > 0)
              <ul class="pps__features" role="list">
                @foreach($plan['features'] as $feature)
                  <li class="pps__feature">
                    {{-- The same check mark for every feature; decorative --}}
                    <svg class="pps__check" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
                      <circle cx="10" cy="10" r="10" class="pps__check-circle" />
                      <path d="M5.6 10.4 8.7 13.4 14.4 7.2" class="pps__check-mark" />
                    </svg>
                    <span class="pps__feature-text">{{ $feature }}</span>
                  </li>
                @endforeach
              </ul>
            @endif

            @if($plan['button'])
              <div class="pps__buttons">
                <a href="{!! esc_url($plan['button']['url']) !!}"
                  class="{!! esc_attr($plan['button']['class']) !!}"
                  @if($plan['button']['target']) target="{!! esc_attr($plan['button']['target']) !!}" @endif
                  @if($plan['button']['rel']) rel="{!! esc_attr($plan['button']['rel']) !!}" @endif
                  @if($plan['button']['aria_label']) aria-label="{!! esc_attr($plan['button']['aria_label']) !!}" @endif
                  @if($plan['button']['event_label']) data-google-event="{!! esc_attr($plan['button']['event_label']) !!}" @endif>
                  <span>{{ $plan['button']['title'] }}</span>
                  @if($plan['button']['arrow_span'])
                    <span class="btn-trans-border-arrow__icon" aria-hidden="true"></span>
                  @else
                    <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="pps-btn__icon" aria-hidden="true">
                  @endif
                </a>
              </div>
            @endif

          </article>
        </div>
      @endforeach
    </div>
  </div>
</section>
