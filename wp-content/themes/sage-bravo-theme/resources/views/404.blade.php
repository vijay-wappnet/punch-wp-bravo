@extends('layouts.app')

@section('content')
  {{-- Content comes from Website Options > 404 Page Setting (ACF options), prepared in App\View\Composers\Error404. --}}
  <section class="error-404-page js-dot-grid" data-dot-style="varied">
    <canvas class="dot-grid-canvas" aria-hidden="true"></canvas>

    <div class="error-404-page__inner container">
      @if($error_heading || $error_main_heading || $error_description)
        <div class="error-404-page__content">
          @if($error_heading)
            <{{ $error_heading_level }} class="error-404-page__heading">{{ $error_heading }}</{{ $error_heading_level }}>
          @endif

          @if($error_main_heading)
            <{{ $error_main_heading_level }} class="error-404-page__main-heading">{!! nl2br(esc_html($error_main_heading)) !!}</{{ $error_main_heading_level }}>
          @endif

          @if($error_description)
            <p class="error-404-page__description">
              {!! wp_kses_post($error_description) !!}
            </p>
          @endif
        </div>
      @endif

      @if($error_button)
        <div class="error-404-page__buttons">
          <a href="{!! esc_url($error_button['url']) !!}"
            class="btn error-404-page__btn"
            @if($error_button['aria_label']) aria-label="{!! esc_attr($error_button['aria_label']) !!}" @endif
            @if($error_button['target']) target="{!! esc_attr($error_button['target']) !!}" @endif
            @if($error_button['rel']) rel="{!! esc_attr($error_button['rel']) !!}" @endif
            @if($error_button['event_label']) data-event="{!! esc_attr($error_button['event_label']) !!}" @endif>
            <span>{{ $error_button['title'] }}</span>
            <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="error-404-page__btn-icon" aria-hidden="true">
          </a>
        </div>
      @endif
    </div>
  </section>
@endsection
