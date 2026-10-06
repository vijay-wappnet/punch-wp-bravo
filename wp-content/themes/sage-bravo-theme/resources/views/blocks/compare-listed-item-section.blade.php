@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="compare-listed-item-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    <div class="row gx-0">
      <div class="col-12">
        <div class="clis__card">
          {{-- One column per repeater row, side by side on desktop (a vertical divider between) and stacked on mobile (a horizontal one) --}}
          <div class="row gx-0 clis__row">
            @foreach($columns as $column)
              <div class="col-12 col-md clis__column">

                @if($column['heading'])
                  <{{ $column['level'] }} class="clis__heading">{!! nl2br(esc_html($column['heading'])) !!}</{{ $column['level'] }}>
                @endif

                @if(count($column['items']) > 0)
                  <ul class="clis__list" role="list">
                    @foreach($column['items'] as $item)
                      <li class="clis__item">
                        {{-- The icon says whether the point is a plus or a minus, so that is also given to screen readers as text --}}
                        @if($item['tick'])
                          <svg class="clis__icon clis__icon--tick" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
                            <circle cx="10" cy="10" r="10" class="clis__icon-circle" />
                            <path d="M5.6 10.4 8.7 13.4 14.4 7.2" class="clis__icon-mark" />
                          </svg>
                          <span class="clis__sr-only">{{ __('Included:', 'sage') }}</span>
                        @else
                          <svg class="clis__icon clis__icon--cross" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
                            <circle cx="10" cy="10" r="10" class="clis__icon-circle" />
                            <path d="M6.6 6.6 13.4 13.4 M13.4 6.6 6.6 13.4" class="clis__icon-mark" />
                          </svg>
                          <span class="clis__sr-only">{{ __('Not included:', 'sage') }}</span>
                        @endif
                        <p class="clis__text">{{ $item['text'] }}</p>
                      </li>
                    @endforeach
                  </ul>
                @endif

              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
