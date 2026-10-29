@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Desktop: 3-column grid. Tablet/mobile: the same list becomes a swipeable scroll-snap row
     with prev/next arrows (resources/js/blocks/latest-article-list-section.js) --}}
<section id="{{ $blockId }}"
  class="latest-article-list-section js-lals"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="latest-article-list-section__header">
      @if($heading_text)
        <{{ $heading_level }} class="latest-article-list-section__heading">{{ $heading_text }}</{{ $heading_level }}>
      @endif

      <div class="latest-article-list-section__actions">
        @if($button)
          <a href="{!! esc_url($button['url']) !!}"
            class="btn {!! esc_attr($button['class']) !!}"
            aria-label="{!! esc_attr(bravo_label($button['aria_label'], $button['title'])) !!}"
            @if($button['target']) target="{!! esc_attr($button['target']) !!}" @endif
            @if($button['rel']) rel="{!! esc_attr($button['rel']) !!}" @endif
            data-event="{!! esc_attr(bravo_label($button['event_label'], $button['title'])) !!}">
            <span>{{ $button['title'] }}</span>
            <span class="btn-trans-border-arrow__icon" aria-hidden="true"></span>
          </a>
        @endif

        @if(count($articles) > 1)
          <div class="latest-article-list-section__arrows">
            <button type="button" class="latest-article-list-section__arrow latest-article-list-section__arrow--prev js-lals-prev" aria-label="{{ __('Previous articles', 'sage') }}" data-event="{{ __('Previous articles', 'sage') }}" disabled>
              <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="{{ __('Previous arrow icon', 'sage') }}" aria-hidden="true" width="22" height="15">
              <span class="visually-hidden">{{ __('Previous articles', 'sage') }}</span>
            </button>
            <button type="button" class="latest-article-list-section__arrow latest-article-list-section__arrow--next js-lals-next" aria-label="{{ __('Next articles', 'sage') }}" data-event="{{ __('Next articles', 'sage') }}">
              <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="{{ __('Next arrow icon', 'sage') }}" aria-hidden="true" width="22" height="15">
              <span class="visually-hidden">{{ __('Next articles', 'sage') }}</span>
            </button>
          </div>
        @endif
      </div>
    </div>

    <ul class="latest-article-list-section__list js-lals-track" role="list">
      @foreach($articles as $article)
        <li class="latest-article-list-section__item">
          {{-- The whole card is one link --}}
          <a href="{!! esc_url($article['url']) !!}" class="latest-article-list-section__card"
            aria-label="{{ bravo_label($article['title'] . ' - ' . __('Read More', 'sage')) }}"
            data-event="{{ bravo_label($article['title']) }}">
            <div class="latest-article-list-section__media">
              @if($article['image'])
                {!! $article['image'] !!}
              @else
                <span class="latest-article-list-section__placeholder" aria-hidden="true"></span>
              @endif
            </div>

            <h3 class="latest-article-list-section__title">{{ $article['title'] }}</h3>

            <span class="latest-article-list-section__read-more">
              {{ __('Read More', 'sage') }}
              <span class="latest-article-list-section__read-more-icon" aria-hidden="true"></span>
            </span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>
