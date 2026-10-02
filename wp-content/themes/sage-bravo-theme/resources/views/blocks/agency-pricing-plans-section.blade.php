@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="agency-pricing-plans-section agency-pricing-plans-section--align-{{ $content_alignment }} agency-pricing-plans-section--mobile-align-{{ $content_alignment_mobile }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="row gx-0">
      <div class="col-12">
        <article class="agency-pricing-card" @if($card_style) style="{!! esc_attr($card_style) !!}" @endif>

          {{-- Desktop: plan details on the left, features on the right, a vertical divider between them.
               Mobile: one column - details, a horizontal divider, the features, then the button.
               The same markup does both: the details column "disappears" on mobile (display: contents) so
               its button can follow the features. --}}
          <div class="row gx-0 agency-pricing-card__row">

            <div class="col-12 col-md-5 agency-pricing-content">
              @if($plan_name || $price)
                <header class="agency-pricing-head">
                  @if($plan_name)
                    <h3 class="agency-pricing-name">{{ $plan_name }}</h3>
                  @endif

                  @if($price)
                    <p class="agency-pricing-price">
                      <span class="agency-pricing-amount">{{ $price }}</span>
                      @if($price_period) <span class="agency-pricing-period">{{ $price_period }}</span> @endif
                    </p>
                  @endif
                </header>
              @endif

              @if($description)
                <p class="agency-pricing-description">{!! nl2br(esc_html($description)) !!}</p>
              @endif

              @if($button)
                <div class="agency-pricing-buttons">
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
                      <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="apps-btn__icon" aria-hidden="true">
                    @endif
                  </a>
                </div>
              @endif
            </div>

            @if(count($features) > 0)
              {{-- The divider is this column's left border (desktop) / top border (mobile) --}}
              <div class="col-12 col-md-7 agency-pricing-features">
                <ul class="agency-pricing-feature-list" role="list">
                  @foreach($features as $feature)
                    <li class="agency-pricing-feature">
                      {{-- The same check mark for every feature; decorative --}}
                      <svg class="agency-pricing-check" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
                        <circle cx="10" cy="10" r="10" class="agency-pricing-check-circle" />
                        <path d="M5.6 10.4 8.7 13.4 14.4 7.2" class="agency-pricing-check-mark" />
                      </svg>
                      <span class="agency-pricing-feature-text">{{ $feature }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

          </div>
        </article>
      </div>
    </div>
  </div>
</section>
