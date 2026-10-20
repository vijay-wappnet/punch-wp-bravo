<?php

/**
 * Theme security hardening.
 *
 * - HTTP security headers (graded by https://securityheaders.com)
 * - Content-Security-Policy listing only the services the site uses
 * - Less information for attackers: no WordPress/Acorn versions, no user
 *   enumeration (REST /wp/v2/users, ?author=N), generic login errors
 * - XML-RPC off (brute-force / pingback amplification)
 *
 * Server-level rules (blocking direct access to theme source files, debug.log,
 * directory listing...) live in .htaccess files - see the theme's .htaccess.
 */

/**
 * Origins the Content-Security-Policy allows, per directive.
 * Add a host here when a new third-party service is introduced.
 */
function bravo_csp_sources(): array
{
    $sources = [
        'default-src' => ["'self'"],
        // 'unsafe-inline': WordPress core and Contact Form 7 print inline
        // <script> config blocks. Google Tag Manager / Analytics are allowed
        // ready for the data-event click tracking.
        'script-src' => ["'self'", "'unsafe-inline'", 'https://www.googletagmanager.com', 'https://*.google-analytics.com'],
        // Inline style attributes (block colours, ACF spacing) + Adobe Fonts
        'style-src' => ["'self'", "'unsafe-inline'", 'https://use.typekit.net', 'https://p.typekit.net'],
        'font-src' => ["'self'", 'data:', 'https://use.typekit.net', 'https://p.typekit.net'],
        // Images: own uploads/assets, data URIs (CSS icons), analytics pixels
        'img-src' => ["'self'", 'data:', 'https://*.google-analytics.com', 'https://www.googletagmanager.com'],
        'connect-src' => ["'self'", 'https://*.google-analytics.com', 'https://*.analytics.google.com', 'https://www.googletagmanager.com'],
        'media-src' => ["'self'"],
        'frame-src' => ["'self'", 'https://www.googletagmanager.com'],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'self'"],
    ];

    // `npm run dev`: assets and live reload come from the Vite dev server,
    // whose URL is written to public/hot. Never present on a built site.
    $hot = get_theme_file_path('public/hot');
    if (is_readable($hot)) {
        $parts = wp_parse_url(trim((string) file_get_contents($hot)));
        if (!empty($parts['host'])) {
            $origin = $parts['host'] . (!empty($parts['port']) ? ':' . $parts['port'] : '');
            foreach (['script-src', 'style-src', 'font-src', 'img-src'] as $directive) {
                $sources[$directive][] = 'http://' . $origin;
            }
            $sources['connect-src'][] = 'http://' . $origin;
            $sources['connect-src'][] = 'ws://' . $origin;
        }
    }

    return apply_filters('bravo_csp_sources', $sources);
}

/**
 * HTTPS check that also works behind a load balancer / CDN (Cloudflare,
 * managed WordPress hosts) where PHP itself receives plain http.
 */
function bravo_is_https(): bool
{
    return is_ssl() || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function bravo_content_security_policy(): string
{
    $parts = [];
    foreach (bravo_csp_sources() as $directive => $values) {
        $parts[] = $directive . ' ' . implode(' ', array_unique($values));
    }

    // Upgrade any stray http:// asset to https:// on a secure site
    if (bravo_is_https()) {
        $parts[] = 'upgrade-insecure-requests';
    }

    return implode('; ', $parts);
}

/**
 * Headers for every response: front end, wp-login.php and wp-admin.
 * The CSP is front-end only - the block editor needs blob:/eval and is
 * already protected by being behind a login.
 */
function bravo_security_headers(bool $with_csp): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: accelerometer=(), autoplay=(self), camera=(), display-capture=(), fullscreen=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');

    // HSTS only over HTTPS (browsers ignore it on http://, and it must never
    // be sent from a site that isn't fully on HTTPS)
    if (bravo_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    if ($with_csp) {
        header('Content-Security-Policy: ' . bravo_content_security_policy());
    }

    // Don't advertise the stack (Acorn/Laravel/PHP versions)
    header_remove('X-Powered-By');
    header_remove('X-Pingback');
}

add_action('send_headers', function () {
    bravo_security_headers(true);
}, 99);

add_action('login_init', function () {
    bravo_security_headers(false);
});

add_action('admin_init', function () {
    bravo_security_headers(false);
});

// Acorn sets X-Powered-By on its own responses after WordPress' headers -
// drop it again right before output starts
add_action('template_redirect', function () {
    header_remove('X-Powered-By');
}, PHP_INT_MAX);

/**
 * Version leaks: generator meta tag, and the WordPress version in core
 * asset URLs (?ver=7.1.3) - replaced with a hash so caches still bust.
 */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

$bravo_strip_wp_version = function ($src) {
    if (is_string($src) && str_contains($src, 'ver=' . get_bloginfo('version'))) {
        $src = add_query_arg('ver', substr(md5(get_bloginfo('version') . wp_salt('nonce')), 0, 8), $src);
    }

    return $src;
};
add_filter('style_loader_src', $bravo_strip_wp_version, 9999);
add_filter('script_loader_src', $bravo_strip_wp_version, 9999);

/**
 * WordPress emoji script: modern browsers render emoji natively. It loads
 * wp-emoji-release.min.js?ver=<WP version> and starts a blob: worker, both of
 * which the CSP would otherwise have to allow.
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

/**
 * XML-RPC: not used by this site; a common brute-force / DDoS amplification
 * target. (The .htaccess also blocks xmlrpc.php at the server.)
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);

    return $headers;
});
remove_action('wp_head', 'rsd_link');

/**
 * User enumeration: logged-out visitors can't list users through the REST
 * API, and /?author=N no longer redirects to /author/<login-name>/.
 */
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }

    return $endpoints;
});

add_action('template_redirect', function () {
    if (!is_user_logged_in() && isset($_GET['author'])) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

// Login errors don't confirm whether a username exists
add_filter('login_errors', function () {
    return __('The username or password is incorrect.', 'sage');
});
