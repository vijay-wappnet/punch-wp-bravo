@php
    use Illuminate\Support\Facades\Vite;

    $siteName = get_bloginfo('name');

    $footer_logo = get_field('footer_logo', 'option');
    $footer_logo_link_group = get_field('footer_logo_link', 'option');

    $footer_logo_url = '';
    $footer_logo_target = '';
    $footer_logo_aria_label = $siteName;
    $footer_logo_ga_label = '';

    if ($footer_logo_link_group) {
        $link_type = $footer_logo_link_group['link_type'] ?? '';
        $logo_link = $footer_logo_link_group['logo_link'] ?? '';
        $footer_logo_aria_label = $footer_logo_link_group['aria_label'] ?: $footer_logo_aria_label;
        $footer_logo_ga_label = $footer_logo_link_group['button_google_event_label'] ?? '';

        if ($logo_link) {
            $footer_logo_url = is_array($logo_link) ? ($logo_link['url'] ?? '') : $logo_link;
            $footer_logo_target = is_array($logo_link) ? ($logo_link['target'] ?? '') : '';

            if ($link_type === 'external') {
                $footer_logo_target = $footer_logo_target ?: '_blank';
            }

            if ($link_type === 'internal' && ! preg_match('#^https?://#i', $footer_logo_url)) {
                $footer_logo_url = home_url($footer_logo_url);
            }
        }

        if ($link_type === 'none') {
            $footer_logo_url = '';
        }
    }

    $contact_details = get_field('contact_details', 'option') ?: [];
    $footer_banner_image = get_field('footer_banner_image', 'option');

    $newsletter = get_field('newsletter_section', 'option') ?: [];
    $newsletter_title = $newsletter['newsletter_title'] ?? '';
    $newsletter_title_mobile = $newsletter['newsletter_title_mobile'] ?? '';
    $newsletter_description = $newsletter['newsletter_description'] ?? '';
    $newsletter_shortcode = $newsletter['newsletter_contact_form_shortcode'] ?? '';

    $copy_right = get_field('copy_right_section', 'option') ?: [];
    $copy_right_content = $copy_right['copy_right_content'] ?? '';

    $has_footer_menu = has_nav_menu('footer_main_navigation_one');
@endphp

<footer id="site-footer" class="site-footer" role="contentinfo">
  @if ($newsletter_title || $newsletter_title_mobile || $newsletter_shortcode)
    <div class="footer-newsletter">
      <div class="footer-newsletter-inner">
        <div class="footer-newsletter-copy">
          @if ($newsletter_title)
            <h2 class="footer-newsletter-title footer-newsletter-title-desktop">{{ $newsletter_title }}</h2>
          @endif

          @if ($newsletter_title_mobile)
            <h2 class="footer-newsletter-title footer-newsletter-title-mobile">{{ $newsletter_title_mobile }}</h2>
          @endif

          @if ($newsletter_description)
            <p class="footer-newsletter-description">{{ $newsletter_description }}</p>
          @endif
        </div>

        @if ($newsletter_shortcode)
          <div class="footer-newsletter-form">
            {!! do_shortcode($newsletter_shortcode) !!}
          </div>
        @endif
      </div>
    </div>
  @endif

  <div class="footer-main">
    <div class="footer-main-inner">
      <div class="footer-branding">
        <div class="footer-logo-wrapper">
          @if ($footer_logo_url)
            <a
              href="{{ esc_url($footer_logo_url) }}"
              class="footer-logo"
              aria-label="{{ esc_attr($footer_logo_aria_label) }}"
              @if ($footer_logo_target) target="{{ esc_attr($footer_logo_target) }}" @endif
              @if ($footer_logo_ga_label) data-event="{{ esc_attr($footer_logo_ga_label) }}" @endif
            >
              @if ($footer_logo)
                <img src="{{ esc_url($footer_logo['url']) }}" alt="{{ esc_attr($footer_logo['alt'] ?: $siteName) }}">
              @else
                {!! $siteName !!}
              @endif
            </a>
          @else
            <span class="footer-logo" aria-label="{{ esc_attr($footer_logo_aria_label) }}">
              @if ($footer_logo)
                <img src="{{ esc_url($footer_logo['url']) }}" alt="{{ esc_attr($footer_logo['alt'] ?: $siteName) }}">
              @else
                {!! $siteName !!}
              @endif
            </span>
          @endif
        </div>

        @if (! empty($contact_details))
          <ul class="footer-contact-list">
            @foreach ($contact_details as $contact)
              @php
                $contact_link = $contact['link'] ?? '';
                $contact_url = is_array($contact_link) ? ($contact_link['url'] ?? '') : $contact_link;
                $contact_target = is_array($contact_link) ? ($contact_link['target'] ?? '') : '';

                if (! empty($contact['link_target_new_tab'])) {
                    $contact_target = '_blank';
                }

                $contact_icon = $contact['icon'] ?? null;
                $contact_title = $contact['title'] ?? '';
                $contact_aria_label = $contact['aria_label'] ?: $contact_title;
                $contact_ga_label = $contact['data_event_label'] ?? '';
              @endphp

              @if ($contact_url)
                <li class="footer-contact-item">
                  <a
                    href="{{ esc_url($contact_url) }}"
                    class="footer-contact-link"
                    aria-label="{{ esc_attr($contact_aria_label) }}"
                    @if ($contact_target) target="{{ esc_attr($contact_target) }}" @endif
                    @if ($contact_ga_label) data-event="{{ esc_attr($contact_ga_label) }}" @endif
                  >
                    @if ($contact_icon)
                      <img src="{{ esc_url($contact_icon['url']) }}" alt="" class="footer-contact-icon" aria-hidden="true">
                    @endif
                    @if ($contact_title)
                      <span class="footer-contact-title">{{ $contact_title }}</span>
                    @endif
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        @endif
      </div>

      @if ($has_footer_menu)
        <div class="footer-nav-wrapper">
          <button
            type="button"
            class="footer-nav-toggle"
            aria-expanded="false"
            aria-controls="footer-nav-menu"
          >
            <span class="footer-nav-toggle-label">{{ __('Site map', 'sage') }}</span>
            <img src="{{ Vite::asset('resources/images/arrow-down.svg') }}" alt="" class="footer-nav-toggle-icon" aria-hidden="true">
          </button>

          <nav
            id="footer-nav-menu"
            class="footer-navigation"
            role="navigation"
            aria-label="{{ wp_get_nav_menu_name('footer_main_navigation_one') }}"
          >
            {!! wp_nav_menu([
                'theme_location' => 'footer_main_navigation_one',
                'menu_class' => 'footer-menu-list',
                'echo' => false,
                'depth' => 1,
                'container' => false,
                'walker' => new \App\FooterBottomMenuWalker(),
                'items_wrap' => '<ul id="%1$s" class="%2$s" role="menu">%3$s</ul>',
            ]) !!}
          </nav>
        </div>
      @endif
    </div>

    @if ($footer_banner_image)
      <div class="footer-banner" aria-hidden="true">
        <img src="{{ esc_url($footer_banner_image['url']) }}" alt="" class="footer-banner-image">
      </div>
    @endif
  </div>

  @if ($copy_right_content)
    <div class="footer-bottom">
      <div class="footer-bottom-inner">
        {!! $copy_right_content !!}
      </div>
    </div>
  @endif
</footer>
