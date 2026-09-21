@php
    use Illuminate\Support\Facades\Vite;

    $header_logo_black = get_field('header_logo', 'option');
    $header_logo_white = get_field('header_logo_white', 'option');
    $header_logo_for_transition = get_field('header_logo_for_transition', 'option');

    $logo_link_group = get_field('header_logo_link', 'option');
    $siteName = get_bloginfo('name');

    $logo_url = home_url('/');
    $logo_target = '';
    $logo_aria_label = __('Home', 'sage');
    $logo_ga_label = '';

    if ($logo_link_group) {
        $link_type = $logo_link_group['link_type'] ?? '';
        $logo_link = $logo_link_group['logo_link'] ?? '';
        $logo_aria_label = $logo_link_group['aria_label'] ?: $logo_aria_label;
        $logo_ga_label = $logo_link_group['button_google_event_label'] ?? '';

        if ($logo_link) {
            $logo_url = is_array($logo_link) ? ($logo_link['url'] ?? $logo_url) : $logo_link;
            $logo_target = is_array($logo_link) ? ($logo_link['target'] ?? '') : '';

            if ($link_type === 'external') {
                $logo_target = $logo_target ?: '_blank';
            }

            if ($link_type === 'internal' && ! preg_match('#^https?://#i', $logo_url)) {
                $logo_url = home_url($logo_url);
            }
        }

        if ($link_type === 'none') {
            $logo_url = '';
        }
    }

    // First row of the burger menu buttons repeater doubles as the header bar CTA.
    $header_buttons = get_field('header_bottom_buttons', 'option');
    $primary_button = is_array($header_buttons) && ! empty($header_buttons) ? $header_buttons[0] : null;

    $cta_url = '';
    $cta_target = '';
    if ($primary_button) {
        $cta_link = $primary_button['btn_link'] ?? '';
        $cta_link_type = $primary_button['btn_link_type'] ?? '';

        if ($cta_link) {
            $cta_url = is_array($cta_link) ? ($cta_link['url'] ?? '') : $cta_link;
            $cta_target = is_array($cta_link) ? ($cta_link['target'] ?? '') : '';

            if ($cta_link_type === 'external') {
                $cta_target = $cta_target ?: '_blank';
            }

            if ($cta_link_type === 'internal' && ! preg_match('#^https?://#i', $cta_url)) {
                $cta_url = home_url($cta_url);
            }
        }

        if ($cta_link_type === 'none') {
            $cta_url = '';
        }
    }

    $login = get_field('header_login', 'option');
    $login_title = $login['login_title'] ?? __('Login', 'sage');
    $login_url = '';
    $login_target = '';

    if ($login) {
        $login_link = $login['login_url'] ?? '';
        if ($login_link) {
            $login_url = is_array($login_link) ? ($login_link['url'] ?? '') : $login_link;
            $login_target = is_array($login_link) ? ($login_link['target'] ?? '') : '';
        }
    }

    $login_aria_label = $login['login_aria_label'] ?? $login_title;
    $login_ga_label = $login['login_button_google_event_label'] ?? '';
@endphp

