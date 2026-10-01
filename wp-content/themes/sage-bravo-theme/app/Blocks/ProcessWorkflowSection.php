<?php

namespace App\Blocks;

class ProcessWorkflowSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    /**
     * Allowed values for content_alignment / content_alignment_mobile.
     */
    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Allowed values for mobile_workflow_position.
     */
    private const MOBILE_POSITIONS = ['top', 'bottom'];

    /**
     * Workflow drawing, in abstract units (the SVG viewBox scales it to any width).
     * Every step is a circle that touches the shared origin point on the left;
     * its line runs from the origin round the bottom of that circle to the
     * right-most point and straight up to the step label. Circle diameters grow
     * by STEP_UNIT, so the lines end STEP_UNIT apart, as in the design.
     */
    private const STEP_UNIT = 100;
    private const ICON_SIZE = 64;
    private const LINE_TOP_RATIO = 1.2;   // line top, as a multiple of the largest circle's radius
    private const LABEL_SPACE = 40;        // room above the lines for the labels
    private const SIDE_PAD_LEFT = 10;
    private const SIDE_PAD_RIGHT = 50;     // half of the last label hangs past the last line
    private const BOTTOM_PAD = 10;

    /**
     * Render the Process Workflow Section block
     *
     * @param array $block The block settings and attributes
     * @param string $content The block content
     * @param bool $is_preview Whether we are in preview mode
     * @param int $post_id The post ID
     * @return void
     */
    public static function render($block, $content = '', $is_preview = false, $post_id = 0)
    {
        // Get field values using ACF
        $workflow_items = get_field('workflow_items');
        $heading_text = trim((string) get_field('heading_text'));
        $heading_level = get_field('heading_level') ?: 'h2';
        $description = get_field('description');
        $button = get_field('button');
        $image_left = (bool) get_field('image_alignment_left_side');
        $content_alignment = get_field('content_alignment') ?: 'left';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'left';
        $mobile_workflow_position = get_field('mobile_workflow_position') ?: 'bottom';
        $section_bg_color = get_field('section_bg_color');
        $section_bg_image = get_field('section_bg_image');
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }
        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'left';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'left';
        }
        if (!in_array($mobile_workflow_position, self::MOBILE_POSITIONS, true)) {
            $mobile_workflow_position = 'bottom';
        }

        // Generate unique block ID
        $blockId = 'pws-' . ($block['id'] ?? uniqid());

        $steps = self::formatSteps($workflow_items);
        $workflow = $steps ? self::buildWorkflow($steps) : null;

        // Generate responsive CSS for margin and padding, plus the workflow geometry
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);
        if ($workflow) {
            $responsiveCss .= self::workflowCss($blockId, $workflow);
        }

        // Inline styles are limited to the background color and image
        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }

        $section_bg_image_url = self::imageUrl($section_bg_image);
        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        // Render the Blade template with data using view helper
        echo view('blocks.process-workflow-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'workflow'                 => $workflow,
            'heading_text'             => $heading_text,
            'heading_level'            => $heading_level,
            'description'              => is_string($description) ? trim($description) : '',
            'button'                   => self::formatButton($button),
            'image_left'               => $image_left,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
            'mobile_workflow_position' => $mobile_workflow_position,
            'section_style'            => $section_styles ? implode('; ', $section_styles) . ';' : '',
            'is_preview'               => $is_preview,
        ]);
    }

    /**
     * Resolve an ACF image value (array, ID or URL) to a URL.
     */
    private static function imageUrl($image): string
    {
        if (is_array($image)) {
            return $image['url'] ?? '';
        }

        if (is_numeric($image)) {
            return wp_get_attachment_url($image) ?: '';
        }

        return is_string($image) ? $image : '';
    }

    /**
     * Normalise an ACF image value to ['url', 'alt', 'width', 'height'].
     * Images without alt text in the media library are treated as decorative (alt="").
     */
    private static function formatImage($image): ?array
    {
        if (is_numeric($image)) {
            $image = acf_get_attachment($image);
        }

        $url = self::imageUrl($image);
        if (!$url) {
            return null;
        }

        return [
            'url'    => $url,
            'alt'    => is_array($image) ? trim($image['alt'] ?? '') : '',
            'width'  => is_array($image) ? ($image['width'] ?? '') : '',
            'height' => is_array($image) ? ($image['height'] ?? '') : '',
        ];
    }

    /**
     * Normalise the workflow_items repeater (order kept), skipping empty rows.
     */
    private static function formatSteps($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $steps = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['step_title'] ?? ''));
            $icon = self::formatImage($row['step_icon'] ?? null);

            if (!$title && !$icon) {
                continue;
            }

            $steps[] = [
                'title' => $title,
                'icon'  => $icon,
            ];
        }

        return $steps;
    }

    /**
     * Normalise the button group; null when it has no link URL.
     * The link's own title is the visible text.
     */
    private static function formatButton($group): ?array
    {
        $link = is_array($group) ? ($group['button_link'] ?? null) : null;
        if (!is_array($link) || empty($link['url'])) {
            return null;
        }

        $title = trim($link['title'] ?? '') ?: __('Learn more', 'sage');
        $target = $link['target'] ?? '';

        // button_class may hold several space-separated classes; without one the
        // button falls back to the standard red CTA
        $extra_classes = array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($group['button_class'] ?? ''))
        ));

        return [
            'url'         => $link['url'],
            'title'       => $title,
            'target'      => $target,
            'rel'         => $target === '_blank' ? 'noopener noreferrer' : '',
            'aria_label'  => trim($group['aria_label'] ?? ''),
            'event_label' => trim($group['button_google_event_label'] ?? ''),
            'class'       => implode(' ', array_merge(['btn', 'pws-btn'], $extra_classes ?: ['btn-red-fusion'])),
            'arrow_span'  => in_array('btn-trans-border-arrow', $extra_classes, true),
        ];
    }

    /**
     * Work out the drawing for any number of steps. All coordinates are in the
     * SVG's own units, with the origin (shared start point) at 0,0.
     */
    private static function buildWorkflow(array $steps): array
    {
        $count = count($steps);
        $unit = self::STEP_UNIT;
        $largestRadius = $count * $unit / 2;
        $lineTop = -self::LINE_TOP_RATIO * $largestRadius;

        $viewX = -self::SIDE_PAD_LEFT;
        $viewY = $lineTop - self::LABEL_SPACE;
        $viewW = $count * $unit + self::SIDE_PAD_LEFT + self::SIDE_PAD_RIGHT;
        $viewH = $largestRadius + self::BOTTOM_PAD - $viewY;

        $pct = fn($value, $size) => round($value / $size * 100, 3);

        foreach ($steps as $i => &$step) {
            $n = $i + 1;
            $radius = $n * $unit / 2;
            $lineX = $n * $unit;

            // Origin -> round the bottom of the circle -> up to the label
            $step['path'] = sprintf('M0 0 A%1$s %1$s 0 0 0 %2$s 0 L%2$s %3$s', $radius, $lineX, $lineTop);
            // The largest circle is also closed over the top, as in the design
            $step['cap'] = $n === $count ? sprintf('M0 0 A%1$s %1$s 0 0 1 %2$s 0', $radius, $lineX) : null;

            $step['label_left'] = $pct($lineX - $viewX, $viewW);
            $step['label_top'] = $pct($lineTop - 8 - $viewY, $viewH);
            // Each icon sits between its own line and the previous one
            $step['icon_left'] = $pct(($n - 0.5) * $unit - $viewX, $viewW);
            $step['icon_top'] = $pct(0 - $viewY, $viewH);
        }
        unset($step);

        return [
            'steps'     => $steps,
            'view_box'  => sprintf('%s %s %s %s', $viewX, $viewY, $viewW, $viewH),
            'ratio'     => round($viewW / $viewH, 4),
            'icon_size' => $pct(self::ICON_SIZE, $viewW),
        ];
    }

    /**
     * Per-block CSS: the drawing's aspect ratio and each step's position.
     * Scoped to the block ID like the margin/padding CSS.
     */
    private static function workflowCss(string $blockId, array $workflow): string
    {
        $css = sprintf(
            '#%1$s .pws__workflow-visual{aspect-ratio:%2$s}#%1$s .pws__step-icon{width:%3$s%%}',
            $blockId,
            $workflow['ratio'],
            $workflow['icon_size']
        );

        foreach ($workflow['steps'] as $i => $step) {
            $css .= sprintf(
                '#%1$s .pws__step-label--%2$d{left:%3$s%%;top:%4$s%%}#%1$s .pws__step-icon--%2$d{left:%5$s%%;top:%6$s%%}',
                $blockId,
                $i + 1,
                $step['label_left'],
                $step['label_top'],
                $step['icon_left'],
                $step['icon_top']
            );
        }

        return $css;
    }
}
