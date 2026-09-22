<?php

namespace App;

use Walker_Nav_Menu;

/**
 * Custom nav menu walker for the footer navigation (footer_main_navigation_one).
 * Single level list — no submenus in this design.
 */
class FooterBottomMenuWalker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';

        $atts = [
            'href' => ! empty($item->url) ? $item->url : '#',
            'class' => 'footer-menu-link',
            'data-event' => $item->title,
            'aria-label' => $item->title,
        ];

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (! empty($value)) {
                $value = ($attr === 'href') ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);

        $item_output = $args->before ?? '';
        $item_output .= '<a role="menuitem"' . $attributes . '>' . $title . '</a>';
        $item_output .= $args->after ?? '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}
