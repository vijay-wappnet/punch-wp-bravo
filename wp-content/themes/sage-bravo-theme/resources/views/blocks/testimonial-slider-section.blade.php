@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Arrow icon is the same one used by the CTA buttons; the "previous" arrow is the same image flipped in CSS --}}
<section id="{{ $blockId }}"
  class="testimonial-slider-section"
  data-arrow="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    @if(count($testimonials) > 0)
      <div class="testimonial-slider-section__inner @if(count($testimonials) > 1) has-slider @endif">
        <div class="testimonial-slider-section__slider js-tss-slider" aria-label="{{ __('Testimonials', 'sage') }}">
          @foreach($testimonials as $testimonial)
            <div class="testimonial-slider-section__slide">
              <figure class="testimonial-slider-section__item">
                @if($testimonial['quote'])
                  <blockquote class="testimonial-slider-section__blockquote">
                    <{{ $testimonial['heading_level'] }} class="testimonial-slider-section__quote">{!! nl2br(esc_html($testimonial['quote'])) !!}</{{ $testimonial['heading_level'] }}>
                  </blockquote>
                @endif

                @if($testimonial['person'] || $testimonial['logo'])
                  <figcaption class="testimonial-slider-section__caption">
                    @if($testimonial['person'])
                      <span class="testimonial-slider-section__person">{{ $testimonial['person'] }}</span>
                    @endif

                    @if($testimonial['logo'])
                      <img src="{!! esc_url($testimonial['logo']['url']) !!}"
                        alt="{!! esc_attr($testimonial['logo']['alt']) !!}"
                        @if($testimonial['logo']['width']) width="{!! esc_attr($testimonial['logo']['width']) !!}" @endif
                        @if($testimonial['logo']['height']) height="{!! esc_attr($testimonial['logo']['height']) !!}" @endif
                        loading="lazy"
                        decoding="async"
                        class="testimonial-slider-section__logo">
                    @endif
                  </figcaption>
                @endif
              </figure>
            </div>
          @endforeach
        </div>

        {{-- Slick appends the prev/next buttons here (only when there is more than one testimonial) --}}
        @if(count($testimonials) > 1)
          <div class="testimonial-slider-section__arrows js-tss-arrows"></div>
        @endif
      </div>
    @endif
  </div>
</section>
