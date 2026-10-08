@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Bootstrap 2-column grid on desktop, one full-width column on mobile. Every article is rendered;
     resources/js/blocks/article-post-listing-section.js shows one page at a time (posts_per_page on
     desktop, posts_per_page_mobile on mobile). Without JS all articles stay visible. --}}
<section id="{{ $blockId }}"
  class="article-post-listing-section js-apls"
  data-per-page="{{ $per_page }}"
  data-per-page-mobile="{{ $per_page_mobile }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="row g-4 article-post-listing-section__list js-apls-list">
      @foreach($articles as $article)
        <div class="col-12 col-md-6 article-post-listing-section__item js-apls-item">
          {{-- The whole card is one link --}}
          <a href="{!! esc_url($article['url']) !!}"
            class="article-post-listing-section__link"
            aria-label="{{ sprintf(__('Read more: %s', 'sage'), $article['title']) }}">
            <div class="article-post-card">
              <div class="article-post-card__image">
                @if($article['image'])
                  {!! $article['image'] !!}
                @else
                  <span class="article-post-card__placeholder" aria-hidden="true"></span>
                @endif
              </div>

              <div class="article-post-card__body">
                @if($article['category'])
                  <p class="article-post-card__category">{{ $article['category'] }}</p>
                @endif

                <h2 class="article-post-card__title">{{ $article['title'] }}</h2>

                <span class="article-post-card__read-more">
                  {{ __('Read More', 'sage') }}
                  <img class="article-post-card__read-more-icon" src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="19" height="13">
                </span>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>

    @if(count($articles) > min($per_page, $per_page_mobile))
      <nav class="article-post-listing-section__pagination js-apls-pager" aria-label="{{ __('Article pages', 'sage') }}" hidden>
        <button type="button" class="article-post-listing-section__arrow article-post-listing-section__arrow--prev js-apls-prev" disabled>
          <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
          <span class="visually-hidden">{{ __('Previous page', 'sage') }}</span>
        </button>

        <span class="article-post-listing-section__count" aria-live="polite">
          <span class="js-apls-current">1</span>/<span class="js-apls-total">1</span>
        </span>

        <button type="button" class="article-post-listing-section__arrow article-post-listing-section__arrow--next js-apls-next">
          <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" aria-hidden="true" width="22" height="15">
          <span class="visually-hidden">{{ __('Next page', 'sage') }}</span>
        </button>
      </nav>
    @endif
  </div>
</section>
