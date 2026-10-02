@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- The section can have its own full-width background; the banner image sits inside the container on top of it --}}
<section id="{{ $blockId }}"
  class="image-banner-section @unless($banner_image) image-banner-section--background-only @endunless"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  @if($banner_image)
  <div class="container">
    <div class="row gx-0">
      <div class="col-12">
        <div class="image-banner">
          <img src="{!! esc_url($banner_image['url']) !!}"
            alt="{!! esc_attr($banner_image['alt']) !!}"
            @if($banner_image['width']) width="{!! esc_attr($banner_image['width']) !!}" @endif
            @if($banner_image['height']) height="{!! esc_attr($banner_image['height']) !!}" @endif
            loading="lazy"
            decoding="async"
            class="image-banner__image @if($filtered) image-banner__image--filtered @endif"
            @if($image_style) style="{!! esc_attr($image_style) !!}" @endif>
        </div>
      </div>
    </div>
  </div>
  @endif
</section>
