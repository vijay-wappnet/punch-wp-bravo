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
      <div class="footer-newsletter-inner container">
        <div class="footer-newsletter-copy">
          @if ($newsletter_title)
            <h4 class="footer-newsletter-title footer-newsletter-title-desktop">{{ $newsletter_title }}</h4>
          @endif

          @if ($newsletter_title_mobile)
            <h4 class="footer-newsletter-title footer-newsletter-title-mobile">{{ $newsletter_title_mobile }}</h4>
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
            aria-label="{{ __('Site map', 'sage') }}"
            data-event="{{ __('Site map', 'sage') }}"
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
                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
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
        {!! bravo_link_attrs($copy_right_content) !!}
      </div>
    </div>
  @endif
</footer>




<!-- Accessibility Modal -->
<div class="modal fade accessibility-modal" id="accessibilityModal" tabindex="-1"
    aria-labelledby="accessibilityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title accessibility-title" id="accessibilityModalLabel">Accessibility Display Settings
                </h5>
                <button type="button" class="btn-close custom-close" data-bs-dismiss="modal" aria-label="{{ __('Close accessibility settings', 'sage') }}" data-event="{{ __('Close accessibility settings', 'sage') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contrast Options -->
                <div class="accessibility-option">
                    <label class="setting-label">Contrast</label>
                    <div class="button-group d-flex">
                        <button type="button" class="btn-setting active" data-setting="contrast"
                            data-value="default" aria-label="Contrast: Default" data-event="Accessibility - Contrast: Default">Default</button>
                        <button type="button" class="btn-setting" data-setting="contrast"
                            data-value="higher" aria-label="Contrast: Higher" data-event="Accessibility - Contrast: Higher">Higher</button>
                        <button type="button" class="btn-setting" data-setting="contrast"
                            data-value="inverted" aria-label="Contrast: Inverted" data-event="Accessibility - Contrast: Inverted">Inverted</button>
                    </div>
                </div>

                <!-- Text Size Options -->
                <div class="accessibility-option">
                    <label class="setting-label">Text size</label>
                    <div class="button-group d-flex">
                        <button type="button" class="btn-setting active" data-setting="textSize"
                            data-value="default" aria-label="Text size: Default" data-event="Accessibility - Text size: Default">Default</button>
                        <button type="button" class="btn-setting" data-setting="textSize"
                            data-value="medium" aria-label="Text size: Medium" data-event="Accessibility - Text size: Medium">Medium</button>
                        <button type="button" class="btn-setting" data-setting="textSize"
                            data-value="large" aria-label="Text size: Large" data-event="Accessibility - Text size: Large">Large</button>
                    </div>
                </div>

                <!-- Font Options -->
                <div class="accessibility-option">
                    <label class="setting-label">Font (dyslexia)</label>
                    <div class="button-group d-flex">
                        <button type="button" class="btn-setting active" data-setting="font"
                            data-value="default" aria-label="Font (dyslexia): Default" data-event="Accessibility - Font (dyslexia): Default">Default</button>
                        <button type="button" class="btn-setting" data-setting="font"
                            data-value="adapted" aria-label="Font (dyslexia): Adapted" data-event="Accessibility - Font (dyslexia): Adapted">Adapted</button>
                    </div>
                </div>

                <!-- Line Spacing Options -->
                <div class="accessibility-option">
                    <label class="setting-label">Line spacing</label>
                    <div class="button-group d-flex">
                        <button type="button" class="btn-setting active" data-setting="lineSpacing"
                            data-value="default" aria-label="Line spacing: Default" data-event="Accessibility - Line spacing: Default">Default</button>
                        <button type="button" class="btn-setting" data-setting="lineSpacing"
                            data-value="adapted" aria-label="Line spacing: Adapted" data-event="Accessibility - Line spacing: Adapted">Adapted</button>
                    </div>
                </div>

                <!-- Justification Options -->
                <div class="accessibility-option">
                    <label class="setting-label">Justification</label>
                    <div class="button-group d-flex">
                        <button type="button" class="btn-setting active" data-setting="justification"
                            data-value="default" aria-label="Justification: Default" data-event="Accessibility - Justification: Default">Default</button>
                        <button type="button" class="btn-setting" data-setting="justification"
                            data-value="adapted" aria-label="Justification: Adapted" data-event="Accessibility - Justification: Adapted">Adapted</button>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="accessibility-search mt-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path
                                    d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                            </svg>
                        </span>
                        <form class="search-results-form" role="search" method="GET"
                                        action="@php echo esc_url(home_url('/search-results/')); @endphp">
                                                    <input type="search" name="s" class="form-control footer-search-input" placeholder="Search"
                                                        aria-label="Search">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
