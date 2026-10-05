@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="meet-the-team-section meet-the-team-section--align-{{ $content_alignment }} meet-the-team-section--align-mobile-{{ $content_alignment_mobile }} js-mts"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>
  <div class="container">

    @if($heading_text || count($members) > 1)
    <div class="meet-the-team-section__header">
      @if($heading_text)
        <{{ $heading_level }} class="meet-the-team-section__heading">{{ $heading_text }}</{{ $heading_level }}>
      @endif

      {{-- Mobile only (CSS); JS hides it when there is nothing to scroll --}}
      @if(count($members) > 1)
      <div class="meet-the-team-section__navigation js-mts-nav">
        <button type="button" class="meet-the-team-section__nav-button js-mts-prev" aria-label="{{ __('Previous team member', 'sage') }}" disabled>
          <svg width="22" height="15" viewBox="0 0 22 15" fill="none" aria-hidden="true" focusable="false"><path d="M21 7.5H1.5M7.5 1.5l-6 6 6 6" stroke="currentColor" stroke-width="1.4"/></svg>
        </button>
        <button type="button" class="meet-the-team-section__nav-button js-mts-next" aria-label="{{ __('Next team member', 'sage') }}">
          <svg width="22" height="15" viewBox="0 0 22 15" fill="none" aria-hidden="true" focusable="false"><path d="M1 7.5h19.5M14.5 1.5l6 6-6 6" stroke="currentColor" stroke-width="1.4"/></svg>
        </button>
      </div>
      @endif
    </div>
    @endif

    @if(count($members))
    <div class="meet-the-team-section__slider">
      <div class="meet-the-team-section__track js-mts-track">
        @foreach($members as $member)
          <article class="meet-the-team-section__card">
            @if($member['image'])
            <div class="meet-the-team-section__image">
              <img src="{!! esc_url($member['image']['url']) !!}"
                alt="{!! esc_attr($member['image']['alt']) !!}"
                @if($member['image']['width']) width="{!! esc_attr($member['image']['width']) !!}" @endif
                @if($member['image']['height']) height="{!! esc_attr($member['image']['height']) !!}" @endif
                loading="lazy" decoding="async">
            </div>
            @endif

            @if($member['name'] || $member['job_title'])
            <div class="meet-the-team-section__content">
              @if($member['name'])
                <h4 class="meet-the-team-section__name">{{ $member['name'] }}</h4>
              @endif
              @if($member['job_title'])
                <p class="meet-the-team-section__job-title">{{ $member['job_title'] }}</p>
              @endif
            </div>
            @endif
          </article>
        @endforeach
      </div>
    </div>
    @endif

  </div>
</section>
