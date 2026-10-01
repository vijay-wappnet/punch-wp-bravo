@php
use Illuminate\Support\Facades\Vite;

$hasContent = $heading_text || $description || $button;
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Line/icon animation: resources/js/blocks/process-workflow-section.js (final state is shown without JS or with reduced motion) --}}
<section id="{{ $blockId }}"
  class="process-workflow-section process-workflow-section--workflow-{{ $image_left ? 'left' : 'right' }} process-workflow-section--mobile-{{ $mobile_workflow_position }} process-workflow-section--align-{{ $content_alignment }} process-workflow-section--mobile-align-{{ $content_alignment_mobile }} js-pws"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="process-workflow-section__inner">

      {{-- Workflow column. Column order (desktop and mobile) is set in CSS from the modifier classes. --}}
      @if($workflow)
        <div class="process-workflow-section__column process-workflow-section__column--workflow">
          <div class="pws__workflow-visual">
            <svg class="pws__lines" viewBox="{{ $workflow['view_box'] }}" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
              @foreach($workflow['steps'] as $step)
                <g class="pws__line-group js-pws-line-group">
                  <path class="pws__line js-pws-line" d="{{ $step['path'] }}" pathLength="1" />
                  @if($step['cap'])
                    <path class="pws__line js-pws-line" d="{{ $step['cap'] }}" pathLength="1" />
                  @endif
                </g>
              @endforeach
            </svg>

            @foreach($workflow['steps'] as $step)
              @if($step['title'])
                <span class="pws__step-label pws__step-label--{{ $loop->iteration }} js-pws-label">{{ $step['title'] }}</span>
              @endif

              @if($step['icon'])
                <span class="pws__step-icon pws__step-icon--{{ $loop->iteration }} js-pws-icon">
                  <img src="{!! esc_url($step['icon']['url']) !!}"
                    alt="{!! esc_attr($step['icon']['alt']) !!}"
                    @if($step['icon']['width']) width="{!! esc_attr($step['icon']['width']) !!}" @endif
                    @if($step['icon']['height']) height="{!! esc_attr($step['icon']['height']) !!}" @endif
                    loading="lazy"
                    decoding="async">
                </span>
              @endif
            @endforeach
          </div>
        </div>
      @endif

      {{-- Content column --}}
      @if($hasContent)
        <div class="process-workflow-section__column process-workflow-section__column--content">
          <div class="process-workflow-section__content">

            @if($heading_text)
              <{{ $heading_level }} class="process-workflow-section__heading">{!! nl2br(esc_html($heading_text)) !!}</{{ $heading_level }}>
            @endif

            @if($description)
              <div class="fs-30 process-workflow-section__description">
                {!! wp_kses_post($description) !!}
              </div>
            @endif

            @if($button)
              <div class="process-workflow-section__buttons">
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
                    <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="pws-btn__icon" aria-hidden="true">
                  @endif
                </a>
              </div>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
</section>
