@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
<section id="{{ $blockId }}"
  class="line-block-section"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  <div class="container">
    {{-- Decorative divider: colour and width (% of the container) from ACF --}}
    <hr class="line-block-section__line" style="{!! esc_attr($line_style) !!}" aria-hidden="true">
  </div>
</section>
