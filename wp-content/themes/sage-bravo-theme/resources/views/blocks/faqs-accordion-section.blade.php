@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- Accordion toggling: resources/js/blocks/faqs-accordion-section.js. All questions start closed. --}}
<section id="{{ $blockId }}"
  class="faqs-accordion-section js-faqs-accordion"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>
  <div class="container">
    <div class="faqs-accordion-section__list">
      @foreach($faqs as $index => $faq)
        @php
          $questionId = $blockId . '-question-' . $index;
          $answerId = $blockId . '-answer-' . $index;
        @endphp
        <div class="faqs-accordion-section__item">
          <h3 class="faqs-accordion-section__heading">
            <button class="faqs-accordion-section__question js-faqs-accordion-toggle"
              type="button"
              id="{{ $questionId }}"
              aria-expanded="false"
              aria-controls="{{ $answerId }}">
              <span class="faqs-accordion-section__question-text">{{ $faq['question'] }}</span>
              <img src="{{ Vite::asset('resources/images/btn-left-arrow.svg') }}" alt="" class="faqs-accordion-section__icon" aria-hidden="true" width="22" height="15">
            </button>
          </h3>

          <div class="faqs-accordion-section__answer"
            id="{{ $answerId }}"
            role="region"
            aria-labelledby="{{ $questionId }}"
            inert>
            <div class="faqs-accordion-section__answer-content">{!! $faq['answer'] !!}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
