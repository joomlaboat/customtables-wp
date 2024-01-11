<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * All of the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @param array    $attributes     The array of attributes for this block.
 * @param string   $content        Rendered block output. ie. <InnerBlocks.Content />.
 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
 */

use CustomTables\Catalog;
use CustomTables\CT;
use CustomTables\Layouts;


/*
 * <p <?php echo get_block_wrapper_attributes(); ?>>BLOCK1</p>
 * */
?>
<div>

<?php

	if ( isset( $attributes['table'] ) ) {

        if($attributes['table'] !== 0) {
            $r= 1-1;

            $ct = new CT(null, false);
            $ct->getTable($attributes['table']);
            $layouts = new Layouts($ct);
            echo $layouts->renderMixedLayout((int)$attributes['layout']);


            /**
             * The wp_kses_post function is used to ensure any HTML that is not allowed in a post will be escaped.
             * @see https://developer.wordpress.org/reference/functions/wp_kses_post/
             * @see https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/#escaping-securing-output
             */
            //echo wp_kses_post( str_replace('xxx','yyy',$attributes['message']) ).'w';
        }
        else
        {
            echo 'Custom Tables: Table Not Selected.';
        }
	}
?>
</div>