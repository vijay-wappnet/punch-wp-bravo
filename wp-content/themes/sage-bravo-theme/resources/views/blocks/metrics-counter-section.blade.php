@php
use Illuminate\Support\Facades\Vite;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Counter animation and the mobile arrows: resources/js/blocks/metrics-counter-section.js (the final numbers are in the markup, so they show without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="metrics-counter-section metrics-counter-section--align-{{ $content_alignment }} metrics-counter-section--mobile-align-{{ $content_alignment_mobile }} js-mcs"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="metrics-counter-wrapper">

      {{-- Desktop: the cards in a row. Mobile: a horizontally scrolling track (the next card peeks in). --}}
      <div class="metrics-counter js-mcs-track"
        role="group"
        aria-label="{{ __('Metrics', 'sage') }}">
        @foreach($metrics as $metric)
          <article class="metric-card js-mcs-card">
            <div class="metric-value-wrapper">
              @if($metric['prefix'])
                <span class="metric-prefix" aria-hidden="true">{{ $metric['prefix'] }}</span>
              @endif

              {{-- The counting number is hidden from screen readers; they get the final value below --}}
              <span class="metric-value js-mcs-value" aria-hidden="true"
                @if($metric['numeric'])
                  data-target="{{ $metric['target'] }}"
                  data-decimals="{{ $metric['decimals'] }}"
                  data-grouping="{{ $metric['grouping'] ? 'true' : 'false' }}"
                @endif>{{ $metric['value'] }}</span>

              @if($metric['suffix'])
                <span class="metric-suffix" aria-hidden="true">{{ $metric['suffix'] }}</span>
              @endif

              @if($metric['value'] !== '')
                <span class="visually-hidden metric-value-accessible">{{ $metric['accessible'] }}</span>
              @endif
            </div>

            @if($metric['label'] || $metric['trend'] !== 'none')
              <div class="metric-footer">
                @if($metric['label'])
                  <p class="metric-label">{{ $metric['label'] }}</p>
                @endif

                {{-- Up / down triangle from trend_type; decorative (the label and value carry the meaning) --}}
                @if($metric['trend'] !== 'none')
                  <span class="metric-trend metric-trend--{{ $metric['trend'] }}" aria-hidden="true"
                    @if($metric['trend_style']) style="{!! esc_attr($metric['trend_style']) !!}" @endif>{{ $metric['trend'] === 'positive' ? '▲' : '▼' }}</span>
                @endif
              </div>
            @endif
          </article>
        @endforeach
      </div>

      {{-- Mobile only: real buttons that move the track one card at a time --}}
      @if(count($metrics) > 1)
        <div class="metric-nav">
          <button type="button" class="metric-nav-button metric-nav-button--prev js-mcs-prev" aria-label="{{ __('Previous metric', 'sage') }}" disabled>
            <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
          </button>
          <button type="button" class="metric-nav-button metric-nav-button--next js-mcs-next" aria-label="{{ __('Next metric', 'sage') }}">
            <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
          </button>
        </div>
      @endif

    </div>
  </div>
</section>