<header id="site-header" class="site-header @if (is_front_page()) site-header--home @endif" role="banner">
  <div class="header-inner container-fluid">
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="fullscreen-menu" aria-label="{{ __('Open menu', 'sage') }}">
      <span class="menu-toggle-icon">
        <img src="{{ Vite::asset('resources/images/menu-icon-desktop-white.svg') }}" alt="" class="icon menu-icon-desktop menu-icon-white" aria-hidden="true">
        <img src="{{ Vite::asset('resources/images/menu-icon-desktop-black.svg') }}" alt="" class="icon menu-icon-desktop menu-icon-black" aria-hidden="true">
        <img src="{{ Vite::asset('resources/images/menu-icon-mobile-white.svg') }}" alt="" class="icon menu-icon-mobile menu-icon-white" aria-hidden="true">
        <img src="{{ Vite::asset('resources/images/menu-icon-mobile-black.svg') }}" alt="" class="icon menu-icon-mobile menu-icon-black" aria-hidden="true">
      </span>
    </button>

    <div class="logo-wrapper">
      @if ($logo_url)
        <a
          href="{{ esc_url($logo_url) }}"
          class="brand-logo brand-logo-black"
          aria-label="{{ esc_attr($logo_aria_label) }}"
          @if ($logo_target) target="{{ esc_attr($logo_target) }}" @endif
          @if ($logo_ga_label) data-event="{{ esc_attr($logo_ga_label) }}" @endif
        >
          @if ($header_logo_black)
            <img src="{{ esc_url($header_logo_black['url']) }}" alt="{{ esc_attr($header_logo_black['alt'] ?: $siteName) }}" class="img-fluid">
          @else
            {!! $siteName !!}
          @endif
        </a>

        @if (is_front_page())
          <a
            href="{{ esc_url($logo_url) }}"
            class="brand-logo brand-logo-white logo-link-home"
            aria-label="{{ esc_attr($logo_aria_label) }}"
            aria-hidden="true"
            tabindex="-1"
            @if ($logo_target) target="{{ esc_attr($logo_target) }}" @endif
            @if ($logo_ga_label) data-event="{{ esc_attr($logo_ga_label) }}" @endif
          >
            @if ($header_logo_white)
              <img src="{{ esc_url($header_logo_white['url']) }}" alt="{{ esc_attr($header_logo_white['alt'] ?: $siteName) }}" class="img-fluid">
            @else
              {!! $siteName !!}
            @endif
          </a>

          <a
            href="{{ esc_url($logo_url) }}"
            class="brand-logo brand-logo-transition logo-link-scrolled"
            aria-label="{{ esc_attr($logo_aria_label) }}"
            aria-hidden="true"
            tabindex="-1"
            @if ($logo_target) target="{{ esc_attr($logo_target) }}" @endif
            @if ($logo_ga_label) data-event="{{ esc_attr($logo_ga_label) }}" @endif
          >
            @if ($header_logo_for_transition)
              <img src="{{ esc_url($header_logo_for_transition['url']) }}" alt="{{ esc_attr($header_logo_for_transition['alt'] ?: $siteName) }}" class="img-fluid">
            @else
              {!! $siteName !!}
            @endif
          </a>
        @endif
      @else
        <span class="brand-logo" aria-label="{{ esc_attr($logo_aria_label) }}">
          @if ($header_logo_black)
            <img src="{{ esc_url($header_logo_black['url']) }}" alt="{{ esc_attr($header_logo_black['alt'] ?: $siteName) }}" class="img-fluid">
          @else
            {!! $siteName !!}
          @endif
        </span>
      @endif
    </div>

    <div class="header-actions">
      @if ($login_url)
        <a
          href="{{ esc_url($login_url) }}"
          class="header-login-link"
          aria-label="{{ esc_attr($login_aria_label) }}"
          @if ($login_target) target="{{ esc_attr($login_target) }}" @endif
          @if ($login_ga_label) data-event="{{ esc_attr($login_ga_label) }}" @endif
        >
          {{ $login_title }}
        </a>
      @endif

      @if ($primary_button && $cta_url)
        <a
          href="{{ esc_url($cta_url) }}"
          class="btn header-cta-btn"
          aria-label="{{ esc_attr($primary_button['aria_label'] ?? $primary_button['button_main_title'] ?? '') }}"
          @if ($cta_target) target="{{ esc_attr($cta_target) }}" @endif
          @if (! empty($primary_button['button_google_event_label'])) data-event="{{ esc_attr($primary_button['button_google_event_label']) }}" @endif
        >
          <span>{{ $primary_button['button_main_title'] ?? __('Get Started', 'sage') }}</span>
        </a>
      @endif
    </div>
  </div>

  {{-- fullscreen overlay menu partial --}}
  @include('partials.fullscreen-menu')
</header>
