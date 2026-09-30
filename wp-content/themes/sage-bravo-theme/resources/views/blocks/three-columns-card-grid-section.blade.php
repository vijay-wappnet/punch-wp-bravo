@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="three-columns-card-grid-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    @if(count($cards) > 0)
      <ul class="three-columns-card-grid-section__grid" role="list">
        @foreach($cards as $card)
          {{-- tabindex lets keyboard / touch users reveal the description (:focus-within) the same way hover does --}}
          <li class="three-columns-card-grid-section__item">
            <article class="three-columns-card-grid-section__card @if($card['description']) has-description @endif"
              @if($card['description']) tabindex="0" @endif>

              <div class="three-columns-card-grid-section__media">
                @if($card['image'])
                  <img src="{!! esc_url($card['image']['url']) !!}"
                    alt="{!! esc_attr($card['image']['alt']) !!}"
                    @if($card['image']['width']) width="{!! esc_attr($card['image']['width']) !!}" @endif
                    @if($card['image']['height']) height="{!! esc_attr($card['image']['height']) !!}" @endif
                    loading="lazy"
                    decoding="async"
                    class="three-columns-card-grid-section__image">
                @endif
              </div>

              <div class="three-columns-card-grid-section__content">
                <div class="three-columns-card-grid-section__header">
                  @if($card['heading'])
                    <{{ $card['heading_level'] }} class="three-columns-card-grid-section__heading">
                      {{ $card['heading'] }}
                    </{{ $card['heading_level'] }}>
                  @endif

                  @if($card['description'])
                    <span class="three-columns-card-grid-section__icon" aria-hidden="true"></span>
                  @endif
                </div>

                {{-- Collapsed visually by default but kept in the accessibility tree --}}
                @if($card['description'])
                  <div class="three-columns-card-grid-section__description-wrap">
                    <div class="three-columns-card-grid-section__description">
                      {!! nl2br(esc_html($card['description'])) !!}
                    </div>
                  </div>
                @endif
              </div>

            </article>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</section>
