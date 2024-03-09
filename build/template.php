<?php
/**
 * Plugin Name:       CustomTables
 * Plugin URI:        https://ct4.us/
 * GitHub:            https://github.com/joomlaboat/customtables-wp
 * Author:            Ivan Komlev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * All the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @param array $attributes The array of attributes for this block.
 * @param string $content Rendered block output. ie. <InnerBlocks.Content />.
 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use CustomTables\common;
use CustomTables\CT;
use CustomTables\Layouts;

function enqueue_frontend_scripts()
{
	global $CUSTOM_TABLES_ENQUEUE;

	wp_enqueue_script('ct-edit-form-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/edit.js', array(), '1.1.6', true);
	wp_enqueue_script('ct-catalog-script', CUSTOMTABLES_MEDIA_WEBPATH . 'js/catalog.js', array(), '1.1.6', true);

	wp_enqueue_style('ct-catalog-style', CUSTOMTABLES_MEDIA_WEBPATH . 'css/style.css', array(), '1.1.6', true);

	// Add inline script after enqueuing the main script
	wp_add_inline_script('ct-edit-form-script', 'let ctWebsiteRoot = "' . esc_url(home_url()) . '";');

	// Add inline script after enqueuing the main script
	if (isset($CUSTOM_TABLES_ENQUEUE['style']) and $CUSTOM_TABLES_ENQUEUE['style'] !== null)
		wp_add_inline_style('ct-catalog-style', $CUSTOM_TABLES_ENQUEUE['style']);

	// Add inline script after enqueuing the main script
	if (isset($CUSTOM_TABLES_ENQUEUE['script']) and $CUSTOM_TABLES_ENQUEUE['script'] !== null)
		wp_add_inline_script('ct-edit-form-script', $CUSTOM_TABLES_ENQUEUE['script']);

	if (isset($CUSTOM_TABLES_ENQUEUE['recaptcha']) and $CUSTOM_TABLES_ENQUEUE['recaptcha'] !== null)
	    wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js' );
}


/*
 * <p <?php echo get_block_wrapper_attributes(); ?>>BLOCK1</p>
 * */
?>
<div>

	<?php

	if (isset($attributes['table'])) {

		if ($attributes['table'] !== 0) {

			$mixedLayout_array = [];
			$mixedLayout_safe = '';
			$ct = new CT(null, false);

			try {
				$ct->getTable($attributes['table']);
				if ($ct->Table->tablename !== null) {
					$layoutType = null;
					$view = common::inputGetCmd('view' . $ct->Table->tableid);
					if ($view == 'edititem') {
						$layoutType = 2;
						$layoutId = 0;
					} else {
						$layoutType = $attributes['type'];

						if ((int)$layoutType == 1)
							$layoutId = (int)$attributes['cataloglayout'];
                        elseif ((int)$layoutType == 2)
							$layoutId = (int)$attributes['editlayout'];
                        elseif ((int)$layoutType == 4)
							$layoutId = (int)$attributes['detailslayout'];
						else
							$layoutId = 0;
					}

					$layouts = new Layouts($ct);
					$mixedLayout_array = $layouts->renderMixedLayout($layoutId, $layoutType);
					$mixedLayout_safe = $mixedLayout_array['html'];
				} else {
					$mixedLayout_safe = 'Table "' . $attributes['table'] . '" not found.';
				}
			} catch (Exception $e) {
				$mixedLayout_safe = 'Error: ' . $e->getMessage();
			}

			$message = get_transient('plugin_error_message');
			if ($message) {
				echo '<blockquote style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 10px;"><p>' . esc_html($message) . '</p></blockquote>';
				// Once displayed, clear the transient
				delete_transient('plugin_error_message');
			}

			$success_message = get_transient('plugin_success_message');
			if (!empty($success_message)) {
				echo '<blockquote style="background-color: #d4edda;border-left: 5px solid #28a745;padding: 10px;"><p>' . esc_html($success_message) . '</p></blockquote>';
				// Optionally, you can delete the transient after displaying it
				delete_transient('plugin_success_message');
			}

			if (!is_admin()) {
				global $CUSTOM_TABLES_ENQUEUE;

				if (isset($mixedLayout_array['style']) and $mixedLayout_array['style'] !== null)
					$CUSTOM_TABLES_ENQUEUE['style'] = $mixedLayout_array['style'];

				if (isset($mixedLayout_array['script']) and $mixedLayout_array['script'] !== null)
					$CUSTOM_TABLES_ENQUEUE['script'] = $mixedLayout_array['script'];

				if (isset($mixedLayout_array['captcha']) and $mixedLayout_array['captcha'] !== null)
					$CUSTOM_TABLES_ENQUEUE['recaptcha'] = $mixedLayout_array['captcha'];

				add_action('wp_enqueue_scripts', 'enqueue_frontend_scripts');
			}
			echo $mixedLayout_safe;

		} else {
			echo 'Custom Tables: Table Not Selected.';
		}
	}
	?>
</div>