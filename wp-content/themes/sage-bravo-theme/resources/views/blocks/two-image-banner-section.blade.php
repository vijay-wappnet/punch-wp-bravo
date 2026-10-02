@php
// One image takes the full width; two share the row from md up and stack on mobile
$columnClass = count($banner_images) > 1 ? 'col-12 col-md-6' : 'col-12';
@endphp

@if(!empty($responsiveCss))
<style>{{ $responsiveCss }}</style>
@endif
{{-- The section can have its own full-width background; the banner images sit inside the container on top of it --}}
<section id="{{ $blockId }}"
  class="two-image-banner-section @if(count($banner_images) === 0) two-image-banner-section--background-only @endif"
  @if($section_style) style="{!! esc_attr($section_style) !!}" @endif>

  @if(count($banner_images) > 0)
  <div class="container">
    <div class="row g-3 tibs__row">
      @foreach($banner_images as $banner_image)
        <div class="{{ $columnClass }}">
          <div class="image-banner @if(count($banner_images) > 1) image-banner--tile @endif">
            <img src="{!! esc_url($banner_image['url']) !!}"
              alt="{!! esc_attr($banner_image['alt']) !!}"
              @if($banner_image['width']) width="{!! esc_attr($banner_image['width']) !!}" @endif
              @if($banner_image['height']) height="{!! esc_attr($banner_image['height']) !!}" @endif
              @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
              decoding="async"
              class="image-banner__image @if($filtered) image-banner__image--filtered @endif"
              @if($image_style) style="{!! esc_attr($image_style) !!}" @endif>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @endif
</section>
