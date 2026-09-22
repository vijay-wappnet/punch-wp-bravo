@php
    use Illuminate\Support\Facades\Vite;

    $header_logo_black = get_field('header_logo', 'option');
    $siteName = get_bloginfo('name');

    $header_buttons = get_field('header_bottom_buttons', 'option');
    $primary_button = is_array($header_buttons) && ! empty($header_buttons) ? $header_buttons[0] : null;

    $btn_url = '';
    $btn_target = '';

    if ($primary_button) {
        $btn_link = $primary_button['btn_link'] ?? '';
        $btn_link_type = $primary_button['btn_link_type'] ?? '';

        if ($btn_link) {
            $btn_url = is_array($btn_link) ? ($btn_link['url'] ?? '') : $btn_link;
            $btn_target = is_array($btn_link) ? ($btn_link['target'] ?? '') : '';

            if ($btn_link_type === 'external') {
                $btn_target = $btn_target ?: '_blank';
            }

            if ($btn_link_type === 'internal' && ! preg_match('#^https?://#i', $btn_url)) {
                $btn_url = home_url($btn_url);
            }
        }

        if ($btn_link_type === 'none') {
            $btn_url = '';
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
@endphp

<div id="fullscreen-menu" class="fullscreen-menu" aria-hidden="true">
  <div class="fullscreen-menu-body">
    @if (has_nav_menu('primary_navigation'))
      <nav class="fullscreen-nav" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}" role="navigation">
        <div class="menu-left">
          {{-- Centers the nav block within the half-column at very wide/zoomed-out
               viewports, instead of the fixed side padding leaving it stranded
               near the left edge with a large empty gap. .menu-left itself keeps
               the 50% split with .menu-right untouched. --}}
          <div class="menu-left-inner">
            <div class="menu-header">
              <div class="menu-header-logo">
                @if ($header_logo_black)
                  <img src="{{ esc_url($header_logo_black['url']) }}" alt="{{ esc_attr($header_logo_black['alt'] ?: $siteName) }}" class="img-fluid">
                @else
                  {!! $siteName !!}
                @endif
              </div>

              <button class="close-btn" type="button" aria-label="{{ __('Close menu', 'sage') }}" aria-controls="fullscreen-menu">
                <img src="{{ Vite::asset('resources/images/Menu-close-icon.svg') }}" alt="" class="close-icon" aria-hidden="true">
                <span class="close-label">{{ __('Close', 'sage') }}</span>
              </button>
            </div>

            {!! wp_nav_menu([
                'theme_location' => 'primary_navigation',
                'menu_class' => 'fullscreen-menu-list',
                'echo' => false,
                'depth' => 1,
                'container' => false,
                'walker' => new \App\MenuWalker(),
                'items_wrap' => '<ul id="%1$s" class="%2$s" role="menu">%3$s</ul>',
            ]) !!}

            @if ($login_url)
              <div class="fullscreen-menu-login-item">
                <a
                  href="{{ esc_url($login_url) }}"
                  class="fullscreen-menu-link fullscreen-menu-login"
                  aria-label="{{ esc_attr($login['login_aria_label'] ?? $login_title) }}"
                  @if ($login_target) target="{{ esc_attr($login_target) }}" @endif
                  @if (! empty($login['login_button_google_event_label'])) data-event="{{ esc_attr($login['login_button_google_event_label']) }}" @endif
                >
                  {{ $login_title }}
                </a>
              </div>
            @endif
          </div>
        </div>

        <div class="menu-right js-dot-grid">
          <canvas class="dot-grid-canvas" aria-hidden="true"></canvas>

          @if ($primary_button)
            <div class="menu-right-content">
              @if (! empty($primary_button['button_top_title']))
                <span class="menu-right-label">{{ $primary_button['button_top_title'] }}</span>
              @endif

              @if ($btn_url)
                <a
                  href="{{ esc_url($btn_url) }}"
                  class="btn menu-right-btn"
                  aria-label="{{ esc_attr($primary_button['aria_label'] ?? $primary_button['button_title'] ?? '') }}"
                  @if ($btn_target) target="{{ esc_attr($btn_target) }}" @endif
                  @if (! empty($primary_button['button_google_event_label'])) data-event="{{ esc_attr($primary_button['button_google_event_label']) }}" @endif
                >
                  <span>{{ $primary_button['button_title'] ?? '' }}</span>
                </a>
              @endif
            </div>
          @endif
        </div>
      </nav>
    @endif
  </div>

  @if ($primary_button && $btn_url)
    <div class="menu-footer">
      <a
        href="{{ esc_url($btn_url) }}"
        class="btn menu-footer-btn"
        aria-label="{{ esc_attr($primary_button['aria_label'] ?? $primary_button['button_title'] ?? '') }}"
        @if ($btn_target) target="{{ esc_attr($btn_target) }}" @endif
        @if (! empty($primary_button['button_google_event_label'])) data-event="{{ esc_attr($primary_button['button_google_event_label']) }}" @endif
      >
        {{ $primary_button['button_top_title'] ?? $primary_button['button_title'] ?? '' }}
      </a>
    </div>
  @endif
</div>
