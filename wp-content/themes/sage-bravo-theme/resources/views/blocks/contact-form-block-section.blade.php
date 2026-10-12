@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="contact-form-block-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    {{-- Contact Form 7 output (escaped by CF7 itself) --}}
    {!! $form_html !!}
  </div>
</section>
