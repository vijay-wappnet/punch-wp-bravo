<?php

namespace App\Blocks;

class MeetTheTeamSection
{
    /**
     * Allowed heading tags for the heading_level field.
     */
    private const HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'];

    private const ALIGNMENTS = ['left', 'center', 'right'];

    /**
     * Render the Meet The Team Section block
     *
     * @param array $block The block settings and attributes
     * @param string $content The block content
     * @param bool $is_preview Whether we are in preview mode
     * @param int $post_id The post ID
     * @return void
     */
    public static function render($block, $content = '', $is_preview = false, $post_id = 0)
    {
        $heading_text = trim((string) get_field('heading_text'));
        $heading_level = get_field('heading_level') ?: 'h2';
        $members = self::formatMembers(get_field('team_members'));
        $content_alignment = get_field('content_alignment') ?: 'center';
        $content_alignment_mobile = get_field('content_alignment_mobile') ?: 'center';
        $section_bg_color = self::formatColor(get_field('section_bg_color'));
        $section_bg_image_url = self::imageUrl(get_field('section_bg_image'));
        $margin = get_field('margin');
        $padding = get_field('padding');

        if (!in_array($heading_level, self::HEADING_LEVELS, true)) {
            $heading_level = 'h2';
        }
        if (!in_array($content_alignment, self::ALIGNMENTS, true)) {
            $content_alignment = 'center';
        }
        if (!in_array($content_alignment_mobile, self::ALIGNMENTS, true)) {
            $content_alignment_mobile = 'center';
        }

        // Nothing to show without a heading or team members
        if (!$heading_text && !$members) {
            if ($is_preview) {
                echo '<p style="padding: 20px; text-align: center;">' . esc_html__('Meet The Team Section: add a heading or team members.', 'sage') . '</p>';
            }
            return;
        }

        $blockId = 'mts-' . ($block['id'] ?? uniqid());
        $responsiveCss = custom_acf_dimensions($margin, $padding, $blockId);

        $section_styles = [];
        if ($section_bg_color) {
            $section_styles[] = 'background-color: ' . $section_bg_color;
        }
        if ($section_bg_image_url) {
            $section_styles[] = 'background-image: url("' . esc_url($section_bg_image_url) . '")';
        }

        echo view('blocks.meet-the-team-section', [
            'blockId'                  => $blockId,
            'responsiveCss'            => $responsiveCss,
            'heading_text'             => $heading_text,
            'heading_level'            => $heading_level,
            'members'                  => $members,
            'content_alignment'        => $content_alignment,
            'content_alignment_mobile' => $content_alignment_mobile,
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
     * Normalise a member image to ['url', 'alt', 'width', 'height'], using the
     * "large" size when it exists. The alt text is the media library's.
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

        if (!is_array($image)) {
            return ['url' => $url, 'alt' => '', 'width' => '', 'height' => ''];
        }

        $sizes = $image['sizes'] ?? [];
        if (!empty($sizes['large'])) {
            return [
                'url'    => $sizes['large'],
                'alt'    => trim($image['alt'] ?? ''),
                'width'  => $sizes['large-width'] ?? '',
                'height' => $sizes['large-height'] ?? '',
            ];
        }

        return [
            'url'    => $url,
            'alt'    => trim($image['alt'] ?? ''),
            'width'  => $image['width'] ?? '',
            'height' => $image['height'] ?? '',
        ];
    }

    /**
     * The team_members repeater; rows with no image, name or job title are skipped.
     */
    private static function formatMembers($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $members = [];
        foreach ($rows as $row) {
            $member = [
                'image'     => self::formatImage($row['member_image'] ?? null),
                'name'      => trim((string) ($row['member_name'] ?? '')),
                'job_title' => trim((string) ($row['member_job_title'] ?? '')),
            ];

            if ($member['image'] || $member['name'] || $member['job_title']) {
                $members[] = $member;
            }
        }

        return $members;
    }

    /**
     * Only a plain colour value is used as the section background colour.
     */
    private static function formatColor($color): string
    {
        $color = trim((string) $color);

        $isHex = (bool) preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color);
        $isRgb = (bool) preg_match('/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color);

        return ($isHex || $isRgb) ? $color : '';
    }
}
