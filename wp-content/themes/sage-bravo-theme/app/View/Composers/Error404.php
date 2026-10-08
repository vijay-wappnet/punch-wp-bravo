<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Error404 extends Composer
{
    /**
     * Allowed heading tags for the heading level fields.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '404',
    ];

    /**
     * Data to be passed to the view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'error_heading'            => $this->text('404_page_heading', '404 error'),
            'error_heading_level'      => $this->level('404_heading_level', 'p'),
            'error_main_heading'       => $this->text('404_page_main_heading', 'Oops! Something is broken'),
            'error_main_heading_level' => $this->level('404_main_heading_level', 'h1'),
            'error_description'        => $this->description(),
            'error_button'             => $this->button(),
        ];
    }

    /**
     * Read an ACF option. The default is used only while the field has never
     * been saved, so clearing the field in the admin hides the element.
     */
    private function text(string $name, string $default): string
    {
        $value = function_exists('get_field') ? get_field($name, 'option') : null;

        return is_string($value) ? trim($value) : __($default, 'sage');
    }

    private function level(string $name, string $default): string
    {
        $value = function_exists('get_field') ? get_field($name, 'option') : null;

        return in_array($value, self::HEADING_LEVELS, true) ? $value : $default;
    }

    private function description(): string
    {
        $value = function_exists('get_field') ? get_field('404_page_description', 'option') : null;

        return is_string($value)
            ? trim($value)
            : __('The page you were looking for has either been moved or is no longer accessible.', 'sage');
    }

    /**
     * The 404_button group (button_link, aria_label, button_google_event_label).
     * Falls back to a "Homepage" button until the group has been saved.
     */
    private function button(): ?array
    {
        $group = function_exists('get_field') ? get_field('404_button', 'option') : null;

        if (!is_array($group) || !array_filter($group)) {
            $group = ['button_link' => ['url' => home_url('/'), 'title' => __('Homepage', 'sage'), 'target' => '']];
        }

        $link = $group['button_link'] ?? null;
        if (!is_array($link) || empty($link['url'])) {
            return null;
        }

        $title = trim($link['title'] ?? '');
        $target = $link['target'] ?? '';

        return [
            'url'         => $link['url'],
            'title'       => $title,
            'target'      => $target,
            'rel'         => $target === '_blank' ? 'noopener noreferrer' : '',
            'aria_label'  => trim($group['aria_label'] ?? '') ?: $title,
            'event_label' => trim($group['button_google_event_label'] ?? ''),
        ];
    }
}
