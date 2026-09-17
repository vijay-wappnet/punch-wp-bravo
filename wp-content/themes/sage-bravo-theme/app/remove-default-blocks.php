<?php
/*
* Remove default blocks.
*
* @package Sage\AngelathettonTheme
*/
add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {

    $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

    $acf_blocks = [];

    foreach ($registered_blocks as $block_name => $block) {
        if (strpos($block_name, 'acf/') === 0) {
            $acf_blocks[] = $block_name;
        }
    }

    return $acf_blocks;

}, 10, 2);
?>
