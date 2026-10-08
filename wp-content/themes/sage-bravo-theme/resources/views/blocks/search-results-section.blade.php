@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="search-results-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    @if($main_title)
      <{{ $heading_level }} class="search-results-section__title">{{ $main_title }}</{{ $heading_level }}>
    @endif

    {{-- Desktop: input + button on one row. Mobile: stacked --}}
    <form class="search-results-section__form js-srs-form" role="search" method="get" action="{!! esc_url($form_action) !!}">
      <label class="search-results-section__field">
        <img src="{{ Vite::asset('resources/images/Search-icon.svg') }}" alt="" aria-hidden="true" width="25" height="27" class="search-results-section__icon">
        <span class="visually-hidden">{{ __('Search', 'sage') }}</span>
        <input type="search"
          name="{{ $search_param }}"
          class="search-results-section__input"
          placeholder="{{ $placeholder }}"
          value="{{ $search_term }}"
          autocomplete="off">
      </label>

      <button type="submit"
        class="btn {!! esc_attr($button['class']) !!}"
        @if($button['aria_label']) aria-label="{!! esc_attr($button['aria_label']) !!}" @endif
        @if($button['event_label']) data-event="{!! esc_attr($button['event_label']) !!}" @endif>
        <span>{{ $button['title'] }}</span>
        <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
      </button>
    </form>

    @if($search_term !== '' && !empty($results))
      <div class="search-results-section__results">
        <h2 class="search-results-section__results-title">{{ sprintf(__('Your search results for ‘%s’', 'sage'), $search_term) }}</h2>
        <p class="search-results-section__results-text">{{ __('Essential info you may need ahead of your stay with us. If you haven’t found the answer you need, get in touch and one of our team will be happy to help.', 'sage') }}</p>

        <ul class="search-results-section__list" role="list">
          @foreach($results as $result)
            <li class="search-results-section__item">
              <span class="search-results-section__item-title">{{ $result['title'] }}</span>
              <a href="{!! esc_url($result['link']) !!}"
                class="btn btn-red-fusion search-results-section__more"
                aria-label="{{ sprintf(__('Find out more about %s', 'sage'), $result['title']) }}">
                <span>{{ __('Find out More', 'sage') }}</span>
                <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
              </a>
            </li>
          @endforeach
        </ul>

        @if($pagination)
          <nav class="search-results-section__pagination" aria-label="{{ __('Search results pages', 'sage') }}">
            {!! $pagination !!}
          </nav>
        @endif
      </div>
    @else
      {{-- No keyword yet, or nothing matched --}}
      <p class="search-results-section__empty">{{ __('No results found.', 'sage') }}</p>
    @endif
  </div>
</section>
